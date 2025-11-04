# MULTI-TENANT SAAS & DYNAMIC STANDARD ANALYSIS

Dokumentasi implementasi transformasi aplikasi menjadi multi-tenant SaaS dengan fitur analisis standar dinamis.

---

## TABLE OF CONTENTS

1. [Overview](#overview)
2. [Database Changes](#database-changes)
3. [Authentication & Authorization](#authentication--authorization)
4. [Dynamic Standard Service](#dynamic-standard-service)
5. [UI Components](#ui-components)
6. [Implementation Steps](#implementation-steps)
7. [Testing](#testing)

---

## OVERVIEW

### Goals

1. **Multi-Tenancy**: Setiap user terikat ke satu institution, hanya bisa akses data institution mereka
2. **Role-Based Access**:
   - **Admin**: Akses semua institution + manage users/institutions
   - **Client**: Akses hanya institution mereka sendiri
3. **Dynamic Standard Analysis**: User bisa adjust bobot & rating untuk analisis tanpa ubah database
4. **Session-Based**: Adjustment tersimpan di session (seperti tolerance), bisa di-reset

### Key Principles

- ✅ Data asli di database **TIDAK BERUBAH**
- ✅ Adjustment tersimpan di **SESSION** (per user, per template)
- ✅ **Template-level** adjustment (bukan per event/participant)
- ✅ Reset button untuk kembali ke standar asli
- ✅ Real-time calculation preview

### Architecture

```
User Authentication
    ↓
Role Check (Spatie Permission)
    ↓
Institution Filter (Global Scope)
    ↓
Standard Adjustment (Session)
    ↓
Dynamic Calculation (With/Without Adjustment)
    ↓
Display Results
```

---

## DATABASE CHANGES

### 1. Users Table Migration

**File:** `database/migrations/2025_01_04_000001_add_multi_tenancy_to_users_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Multi-tenancy
            $table->foreignId('institution_id')
                ->nullable()
                ->after('id')
                ->constrained('institutions')
                ->nullOnDelete();

            // User management
            $table->boolean('is_active')->default(true)->after('password');
            $table->timestamp('last_login_at')->nullable()->after('is_active');

            // Indexes
            $table->index('institution_id');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['institution_id']);
            $table->dropIndex(['institution_id']);
            $table->dropIndex(['is_active']);
            $table->dropColumn(['institution_id', 'is_active', 'last_login_at']);
        });
    }
};
```

### 2. No Additional Tables Needed

**Important:** Kita TIDAK membuat table baru untuk standard adjustment karena:
- Adjustment bersifat temporary (analisis saja)
- Tersimpan di session (seperti tolerance)
- Bisa di-reset kapan saja
- Tidak perlu persistence

---

## AUTHENTICATION & AUTHORIZATION

### 1. Install Spatie Permission

**Already installed** (sudah ada di project - cek `model_has_roles`, `model_has_permissions` tables)

### 2. Roles & Permissions Seeder

**File:** `database/seeders/RolesAndPermissionsSeeder.php`

**SIMPLIFIED IMPLEMENTATION** - Hanya 2 role: `admin` dan `client`

```php
<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            'view all institutions',  // Only for admin
            'manage users',            // Only for admin
            'manage institutions',     // Only for admin
            'analyze standards',       // For dynamic standard analysis
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Create roles and assign permissions

        // 1. Admin (global access to all institutions)
        $admin = Role::create(['name' => 'admin']);
        $admin->givePermissionTo(Permission::all());

        // 2. Client (access only their own institution data)
        $client = Role::create(['name' => 'client']);
        $client->givePermissionTo([
            'analyze standards',
        ]);
    }
}
```

**Roles Explanation:**
- **admin**: Dapat akses semua data dari semua institution + manage users/institutions
- **client**: Hanya dapat akses data dari institution mereka sendiri + dapat melakukan dynamic standard analysis

**Run seeder:**
```bash
php artisan db:seed --class=RolesAndPermissionsSeeder
```

### 3. Update User Model

**File:** `app/Models/User.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name',
        'email',
        'password',
        'institution_id',
        'is_active',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'last_login_at' => 'datetime',
        ];
    }

    /**
     * Relationship to institution
     */
    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }

    /**
     * Check if user is admin
     */
    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    /**
     * Check if user can access institution
     */
    public function canAccessInstitution(int $institutionId): bool
    {
        // Admin can access all institutions
        if ($this->isAdmin()) {
            return true;
        }

        // Clients can only access their own institution
        return $this->institution_id === $institutionId;
    }

    /**
     * Scope: Only active users
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
```

### 4. Global Scope for Auto-Filtering

**File:** `app/Models/Scopes/InstitutionScope.php`

```php
<?php

declare(strict_types=1);

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class InstitutionScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        $user = auth()->user();

        // Skip if no authenticated user
        if (!$user) {
            return;
        }

        // Admin can see all institutions
        if ($user->isAdmin()) {
            return;
        }

        // Clients only see their institution's data
        if ($user->institution_id) {
            $builder->where('institution_id', $user->institution_id);
        }
    }
}
```

**Apply to Models:**

```php
// app/Models/AssessmentEvent.php

use App\Models\Scopes\InstitutionScope;

class AssessmentEvent extends Model
{
    protected static function booted(): void
    {
        static::addGlobalScope(new InstitutionScope());
    }
}
```

Apply `InstitutionScope` to:
- `AssessmentEvent`
- Other models already filtered via relationships (Participant, Batch, etc.)

### 5. Middleware for Access Control

**File:** `app/Http/Middleware/EnsureUserBelongsToInstitution.php`

```php
<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserBelongsToInstitution
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        // Allow if no user (handled by auth middleware)
        if (!$user) {
            return $next($request);
        }

        // Admin bypass
        if ($user->isAdmin()) {
            return $next($request);
        }

        // Check if user has institution_id
        if (!$user->institution_id) {
            abort(403, 'User is not assigned to any institution.');
        }

        return $next($request);
    }
}
```

**Register middleware:**

```php
// bootstrap/app.php

->withMiddleware(function (Middleware $middleware) {
    $middleware->alias([
        'institution.access' => \App\Http\Middleware\EnsureUserBelongsToInstitution::class,
    ]);
})
```

**Apply to routes:**

```php
// routes/web.php

// ✅ APPLIED - All protected routes now use institution.access middleware
Route::middleware(['auth', 'institution.access'])->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
    Route::get('/dashboard', \App\Livewire\Pages\Dashboard::class)->name('dashboard');
    Route::get('/shortlist-peserta', \App\Livewire\Pages\ParticipantsList::class)->name('shortlist');
    // ... all other protected routes
});
```

---

## DYNAMIC STANDARD SERVICE

### 1. Service Class

**File:** `app/Services/DynamicStandardService.php`

```php
<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Aspect;
use App\Models\AssessmentTemplate;
use App\Models\CategoryType;
use App\Models\SubAspect;
use Illuminate\Support\Facades\Session;

class DynamicStandardService
{
    /**
     * Session key prefix
     */
    private const SESSION_PREFIX = 'standard_adjustment';

    /**
     * Get session key for template
     */
    private function getSessionKey(int $templateId): string
    {
        return self::SESSION_PREFIX . ".{$templateId}";
    }

    /**
     * Get all adjustments for a template
     */
    public function getAdjustments(int $templateId): array
    {
        return Session::get($this->getSessionKey($templateId), []);
    }

    /**
     * Check if template has any adjustments
     */
    public function hasAdjustments(int $templateId): bool
    {
        return Session::has($this->getSessionKey($templateId));
    }

    /**
     * Get category weight (adjusted or original)
     */
    public function getCategoryWeight(int $templateId, string $categoryCode): int
    {
        $adjustments = $this->getAdjustments($templateId);

        if (isset($adjustments['category_weights'][$categoryCode])) {
            return (int) $adjustments['category_weights'][$categoryCode];
        }

        // Get original from database
        $category = CategoryType::where('template_id', $templateId)
            ->where('code', $categoryCode)
            ->first();

        return $category ? $category->weight_percentage : 0;
    }

    /**
     * Get aspect weight (adjusted or original)
     */
    public function getAspectWeight(int $templateId, string $aspectCode): int
    {
        $adjustments = $this->getAdjustments($templateId);

        if (isset($adjustments['aspect_weights'][$aspectCode])) {
            return (int) $adjustments['aspect_weights'][$aspectCode];
        }

        // Get original from database
        $aspect = Aspect::where('template_id', $templateId)
            ->where('code', $aspectCode)
            ->first();

        return $aspect ? $aspect->weight_percentage : 0;
    }

    /**
     * Get aspect standard rating (adjusted or original)
     */
    public function getAspectRating(int $templateId, string $aspectCode): float
    {
        $adjustments = $this->getAdjustments($templateId);

        if (isset($adjustments['aspect_ratings'][$aspectCode])) {
            return (float) $adjustments['aspect_ratings'][$aspectCode];
        }

        // Get original from database
        $aspect = Aspect::where('template_id', $templateId)
            ->where('code', $aspectCode)
            ->first();

        return $aspect ? (float) $aspect->standard_rating : 0.0;
    }

    /**
     * Get sub-aspect standard rating (adjusted or original)
     */
    public function getSubAspectRating(int $templateId, string $subAspectCode): int
    {
        $adjustments = $this->getAdjustments($templateId);

        if (isset($adjustments['sub_aspect_ratings'][$subAspectCode])) {
            return (int) $adjustments['sub_aspect_ratings'][$subAspectCode];
        }

        // Get original from database
        $subAspect = SubAspect::whereHas('aspect', function ($query) use ($templateId) {
            $query->where('template_id', $templateId);
        })->where('code', $subAspectCode)->first();

        return $subAspect ? $subAspect->standard_rating : 0;
    }

    /**
     * Save category weight adjustment
     */
    public function saveCategoryWeight(int $templateId, string $categoryCode, int $weight): void
    {
        $adjustments = $this->getAdjustments($templateId);
        $adjustments['category_weights'][$categoryCode] = $weight;
        $adjustments['adjusted_at'] = now()->toDateTimeString();

        Session::put($this->getSessionKey($templateId), $adjustments);
    }

    /**
     * Save aspect weight adjustment
     */
    public function saveAspectWeight(int $templateId, string $aspectCode, int $weight): void
    {
        $adjustments = $this->getAdjustments($templateId);
        $adjustments['aspect_weights'][$aspectCode] = $weight;
        $adjustments['adjusted_at'] = now()->toDateTimeString();

        Session::put($this->getSessionKey($templateId), $adjustments);
    }

    /**
     * Save aspect rating adjustment
     */
    public function saveAspectRating(int $templateId, string $aspectCode, float $rating): void
    {
        $adjustments = $this->getAdjustments($templateId);
        $adjustments['aspect_ratings'][$aspectCode] = $rating;
        $adjustments['adjusted_at'] = now()->toDateTimeString();

        Session::put($this->getSessionKey($templateId), $adjustments);
    }

    /**
     * Save sub-aspect rating adjustment
     */
    public function saveSubAspectRating(int $templateId, string $subAspectCode, int $rating): void
    {
        $adjustments = $this->getAdjustments($templateId);
        $adjustments['sub_aspect_ratings'][$subAspectCode] = $rating;
        $adjustments['adjusted_at'] = now()->toDateTimeString();

        Session::put($this->getSessionKey($templateId), $adjustments);
    }

    /**
     * Save bulk adjustments
     */
    public function saveBulkAdjustments(int $templateId, array $adjustments): void
    {
        $adjustments['adjusted_at'] = now()->toDateTimeString();
        Session::put($this->getSessionKey($templateId), $adjustments);
    }

    /**
     * Reset all adjustments for a template
     */
    public function resetAdjustments(int $templateId): void
    {
        Session::forget($this->getSessionKey($templateId));
    }

    /**
     * Get original (unadjusted) template data
     */
    public function getOriginalTemplateData(int $templateId): array
    {
        $template = AssessmentTemplate::with([
            'categoryTypes',
            'aspects.subAspects',
        ])->findOrFail($templateId);

        $data = [
            'template' => $template,
            'category_weights' => [],
            'potensi_aspects' => [],
            'kompetensi_aspects' => [],
        ];

        foreach ($template->categoryTypes as $category) {
            $data['category_weights'][$category->code] = $category->weight_percentage;

            $aspects = $category->aspects->map(function ($aspect) {
                return [
                    'id' => $aspect->id,
                    'code' => $aspect->code,
                    'name' => $aspect->name,
                    'weight_percentage' => $aspect->weight_percentage,
                    'standard_rating' => $aspect->standard_rating,
                    'sub_aspects' => $aspect->subAspects->map(function ($subAspect) {
                        return [
                            'id' => $subAspect->id,
                            'code' => $subAspect->code,
                            'name' => $subAspect->name,
                            'standard_rating' => $subAspect->standard_rating,
                        ];
                    })->toArray(),
                ];
            })->toArray();

            if ($category->code === 'potensi') {
                $data['potensi_aspects'] = $aspects;
            } else {
                $data['kompetensi_aspects'] = $aspects;
            }
        }

        return $data;
    }

    /**
     * Validate adjustments
     */
    public function validateAdjustments(array $adjustments): array
    {
        $errors = [];

        // Validate category weights sum to 100
        if (isset($adjustments['category_weights'])) {
            $total = array_sum($adjustments['category_weights']);
            if ($total !== 100) {
                $errors['category_weights'] = "Total bobot kategori harus 100% (saat ini: {$total}%)";
            }
        }

        // Validate aspect weights per category sum to 100
        if (isset($adjustments['aspect_weights'])) {
            // Group by category and validate each
            // (Implementation depends on how you structure the data)
        }

        // Validate rating ranges
        if (isset($adjustments['aspect_ratings'])) {
            foreach ($adjustments['aspect_ratings'] as $code => $rating) {
                if ($rating < 1 || $rating > 5) {
                    $errors["aspect_ratings.{$code}"] = 'Rating harus antara 1-5';
                }
            }
        }

        if (isset($adjustments['sub_aspect_ratings'])) {
            foreach ($adjustments['sub_aspect_ratings'] as $code => $rating) {
                if ($rating < 1 || $rating > 5) {
                    $errors["sub_aspect_ratings.{$code}"] = 'Rating harus antara 1-5';
                }
            }
        }

        return $errors;
    }
}
```

### 2. Integration with Calculation Services

Update existing calculation services to use adjusted values when available.

**File:** `app/Services/Assessment/AspectService.php`

Add method parameter and logic:

```php
use App\Services\DynamicStandardService;

class AspectService
{
    public function __construct(
        private readonly DynamicStandardService $dynamicStandardService
    ) {}

    /**
     * Calculate aspect assessment for Potensi
     *
     * @param bool $useAdjustedStandard Use adjusted standard from session
     */
    public function calculatePotensiAspect(
        AspectAssessment $aspectAssessment,
        bool $useAdjustedStandard = false
    ): void {
        // Get sub-aspect assessments
        $subAssessments = SubAspectAssessment::where(
            'aspect_assessment_id',
            $aspectAssessment->id
        )->get();

        if ($subAssessments->isEmpty()) {
            return;
        }

        // Calculate individual_rating (average of sub-aspects)
        $individualRating = $subAssessments->avg('individual_rating');

        // Get aspect from master
        $aspect = Aspect::findOrFail($aspectAssessment->aspect_id);

        // Get template from aspect
        $templateId = $aspect->template_id;

        // Get weight (adjusted or original)
        if ($useAdjustedStandard) {
            $weight = $this->dynamicStandardService->getAspectWeight($templateId, $aspect->code);

            // Recalculate standard_rating from adjusted sub-aspect ratings
            $adjustedSubRatings = [];
            foreach ($aspect->subAspects as $subAspect) {
                $adjustedSubRatings[] = $this->dynamicStandardService->getSubAspectRating(
                    $templateId,
                    $subAspect->code
                );
            }
            $standardRating = count($adjustedSubRatings) > 0
                ? array_sum($adjustedSubRatings) / count($adjustedSubRatings)
                : $aspectAssessment->standard_rating;
        } else {
            $weight = $aspect->weight_percentage;
            $standardRating = $aspectAssessment->standard_rating;
        }

        // Calculate scores
        $standardScore = $standardRating * $weight;
        $individualScore = $individualRating * $weight;

        // Calculate gaps
        $gapRating = $individualRating - $standardRating;
        $gapScore = $individualScore - $standardScore;

        // Calculate percentage for spider chart
        $percentageScore = (int) round(($individualRating / 5) * 100);

        // Determine conclusion
        $conclusionCode = $this->determineConclusion($gapRating);
        $conclusionText = $this->getConclusionText($conclusionCode);

        // Update aspect assessment
        $aspectAssessment->update([
            'individual_rating' => round($individualRating, 2),
            'standard_score' => round($standardScore, 2),
            'individual_score' => round($individualScore, 2),
            'gap_rating' => round($gapRating, 2),
            'gap_score' => round($gapScore, 2),
            'percentage_score' => $percentageScore,
            'conclusion_code' => $conclusionCode,
            'conclusion_text' => $conclusionText,
        ]);
    }

    /**
     * Calculate aspect assessment for Kompetensi
     */
    public function calculateKompetensiAspect(
        AspectAssessment $aspectAssessment,
        int $individualRating,
        bool $useAdjustedStandard = false
    ): void {
        // Get aspect from master
        $aspect = Aspect::findOrFail($aspectAssessment->aspect_id);

        // Get template from aspect
        $templateId = $aspect->template_id;

        // Get weight and standard rating (adjusted or original)
        if ($useAdjustedStandard) {
            $weight = $this->dynamicStandardService->getAspectWeight($templateId, $aspect->code);
            $standardRating = $this->dynamicStandardService->getAspectRating($templateId, $aspect->code);
        } else {
            $weight = $aspect->weight_percentage;
            $standardRating = $aspectAssessment->standard_rating;
        }

        // Calculate scores
        $standardScore = $standardRating * $weight;
        $individualScore = $individualRating * $weight;

        // Calculate gaps
        $gapRating = $individualRating - $standardRating;
        $gapScore = $individualScore - $standardScore;

        // Calculate percentage
        $percentageScore = (int) round(($individualRating / 5) * 100);

        // Determine conclusion
        $conclusionCode = $this->determineConclusion($gapRating);
        $conclusionText = $this->getConclusionText($conclusionCode);

        // Update aspect assessment
        $aspectAssessment->update([
            'individual_rating' => $individualRating,
            'standard_score' => round($standardScore, 2),
            'individual_score' => round($individualScore, 2),
            'gap_rating' => round($gapRating, 2),
            'gap_score' => round($gapScore, 2),
            'percentage_score' => $percentageScore,
            'conclusion_code' => $conclusionCode,
            'conclusion_text' => $conclusionText,
        ]);
    }

    // ... existing methods ...
}
```

Similar updates to `CategoryService` and `FinalAssessmentService`.

---

## UI COMPONENTS

### 1. Standard Analysis Livewire Component

**File:** `app/Livewire/StandardAnalysis.php`

```php
<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\AssessmentTemplate;
use App\Services\DynamicStandardService;
use Livewire\Attributes\Computed;
use Livewire\Component;

class StandardAnalysis extends Component
{
    public AssessmentTemplate $template;

    public array $categoryWeights = [];

    public array $aspectWeights = [];

    public array $aspectRatings = [];

    public array $subAspectRatings = [];

    private DynamicStandardService $dynamicStandardService;

    public function boot(DynamicStandardService $dynamicStandardService): void
    {
        $this->dynamicStandardService = $dynamicStandardService;
    }

    public function mount(int $templateId): void
    {
        $this->template = AssessmentTemplate::with([
            'categoryTypes',
            'aspects.subAspects',
        ])->findOrFail($templateId);

        $this->loadCurrentValues();
    }

    /**
     * Load current values (adjusted or original)
     */
    private function loadCurrentValues(): void
    {
        $templateId = $this->template->id;

        // Load category weights
        foreach ($this->template->categoryTypes as $category) {
            $this->categoryWeights[$category->code] = $this->dynamicStandardService->getCategoryWeight(
                $templateId,
                $category->code
            );
        }

        // Load aspect weights and ratings
        foreach ($this->template->aspects as $aspect) {
            $this->aspectWeights[$aspect->code] = $this->dynamicStandardService->getAspectWeight(
                $templateId,
                $aspect->code
            );

            $this->aspectRatings[$aspect->code] = $this->dynamicStandardService->getAspectRating(
                $templateId,
                $aspect->code
            );

            // Load sub-aspect ratings (for Potensi)
            foreach ($aspect->subAspects as $subAspect) {
                $this->subAspectRatings[$subAspect->code] = $this->dynamicStandardService->getSubAspectRating(
                    $templateId,
                    $subAspect->code
                );
            }
        }
    }

    /**
     * Check if template has adjustments
     */
    #[Computed]
    public function hasAdjustments(): bool
    {
        return $this->dynamicStandardService->hasAdjustments($this->template->id);
    }

    /**
     * Validate category weights
     */
    #[Computed]
    public function categoryWeightsValid(): bool
    {
        return array_sum($this->categoryWeights) === 100;
    }

    /**
     * Get total category weight
     */
    #[Computed]
    public function totalCategoryWeight(): int
    {
        return array_sum($this->categoryWeights);
    }

    /**
     * Get Potensi aspects
     */
    #[Computed]
    public function potensiAspects()
    {
        return $this->template->aspects->filter(function ($aspect) {
            return $aspect->categoryType->code === 'potensi';
        });
    }

    /**
     * Get Kompetensi aspects
     */
    #[Computed]
    public function kompetensiAspects()
    {
        return $this->template->aspects->filter(function ($aspect) {
            return $aspect->categoryType->code === 'kompetensi';
        });
    }

    /**
     * Get validation errors
     */
    #[Computed]
    public function validationErrors(): array
    {
        $errors = [];

        // Category weights must sum to 100
        if (!$this->categoryWeightsValid) {
            $errors[] = "Total bobot kategori harus 100% (saat ini: {$this->totalCategoryWeight}%)";
        }

        // Aspect weights per category must sum to 100
        foreach ($this->template->categoryTypes as $category) {
            $categoryAspects = $this->template->aspects->where('category_type_id', $category->id);
            $totalWeight = 0;

            foreach ($categoryAspects as $aspect) {
                $totalWeight += $this->aspectWeights[$aspect->code] ?? 0;
            }

            if ($totalWeight !== 100) {
                $errors[] = "Total bobot aspek {$category->name} harus 100% (saat ini: {$totalWeight}%)";
            }
        }

        // Ratings must be between 1-5
        foreach ($this->aspectRatings as $code => $rating) {
            if ($rating < 1 || $rating > 5) {
                $errors[] = "Rating aspek {$code} harus antara 1-5";
            }
        }

        foreach ($this->subAspectRatings as $code => $rating) {
            if ($rating < 1 || $rating > 5) {
                $errors[] = "Rating sub-aspek {$code} harus antara 1-5";
            }
        }

        return $errors;
    }

    /**
     * Save adjustments to session
     */
    public function saveAdjustments(): void
    {
        // Validate first
        if (! empty($this->validationErrors)) {
            session()->flash('error', 'Terdapat kesalahan validasi. Silakan periksa input Anda.');

            return;
        }

        $adjustments = [
            'category_weights' => $this->categoryWeights,
            'aspect_weights' => $this->aspectWeights,
            'aspect_ratings' => $this->aspectRatings,
            'sub_aspect_ratings' => $this->subAspectRatings,
        ];

        $this->dynamicStandardService->saveBulkAdjustments($this->template->id, $adjustments);

        session()->flash('success', 'Penyesuaian standar berhasil disimpan ke session.');

        // Emit event for other components to refresh
        $this->dispatch('standard-adjusted', templateId: $this->template->id);
    }

    /**
     * Reset adjustments
     */
    public function resetAdjustments(): void
    {
        $this->dynamicStandardService->resetAdjustments($this->template->id);

        // Reload original values
        $this->loadCurrentValues();

        session()->flash('success', 'Standar berhasil dikembalikan ke nilai asli.');

        // Emit event for other components to refresh
        $this->dispatch('standard-reset', templateId: $this->template->id);
    }

    public function render()
    {
        return view('livewire.standard-analysis');
    }
}
```

**File:** `resources/views/livewire/standard-analysis.blade.php`

```blade
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-2xl font-bold">Analisis Standar: {{ $template->name }}</h2>
            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                Sesuaikan bobot dan rating untuk analisis. Perubahan tidak akan mengubah data asli.
            </p>
        </div>

        <div class="flex gap-3">
            @if($this->hasAdjustments)
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-amber-100 text-amber-800 dark:bg-amber-900 dark:text-amber-200">
                    <svg class="w-4 h-4 mr-1.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                    Standar Disesuaikan
                </span>

                <button
                    wire:click="resetAdjustments"
                    wire:confirm="Apakah Anda yakin ingin reset ke standar asli? Semua penyesuaian akan hilang."
                    class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors"
                >
                    Reset ke Standar Asli
                </button>
            @endif

            <button
                wire:click="saveAdjustments"
                @disabled(!$this->categoryWeightsValid)
                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
            >
                Simpan Penyesuaian
            </button>
        </div>
    </div>

    {{-- Validation Errors --}}
    @if(!empty($this->validationErrors))
        <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4">
            <h3 class="text-sm font-semibold text-red-800 dark:text-red-200 mb-2">Kesalahan Validasi:</h3>
            <ul class="list-disc list-inside text-sm text-red-700 dark:text-red-300 space-y-1">
                @foreach($this->validationErrors as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Flash Messages --}}
    @if(session()->has('success'))
        <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4">
            <p class="text-sm text-green-800 dark:text-green-200">{{ session('success') }}</p>
        </div>
    @endif

    @if(session()->has('error'))
        <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4">
            <p class="text-sm text-red-800 dark:text-red-200">{{ session('error') }}</p>
        </div>
    @endif

    {{-- Category Weights --}}
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold mb-4">Bobot Kategori</h3>

        <div class="grid grid-cols-2 gap-6">
            @foreach($template->categoryTypes as $category)
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        {{ $category->name }} (%)
                    </label>
                    <input
                        type="number"
                        wire:model.live="categoryWeights.{{ $category->code }}"
                        min="0"
                        max="100"
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100"
                    />
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                        Asli: {{ $category->weight_percentage }}%
                    </p>
                </div>
            @endforeach
        </div>

        <div class="mt-4 p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
            <p class="text-sm">
                <span class="font-medium">Total:</span>
                <span class="@if($this->categoryWeightsValid) text-green-600 dark:text-green-400 @else text-red-600 dark:text-red-400 @endif font-semibold">
                    {{ $this->totalCategoryWeight }}%
                </span>
                @if(!$this->categoryWeightsValid)
                    <span class="text-red-600 dark:text-red-400 ml-2">(Harus 100%)</span>
                @endif
            </p>
        </div>
    </div>

    {{-- Potensi Aspects --}}
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold mb-4">Aspek Potensi</h3>

        <div class="space-y-6">
            @foreach($this->potensiAspects as $aspect)
                <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                    <div class="flex items-start justify-between mb-4">
                        <div>
                            <h4 class="font-semibold text-gray-900 dark:text-gray-100">{{ $aspect->name }}</h4>
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ $aspect->code }}</p>
                        </div>
                        <div class="text-right">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Bobot (%)
                            </label>
                            <input
                                type="number"
                                wire:model.live="aspectWeights.{{ $aspect->code }}"
                                min="0"
                                max="100"
                                class="w-24 px-3 py-1 border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100"
                            />
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                Asli: {{ $aspect->weight_percentage }}%
                            </p>
                        </div>
                    </div>

                    {{-- Sub-Aspects --}}
                    @if($aspect->subAspects->isNotEmpty())
                        <div class="ml-4 mt-4 space-y-3 border-l-2 border-gray-200 dark:border-gray-700 pl-4">
                            <h5 class="text-sm font-semibold text-gray-700 dark:text-gray-300">Sub-Aspek:</h5>

                            @foreach($aspect->subAspects as $subAspect)
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-gray-700 dark:text-gray-300">
                                        {{ $subAspect->name }}
                                    </span>
                                    <div class="flex items-center gap-3">
                                        <input
                                            type="number"
                                            wire:model.live="subAspectRatings.{{ $subAspect->code }}"
                                            min="1"
                                            max="5"
                                            step="1"
                                            class="w-20 px-3 py-1 border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100"
                                        />
                                        <span class="text-xs text-gray-500 dark:text-gray-400 w-16">
                                            Asli: {{ $subAspect->standard_rating }}
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>

    {{-- Kompetensi Aspects --}}
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold mb-4">Aspek Kompetensi</h3>

        <div class="space-y-4">
            @foreach($this->kompetensiAspects as $aspect)
                <div class="grid grid-cols-3 gap-4 border-b border-gray-200 dark:border-gray-700 pb-4">
                    <div>
                        <h4 class="font-medium text-gray-900 dark:text-gray-100">{{ $aspect->name }}</h4>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $aspect->code }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Bobot (%)
                        </label>
                        <input
                            type="number"
                            wire:model.live="aspectWeights.{{ $aspect->code }}"
                            min="0"
                            max="100"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100"
                        />
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                            Asli: {{ $aspect->weight_percentage }}%
                        </p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Standard Rating (1-5)
                        </label>
                        <input
                            type="number"
                            wire:model.live="aspectRatings.{{ $aspect->code }}"
                            min="1"
                            max="5"
                            step="0.1"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100"
                        />
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                            Asli: {{ number_format($aspect->standard_rating, 2) }}
                        </p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
```

### 2. Integration with StandardPsikometrik & StandardMc

Add toggle to switch between original and adjusted standards.

**Example for StandardPsikometrik.php:**

```php
class StandardPsikometrik extends Component
{
    public AssessmentEvent $event;
    public bool $useAdjustedStandard = false;

    protected $listeners = [
        'standard-adjusted' => '$refresh',
        'standard-reset' => '$refresh',
    ];

    public function mount(int $eventId): void
    {
        $this->event = AssessmentEvent::findOrFail($eventId);
    }

    public function toggleStandardMode(): void
    {
        $this->useAdjustedStandard = !$this->useAdjustedStandard;
    }

    public function render()
    {
        // Pass $useAdjustedStandard to calculations
        return view('livewire.standard-psikometrik', [
            'useAdjustedStandard' => $this->useAdjustedStandard,
        ]);
    }
}
```

**Blade:**

```blade
<div class="mb-6 flex items-center justify-between">
    <div class="flex items-center gap-4">
        <label class="flex items-center gap-2 cursor-pointer">
            <input
                type="checkbox"
                wire:model.live="useAdjustedStandard"
                class="rounded border-gray-300"
            />
            <span class="text-sm font-medium">Gunakan Standar Disesuaikan</span>
        </label>

        @if($useAdjustedStandard)
            <a
                href="{{ route('standard-analysis', $event->template_id) }}"
                class="text-sm text-blue-600 hover:text-blue-700"
            >
                Edit Standar
            </a>
        @endif
    </div>
</div>
```

---

## IMPLEMENTATION STEPS

### Phase 1: Database & Authentication

```bash
# 1. Create migration
php artisan make:migration add_multi_tenancy_to_users_table

# 2. Run migration
php artisan migrate

# 3. Run seeder (or use migrate:fresh --seed)
php artisan db:seed --class=RolesAndPermissionsSeeder

# 4. DatabaseSeeder automatically creates admin user with role
# See: database/seeders/DatabaseSeeder.php
# Admin user: admin@example.com (password from factory)

# 5. To manually assign roles to existing users:
php artisan tinker
>>> $user = User::where('email', 'user@example.com')->first();
>>> $user->assignRole('client');
>>> $user->institution_id = 1;  // Required for client
>>> $user->save();
```

### Phase 2: Middleware & Scopes

```bash
# 1. Create middleware
php artisan make:middleware EnsureUserBelongsToInstitution

# 2. Create scope
mkdir -p app/Models/Scopes
# Create InstitutionScope.php

# 3. Register middleware in bootstrap/app.php

# 4. Apply scope to models
```

### Phase 3: Dynamic Standard Service

```bash
# 1. Create service
mkdir -p app/Services
# Create DynamicStandardService.php

# 2. Update calculation services
# Modify AspectService, CategoryService, FinalAssessmentService

# 3. Test with tinker
php artisan tinker
>>> $service = app(\App\Services\DynamicStandardService::class);
>>> $service->saveCategoryWeight(1, 'potensi', 45);
>>> $service->getCategoryWeight(1, 'potensi'); // Should return 45
>>> $service->resetAdjustments(1);
>>> $service->getCategoryWeight(1, 'potensi'); // Should return original (50)
```

### Phase 4: UI Components

```bash
# 1. Create Livewire component
php artisan make:livewire StandardAnalysis

# 2. Create route
# Add to routes/web.php

# 3. Update StandardPsikometrik & StandardMc
# Add toggle and integration

# 4. Test in browser
```

---

## TESTING

### 1. Multi-Tenancy Tests

**File:** `tests/Feature/MultiTenancyTest.php`

```php
<?php

use App\Models\AssessmentEvent;
use App\Models\Institution;
use App\Models\User;

test('admin can access all institutions', function () {
    $user = User::factory()->create();
    $user->assignRole('admin');

    $institution1 = Institution::factory()->create();
    $institution2 = Institution::factory()->create();

    $event1 = AssessmentEvent::factory()->create(['institution_id' => $institution1->id]);
    $event2 = AssessmentEvent::factory()->create(['institution_id' => $institution2->id]);

    actingAs($user);

    $events = AssessmentEvent::all();
    expect($events)->toHaveCount(2);
});

test('client can only access own institution', function () {
    $institution1 = Institution::factory()->create();
    $institution2 = Institution::factory()->create();

    $user = User::factory()->create(['institution_id' => $institution1->id]);
    $user->assignRole('client');

    $event1 = AssessmentEvent::factory()->create(['institution_id' => $institution1->id]);
    $event2 = AssessmentEvent::factory()->create(['institution_id' => $institution2->id]);

    actingAs($user);

    $events = AssessmentEvent::all();
    expect($events)->toHaveCount(1);
    expect($events->first()->id)->toBe($event1->id);
});

test('client cannot access other institution event', function () {
    $institution1 = Institution::factory()->create();
    $institution2 = Institution::factory()->create();

    $user = User::factory()->create(['institution_id' => $institution1->id]);
    $user->assignRole('client');

    $event = AssessmentEvent::factory()->create(['institution_id' => $institution2->id]);

    actingAs($user)
        ->get(route('events.show', $event))
        ->assertForbidden();
});
```

### 2. Dynamic Standard Tests

**File:** `tests/Unit/DynamicStandardServiceTest.php`

```php
<?php

use App\Models\AssessmentTemplate;
use App\Services\DynamicStandardService;

beforeEach(function () {
    $this->service = app(DynamicStandardService::class);
    $this->template = AssessmentTemplate::factory()->create();
});

test('it can save and retrieve category weight adjustment', function () {
    $this->service->saveCategoryWeight($this->template->id, 'potensi', 45);

    $weight = $this->service->getCategoryWeight($this->template->id, 'potensi');
    expect($weight)->toBe(45);
});

test('it returns original value when no adjustment exists', function () {
    // Assuming original is 50
    $weight = $this->service->getCategoryWeight($this->template->id, 'potensi');
    expect($weight)->toBe(50);
});

test('it can reset adjustments', function () {
    $this->service->saveCategoryWeight($this->template->id, 'potensi', 45);
    $this->service->resetAdjustments($this->template->id);

    $weight = $this->service->getCategoryWeight($this->template->id, 'potensi');
    expect($weight)->toBe(50); // Back to original
});

test('it detects when adjustments exist', function () {
    expect($this->service->hasAdjustments($this->template->id))->toBeFalse();

    $this->service->saveCategoryWeight($this->template->id, 'potensi', 45);

    expect($this->service->hasAdjustments($this->template->id))->toBeTrue();
});
```

---

## SECURITY CONSIDERATIONS

### 1. Permission Checks

Always check permissions before allowing actions:

```php
// In Livewire component
public function mount(int $templateId): void
{
    $this->authorize('analyze standards');

    $this->template = AssessmentTemplate::findOrFail($templateId);

    // Check institution access
    if (!auth()->user()->canAccessInstitution($this->template->institution_id)) {
        abort(403);
    }
}
```

### 2. Validation

Always validate user input:

```php
// Validate ranges
$this->validate([
    'categoryWeights.*' => 'required|integer|min:0|max:100',
    'aspectWeights.*' => 'required|integer|min:0|max:100',
    'aspectRatings.*' => 'required|numeric|min:1|max:5',
    'subAspectRatings.*' => 'required|integer|min:1|max:5',
]);

// Custom validation
if (array_sum($this->categoryWeights) !== 100) {
    throw ValidationException::withMessages([
        'categoryWeights' => 'Total bobot kategori harus 100%',
    ]);
}
```

### 3. Session Security

Session data is per-user and server-side, so it's already secure. No additional security needed.

---

## NOTES

### Session vs Database

**Why Session?**
- ✅ Temporary by nature (perfect for analysis)
- ✅ Per-user isolation
- ✅ No database writes (performance)
- ✅ Auto-cleanup on logout
- ✅ Easy reset

**Trade-offs:**
- ❌ Lost on logout (acceptable for analysis)
- ❌ Not shareable (acceptable for personal analysis)
- ❌ Not persistent (by design)

### Future Enhancements (Optional)

If later you need to save scenarios:

1. Create `standard_analysis_scenarios` table
2. Allow user to "Save Scenario" with name
3. Load saved scenarios
4. Share scenarios with team

But for now, session-based is perfect for the use case!

---

## SUMMARY

### What We're Building

1. **Multi-Tenant SaaS**
   - Role-based access (admin, client)
   - Institution-level data isolation
   - Global scopes for auto-filtering

2. **Dynamic Standard Analysis**
   - Session-based adjustment storage
   - Template-level adjustments
   - Real-time calculation preview
   - Easy reset to original

3. **UI Components**
   - StandardAnalysis component (new)
   - Toggle in StandardPsikometrik & StandardMc
   - Real-time validation
   - User-friendly interface

### Implementation Order

1. ✅ Database migration
2. ✅ Spatie Permission setup
3. ✅ Middleware & Scopes
4. ✅ DynamicStandardService
5. ⏳ UI Components (Next Phase)
6. ⏳ Testing (Next Phase)

---

## IMPLEMENTATION STATUS

### ✅ Phase 1: Completed (2025-01-04)

**1. Database & Migration**
- Migration `add_multi_tenancy_to_users_table` created and executed
- Added: `institution_id`, `is_active`, `last_login_at` to users table

**2. User Model Updates**
- File: `app/Models/User.php`
- Added fillable fields and casts
- Added `institution()` relationship
- Added `isAdmin()` helper method
- Added `canAccessInstitution()` helper method
- Added `scopeActive()` query scope

**3. Roles & Permissions**
- File: `database/seeders/RolesAndPermissionsSeeder.php`
- Created 2 roles:
  - **admin**: Full access to all institutions
  - **client**: Access only to their own institution
- Created 4 permissions:
  - `view all institutions`
  - `manage users`
  - `manage institutions`
  - `analyze standards`
- **DatabaseSeeder** updated to automatically:
  - Run RolesAndPermissionsSeeder first
  - Create admin user (admin@example.com) with admin role
  - Set `is_active = true` for admin user

**4. Multi-Tenant Security**
- File: `app/Models/Scopes/InstitutionScope.php`
- Global scope for auto-filtering by institution_id
- Applied to `AssessmentEvent` model
- Admin users bypass the scope

**5. Middleware**
- File: `app/Http/Middleware/EnsureUserBelongsToInstitution.php`
- Ensures clients have institution_id
- Admin users bypass
- Registered as `institution.access` alias in `bootstrap/app.php`
- **Applied to all protected routes** in `routes/web.php`

**6. Dynamic Standard Service**
- File: `app/Services/DynamicStandardService.php`
- Session-based storage with prefix `standard_adjustment`
- Methods for get/save category weights, aspect weights/ratings, sub-aspect ratings
- Bulk save and reset functionality
- Validation methods
- Returns original values from database when no adjustment exists

### ⏳ Phase 2: Next Steps

**1. Update Calculation Services**
- Add `$useAdjustedStandard` parameter to:
  - `AspectService::calculatePotensiAspect()`
  - `AspectService::calculateKompetensiAspect()`
  - `CategoryService::calculateCategory()`
  - `FinalAssessmentService::calculateFinal()`

**2. UI Components**
- Create `StandardAnalysis` Livewire component
- Add toggle to `StandardPsikometrik` component
- Add toggle to `StandardMc` component

**3. Testing**
- Multi-tenancy tests
- Dynamic standard service tests
- Integration tests

This architecture is clean, secure, and maintainable! 🚀
