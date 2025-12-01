# N+1 Query Refactoring Context

**Tujuan:** Mengurangi query berulang (N+1 queries) di seluruh aplikasi melalui Eager Loading dan In-Memory Caching.

## Status Masalah

Aplikasi mengalami **N+1 query problem** yang serius di berbagai halaman/Livewire components. Contoh:
- **RankingPsyMapping**: 287 queries (270 duplicates)
- Query yang sama dijalankan berkali-kali dalam satu request
- Hampir semua halaman/Livewire memiliki masalah serupa

## Root Cause

### 1. Query Berulang di Service Layer
**File bermasalah utama:**
- `app/Services/DynamicStandardService.php` - Lines 115, 1183
- `app/Services/RankingService.php` - Line 64
- Service lain yang belum diidentifikasi

**Pattern masalah:**
```php
// ❌ BAD: Query dalam loop
foreach ($participants as $participant) {
    $aspect = Aspect::where('template_id', $templateId)
        ->where('code', $aspectCode)
        ->first(); // Query berulang untuk setiap participant!
}
```

### 2. Tidak Ada Eager Loading
Model relationships tidak dimuat sebelumnya, menyebabkan lazy loading query di dalam loop.

### 3. Data Statis Tidak Di-cache
Data yang jarang berubah (aspects, sub_aspects, templates) di-query berulang kali.

---

## Strategi Solusi

### **1. Eager Loading (Wajib)**
Load semua relasi yang diperlukan di awal query.

**Contoh:**
```php
// ✅ GOOD: Eager load semua relasi
$assessments = AspectAssessment::query()
    ->with([
        'aspect.subAspects',           // Load aspects + sub-aspects
        'subAspectAssessments.subAspect', // Load sub-aspect assessments
        'participant.positionFormation'   // Load participant data
    ])
    ->where('event_id', $eventId)
    ->get();
```

### **2. In-Memory Caching untuk Data Statis**
Cache data yang tidak berubah selama request lifecycle.

**Data yang cocok di-cache:**
- `aspects` by template_id + code
- `sub_aspects` by aspect_id
- `assessment_templates`
- `category_types`

**Implementasi:**
```php
// ✅ GOOD: In-memory cache untuk request lifecycle
class AspectCacheService
{
    private static array $aspectCache = [];

    public static function getAspect(int $templateId, string $code): ?Aspect
    {
        $key = "{$templateId}:{$code}";

        if (!isset(self::$aspectCache[$key])) {
            self::$aspectCache[$key] = Aspect::where('template_id', $templateId)
                ->where('code', $code)
                ->first();
        }

        return self::$aspectCache[$key];
    }

    public static function clearCache(): void
    {
        self::$aspectCache = [];
    }
}
```

### **3. Batch Loading**
Load data dalam batch, bukan satu per satu.

**Contoh:**
```php
// ❌ BAD: Query dalam loop
foreach ($codes as $code) {
    $aspect = Aspect::where('code', $code)->first();
}

// ✅ GOOD: Batch load
$aspects = Aspect::whereIn('code', $codes)->get()->keyBy('code');
foreach ($codes as $code) {
    $aspect = $aspects[$code] ?? null;
}
```

---

## Service Architecture

### Current Services (dari `app/Services/`)
```
app/Services/
├── Assessment/
│   ├── AssessmentCalculationService.php
│   ├── AspectService.php
│   ├── CategoryService.php
│   ├── FinalAssessmentService.php
│   └── SubAspectService.php
├── ConclusionService.php
├── CustomStandardService.php
├── DynamicStandardService.php     ⚠️ MASALAH UTAMA (270+ duplicate queries)
├── IndividualAssessmentService.php
├── InterpretationGeneratorService.php
├── InterpretationTemplateService.php
├── RankingService.php             ⚠️ MASALAH (N+1 queries)
├── StatisticService.php
└── TrainingRecommendationService.php
```

### Common Patterns

**DynamicStandardService:**
- Mengambil `aspects` by `template_id` + `code` berkali-kali
- Method: `getAspectRating()`, `getSubAspectRating()`, `isAspectActive()`
- **Solusi:** Cache in-memory atau batch load aspects

**RankingService:**
- Mengambil `aspect_assessments` dengan relasi
- Recalculate scores dalam loop
- **Solusi:** Eager load semua relasi sekaligus

---

## Model Relationships (Reference)

### Aspect Model
```php
class Aspect extends Model
{
    public function template() { return $this->belongsTo(AssessmentTemplate::class); }
    public function categoryType() { return $this->belongsTo(CategoryType::class); }
    public function subAspects() { return $this->hasMany(SubAspect::class); }
    public function aspectAssessments() { return $this->hasMany(AspectAssessment::class); }
}
```

### AspectAssessment Model
```php
class AspectAssessment extends Model
{
    public function aspect() { return $this->belongsTo(Aspect::class); }
    public function participant() { return $this->belongsTo(Participant::class); }
    public function subAspectAssessments() { return $this->hasMany(SubAspectAssessment::class); }
}
```

### SubAspect Model
```php
class SubAspect extends Model
{
    public function aspect() { return $this->belongsTo(Aspect::class); }
    public function subAspectAssessments() { return $this->hasMany(SubAspectAssessment::class); }
}
```

---

## Refactoring Workflow

### Step 1: Identifikasi Masalah
**User memberikan:**
- Laravel Debugbar query log
- Nama halaman/Livewire component yang bermasalah
- Screenshot jika perlu

**Claude akan:**
- Analisis query log untuk menemukan duplicate queries
- Identifikasi service/method yang menjadi root cause
- Buat daftar query yang perlu dioptimasi

### Step 2: Refactor Service Layer
**Priority:**
1. Fix DynamicStandardService (paling banyak duplicate)
2. Fix RankingService
3. Fix service lain yang teridentifikasi

**Checklist per service:**
- [ ] Tambahkan eager loading di query utama
- [ ] Implementasi in-memory cache untuk data statis
- [ ] Ganti loop query dengan batch loading
- [ ] Test dengan minimal 1 halaman yang menggunakan service

### Step 3: Update Livewire Components
**Pastikan:**
- Livewire component memanggil service yang sudah dioptimasi
- Clear cache saat ada perubahan data (jika pakai in-memory cache)
- Property caching di Livewire tetap berfungsi

### Step 4: Validation
**User melakukan:**
1. Test halaman yang sudah direfactor
2. Cek Laravel Debugbar untuk verifikasi query berkurang
3. Report hasil (berapa query sebelum vs sesudah)

---

## Testing Checklist

Setelah refactoring service, test halaman-halaman berikut:

### Ranking Pages (Priority High)
- [ ] `/general-report/ranking/psy-mapping` - RankingPsyMapping
- [ ] `/general-report/ranking/mc-mapping` - RankingMcMapping
- [ ] `/general-report/ranking/general-mapping` - RankingGeneralMapping

### General Report Pages
- [ ] `/general-report/psy-mapping` - GeneralPsyMapping
- [ ] `/general-report/mc-mapping` - GeneralMcMapping
- [ ] `/general-report/general-mapping` - GeneralMapping

### Individual Report Pages
- [ ] `/individual-report/*` - IndividualAssessmentService dependent pages

### Other Pages
- [ ] Halaman lain yang menggunakan service yang direfactor

---

## Expected Results

### Before Refactoring
```
RankingPsyMapping: 287 queries (270 duplicates)
- select * from aspects where template_id = 2 and code = 'kecerdasan' limit 1 (x68)
- select * from aspects where template_id = 2 and code = 'cara_kerja' limit 1 (x68)
- select * from aspects where template_id = 2 and code = 'hubungan_sosial' limit 1 (x68)
- select * from aspects where template_id = 2 and code = 'kepribadian' limit 1 (x68)
```

### After Refactoring (Target)
```
RankingPsyMapping: 10-20 queries (0-5 duplicates)
- select * from aspect_assessments with eager loading (1 query)
- select * from aspects where id in (...) (1 query)
- select * from sub_aspects where aspect_id in (...) (1 query)
- select * from participants where id in (...) (1 query)
```

**Target reduction: 90-95% fewer queries**

---

## Important Notes

### 🚨 Jangan Hapus/Ubah Logic
- **Hanya optimalkan query, JANGAN ubah business logic**
- Hasil kalkulasi harus tetap sama
- Test existing tests untuk memastikan tidak ada regression

### ✅ Maintain Compatibility
- Service harus tetap backward compatible
- Livewire components yang sudah ada tidak perlu perubahan besar
- Session-based adjustments (DynamicStandardService) harus tetap berfungsi

### 📊 Monitor Performance
- Check query count before & after
- Check response time (jika bisa)
- Verify hasil perhitungan tetap akurat

---

## Example: DynamicStandardService Refactoring

### Before (Current - PROBLEMATIC)
```php
public function getAspectRating(int $templateId, string $aspectCode): float
{
    // ❌ Query in loop - called 68 times per request!
    $aspect = Aspect::where('template_id', $templateId)
        ->where('code', $aspectCode)
        ->first();

    return $aspect ? $aspect->standard_rating : 0.0;
}
```

### After (Optimized)
```php
// New AspectCacheService
class AspectCacheService
{
    private static array $cache = [];

    public static function getByTemplateAndCode(int $templateId, string $code): ?Aspect
    {
        $key = "{$templateId}:{$code}";

        if (!isset(self::$cache[$key])) {
            self::$cache[$key] = Aspect::where('template_id', $templateId)
                ->where('code', $code)
                ->with('subAspects')
                ->first();
        }

        return self::$cache[$key];
    }

    public static function preloadByTemplate(int $templateId): void
    {
        $aspects = Aspect::where('template_id', $templateId)
            ->with('subAspects')
            ->get();

        foreach ($aspects as $aspect) {
            $key = "{$templateId}:{$aspect->code}";
            self::$cache[$key] = $aspect;
        }
    }
}

// Updated DynamicStandardService
public function getAspectRating(int $templateId, string $aspectCode): float
{
    // ✅ Use cache - only 1 query for all aspects!
    $aspect = AspectCacheService::getByTemplateAndCode($templateId, $aspectCode);

    return $aspect ? $aspect->standard_rating : 0.0;
}
```

---

## File Structure untuk Refactoring

### New Files to Create
```
app/Services/Cache/
└── AspectCacheService.php     (in-memory cache untuk Aspect queries)
```

### Files to Refactor
```
app/Services/
├── DynamicStandardService.php  (Priority 1 - highest impact)
├── RankingService.php          (Priority 2)
├── IndividualAssessmentService.php (Priority 3)
└── [Other services berdasarkan debugbar log]

app/Livewire/Pages/GeneralReport/Ranking/
├── RankingPsyMapping.php
├── RankingMcMapping.php
└── RankingGeneralMapping.php
```

---

## Quick Reference Commands

### Check Database Queries
```bash
# Enable query logging di tinker
DB::enableQueryLog();
// ... run your code
dd(DB::getQueryLog());
```

### Clear Laravel Cache (if needed)
```bash
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

### Run Tests
```bash
php artisan test --filter=RankingServiceTest
```

---

## Komunikasi dengan Claude di Sesi Baru

### Format Request
```
Saya ingin refactor N+1 queries untuk halaman [NAMA_HALAMAN].

Dari Laravel Debugbar saya lihat:
- Total queries: [JUMLAH]
- Duplicate queries: [JUMLAH]
- Query yang paling sering muncul:
  [PASTE QUERY LOG DARI DEBUGBAR]

File Livewire: [PATH]
Service yang dipakai: [NAMA SERVICE jika tahu]

Tolong analisis dan refactor service layer yang bermasalah.
```

### Claude akan:
1. ✅ Read file Livewire component yang disebutkan
2. ✅ Identifikasi service yang dipakai
3. ✅ Read service file untuk analisis
4. ✅ Buat atau update AspectCacheService jika belum ada
5. ✅ Refactor service dengan eager loading + caching
6. ✅ Update Livewire component jika perlu
7. ✅ Berikan summary perubahan dan testing guide

### Jika Butuh Info Lebih
Claude bisa minta:
- "Tolong read file [PATH_SERVICE_LAIN] untuk saya analisis"
- "Bisa kasih debugbar log untuk halaman [NAMA] juga?"
- "Apakah ada service lain yang pakai method ini?"

---

## Known Services & Their Patterns

| Service | Common Query Pattern | Solution Strategy |
|---------|---------------------|-------------------|
| DynamicStandardService | `Aspect::where('template_id', X)->where('code', Y)->first()` (in loop) | In-memory cache |
| RankingService | `AspectAssessment::with(...)` + loop calculations | Eager loading + batch load |
| IndividualAssessmentService | Multiple relationship loads | Eager loading |
| StatisticService | Aggregate queries | Query builder optimization |
| InterpretationGeneratorService | Template lookups | Cache templates |

---

## Frequently Asked Questions

### Q: Apakah perlu refactor semua service sekaligus?
**A:** Tidak. Prioritaskan berdasarkan:
1. Service yang paling banyak duplicate queries (lihat debugbar)
2. Halaman yang paling sering dipakai user
3. Service yang dipakai di banyak halaman

### Q: Apakah in-memory cache aman?
**A:** Ya, untuk request lifecycle. Cache akan clear otomatis setelah request selesai. Jika perlu manual clear, panggil `AspectCacheService::clearCache()`.

### Q: Bagaimana jika ada perubahan data di tengah request?
**A:** Untuk data yang bisa berubah (participant scores, dll), JANGAN cache. Hanya cache data master yang statis (aspects, templates, category_types).

### Q: Apakah eager loading selalu lebih baik?
**A:** Ya untuk relationship yang PASTI dipakai. Jangan eager load data yang tidak selalu dipakai.

---

**Last Updated:** 2025-12-01
**Next Review:** Setelah refactoring 3 service utama
