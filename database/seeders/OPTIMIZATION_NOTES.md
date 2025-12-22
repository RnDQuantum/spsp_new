# DynamicAssessmentSeeder Optimization Notes

## Tanggal Terakhir Update: 2025-12-22

## ⚡ OPTIMASI PHASE 3: Bulk Insert Total (FINAL)

### Masalah Sebelum Optimasi Phase 3
- **Progressive Slowdown**: Kecepatan seeding menurun progresif dalam satu event (15 p/s → 5.1 p/s)
- Degradasi performa reset ketika pindah event baru, mengindikasikan masalah memory/cache
- Service layer (AssessmentCalculationService) masih menyebabkan overhead signifikan
- N+1 queries masih terjadi di CategoryService::calculateCategory()
- updateOrCreate() melakukan ~60,000 SELECT queries sebelum INSERT/UPDATE

### Root Cause Analysis (Phase 3)
1. **N+1 Query Problem**: CategoryService melakukan query AspectAssessment untuk setiap participant
2. **updateOrCreate() Overhead**: Setiap record melakukan SELECT + INSERT/UPDATE (~60k queries total)
3. **Missing Composite Indexes**: Lookup queries tidak optimal
4. **Eloquent Memory Leak**: Model cache tumbuh tanpa batas dalam loop
5. **Transaction Lock Contention**: Lock waiting overhead pada concurrent processing

### Solusi Optimasi Phase 3: COMPLETE SERVICE LAYER BYPASS

#### 1. **Full Bulk Insert Strategy**
- **SEMUA** assessment data (category, aspect, sub-aspect, final) di-generate dalam memory
- Manual ID generation menggunakan counter (tidak bergantung auto-increment)
- Bulk insert menggunakan `DB::table()->insert()` untuk semua tabel
- **Zero Eloquent calls** dalam processing loop

#### 2. **Manual Calculation Engine**
```php
private function generateAssessmentRecords(
    Participant $participant,
    AssessmentTemplate $template,
    array $assessmentsData,
    $categoriesCache,
    $aspectsCache,
    int &$categoryIdCounter,
    int &$aspectIdCounter,
    int &$subAspectIdCounter,
    array &$categoryAssessmentsData,
    array &$aspectAssessmentsData,
    array &$subAspectAssessmentsData,
    array &$finalAssessmentsData
): void
```
- Semua kalkulasi (standard rating, individual rating, scores, gaps) dilakukan dalam PHP
- Mengikuti business logic dari AspectService, CategoryService, FinalAssessmentService
- Data disimpan dalam array untuk bulk insert

#### 3. **Chunked Bulk Inserts (MySQL Limit Protection)**
```php
$insertChunkSize = 1000;

if (!empty($subAspectAssessmentsData)) {
    foreach (array_chunk($subAspectAssessmentsData, $insertChunkSize) as $chunk) {
        DB::table('sub_aspect_assessments')->insert($chunk);
    }
}
```
- MySQL memiliki limit 65,535 placeholders per query
- Chunk size 1000 rows mencegah "too many placeholders" error
- Berlaku untuk semua tabel: categories, aspects, sub_aspects, finals

#### 4. **Cache-First Data Retrieval**
- Gunakan AspectCacheService untuk semua master data lookups
- Preload template data di awal event: `AspectCacheService::preloadByTemplate($templateId)`
- Zero database queries untuk master data setelah preload

### Alur Kerja Baru (Phase 3)

1. **seedEvent()**: Setup event, batches, positions, **preload cache**
2. **seedParticipantsOptimized()**: Koordinator utama dengan adaptive chunking
3. **processParticipantChunk()**:
   - Generate participant data → bulk insert
   - **Generate ALL assessment data dalam memory** (kategori, aspek, sub-aspek, final)
   - **Bulk insert semua assessments** dalam chunks
   - Generate psych tests → bulk insert
   - Generate interpretations → bulk insert

### Peningkatan Performa AKTUAL

| Metric | Phase 1-2 | Phase 3 | Improvement |
|--------|-----------|---------|-------------|
| **Speed (p/s)** | 5.1 p/s (degraded) | **186.6 p/s** | **36x lebih cepat** |
| **Consistency** | Progressive slowdown | Konsisten | **Stable** |
| **2000 participants** | ~400 detik | **10.72 detik** | **37x lebih cepat** |
| **1500 participants** | ~300 detik | **8.10 detik** | **37x lebih cepat** |
| **Memory pattern** | Growing | Stable | **Fixed** |

### Hasil Testing Produksi
```
Event 1 (2000 participants): 10.72s total, 186.6 p/s
Event 2 (1500 participants): 8.10s total, 185.1 p/s
Event 3 (1000 participants): 5.40s total, 185.1 p/s
Event 4 (500 participants): 2.70s total, 185.1 p/s
```
**Tidak ada progressive slowdown!** Speed konsisten ~186 p/s untuk semua events.

### Catatan Penting Phase 3

1. **Service Layer Bypass**: Seeder TIDAK menggunakan AssessmentCalculationService
   - Business logic di-replicate manual dalam `generateAssessmentRecords()`
   - Harus maintain consistency dengan service layer jika ada perubahan logic

2. **Type Safety**: Semua variabel numeric di-cast explicit
   ```php
   $standardRating = (float) $aspect->standard_rating;
   $weight = (float) $aspect->weight_percentage;
   ```

3. **Adaptive Chunk Sizing**: Tetap digunakan untuk participants
   ```php
   $chunkSize = match (true) {
       $totalParticipants < 500 => 50,
       $totalParticipants < 2000 => 75,
       $totalParticipants < 10000 => 100,
       default => 150
   };
   ```

---

## 📋 HISTORY: Optimasi Phases 1-2 (Sebelum Phase 3)

### Phase 1-2: Batch Insert Strategy
- Kecepatan awal: 2-4 participant/detik
- Solusi: Batch insert participants, psych tests, interpretations
- **Masih menggunakan service layer** untuk assessments
- Hasil: 6x-10x improvement tapi masih ada progressive slowdown

### Masalah Phase 1-2
- Assessments masih di-process individual menggunakan service layer
- CategoryService::calculateCategory() melakukan N+1 queries
- updateOrCreate() overhead masih signifikan
- Progressive slowdown dari 15 p/s → 5.1 p/s dalam satu event

---

## 🔧 Error & Fixes Durante Optimasi Phase 3

### Error 1: Type Casting
```
TypeError: round(): Argument #1 ($num) must be of type int|float, string given
```
**Fix**: Explicit type casting untuk semua numeric variables
```php
$standardRating = (float) $aspect->standard_rating;
$individualRating = (float) $aspectData['individual_rating'];
$weight = (float) $aspect->weight_percentage;
```

### Error 2: MySQL Placeholder Limit
```
SQLSTATE[HY000]: General error: 1390 Prepared statement contains too many placeholders
```
**Fix**: Chunk bulk inserts menjadi 1000 rows per query
```php
foreach (array_chunk($subAspectAssessmentsData, 1000) as $chunk) {
    DB::table('sub_aspect_assessments')->insert($chunk);
}
```

### Error 3: Unknown Column 'classification'
```
SQLSTATE[42S22]: Column not found: 1054 Unknown column 'classification' in 'field list'
```
**Fix**: Update final_assessments structure sesuai schema aktual
- `achievement_percentage` (bukan `percentage_score`)
- `potensi_weight`, `potensi_standard_score`, `potensi_individual_score`
- `kompetensi_weight`, `kompetensi_standard_score`, `kompetensi_individual_score`
- `conclusion_code`, `conclusion_text` (bukan `classification`)

---

## 📝 File Backup & Testing

### File Backup
- Original file: `database/seeders/DynamicAssessmentSeeder.php.backup`
- Untuk rollback: `cp DynamicAssessmentSeeder.php.backup DynamicAssessmentSeeder.php`

### Testing Commands
```bash
# Standard seeding
php artisan db:seed --class=DynamicAssessmentSeeder

# Monitor memory usage
php -d memory_limit=2G artisan db:seed --class=DynamicAssessmentSeeder

# Reset database dan seed dari awal
php artisan migrate:fresh --seed
```

### Monitoring Performance
```bash
# Check query log di Laravel Telescope (jika enabled)
# Watch memory usage real-time
watch -n 1 'ps aux | grep artisan'
```

---

## 🚀 Potential Future Optimizations

### Already Implemented ✅
- ~~Bulk Insert Assessments~~ → **DONE in Phase 3**
- ~~Manual calculation engine~~ → **DONE in Phase 3**
- ~~Cache-first data retrieval~~ → **DONE in Phase 3**

### Future Enhancements (Optional)
1. **Queue-based Processing**: Untuk dataset >100k, gunakan queue jobs dengan parallel workers
2. **Database Indexing**: Review dan optimize indexes untuk foreign keys
3. **Parallel Event Processing**: Multiple workers untuk different events secara concurrent
4. **Redis Cache**: Untuk master data caching yang persistent across requests

### Current Performance: EXCELLENT ✅
Speed 186 p/s stabil untuk semua dataset size adalah performance yang sangat baik untuk seeding operation.
