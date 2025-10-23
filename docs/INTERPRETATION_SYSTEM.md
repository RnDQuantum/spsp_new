# Interpretation System Documentation

## Overview

Sistem interpretasi adalah fitur yang menerjemahkan nilai rating assessment (1-5) menjadi teks narasi yang deskriptif dan mudah dipahami. Sistem ini digunakan untuk menampilkan interpretasi hasil assessment pada laporan individual (web version dari PDF report).

## Architecture

### Database Structure

#### Table: `interpretation_templates`
Template penyimpanan teks interpretasi berdasarkan rating.

```sql
CREATE TABLE interpretation_templates (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    interpretable_type VARCHAR(255),  -- 'sub_aspect' or 'aspect'
    interpretable_id BIGINT,          -- ID dari sub_aspect/aspect (0 = generic fallback)
    rating_value TINYINT,             -- Rating 1-5
    template_text TEXT,               -- Template teks interpretasi
    tone VARCHAR(50),                 -- 'formal', 'professional'
    category VARCHAR(50),             -- 'potensi', 'kompetensi'
    version VARCHAR(10),              -- Template version (e.g., 'v1.0')
    is_active BOOLEAN,                -- Active status
    created_at TIMESTAMP,
    updated_at TIMESTAMP,

    UNIQUE KEY (interpretable_type, interpretable_id, rating_value, is_active)
);
```

**Key Concepts:**
- **Polymorphic Relationship**: `interpretable_type` + `interpretable_id` mengacu ke `sub_aspects` atau `aspects`
- **Generic Fallback**: Templates dengan `interpretable_id = 0` berfungsi sebagai fallback umum
- **Rating-Based**: Setiap rating (1-5) memiliki template teks yang berbeda
- **Version Control**: Field `version` memungkinkan template versioning

#### Table: `interpretations`
Storage hasil interpretasi yang sudah di-generate untuk setiap participant.

```sql
CREATE TABLE interpretations (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    participant_id BIGINT,
    event_id BIGINT,
    category_type_id BIGINT,           -- ID dari category_type (Potensi/Kompetensi)
    interpretation_text TEXT,          -- Hasil interpretasi final (gabungan paragraf)
    created_at TIMESTAMP,
    updated_at TIMESTAMP,

    FOREIGN KEY (participant_id) REFERENCES participants(id) ON DELETE CASCADE,
    FOREIGN KEY (event_id) REFERENCES assessment_events(id) ON DELETE CASCADE,
    FOREIGN KEY (category_type_id) REFERENCES category_types(id) ON DELETE CASCADE
);
```

**Key Concepts:**
- **Per Category**: Satu record per kategori (Potensi/Kompetensi) per participant
- **Snapshot Pattern**: Hasil interpretasi disimpan untuk historical integrity
- **Event Scoped**: Terkait dengan event tertentu untuk audit trail

### Models

#### InterpretationTemplate Model
**Location**: `app/Models/InterpretationTemplate.php`

```php
class InterpretationTemplate extends Model
{
    protected $fillable = [
        'interpretable_type', 'interpretable_id', 'rating_value',
        'template_text', 'tone', 'category', 'version', 'is_active'
    ];

    // Polymorphic relationship
    public function interpretable(): MorphTo
    {
        return $this->morphTo();
    }

    // Query scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForRating($query, int $rating)
    {
        return $query->where('rating_value', $rating);
    }
}
```

#### Interpretation Model
**Location**: `app/Models/Interpretation.php`

```php
class Interpretation extends Model
{
    protected $fillable = [
        'participant_id', 'event_id', 'category_type_id', 'interpretation_text'
    ];

    public function participant(): BelongsTo
    {
        return $this->belongsTo(Participant::class);
    }

    public function categoryType(): BelongsTo
    {
        return $this->belongsTo(CategoryType::class);
    }
}
```

### Services

#### InterpretationTemplateService
**Location**: `app/Services/InterpretationTemplateService.php`

**Purpose**: Mengelola pengambilan template interpretasi dengan fallback hierarchy.

**Key Methods**:

```php
public function getTemplate(string $type, int $id, int $rating): ?string
```
- Mengambil template berdasarkan type ('sub_aspect'/'aspect'), ID, dan rating
- **Fallback Hierarchy**:
  1. Cek template spesifik (type + id + rating)
  2. Jika tidak ada, cek generic template (type + id=0 + rating)
  3. Jika tidak ada, gunakan hardcoded default
- Menggunakan cache (24 jam) untuk performa

```php
public function getDefaultTemplate(int $rating): string
```
- Hardcoded fallback template untuk setiap rating
- Digunakan sebagai safety net terakhir

**Cache Strategy**:
```php
Cache Key: "interpretation_template_{type}_{id}_{rating}"
TTL: 24 hours
```

#### InterpretationGeneratorService
**Location**: `app/Services/InterpretationGeneratorService.php`

**Purpose**: Generate interpretasi final dari template dan rating assessment.

**Key Methods**:

```php
public function generateForParticipant(Participant $participant): array
```
- Generate interpretasi untuk Potensi dan Kompetensi
- Returns: `['potensi' => string, 'kompetensi' => string]`
- Auto-save ke `interpretations` table menggunakan upsert

```php
protected function generatePotensiInterpretation(Participant $participant): string
```
- **Logic**:
  1. Load semua sub-aspect assessments untuk kategori Potensi
  2. Untuk setiap sub-aspect, ambil template berdasarkan rating_value
  3. Group sub-aspect interpretations by parent aspect
  4. Gabung menjadi satu paragraf per aspect
  5. Gabung semua aspect paragraphs dengan double line break (`\n\n`)

**Example Output Structure**:
```
[Interpretasi Sub-aspect 1.1]. [Interpretasi Sub-aspect 1.2].

[Interpretasi Sub-aspect 2.1]. [Interpretasi Sub-aspect 2.2]. [Interpretasi Sub-aspect 2.3].

[Interpretasi Sub-aspect 3.1].
```

```php
protected function generateKompetensiInterpretation(Participant $participant): string
```
- **Logic**:
  1. Load semua aspect assessments untuk kategori Kompetensi
  2. Untuk setiap aspect, ambil template berdasarkan rating_value
  3. Gabung semua aspect interpretations dengan double line break (`\n\n`)

**Example Output Structure**:
```
[Interpretasi Aspect 1].

[Interpretasi Aspect 2].

[Interpretasi Aspect 3].
```

```php
public function regenerateInterpretations(Participant $participant): array
```
- Force regenerate interpretasi (untuk testing/update template)
- Menghapus interpretasi lama dan generate ulang

### Livewire Components

#### InterpretationSection Component
**Location**:
- PHP: `app/Livewire/Pages/IndividualReport/InterpretationSection.php`
- Blade: `resources/views/livewire/pages/individual-report/interpretation-section.blade.php`

**Purpose**: Menampilkan interpretasi Potensi dan Kompetensi dalam format card yang terpisah.

**Public Properties**:
```php
public ?Participant $participant = null;
public ?string $potensiInterpretation = null;
public ?string $kompetensiInterpretation = null;
public $eventCode;
public $testNumber;
public $showHeader = true;
public $showPotensi = true;
public $showKompetensi = true;
public $isStandalone = true;
```

**Usage Modes**:

1. **Standalone Mode** (direct route access):
```blade
{{-- Route: /individual-report/{eventCode}/{testNumber}/interpretation --}}
<livewire:pages.individual-report.interpretation-section
    :eventCode="'P3K-KEJAKSAAN-2025'"
    :testNumber="'69-5-3-25-133'"
/>
```

2. **Embedded Mode** (dalam FinalReport):
```blade
<livewire:pages.individual-report.interpretation-section
    :eventCode="$eventCode"
    :testNumber="$testNumber"
    :isStandalone="false"
    :showHeader="false"
    :showPotensi="true"
    :showKompetensi="true"
    :key="'interpretation-' . $eventCode . '-' . $testNumber"
/>
```

**Key Methods**:

```php
protected function loadInterpretations(): void
```
- Load interpretasi dari database
- Jika tidak ada, auto-generate menggunakan `InterpretationGeneratorService`

```php
public function regenerate(): void
```
- Force regenerate interpretasi (debug mode only)
- Dispatch event `interpretation-regenerated`

**UI Features**:
- Separate cards untuk Potensi (blue gradient) dan Kompetensi (green gradient)
- Dark mode support
- Auto paragraph splitting (berdasarkan `\n\n`)
- Empty state handling
- Loading states untuk regenerate action
- Debug button (hanya muncul saat `APP_DEBUG=true` dan standalone mode)

## Data Flow

### 1. Initial Setup (One-time)
```
1. Run migration: php artisan migrate
2. Seed templates: php artisan db:seed --class=InterpretationTemplateSeeder
3. Verify templates: SELECT COUNT(*) FROM interpretation_templates
```

### 2. Template Retrieval Flow
```
User Request
    ↓
InterpretationGeneratorService::generateForParticipant()
    ↓
For each sub-aspect/aspect:
    ↓
InterpretationTemplateService::getTemplate(type, id, rating)
    ↓
Check Cache → Check Specific Template → Check Generic Template → Use Hardcoded Default
    ↓
Return template text
```

### 3. Interpretation Display Flow
```
User visits FinalReport page
    ↓
InterpretationSection::mount()
    ↓
Load Participant by event_code + test_number
    ↓
loadInterpretations()
    ↓
Check if interpretation exists in DB
    ↓
If exists: Load from DB
If not: Auto-generate via InterpretationGeneratorService
    ↓
Display in Blade view
```

### 4. Auto-generation Flow
```
InterpretationGeneratorService::generateForParticipant()
    ↓
Load CategoryAssessments → AspectAssessments → SubAspectAssessments
    ↓
For Potensi:
    For each SubAspect → Get Template → Group by Aspect → Combine
    ↓
For Kompetensi:
    For each Aspect → Get Template → Combine
    ↓
Upsert to interpretations table
    ↓
Return ['potensi' => ..., 'kompetensi' => ...]
```

## Template Structure

### Rating Levels Meaning
- **Rating 1**: Sangat kurang / Very poor
- **Rating 2**: Kurang / Poor
- **Rating 3**: Cukup / Adequate
- **Rating 4**: Kompeten / Competent
- **Rating 5**: Sangat kompeten / Highly competent

### Template Categories

#### 1. Generic Fallback Templates
**interpretable_id = 0**

Digunakan untuk semua sub-aspect/aspect yang tidak memiliki template spesifik.

Example:
```
Rating 3: "Individu memiliki kemampuan yang cukup memadai dalam aspek ini."
Rating 4: "Individu kompeten dalam aspek ini dan menunjukkan kinerja yang baik."
```

#### 2. Specific Sub-Aspect Templates (Potensi)
**interpretable_type = 'sub_aspect', interpretable_id = sub_aspect.id**

Template khusus untuk sub-aspect tertentu (e.g., Kecerdasan Umum, Kemampuan Verbal).

Example untuk Sub-Aspect "Kecerdasan Umum":
```
Rating 3: "Individu memiliki kapasitas kecerdasan umum yang cukup memadai. Hal ini menunjukkan kemampuannya untuk dapat secara cukup cepat mempelajari tugas baru yang akan diberikan kepadanya."
```

#### 3. Specific Aspect Templates (Kompetensi)
**interpretable_type = 'aspect', interpretable_id = aspect.id**

Template khusus untuk aspect tertentu (e.g., Integritas, Kerja Sama).

Example untuk Aspect "Integritas":
```
Rating 4: "Individu kompeten menampilkan kompetensi integritas. Individu mampu mengingatkan rekan kerja untuk bertindak sesuai dengan etika dan kode etik dalam pelaksanaan tugas."
```

## Seeder Reference

### InterpretationTemplateSeeder
**Location**: `database/seeders/InterpretationTemplateSeeder.php`

**Template Count**: 48 templates total
- Generic fallback: 10 templates (5 for sub_aspect, 5 for aspect)
- Potensi sub-aspects: 19 templates (across 8 unique sub-aspects)
- Kompetensi aspects: 19 templates (across 9 unique aspects)

**Seeded Sub-Aspects (Potensi)**:
1. Kecerdasan Umum (sub_aspect_id: 1)
2. Kemampuan Verbal (sub_aspect_id: 2)
3. Kemampuan Numerical (sub_aspect_id: 6)
4. Kemampuan Berpikir Logis (sub_aspect_id: 9)
5. Kemampuan Berpikir Analitis (sub_aspect_id: 10)
6. Kemampuan Berpikir Praktis (sub_aspect_id: 11)
7. Orientasi Berprestasi (sub_aspect_id: 15)
8. Inovasi (sub_aspect_id: 16)

**Seeded Aspects (Kompetensi)**:
1. Integritas (aspect_id: 10)
2. Kerja Sama (aspect_id: 11)
3. Komunikasi (aspect_id: 12)
4. Orientasi pada Hasil (aspect_id: 13)
5. Pelayanan Publik (aspect_id: 14)
6. Pengembangan Diri dan Orang Lain (aspect_id: 15)
7. Mengelola Perubahan (aspect_id: 16)
8. Pengambilan Keputusan (aspect_id: 17)
9. Perekat Bangsa (aspect_id: 18)

## Testing

### Manual Testing via Tinker

```php
// Load participant
$participant = \App\Models\Participant::with([
    'positionFormation.template.categoryTypes.aspects.subAspects',
    'categoryAssessments.aspectAssessments.subAspectAssessments'
])->find(201);

// Generate interpretations
$generator = app(\App\Services\InterpretationGeneratorService::class);
$result = $generator->generateForParticipant($participant);

// View results
echo "POTENSI:\n";
echo $result['potensi'];
echo "\n\nKOMPETENSI:\n";
echo $result['kompetensi'];
```

### Verify Database Records

```sql
-- Check templates count
SELECT interpretable_type, COUNT(*) as count
FROM interpretation_templates
GROUP BY interpretable_type;

-- Check generated interpretations
SELECT i.id, p.name, ct.name as category,
  LEFT(i.interpretation_text, 100) as preview
FROM interpretations i
JOIN participants p ON i.participant_id = p.id
JOIN category_types ct ON i.category_type_id = ct.id
ORDER BY i.created_at DESC
LIMIT 10;
```

### Browser Testing

1. Navigate to: `/individual-report/{eventCode}/{testNumber}/final-report`
2. Verify interpretations display correctly
3. Check dark mode toggle
4. Test regenerate button (debug mode only)

## Maintenance & Updates

### Adding New Templates for New Aspects/Sub-Aspects

**IMPORTANT**: Since v2.0, templates use **name-based matching**. When adding new aspects or sub-aspects, you only need to update the seeder.

#### Step-by-Step Guide:

**1. Identify the Exact Name**
```php
// Via tinker
php artisan tinker

$newSubAspect = \App\Models\SubAspect::find($id);
echo $newSubAspect->name; // Example: "Kreativitas"

// IMPORTANT: Name must be exact match (case-sensitive)
```

**2. Add Template to DetailedInterpretationTemplateSeeder**

Edit `database/seeders/DetailedInterpretationTemplateSeeder.php`:

```php
protected function seedKreativitas(): void
{
    $templates = [
        // Rating 2: Development needed
        [
            'interpretable_type' => 'sub_aspect',
            'interpretable_name' => 'Kreativitas', // Must match exactly
            'rating_value' => 2,
            'template_text' => 'Kreativitas individu masih perlu dikembangkan. Ia cenderung menggunakan pendekatan yang konvensional dan kesulitan dalam menghasilkan ide-ide baru yang inovatif.',
            'tone' => 'neutral',
            'category' => 'development_area',
        ],
        // Rating 3: Adequate
        [
            'interpretable_type' => 'sub_aspect',
            'interpretable_name' => 'Kreativitas',
            'rating_value' => 3,
            'template_text' => 'Individu memiliki kreativitas yang cukup memadai dalam menghasilkan ide-ide baru untuk menyelesaikan permasalahan yang dihadapi.',
            'tone' => 'neutral',
            'category' => 'neutral',
        ],
        // Rating 4: Good
        [
            'interpretable_type' => 'sub_aspect',
            'interpretable_name' => 'Kreativitas',
            'rating_value' => 4,
            'template_text' => 'Kreativitas yang dimiliki individu tergolong baik. Ia mampu menghasilkan ide-ide inovatif dan solusi kreatif untuk berbagai tantangan yang dihadapi.',
            'tone' => 'positive',
            'category' => 'strength',
        ],
    ];

    foreach ($templates as $template) {
        InterpretationTemplate::create([
            ...$template,
            'version' => 'v2.0',
            'is_active' => true,
        ]);
    }
}

// Add to run() method
public function run(): void
{
    InterpretationTemplate::truncate();

    $this->seedHubunganSosial();
    $this->seedKecerdasan();
    $this->seedKepribadian();
    $this->seedCaraKerja();
    $this->seedKreativitas(); // NEW METHOD
    $this->seedGenericFallbacks();
}
```

**3. Run the Seeder**
```bash
php artisan db:seed --class=DetailedInterpretationTemplateSeeder
```

**4. Regenerate Existing Interpretations (if needed)**
```bash
# Regenerate all
php artisan interpretations:regenerate

# Or specific event
php artisan interpretations:regenerate --event=P3K-KEJAKSAAN-2025
```

#### Best Practices:

1. **Create templates for common ratings (2, 3, 4)**
   - Rating 1 and 5 can use generic fallback if not critical

2. **Use consistent tone and category**
   ```php
   Rating 1-2 → 'tone' => 'neutral', 'category' => 'development_area'
   Rating 3   → 'tone' => 'neutral', 'category' => 'neutral'
   Rating 4-5 → 'tone' => 'positive', 'category' => 'strength'
   ```

3. **Ensure exact name match (case-sensitive)**
   ```php
   ✅ Database: "Kepekaan Interpersonal" → Seeder: "Kepekaan Interpersonal"
   ❌ Database: "Kepekaan Interpersonal" → Seeder: "kepekaan interpersonal"
   ```

4. **Write 2-3 sentences per template**
   - First sentence: Main characteristic
   - Second sentence: Impact/implication
   - Third sentence (optional): Future outlook

5. **Organize by aspect category**
   - Create separate methods for each major aspect
   - Keep related sub-aspects together

#### Quick Add via Tinker (for single template):

```php
php artisan tinker

\App\Models\InterpretationTemplate::create([
    'interpretable_type' => 'sub_aspect',
    'interpretable_name' => 'Kreativitas',
    'rating_value' => 3,
    'template_text' => 'Individu memiliki kreativitas yang cukup memadai...',
    'tone' => 'neutral',
    'category' => 'neutral',
    'version' => 'v2.0',
    'is_active' => true,
]);
```

### What You DON'T Need to Update:

✅ **InterpretationTemplateService** - Already uses name-based matching
✅ **InterpretationGeneratorService** - Already dynamic, no hardcoded logic
✅ **Migration files** - Schema already supports name-based templates
✅ **Models** - Already complete with required fields
✅ **Livewire components** - Work automatically with new templates

### Updating Existing Templates

**Option 1: Soft Update (create new version)**
```php
// Deactivate old version
InterpretationTemplate::where('id', $oldId)->update(['is_active' => false]);

// Create new version
InterpretationTemplate::create([..., 'version' => 'v1.1', 'is_active' => true]);
```

**Option 2: Direct Update**
```php
InterpretationTemplate::where('id', $id)->update([
    'template_text' => 'Updated text...',
    'version' => 'v1.1',
]);

// Clear cache
Cache::forget("interpretation_template_{type}_{id}_{rating}");
```

### Regenerating Interpretations

When templates are updated, you may want to regenerate existing interpretations:

```php
// For specific participant
$participant = Participant::find($id);
$generator = app(\App\Services\InterpretationGeneratorService::class);
$generator->regenerateInterpretations($participant);

// For all participants in an event
$participants = Participant::whereHas('assessmentEvent', function($q) {
    $q->where('code', 'P3K-KEJAKSAAN-2025');
})->get();

foreach ($participants as $participant) {
    $generator->regenerateInterpretations($participant);
}
```

### Cache Management

```php
// Clear all interpretation template caches
Cache::flush(); // Nuclear option

// Clear specific template cache
Cache::forget("interpretation_template_sub_aspect_1_3");

// Clear all interpretation template caches (pattern-based)
// Requires Redis or cache driver with tag support
Cache::tags(['interpretation_templates'])->flush();
```

## Performance Considerations

### Database Indexes
```sql
-- Already created via migration
CREATE INDEX idx_templates_lookup
ON interpretation_templates(interpretable_type, interpretable_id, rating_value, is_active);

CREATE INDEX idx_interp_participant
ON interpretations(participant_id, category_type_id);
```

### N+1 Query Prevention

Always eager load relationships when generating interpretations:

```php
$participant = Participant::with([
    'positionFormation.template.categoryTypes.aspects.subAspects',
    'categoryAssessments.aspectAssessments.subAspectAssessments'
])->find($id);
```

### Cache Strategy
- Template retrieval: 24 hours cache
- Interpretation results: Stored in DB (no cache needed)
- Clear cache after template updates

## Common Issues & Solutions

### Issue: "Interpretasi belum tersedia"

**Cause**: No templates found and auto-generation failed

**Solution**:
1. Check if templates are seeded: `SELECT COUNT(*) FROM interpretation_templates`
2. Run seeder: `php artisan db:seed --class=InterpretationTemplateSeeder`
3. Check participant has assessment data
4. Manually regenerate via Livewire component

### Issue: Wrong interpretation text

**Cause**: Using wrong rating value or template

**Solution**:
1. Verify assessment ratings: `SELECT * FROM sub_aspect_assessments WHERE participant_id = ?`
2. Check template mapping: `SELECT * FROM interpretation_templates WHERE rating_value = ?`
3. Clear cache and regenerate

### Issue: Performance slow on report load

**Cause**: N+1 queries or missing cache

**Solution**:
1. Enable query logging: `DB::enableQueryLog()`
2. Check eager loading in component mount
3. Verify cache is working: `Cache::has("interpretation_template_...")`
4. Add database indexes if needed

## Integration Points

### FinalReport Component
**Location**: `resources/views/livewire/pages/individual-report/final-report.blade.php`

```blade
{{-- Interpretation Section --}}
<div class="mb-8">
    <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">
        Interpretasi Hasil Assessment
    </h2>
    <livewire:pages.individual-report.interpretation-section
        :eventCode="$eventCode"
        :testNumber="$testNumber"
        :isStandalone="false"
        :showHeader="false"
        :showPotensi="true"
        :showKompetensi="true"
        :key="'interpretation-' . $eventCode . '-' . $testNumber" />
</div>
```

### API Integration (Future)

If needed, interpretations can be exposed via API:

```php
// Route: GET /api/v1/interpretations/{participantId}
public function show($participantId)
{
    $interpretations = Interpretation::where('participant_id', $participantId)
        ->with('categoryType')
        ->get();

    return response()->json([
        'data' => $interpretations->mapWithKeys(function ($item) {
            return [$item->categoryType->code => $item->interpretation_text];
        })
    ]);
}
```

## Future Enhancements

### 1. Admin UI for Template Management
- CRUD interface for templates
- Preview interpretations before saving
- Bulk import from Excel/CSV

### 2. Template Versioning UI
- View template history
- Compare versions
- Rollback to previous versions

### 3. AI-Assisted Template Generation
- Generate template variations using AI
- Tone adjustment (formal, casual, technical)
- Multi-language support

### 4. Batch Regeneration Command
```bash
php artisan interpretations:regenerate {event_code?} {--force}
```

### 5. Template Analytics
- Track which templates are used most
- A/B testing different template variations
- User feedback on interpretation clarity

## Related Documentation

- [Database and Assessment Logic](./DATABASE_AND_ASSESSMENT_LOGIC.md)
- [API Specification](./API_SPECIFICATION.md)
- [Assessment Calculation Flow](./ASSESSMENT_CALCULATION_FLOW.md)

## Change Log

### Version 2.0.0 (2025-10-23)
- **BREAKING CHANGE**: Template matching now name-based instead of ID-based
- Added `interpretable_name` column to `interpretation_templates` table
- Templates now work across all position templates with same sub-aspect/aspect names
- Updated `InterpretationTemplateService` with `getTemplateByName()` method
- Updated `InterpretationGeneratorService` to use name-based matching
- Seeder updated to use sub-aspect/aspect names instead of IDs
- More detailed templates (2-3 sentences per sub-aspect)
- Better fallback hierarchy (name-based → generic → hardcoded)

### Version 1.0.0 (2025-10-23)
- Initial implementation
- Database schema created
- Template seeder with 48 templates
- Service layer (TemplateService, GeneratorService)
- Livewire InterpretationSection component
- Integration with FinalReport

---

**Last Updated**: 2025-10-23
**Maintained By**: Development Team
**Status**: Production Ready (v2.0)
