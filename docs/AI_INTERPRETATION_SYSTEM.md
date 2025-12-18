# AI-Powered Interpretation System

## 📋 Table of Contents
1. [Overview](#overview)
2. [Business Context](#business-context)
3. [Architecture](#architecture)
4. [AI Integration](#ai-integration)
5. [3-Layer Priority Integration](#3-layer-priority-integration)
6. [Database Schema](#database-schema)
7. [Prompt Engineering](#prompt-engineering)
8. [Implementation Flow](#implementation-flow)
9. [Caching Strategy](#caching-strategy)
10. [Fallback Mechanism](#fallback-mechanism)
11. [API Specification](#api-specification)
12. [Testing & Quality Assurance](#testing--quality-assurance)
13. [Performance Considerations](#performance-considerations)
14. [Future Enhancements](#future-enhancements)

---

## 🎯 Overview

**AI-Powered Interpretation System** adalah evolusi dari template-based interpretation yang menggunakan **Large Language Model (LLM)** untuk menghasilkan interpretasi naratif hasil assessment yang lebih natural, kontekstual, dan profesional.

### Key Features:
- ✅ **AI-Generated**: Interpretasi di-generate oleh GLM-4.6 via z.ai API
- ✅ **Baseline-Aware**: Respects 3-layer priority system (Session → Custom → Quantum)
- ✅ **Context-Rich**: Mempertimbangkan posisi, standar, dan gap analysis
- ✅ **Cached**: Interpretasi di-cache per baseline configuration untuk performance
- ✅ **Fallback-Safe**: Automatic fallback ke template system jika AI gagal
- ✅ **Professional Tone**: Menggunakan bahasa formal untuk konteks rekrutmen instansi

### Why AI vs Template?

| Aspect | Template System (Old) | AI System (New) |
|--------|----------------------|-----------------|
| **Flexibility** | Fixed text per rating | Dynamic, context-aware |
| **Personalization** | Generic statements | Considers position, standards |
| **Maintenance** | Manual template creation | Prompt tuning only |
| **Quality** | Repetitive, robotic | Natural, flowing narrative |
| **Language** | Limited variations | Rich vocabulary |
| **Context** | Rating only | Full assessment context |

---

## 🏢 Business Context

### SPSP as BI System

SPSP adalah **Business Intelligence (BI) system** untuk eksplorasi data assessment, bukan CRUD system. Ini sangat mempengaruhi design AI interpretation:

```
Key Principle:
- Assessment data (individual_rating) = IMMUTABLE (historical)
- Baseline (standard_rating, weights) = VARIABLE (user can explore)
- Interpretation = Function of (DATA × BASELINE)

Implication:
→ SAME participant, DIFFERENT baseline = DIFFERENT interpretation
→ Interpretasi harus di-generate per baseline configuration
→ Caching by baseline_hash (bukan by participant saja)
```

### Use Case Scenarios

#### Scenario 1: Quantum Default Baseline
```
User: Lihat interpretasi participant "WINDA FUJIATI"
Baseline: Quantum Default (standar sistem)
  - Integritas: standard_rating = 3
  - Individual rating: 4
  - Gap: +1 (Melebihi Standar)

AI Interpretation:
"Peserta menunjukkan kemampuan Integritas yang melampaui ekspektasi
dengan konsistensi tinggi dalam menjunjung nilai-nilai kejujuran..."
```

#### Scenario 2: Custom Standard Baseline
```
User: SAME participant, tapi switch ke Custom Standard "Kejaksaan"
Baseline: Custom Standard Kejaksaan
  - Integritas: standard_rating = 5 (lebih tinggi!)
  - Individual rating: 4 (tetap sama)
  - Gap: -1 (Di Bawah Standar)

AI Interpretation:
"Peserta menunjukkan kemampuan Integritas yang cukup baik, namun
masih terdapat area pengembangan untuk memenuhi standar tinggi
yang ditetapkan institusi Kejaksaan..."

SAME DATA, DIFFERENT INTERPRETATION!
```

#### Scenario 3: Session Adjustment (What-If Analysis)
```
User: Adjust sementara rating standar Integritas: 5 → 4
Baseline: Custom Standard + Session Override
  - Integritas: standard_rating = 4 (dari session)
  - Individual rating: 4
  - Gap: 0 (Memenuhi Standar)

AI Interpretation:
"Peserta menunjukkan kemampuan Integritas yang memenuhi standar
dengan pemahaman yang baik terhadap nilai-nilai etika..."
```

### Key Insight:
**Interpretasi HARUS dynamic dan baseline-aware**, bukan static per participant.

---

## 🏗️ Architecture

### System Components

```
┌──────────────────────────────────────────────────────────────┐
│                    User Interface Layer                       │
│  (InterpretationSection.php - Livewire Component)            │
└────────────────┬─────────────────────────────────────────────┘
                 │
                 ↓
┌──────────────────────────────────────────────────────────────┐
│               Service Layer (Business Logic)                  │
│                                                               │
│  ┌────────────────────────────────────────────────────────┐  │
│  │  InterpretationGeneratorService                        │  │
│  │  - generateForDisplay() [AI-powered]                   │  │
│  │  - generateForParticipant() [Template fallback]        │  │
│  └────────────────┬───────────────────────────────────────┘  │
│                   │                                           │
│  ┌────────────────┴───────────────────────────────────────┐  │
│  │  AIInterpretationService (NEW)                         │  │
│  │  - generateInterpretation()                            │  │
│  │  - buildPromptContext()                                │  │
│  │  - parseAIResponse()                                   │  │
│  └────────────────┬───────────────────────────────────────┘  │
│                   │                                           │
└───────────────────┼───────────────────────────────────────────┘
                    │
                    ↓
┌──────────────────────────────────────────────────────────────┐
│              External AI Service (z.ai API)                   │
│                                                               │
│  ┌────────────────────────────────────────────────────────┐  │
│  │  Model: GLM-4.6                                        │  │
│  │  Endpoint: https://api.z.ai/v1/chat/completions       │  │
│  │  Role: Professional Psychologist Interpreter           │  │
│  └────────────────────────────────────────────────────────┘  │
└──────────────────────────────────────────────────────────────┘
                    │
                    ↓
┌──────────────────────────────────────────────────────────────┐
│                   Data Layer                                  │
│                                                               │
│  ┌─────────────────┐  ┌─────────────────┐  ┌──────────────┐ │
│  │ interpretations │  │ participants    │  │ assessments  │ │
│  │ (with baseline_ │  │                 │  │              │ │
│  │  hash tracking) │  │                 │  │              │ │
│  └─────────────────┘  └─────────────────┘  └──────────────┘ │
└──────────────────────────────────────────────────────────────┘
```

### Component Responsibilities

#### 1. InterpretationSection (Livewire Component)
**Location:** `app/Livewire/Pages/IndividualReport/InterpretationSection.php`

**Responsibilities:**
- Handle user interaction (view, regenerate)
- Load participant data
- Display interpretations (Potensi & Kompetensi)
- Listen to baseline change events
- Trigger re-generation when needed

#### 2. InterpretationGeneratorService
**Location:** `app/Services/InterpretationGeneratorService.php`

**Responsibilities:**
- Orchestrate interpretation generation
- Decide: Use AI or Template?
- Manage caching logic
- Handle fallback scenarios
- Save to database

#### 3. AIInterpretationService (NEW)
**Location:** `app/Services/AIInterpretationService.php`

**Responsibilities:**
- Build AI prompt context
- Call z.ai API (GLM-4.6)
- Parse & validate AI response
- Handle API errors
- Retry logic

#### 4. DynamicStandardService (Existing)
**Location:** `app/Services/DynamicStandardService.php`

**Responsibilities:**
- Provide baseline-aware standards (3-layer priority)
- Supply weights, ratings, active status
- Generate baseline_hash for caching

---

## 🤖 AI Integration

### Model Selection: GLM-4.6

**Why GLM-4.6?**
- ✅ Available via z.ai API (user already has access)
- ✅ Strong reasoning capability (important for interpretation)
- ✅ Cost-effective compared to GPT-4/Claude
- ✅ Large context window (can handle full assessment data)
- ✅ Multi-language support (Bahasa Indonesia)

**Performance Benchmarks:**
From user-provided benchmarks:
- AIME 25: 93.9 (Top performer)
- GPQA: 81.0 (Excellent reasoning)
- LiveCodeBench: 82.8 (Strong structured output)
- HLE: 30.4 (Good human-like evaluation)

### API Configuration

```php
// config/ai.php (NEW)
return [
    'provider' => env('AI_PROVIDER', 'zai'),

    'zai' => [
        'api_key' => env('ZAI_API_KEY'),
        'base_url' => env('ZAI_BASE_URL', 'https://api.z.ai/v1'),
        'model' => env('ZAI_MODEL', 'glm-4.6'),
        'timeout' => env('ZAI_TIMEOUT', 30), // seconds
        'max_tokens' => env('ZAI_MAX_TOKENS', 2000),
        'temperature' => env('ZAI_TEMPERATURE', 0.7),
    ],

    'interpretation' => [
        'enabled' => env('AI_INTERPRETATION_ENABLED', true),
        'fallback_to_template' => true,
        'cache_ttl' => 60 * 60 * 24 * 7, // 7 days
        'max_retries' => 3,
        'retry_delay' => 1000, // milliseconds
    ],
];
```

```env
# .env
ZAI_API_KEY=your_api_key_here
ZAI_BASE_URL=https://api.z.ai/v1
ZAI_MODEL=glm-4.6
AI_INTERPRETATION_ENABLED=true
```

### API Request Structure

```php
// Example API call to z.ai
POST https://api.z.ai/v1/chat/completions

Headers:
- Authorization: Bearer {ZAI_API_KEY}
- Content-Type: application/json

Body:
{
  "model": "glm-4.6",
  "messages": [
    {
      "role": "system",
      "content": "Kamu adalah psikolog profesional yang ahli dalam interpretasi assessment competency..."
    },
    {
      "role": "user",
      "content": "{JSON context dengan data assessment}"
    }
  ],
  "temperature": 0.7,
  "max_tokens": 2000,
  "top_p": 1,
  "frequency_penalty": 0,
  "presence_penalty": 0
}

Response:
{
  "id": "chatcmpl-xxx",
  "object": "chat.completion",
  "created": 1234567890,
  "model": "glm-4.6",
  "choices": [
    {
      "index": 0,
      "message": {
        "role": "assistant",
        "content": "{Generated interpretation text}"
      },
      "finish_reason": "stop"
    }
  ],
  "usage": {
    "prompt_tokens": 1500,
    "completion_tokens": 800,
    "total_tokens": 2300
  }
}
```

---

## 🎚️ 3-Layer Priority Integration

AI interpretation system **HARUS respect** 3-layer priority system SPSP:

```
┌─────────────────────────────────────────────────┐
│  LAYER 1: SESSION ADJUSTMENT (Highest Priority) │
│  ↓ User's temporary "what-if" exploration       │
└─────────────────────────────────────────────────┘
                    ↓ (if not exists)
┌─────────────────────────────────────────────────┐
│  LAYER 2: CUSTOM STANDARD (Institution Baseline)│
│  ↓ Saved configuration for specific institution │
└─────────────────────────────────────────────────┘
                    ↓ (if not exists)
┌─────────────────────────────────────────────────┐
│  LAYER 3: QUANTUM DEFAULT (System Baseline)     │
│  ↓ Default system configuration                 │
└─────────────────────────────────────────────────┘
```

### Implementation in AI Context Building

```php
// AIInterpretationService::buildPromptContext()

public function buildPromptContext(Participant $participant, CategoryType $categoryType): array
{
    $templateId = $participant->positionFormation->template_id;

    // Get aspects for this category
    $aspects = $categoryType->aspects()
        ->orderBy('order')
        ->get();

    $aspectsData = [];

    foreach ($aspects as $aspect) {
        // ✅ Respect 3-layer priority for EVERY value

        // Check if aspect is active (Layer 1 → 2 → 3)
        if (!$this->dynamicStandardService->isAspectActive($templateId, $aspect->code)) {
            continue; // Skip inactive aspects
        }

        // Get weight (Layer 1 → 2 → 3)
        $weight = $this->dynamicStandardService->getAspectWeight($templateId, $aspect->code);

        // Get standard rating (Layer 1 → 2 → 3)
        $standardRating = $this->dynamicStandardService->getAspectRating($templateId, $aspect->code);

        // Get individual rating (from historical assessment data - IMMUTABLE)
        $assessment = AspectAssessment::where('participant_id', $participant->id)
            ->where('aspect_id', $aspect->id)
            ->first();

        if (!$assessment) {
            continue;
        }

        $individualRating = (float) $assessment->individual_rating;
        $gap = $individualRating - $standardRating;

        // Determine conclusion based on gap
        $conclusion = $this->getConclusionText($gap);

        $aspectsData[] = [
            'name' => $aspect->name,
            'weight' => $weight,
            'individual_rating' => $individualRating,
            'standard_rating' => $standardRating,
            'gap' => round($gap, 2),
            'conclusion' => $conclusion,
            'is_critical' => $weight >= 10, // Flag high-weight aspects
        ];
    }

    return [
        'participant' => [
            'name' => $participant->name,
            'position' => $participant->positionFormation->name,
            'gender' => $participant->gender,
            'test_number' => $participant->test_number,
        ],
        'baseline_context' => $this->getBaselineContext($templateId),
        'category' => [
            'name' => $categoryType->name,
            'code' => $categoryType->code,
            'weight' => $this->dynamicStandardService->getCategoryWeight($templateId, $categoryType->code),
        ],
        'aspects' => $aspectsData,
        'overall_conclusion' => $this->getOverallConclusion($aspectsData),
    ];
}

private function getBaselineContext(int $templateId): array
{
    // Check which baseline is active (Layer 1 or 2)
    $customStandardId = Session::get("selected_standard.{$templateId}");
    $sessionAdjustments = Session::get("standard_adjustment.{$templateId}", []);

    if (!empty($sessionAdjustments)) {
        return [
            'type' => 'session_adjusted',
            'name' => 'Custom dengan Adjustment Sementara',
            'description' => 'Standar yang disesuaikan untuk eksplorasi what-if analysis',
        ];
    }

    if ($customStandardId) {
        $customStandard = CustomStandard::find($customStandardId);
        return [
            'type' => 'custom_standard',
            'name' => $customStandard->name,
            'description' => $customStandard->description ?? 'Standar khusus institusi',
        ];
    }

    return [
        'type' => 'quantum_default',
        'name' => 'Standar Quantum (Default)',
        'description' => 'Standar umum sistem',
    ];
}
```

### Baseline Hash for Caching

Karena interpretasi tergantung pada baseline configuration, kita perlu **unique identifier** untuk setiap kombinasi:

```php
// DynamicStandardService::getBaselineHash()

public function getBaselineHash(int $templateId): string
{
    $config = [
        'template_id' => $templateId,

        // Layer 1: Session adjustments (if any)
        'session_adjustments' => Session::get("standard_adjustment.{$templateId}", []),

        // Layer 2: Custom standard (if selected)
        'custom_standard_id' => Session::get("selected_standard.{$templateId}"),

        // Include category weights
        'category_weights' => [
            'potensi' => $this->getCategoryWeight($templateId, 'potensi'),
            'kompetensi' => $this->getCategoryWeight($templateId, 'kompetensi'),
        ],

        // Include all aspect weights & ratings for active aspects
        'aspect_configs' => $this->getActiveAspectConfigs($templateId),
    ];

    return md5(json_encode($config));
}

private function getActiveAspectConfigs(int $templateId): array
{
    $template = AssessmentTemplate::with('aspects')->find($templateId);
    $configs = [];

    foreach ($template->aspects as $aspect) {
        if (!$this->isAspectActive($templateId, $aspect->code)) {
            continue;
        }

        $configs[$aspect->code] = [
            'weight' => $this->getAspectWeight($templateId, $aspect->code),
            'rating' => $this->getAspectRating($templateId, $aspect->code),
            'active' => true,
        ];
    }

    return $configs;
}
```

---

## 🗄️ Database Schema

### Updated `interpretations` Table

```sql
ALTER TABLE interpretations
ADD COLUMN baseline_type VARCHAR(50) AFTER category_type_id,
ADD COLUMN baseline_id INT NULL AFTER baseline_type,
ADD COLUMN baseline_hash VARCHAR(64) AFTER baseline_id,
ADD COLUMN generated_by VARCHAR(20) DEFAULT 'template' AFTER baseline_hash,
ADD COLUMN ai_model VARCHAR(50) NULL AFTER generated_by,
ADD COLUMN generation_metadata JSON NULL AFTER ai_model,
ADD INDEX idx_participant_baseline (participant_id, baseline_hash),
ADD INDEX idx_baseline_hash (baseline_hash);
```

**Final Schema:**
```sql
CREATE TABLE interpretations (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    participant_id BIGINT NOT NULL,
    event_id BIGINT NOT NULL,
    category_type_id BIGINT NOT NULL,

    -- Baseline tracking (NEW)
    baseline_type VARCHAR(50),          -- 'quantum', 'custom', 'session'
    baseline_id INT NULL,               -- custom_standard.id (if applicable)
    baseline_hash VARCHAR(64),          -- MD5 hash of full baseline config

    -- Generation metadata (NEW)
    generated_by VARCHAR(20) DEFAULT 'template',  -- 'template' or 'ai'
    ai_model VARCHAR(50) NULL,          -- 'glm-4.6', 'gpt-4', etc
    generation_metadata JSON NULL,      -- Store prompt tokens, cost, etc

    -- Content
    interpretation_text LONGTEXT,

    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    -- Foreign keys
    FOREIGN KEY (participant_id) REFERENCES participants(id) ON DELETE CASCADE,
    FOREIGN KEY (event_id) REFERENCES assessment_events(id) ON DELETE CASCADE,
    FOREIGN KEY (category_type_id) REFERENCES category_types(id) ON DELETE CASCADE,

    -- Indexes
    INDEX idx_participant_baseline (participant_id, baseline_hash),
    INDEX idx_baseline_hash (baseline_hash),
    INDEX idx_generated_by (generated_by)
);
```

### Migration File

```php
// database/migrations/2025_01_xx_add_ai_fields_to_interpretations_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('interpretations', function (Blueprint $table) {
            // Baseline tracking
            $table->string('baseline_type', 50)->after('category_type_id')->nullable();
            $table->integer('baseline_id')->after('baseline_type')->nullable();
            $table->string('baseline_hash', 64)->after('baseline_id')->nullable();

            // Generation metadata
            $table->string('generated_by', 20)->after('baseline_hash')->default('template');
            $table->string('ai_model', 50)->after('generated_by')->nullable();
            $table->json('generation_metadata')->after('ai_model')->nullable();

            // Indexes
            $table->index(['participant_id', 'baseline_hash'], 'idx_participant_baseline');
            $table->index('baseline_hash', 'idx_baseline_hash');
            $table->index('generated_by', 'idx_generated_by');
        });
    }

    public function down(): void
    {
        Schema::table('interpretations', function (Blueprint $table) {
            $table->dropIndex('idx_participant_baseline');
            $table->dropIndex('idx_baseline_hash');
            $table->dropIndex('idx_generated_by');

            $table->dropColumn([
                'baseline_type',
                'baseline_id',
                'baseline_hash',
                'generated_by',
                'ai_model',
                'generation_metadata',
            ]);
        });
    }
};
```

### Updated Interpretation Model

```php
// app/Models/Interpretation.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Interpretation extends Model
{
    protected $fillable = [
        'participant_id',
        'event_id',
        'category_type_id',
        'baseline_type',
        'baseline_id',
        'baseline_hash',
        'generated_by',
        'ai_model',
        'generation_metadata',
        'interpretation_text',
    ];

    protected function casts(): array
    {
        return [
            'generation_metadata' => 'array',
        ];
    }

    public function participant(): BelongsTo
    {
        return $this->belongsTo(Participant::class);
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(AssessmentEvent::class, 'event_id');
    }

    public function categoryType(): BelongsTo
    {
        return $this->belongsTo(CategoryType::class);
    }

    // Scopes
    public function scopeAiGenerated($query)
    {
        return $query->where('generated_by', 'ai');
    }

    public function scopeTemplateGenerated($query)
    {
        return $query->where('generated_by', 'template');
    }

    public function scopeForBaseline($query, string $baselineHash)
    {
        return $query->where('baseline_hash', $baselineHash);
    }

    // Helpers
    public function isAiGenerated(): bool
    {
        return $this->generated_by === 'ai';
    }

    public function getTokensUsed(): ?int
    {
        return $this->generation_metadata['tokens_used'] ?? null;
    }

    public function getGenerationCost(): ?float
    {
        return $this->generation_metadata['estimated_cost'] ?? null;
    }
}
```

---

## 📝 Prompt Engineering

### System Prompt (Persona)

```text
Kamu adalah psikolog profesional yang ahli dalam interpretasi assessment competency untuk rekrutmen instansi pemerintah Indonesia.

Keahlian kamu:
- Menganalisis hasil assessment psikologi berdasarkan metode Gap Analysis
- Mengidentifikasi kekuatan dan area pengembangan kandidat
- Memberikan rekomendasi konstruktif untuk pengembangan kompetensi
- Menulis laporan interpretasi dengan bahasa formal dan profesional

Prinsip kerja kamu:
1. Objektif: Berdasarkan data assessment, bukan asumsi subjektif
2. Konstruktif: Fokus pada pengembangan, bukan kritik negatif
3. Kontekstual: Mempertimbangkan posisi dan standar institusi
4. Professional: Menggunakan bahasa baku Bahasa Indonesia yang sesuai untuk laporan formal
5. Holistik: Melihat kombinasi berbagai aspek, bukan aspek individual saja

Tugas kamu:
Membuat interpretasi naratif hasil assessment dalam format paragraf yang mengalir (flowing narrative), bukan bullet points.
```

### User Prompt Template (Context)

```json
{
  "task": "Buatkan interpretasi naratif untuk hasil assessment kategori {CATEGORY_NAME} dari participant berikut.",

  "participant_info": {
    "name": "{PARTICIPANT_NAME}",
    "position": "{POSITION_NAME}",
    "gender": "{GENDER}",
    "test_number": "{TEST_NUMBER}"
  },

  "baseline_context": {
    "type": "{BASELINE_TYPE}",
    "name": "{BASELINE_NAME}",
    "description": "{BASELINE_DESCRIPTION}"
  },

  "category_info": {
    "name": "{CATEGORY_NAME}",
    "code": "{CATEGORY_CODE}",
    "weight_percentage": {CATEGORY_WEIGHT}
  },

  "assessment_results": {
    "overall_conclusion": "{OVERALL_CONCLUSION}",
    "overall_percentage": {OVERALL_PERCENTAGE},
    "aspects": [
      {
        "name": "Integritas",
        "weight_percentage": 15,
        "individual_rating": 4.0,
        "standard_rating": 5.0,
        "gap": -1.0,
        "conclusion": "Di Bawah Standar",
        "is_critical": true
      },
      {
        "name": "Kepemimpinan",
        "weight_percentage": 12,
        "individual_rating": 4.5,
        "standard_rating": 4.0,
        "gap": 0.5,
        "conclusion": "Melebihi Standar",
        "is_critical": true
      }
      // ... more aspects
    ]
  },

  "instructions": {
    "structure": {
      "format": "3 paragraphs",
      "paragraph_1": "Gambaran umum performa kategori ini dan highlight 2-3 kekuatan utama (aspects dengan gap positif/netral dan weight tinggi)",
      "paragraph_2": "Identifikasi area pengembangan (aspects dengan gap negatif, terutama yang critical/high-weight)",
      "paragraph_3": "Kesimpulan holistik dan rekomendasi pengembangan yang konstruktif"
    },

    "style_guidelines": {
      "tone": "formal_professional",
      "language": "bahasa_indonesia_baku",
      "avoid": [
        "Jangan menyebutkan angka rating secara eksplisit (gunakan deskripsi kualitatif)",
        "Jangan menggunakan bullet points atau numbering",
        "Jangan terlalu teknis atau menggunakan jargon psikologi yang sulit dipahami",
        "Jangan menggunakan kata 'namun' atau 'tetapi' berlebihan",
        "Jangan membuat interpretasi spekulatif tanpa data pendukung"
      ],
      "do": [
        "Gunakan kata transisi yang smooth antar kalimat",
        "Sebutkan nama aspek secara eksplisit untuk clarity",
        "Berikan konteks mengapa aspek tertentu penting untuk posisi ini",
        "Gunakan bahasa yang konstruktif dan development-oriented",
        "Pertahankan objective tone (hindari emotional language)"
      ]
    },

    "terminology": {
      "individual_vs_standard": "Jangan sebut 'individual rating 4.0 vs standard 5.0', tapi gunakan frasa seperti 'masih terdapat ruang pengembangan untuk mencapai standar tinggi yang ditetapkan'",
      "gap_descriptions": {
        "positive_gap": "melampaui ekspektasi / melebihi standar / menjadi kekuatan utama",
        "zero_gap": "memenuhi standar / sesuai ekspektasi / mencukupi kebutuhan",
        "negative_gap": "memerlukan pengembangan / terdapat area peningkatan / perlu perhatian khusus"
      }
    }
  },

  "context_notes": {
    "baseline_explanation": "Interpretasi ini dibuat berdasarkan {BASELINE_NAME}. Standar penilaian mengacu pada ekspektasi institusi untuk posisi {POSITION_NAME}.",
    "weight_importance": "Aspek dengan bobot tinggi (≥10%) adalah aspek critical yang memiliki dampak signifikan terhadap kesimpulan akhir.",
    "holistic_view": "Evaluasi mempertimbangkan kombinasi seluruh aspek, bukan aspek individual saja. Kandidat yang memenuhi standar di mayoritas aspek dapat mengkompensasi area yang lebih lemah."
  },

  "output_format": {
    "return_as": "plain_text",
    "paragraph_separator": "\\n\\n",
    "max_length": "800-1200 words",
    "min_length": "500 words"
  }
}
```

### Example AI Response (Expected Output)

```text
Peserta menunjukkan kemampuan kompetensi yang memenuhi standar untuk posisi Jaksa Penuntut Umum dengan persentase pencapaian 87.5% berdasarkan Standar Khusus Kejaksaan 2025. Dalam aspek Kepemimpinan, peserta menunjukkan kinerja yang melampaui ekspektasi dengan kemampuan untuk memimpin tim secara efektif, mengambil keputusan strategis, dan menginspirasi rekan kerja. Kemampuan Kerjasama Tim juga menjadi kekuatan yang menonjol, dimana peserta mampu berkolaborasi secara produktif dan membangun sinergi dalam lingkungan kerja. Kedua aspek ini menjadi modal penting dalam menjalankan tugas sebagai jaksa yang memerlukan koordinasi intensif dengan berbagai pihak.

Namun demikian, terdapat area yang memerlukan perhatian khusus, terutama dalam aspek Integritas yang merupakan fondasi krusial bagi posisi penegak hukum. Meskipun peserta menunjukkan pemahaman yang baik terhadap nilai-nilai kejujuran dan etika, masih terdapat ruang pengembangan untuk mencapai standar tinggi yang ditetapkan institusi Kejaksaan. Aspek Orientasi Pelayanan juga memerlukan penguatan, khususnya dalam hal responsivitas dan empati terhadap kebutuhan masyarakat yang dilayani. Pengembangan kedua aspek ini sangat penting mengingat peran jaksa sebagai pelayan publik yang harus menjunjung tinggi integritas dan dedikasi terhadap kepentingan masyarakat.

Secara keseluruhan, peserta memiliki potensi yang baik untuk posisi Jaksa Penuntut Umum dengan kombinasi kemampuan kepemimpinan yang kuat dan kerjasama tim yang solid. Untuk memaksimalkan kesiapan, disarankan adanya program pembinaan yang fokus pada penguatan integritas melalui mentoring dengan jaksa senior dan pendalaman pemahaman tentang etika profesi hukum. Pengembangan orientasi pelayanan dapat dilakukan melalui penugasan di unit layanan publik dan pelatihan customer service excellence. Dengan pengembangan pada kedua area tersebut, peserta akan lebih siap menjalankan tanggung jawab sebagai penegak hukum yang tidak hanya kompeten secara teknis, tetapi juga memiliki integritas dan dedikasi tinggi dalam melayani masyarakat.
```

### Prompt Variations

#### For "Di Atas Standar" Overall Conclusion
```json
{
  "context_notes": {
    "performance_level": "exceptional",
    "emphasis": "Fokus pada bagaimana kekuatan ini dapat dioptimalkan dan dibagikan ke rekan kerja (mentorship potential)"
  }
}
```

#### For "Di Bawah Standar" Overall Conclusion
```json
{
  "context_notes": {
    "performance_level": "below_expectations",
    "emphasis": "Fokus pada action plan konkret untuk pengembangan, bukan hanya identifikasi weakness. Tetap konstruktif dan tidak demotivating."
  }
}
```

#### For High-Stakes Position (e.g., Jaksa, Hakim)
```json
{
  "position_context": {
    "criticality": "high",
    "key_aspects": ["Integritas", "Kepemimpinan", "Pengambilan Keputusan"],
    "tone_adjustment": "Lebih tegas pada aspek integritas dan etika, mengingat dampak sosial dari posisi ini"
  }
}
```

---

## 🔄 Implementation Flow

### Complete Flow Diagram

```
┌─────────────────────────────────────────────────────────────┐
│ 1. USER REQUEST                                              │
│    User opens Individual Report page for participant        │
└────────────────┬────────────────────────────────────────────┘
                 │
                 ↓
┌─────────────────────────────────────────────────────────────┐
│ 2. COMPONENT MOUNT                                           │
│    InterpretationSection.php::mount()                        │
│    - Load participant with relations                         │
│    - Call loadInterpretations()                              │
└────────────────┬────────────────────────────────────────────┘
                 │
                 ↓
┌─────────────────────────────────────────────────────────────┐
│ 3. CHECK CACHE (Component Level)                            │
│    potensiInterpretationCache !== null?                      │
│    ├─ YES → Return cached (instant)                          │
│    └─ NO  → Continue to Service Layer                        │
└────────────────┬────────────────────────────────────────────┘
                 │
                 ↓
┌─────────────────────────────────────────────────────────────┐
│ 4. SERVICE ORCHESTRATION                                     │
│    InterpretationGeneratorService::generateForDisplay()      │
│    - Loop each category (Potensi, Kompetensi)               │
│    - Call generateCategoryInterpretationForDisplay()         │
└────────────────┬────────────────────────────────────────────┘
                 │
                 ↓
┌─────────────────────────────────────────────────────────────┐
│ 5. BUILD BASELINE HASH                                       │
│    DynamicStandardService::getBaselineHash()                 │
│    - Collect all weights (3-layer priority)                  │
│    - Collect all ratings (3-layer priority)                  │
│    - Collect active status (3-layer priority)                │
│    - MD5 hash of configuration                               │
└────────────────┬────────────────────────────────────────────┘
                 │
                 ↓
┌─────────────────────────────────────────────────────────────┐
│ 6. CHECK DATABASE CACHE                                      │
│    Query: interpretations WHERE participant_id = ?           │
│           AND category_type_id = ?                           │
│           AND baseline_hash = ?                              │
│    ├─ FOUND → Return interpretation_text                     │
│    └─ NOT FOUND → Continue to generation                     │
└────────────────┬────────────────────────────────────────────┘
                 │
                 ↓
┌─────────────────────────────────────────────────────────────┐
│ 7. CHECK AI ENABLED                                          │
│    config('ai.interpretation.enabled') === true?             │
│    ├─ YES → Try AI Generation (Step 8)                       │
│    └─ NO  → Fallback to Template (Step 12)                   │
└────────────────┬────────────────────────────────────────────┘
                 │
                 ↓
┌─────────────────────────────────────────────────────────────┐
│ 8. BUILD AI CONTEXT                                          │
│    AIInterpretationService::buildPromptContext()             │
│    - Collect participant info                                │
│    - Get baseline context                                    │
│    - Loop active aspects (respect 3-layer priority)          │
│    - Calculate gaps and conclusions                          │
│    - Build structured JSON context                           │
└────────────────┬────────────────────────────────────────────┘
                 │
                 ↓
┌─────────────────────────────────────────────────────────────┐
│ 9. CALL AI API                                               │
│    AIInterpretationService::callZaiAPI()                     │
│    - Build request payload                                   │
│    - POST to z.ai/v1/chat/completions                        │
│    - Model: GLM-4.6                                          │
│    - Timeout: 30s                                            │
│    - Max retries: 3                                          │
└────────────────┬────────────────────────────────────────────┘
                 │
                 ├─ SUCCESS → Continue to Step 10
                 │
                 └─ ERROR → Fallback to Template (Step 12)

                 ↓
┌─────────────────────────────────────────────────────────────┐
│ 10. PARSE & VALIDATE AI RESPONSE                            │
│     AIInterpretationService::parseAIResponse()               │
│     - Extract interpretation text                            │
│     - Validate: not empty, reasonable length                 │
│     - Sanitize output                                        │
│     ├─ VALID → Continue to Step 11                           │
│     └─ INVALID → Fallback to Template (Step 12)              │
└────────────────┬────────────────────────────────────────────┘
                 │
                 ↓
┌─────────────────────────────────────────────────────────────┐
│ 11. SAVE TO DATABASE (AI-Generated)                         │
│     Interpretation::create([                                 │
│       'participant_id' => ...,                               │
│       'category_type_id' => ...,                             │
│       'baseline_hash' => ...,                                │
│       'generated_by' => 'ai',                                │
│       'ai_model' => 'glm-4.6',                               │
│       'interpretation_text' => ...,                          │
│       'generation_metadata' => [...]                         │
│     ])                                                       │
│     → Return interpretation_text                             │
└────────────────┬────────────────────────────────────────────┘
                 │
                 └─────────────────┐
                                   │
┌──────────────────────────────────┼──────────────────────────┐
│ 12. FALLBACK: TEMPLATE GENERATION│ (if AI fails/disabled)   │
│     InterpretationTemplateService::generateFromTemplate()    │
│     - Use existing template system                           │
│     - Build paragraphs from templates                        │
│     - Save with generated_by = 'template'                    │
│     → Return interpretation_text                             │
└────────────────┬────────────────────────────────────────────┘
                 │
                 ↓
┌─────────────────────────────────────────────────────────────┐
│ 13. RETURN TO COMPONENT                                      │
│     InterpretationSection::$potensiInterpretation = result   │
│     InterpretationSection::$kompetensiInterpretation = result│
│     Cache in component for this request lifecycle            │
└────────────────┬────────────────────────────────────────────┘
                 │
                 ↓
┌─────────────────────────────────────────────────────────────┐
│ 14. RENDER VIEW                                              │
│     interpretation-section.blade.php                         │
│     - Display Potensi interpretation                         │
│     - Display Kompetensi interpretation                      │
│     - Show regenerate button (if debug/admin)                │
└─────────────────────────────────────────────────────────────┘
```

### Error Handling Flow

```
API Call Failed
    ↓
Retry Logic (max 3 times)
    ├─ Retry 1: Wait 1s → Try again
    ├─ Retry 2: Wait 2s → Try again
    └─ Retry 3: Wait 3s → Try again
        ↓
Still Failed?
    ├─ Log error with context
    ├─ Dispatch event: 'ai-interpretation-failed'
    └─ Fallback to Template System
        ↓
    Template Generation Success?
        ├─ YES → Return template-based interpretation
        └─ NO  → Return generic fallback text
```

### Baseline Change Event Flow

```
User Changes Baseline
    ↓
Event Dispatched: 'standard-switched' or 'standard-adjusted'
    ↓
InterpretationSection listens to event
    ↓
Call: handleStandardSwitch() / handleStandardAdjust()
    ↓
Clear component cache
    ↓
Call: loadInterpretations()
    ↓
New baseline_hash calculated
    ↓
Check database with new hash
    ├─ Found → Return cached (fast!)
    └─ Not found → Generate new (AI or Template)
        ↓
    Display updated interpretation
```

---

## 💾 Caching Strategy

### Multi-Level Caching

```
┌─────────────────────────────────────────────────┐
│ Level 1: Component-Level Cache (Request Scope)  │
│ TTL: Current request only                        │
│ Purpose: Prevent duplicate calls within 1 render│
│ Implementation: $potensiInterpretationCache      │
└─────────────────────────────────────────────────┘
                    ↓ (if cache miss)
┌─────────────────────────────────────────────────┐
│ Level 2: Database Cache (Per Baseline)          │
│ TTL: Infinite (manual invalidation)              │
│ Purpose: Avoid re-calling AI for same config    │
│ Key: (participant_id, category_id, baseline_hash│
│ Implementation: interpretations table            │
└─────────────────────────────────────────────────┘
                    ↓ (if cache miss)
┌─────────────────────────────────────────────────┐
│ Level 3: Generate New (AI or Template)          │
│ Cost: API call ($$$) or Computation (CPU)       │
│ Save to Level 2 after generation                │
└─────────────────────────────────────────────────┘
```

### Cache Invalidation Rules

#### Automatic Invalidation
```php
// When baseline changes, baseline_hash changes automatically
// Example:

// User on Quantum Default
$hash1 = md5(json_encode(['template' => 1, 'custom_std' => null, 'session' => []]));
// Interpretation cached with hash1

// User switches to Custom Standard
$hash2 = md5(json_encode(['template' => 1, 'custom_std' => 5, 'session' => []]));
// hash1 ≠ hash2 → Cache miss → Generate new

// User adjusts session
$hash3 = md5(json_encode(['template' => 1, 'custom_std' => 5, 'session' => ['integritas' => 4]]));
// hash2 ≠ hash3 → Cache miss → Generate new
```

#### Manual Invalidation
```php
// Regenerate button (admin/debug only)
public function regenerate(): void
{
    $templateId = $this->participant->positionFormation->template_id;
    $baselineHash = app(DynamicStandardService::class)->getBaselineHash($templateId);

    // Delete existing interpretations for this participant + baseline
    Interpretation::where('participant_id', $this->participant->id)
        ->where('baseline_hash', $baselineHash)
        ->delete();

    // Clear component cache
    $this->clearCache();

    // Regenerate
    $this->loadInterpretations();

    // Notify user
    $this->dispatch('interpretation-regenerated');
}
```

### Cache Warming Strategy (Optional)

For frequently accessed participants:

```php
// Artisan command: php artisan interpretation:warm-cache

namespace App\Console\Commands;

use App\Models\Participant;
use App\Services\InterpretationGeneratorService;
use Illuminate\Console\Command;

class WarmInterpretationCache extends Command
{
    protected $signature = 'interpretation:warm-cache
                            {event_code : Event code to warm}
                            {--baseline=quantum : Baseline to use}
                            {--limit=100 : Max participants to process}';

    protected $description = 'Pre-generate interpretations for top participants';

    public function handle(InterpretationGeneratorService $service): int
    {
        $eventCode = $this->argument('event_code');
        $baseline = $this->option('baseline');
        $limit = $this->option('limit');

        // Get top N participants by ranking
        $participants = Participant::whereHas('assessmentEvent', fn($q) =>
            $q->where('code', $eventCode)
        )
        ->orderBy('some_ranking_column')
        ->limit($limit)
        ->get();

        $this->info("Warming cache for {$participants->count()} participants...");

        $bar = $this->output->createProgressBar($participants->count());

        foreach ($participants as $participant) {
            try {
                // This will generate and cache
                $service->generateForDisplay($participant);
                $bar->advance();
            } catch (\Exception $e) {
                $this->error("Failed for participant {$participant->id}: {$e->getMessage()}");
            }
        }

        $bar->finish();
        $this->newLine();
        $this->info('Cache warming completed!');

        return Command::SUCCESS;
    }
}
```

---

## 🛡️ Fallback Mechanism

### Fallback Priority Chain

```
1. AI Generation (Preferred)
   ├─ Try call z.ai API
   ├─ Retry on transient errors (3x)
   └─ If persistent error → Fallback to #2

2. Template System (Existing)
   ├─ Use InterpretationTemplateService
   ├─ Generate from templates
   └─ If template not found → Fallback to #3

3. Generic Default (Last Resort)
   └─ Return hardcoded safe text
```

### Implementation

```php
// InterpretationGeneratorService::generateCategoryInterpretationForDisplay()

protected function generateCategoryInterpretationForDisplay(
    Participant $participant,
    CategoryType $categoryType
): string {
    $templateId = $participant->positionFormation->template_id;
    $baselineHash = $this->dynamicService->getBaselineHash($templateId);

    // Check database cache
    $cached = Interpretation::where('participant_id', $participant->id)
        ->where('category_type_id', $categoryType->id)
        ->where('baseline_hash', $baselineHash)
        ->first();

    if ($cached) {
        Log::info('Interpretation cache hit', [
            'participant_id' => $participant->id,
            'category' => $categoryType->code,
            'generated_by' => $cached->generated_by,
        ]);

        return $cached->interpretation_text;
    }

    // Cache miss - generate new
    Log::info('Interpretation cache miss - generating new', [
        'participant_id' => $participant->id,
        'category' => $categoryType->code,
        'baseline_hash' => $baselineHash,
    ]);

    // Try AI generation (if enabled)
    if (config('ai.interpretation.enabled', true)) {
        try {
            $aiInterpretation = $this->generateWithAI($participant, $categoryType, $baselineHash);

            if ($aiInterpretation) {
                Log::info('AI generation successful', [
                    'participant_id' => $participant->id,
                    'category' => $categoryType->code,
                ]);

                return $aiInterpretation;
            }
        } catch (\Exception $e) {
            Log::error('AI generation failed, falling back to template', [
                'participant_id' => $participant->id,
                'category' => $categoryType->code,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Dispatch event for monitoring
            event(new AIInterpretationFailed($participant, $categoryType, $e));
        }
    }

    // Fallback to template system
    Log::info('Using template fallback', [
        'participant_id' => $participant->id,
        'category' => $categoryType->code,
    ]);

    return $this->generateWithTemplate($participant, $categoryType, $baselineHash);
}

protected function generateWithAI(
    Participant $participant,
    CategoryType $categoryType,
    string $baselineHash
): ?string {
    $aiService = app(AIInterpretationService::class);

    // Generate with AI
    $result = $aiService->generateInterpretation($participant, $categoryType);

    if (!$result['success']) {
        return null;
    }

    // Save to database
    $interpretation = Interpretation::create([
        'participant_id' => $participant->id,
        'event_id' => $participant->event_id,
        'category_type_id' => $categoryType->id,
        'baseline_type' => $result['baseline_type'],
        'baseline_id' => $result['baseline_id'],
        'baseline_hash' => $baselineHash,
        'generated_by' => 'ai',
        'ai_model' => config('ai.zai.model'),
        'generation_metadata' => $result['metadata'],
        'interpretation_text' => $result['interpretation'],
    ]);

    return $interpretation->interpretation_text;
}

protected function generateWithTemplate(
    Participant $participant,
    CategoryType $categoryType,
    string $baselineHash
): string {
    // Use existing template system
    $interpretation = $this->buildCategoryInterpretationFromTemplate(
        $participant,
        $categoryType
    );

    // Save to database
    Interpretation::create([
        'participant_id' => $participant->id,
        'event_id' => $participant->event_id,
        'category_type_id' => $categoryType->id,
        'baseline_hash' => $baselineHash,
        'generated_by' => 'template',
        'ai_model' => null,
        'interpretation_text' => $interpretation,
    ]);

    return $interpretation;
}
```

### Monitoring & Alerts

```php
// app/Events/AIInterpretationFailed.php

namespace App\Events;

use App\Models\CategoryType;
use App\Models\Participant;
use Illuminate\Foundation\Events\Dispatchable;

class AIInterpretationFailed
{
    use Dispatchable;

    public function __construct(
        public Participant $participant,
        public CategoryType $categoryType,
        public \Exception $exception
    ) {}
}

// app/Listeners/NotifyAdminOfAIFailure.php

namespace App\Listeners;

use App\Events\AIInterpretationFailed;
use Illuminate\Support\Facades\Log;

class NotifyAdminOfAIFailure
{
    public function handle(AIInterpretationFailed $event): void
    {
        Log::channel('slack')->critical('AI Interpretation Failed', [
            'participant_id' => $event->participant->id,
            'participant_name' => $event->participant->name,
            'category' => $event->categoryType->code,
            'error' => $event->exception->getMessage(),
        ]);

        // Optionally: Send email to admin
        // Mail::to(config('mail.admin'))->send(new AIFailureNotification($event));
    }
}

// Register in EventServiceProvider
protected $listen = [
    AIInterpretationFailed::class => [
        NotifyAdminOfAIFailure::class,
    ],
];
```

---

## 📡 API Specification

### z.ai API Integration

#### HTTP Client Setup

```php
// app/Services/Http/ZaiClient.php

namespace App\Services\Http;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

class ZaiClient
{
    protected function client(): PendingRequest
    {
        return Http::withHeaders([
            'Authorization' => 'Bearer ' . config('ai.zai.api_key'),
            'Content-Type' => 'application/json',
        ])
        ->baseUrl(config('ai.zai.base_url'))
        ->timeout(config('ai.zai.timeout', 30))
        ->retry(
            times: config('ai.interpretation.max_retries', 3),
            sleep: config('ai.interpretation.retry_delay', 1000),
            when: fn($exception) => $this->shouldRetry($exception)
        );
    }

    protected function shouldRetry($exception): bool
    {
        // Retry on network errors or 5xx server errors
        if ($exception instanceof \Illuminate\Http\Client\ConnectionException) {
            return true;
        }

        if ($exception instanceof \Illuminate\Http\Client\RequestException) {
            $statusCode = $exception->response->status();
            return $statusCode >= 500 || $statusCode === 429; // Server error or rate limit
        }

        return false;
    }

    public function chatCompletion(array $messages, array $options = []): array
    {
        $payload = [
            'model' => config('ai.zai.model'),
            'messages' => $messages,
            'temperature' => $options['temperature'] ?? config('ai.zai.temperature', 0.7),
            'max_tokens' => $options['max_tokens'] ?? config('ai.zai.max_tokens', 2000),
            'top_p' => $options['top_p'] ?? 1,
            'frequency_penalty' => $options['frequency_penalty'] ?? 0,
            'presence_penalty' => $options['presence_penalty'] ?? 0,
        ];

        $response = $this->client()->post('/chat/completions', $payload);

        if ($response->failed()) {
            throw new \Exception(
                "Z.ai API request failed: " . $response->body(),
                $response->status()
            );
        }

        return $response->json();
    }
}
```

#### AIInterpretationService Implementation

```php
// app/Services/AIInterpretationService.php

namespace App\Services;

use App\Models\CategoryType;
use App\Models\Participant;
use App\Services\Http\ZaiClient;
use Illuminate\Support\Facades\Log;

class AIInterpretationService
{
    public function __construct(
        protected ZaiClient $zaiClient,
        protected DynamicStandardService $dynamicService
    ) {}

    /**
     * Generate interpretation using AI
     *
     * @return array ['success' => bool, 'interpretation' => string|null, 'metadata' => array]
     */
    public function generateInterpretation(
        Participant $participant,
        CategoryType $categoryType
    ): array {
        try {
            // Build context
            $context = $this->buildPromptContext($participant, $categoryType);

            // Build messages
            $messages = [
                [
                    'role' => 'system',
                    'content' => $this->getSystemPrompt(),
                ],
                [
                    'role' => 'user',
                    'content' => json_encode($context, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
            ];

            // Call API
            $startTime = microtime(true);
            $response = $this->zaiClient->chatCompletion($messages);
            $duration = microtime(true) - $startTime;

            // Extract interpretation
            $interpretation = $response['choices'][0]['message']['content'] ?? null;

            if (!$interpretation) {
                throw new \Exception('No content in AI response');
            }

            // Validate & sanitize
            $interpretation = $this->sanitizeOutput($interpretation);
            $this->validateOutput($interpretation);

            // Build metadata
            $metadata = [
                'model' => $response['model'] ?? config('ai.zai.model'),
                'tokens_used' => $response['usage']['total_tokens'] ?? null,
                'prompt_tokens' => $response['usage']['prompt_tokens'] ?? null,
                'completion_tokens' => $response['usage']['completion_tokens'] ?? null,
                'duration_seconds' => round($duration, 2),
                'estimated_cost' => $this->estimateCost($response['usage'] ?? []),
                'generated_at' => now()->toDateTimeString(),
            ];

            Log::info('AI interpretation generated successfully', [
                'participant_id' => $participant->id,
                'category' => $categoryType->code,
                'tokens' => $metadata['tokens_used'],
                'duration' => $metadata['duration_seconds'],
            ]);

            return [
                'success' => true,
                'interpretation' => $interpretation,
                'metadata' => $metadata,
                'baseline_type' => $context['baseline_context']['type'],
                'baseline_id' => $this->getBaselineId($context['baseline_context']['type'], $participant),
            ];

        } catch (\Exception $e) {
            Log::error('AI interpretation generation failed', [
                'participant_id' => $participant->id,
                'category' => $categoryType->code,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'interpretation' => null,
                'metadata' => [
                    'error' => $e->getMessage(),
                    'error_type' => get_class($e),
                ],
            ];
        }
    }

    protected function getSystemPrompt(): string
    {
        return <<<PROMPT
Kamu adalah psikolog profesional yang ahli dalam interpretasi assessment competency untuk rekrutmen instansi pemerintah Indonesia.

Keahlian kamu:
- Menganalisis hasil assessment psikologi berdasarkan metode Gap Analysis
- Mengidentifikasi kekuatan dan area pengembangan kandidat
- Memberikan rekomendasi konstruktif untuk pengembangan kompetensi
- Menulis laporan interpretasi dengan bahasa formal dan profesional

Prinsip kerja kamu:
1. Objektif: Berdasarkan data assessment, bukan asumsi subjektif
2. Konstruktif: Fokus pada pengembangan, bukan kritik negatif
3. Kontekstual: Mempertimbangkan posisi dan standar institusi
4. Professional: Menggunakan bahasa baku Bahasa Indonesia yang sesuai untuk laporan formal
5. Holistik: Melihat kombinasi berbagai aspek, bukan aspek individual saja

Tugas kamu:
Membuat interpretasi naratif hasil assessment dalam format paragraf yang mengalir (flowing narrative), bukan bullet points.
PROMPT;
    }

    protected function buildPromptContext(
        Participant $participant,
        CategoryType $categoryType
    ): array {
        // Implementation already shown in "3-Layer Priority Integration" section
        // (See the buildPromptContext() method)

        // Returns structured context array
    }

    protected function sanitizeOutput(string $text): string
    {
        // Remove excessive whitespace
        $text = preg_replace('/\s+/', ' ', $text);

        // Trim
        $text = trim($text);

        // Remove any markdown formatting (if AI adds it)
        $text = preg_replace('/[#*_~`]/', '', $text);

        return $text;
    }

    protected function validateOutput(string $text): void
    {
        $length = mb_strlen($text);

        if ($length < 200) {
            throw new \Exception('AI output too short (< 200 chars)');
        }

        if ($length > 3000) {
            throw new \Exception('AI output too long (> 3000 chars)');
        }

        // Check for placeholder text (AI failed to generate proper content)
        $placeholders = ['[INSERT', '[TODO', '{NAMA', '{POSISI'];
        foreach ($placeholders as $placeholder) {
            if (str_contains($text, $placeholder)) {
                throw new \Exception('AI output contains placeholder text');
            }
        }
    }

    protected function estimateCost(array $usage): float
    {
        // Estimate cost based on token usage
        // These are example rates - adjust based on actual z.ai pricing
        $promptTokens = $usage['prompt_tokens'] ?? 0;
        $completionTokens = $usage['completion_tokens'] ?? 0;

        $costPerMillionPromptTokens = 1.0; // Example: $1 per 1M tokens
        $costPerMillionCompletionTokens = 3.0; // Example: $3 per 1M tokens

        $cost = (
            ($promptTokens / 1_000_000 * $costPerMillionPromptTokens) +
            ($completionTokens / 1_000_000 * $costPerMillionCompletionTokens)
        );

        return round($cost, 6);
    }

    protected function getBaselineId(?string $baselineType, Participant $participant): ?int
    {
        if ($baselineType === 'custom_standard') {
            $templateId = $participant->positionFormation->template_id;
            return Session::get("selected_standard.{$templateId}");
        }

        return null;
    }
}
```

---

## 🧪 Testing & Quality Assurance

### Unit Tests

```php
// tests/Unit/Services/AIInterpretationServiceTest.php

namespace Tests\Unit\Services;

use App\Models\CategoryType;
use App\Models\Participant;
use App\Services\AIInterpretationService;
use App\Services\Http\ZaiClient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AIInterpretationServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_build_prompt_context_includes_all_required_fields(): void
    {
        $participant = Participant::factory()->create();
        $categoryType = CategoryType::factory()->create();

        $service = app(AIInterpretationService::class);
        $context = $service->buildPromptContext($participant, $categoryType);

        $this->assertArrayHasKey('participant_info', $context);
        $this->assertArrayHasKey('baseline_context', $context);
        $this->assertArrayHasKey('category_info', $context);
        $this->assertArrayHasKey('assessment_results', $context);
        $this->assertArrayHasKey('instructions', $context);
    }

    public function test_sanitize_output_removes_excess_whitespace(): void
    {
        $service = app(AIInterpretationService::class);

        $input = "Test   with    multiple    spaces\n\n\nand newlines";
        $output = $service->sanitizeOutput($input);

        $this->assertStringNotContainsString('   ', $output);
    }

    public function test_validate_output_throws_on_short_text(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('too short');

        $service = app(AIInterpretationService::class);
        $service->validateOutput('Too short');
    }

    public function test_validate_output_throws_on_placeholder_text(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('placeholder');

        $service = app(AIInterpretationService::class);
        $longText = str_repeat('Valid text. ', 50) . '[INSERT NAME HERE]';
        $service->validateOutput($longText);
    }
}
```

### Integration Tests

```php
// tests/Feature/InterpretationGenerationTest.php

namespace Tests\Feature;

use App\Models\Participant;
use App\Services\InterpretationGeneratorService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class InterpretationGenerationTest extends TestCase
{
    use RefreshDatabase;

    public function test_generates_ai_interpretation_when_enabled(): void
    {
        // Mock z.ai API response
        Http::fake([
            'api.z.ai/*' => Http::response([
                'id' => 'chatcmpl-test',
                'choices' => [
                    [
                        'message' => [
                            'content' => 'Peserta menunjukkan kemampuan yang sangat baik...' . str_repeat(' Test content.', 50),
                        ],
                    ],
                ],
                'usage' => [
                    'total_tokens' => 2000,
                    'prompt_tokens' => 1500,
                    'completion_tokens' => 500,
                ],
            ], 200),
        ]);

        config(['ai.interpretation.enabled' => true]);

        $participant = Participant::factory()->create();
        $service = app(InterpretationGeneratorService::class);

        $results = $service->generateForDisplay($participant);

        $this->assertArrayHasKey('potensi', $results);
        $this->assertArrayHasKey('kompetensi', $results);

        // Check saved to database
        $this->assertDatabaseHas('interpretations', [
            'participant_id' => $participant->id,
            'generated_by' => 'ai',
            'ai_model' => 'glm-4.6',
        ]);
    }

    public function test_falls_back_to_template_when_ai_fails(): void
    {
        // Mock API failure
        Http::fake([
            'api.z.ai/*' => Http::response(['error' => 'API Error'], 500),
        ]);

        config(['ai.interpretation.enabled' => true]);

        $participant = Participant::factory()->create();
        $service = app(InterpretationGeneratorService::class);

        $results = $service->generateForDisplay($participant);

        // Should still get interpretations (from template fallback)
        $this->assertNotEmpty($results['potensi']);
        $this->assertNotEmpty($results['kompetensi']);

        // Check fallback was used
        $this->assertDatabaseHas('interpretations', [
            'participant_id' => $participant->id,
            'generated_by' => 'template',
        ]);
    }

    public function test_uses_cached_interpretation_on_second_call(): void
    {
        Http::fake([
            'api.z.ai/*' => Http::response([
                'choices' => [['message' => ['content' => str_repeat('Test. ', 100)]]],
                'usage' => ['total_tokens' => 2000],
            ], 200),
        ]);

        $participant = Participant::factory()->create();
        $service = app(InterpretationGeneratorService::class);

        // First call - should hit API
        $service->generateForDisplay($participant);
        Http::assertSentCount(2); // 2 categories

        // Second call - should use cache
        $service->generateForDisplay($participant);
        Http::assertSentCount(2); // No additional API calls
    }
}
```

### Quality Assurance Checklist

```markdown
## AI Output Quality Checklist

### Content Quality
- [ ] Bahasa Indonesia baku dan formal
- [ ] Tidak ada typo atau grammar errors
- [ ] Flowing narrative (bukan bullet points)
- [ ] Tidak menyebut angka rating secara eksplisit
- [ ] Objective tone (tidak emotional)

### Structure
- [ ] Terdiri dari 3 paragraf
- [ ] Paragraf 1: Overview + kekuatan
- [ ] Paragraf 2: Area pengembangan
- [ ] Paragraf 3: Kesimpulan + rekomendasi
- [ ] Smooth transitions antar paragraf

### Accuracy
- [ ] Sesuai dengan data assessment
- [ ] Menyebut aspek yang relevan
- [ ] Gap analysis akurat (positif/negatif)
- [ ] Kesimpulan konsisten dengan data

### Context Awareness
- [ ] Menyebutkan posisi yang relevan
- [ ] Mempertimbangkan baseline yang aktif
- [ ] Aspek critical (high-weight) dibahas lebih detail
- [ ] Tidak membahas aspek yang tidak aktif

### Professional Tone
- [ ] Bahasa formal untuk instansi pemerintah
- [ ] Konstruktif (development-oriented)
- [ ] Tidak judgmental atau negatif
- [ ] Profesional dan respectful
```

### A/B Testing Setup

```php
// For comparing AI vs Template quality

namespace App\Services;

class InterpretationABTestService
{
    public function shouldUseAI(Participant $participant): bool
    {
        // 50/50 split based on participant ID
        return $participant->id % 2 === 0;
    }

    public function logComparison(
        Participant $participant,
        string $aiVersion,
        string $templateVersion
    ): void {
        // Log for manual review
        Log::channel('ab_test')->info('Interpretation A/B Test', [
            'participant_id' => $participant->id,
            'ai_version' => $aiVersion,
            'template_version' => $templateVersion,
        ]);
    }
}
```

---

## ⚡ Performance Considerations

### Cost Analysis

#### Per Interpretation Cost (Estimated)

```
Model: GLM-4.6
Input: ~1,500 tokens (participant data + context)
Output: ~800 tokens (interpretation text)
Total: ~2,300 tokens per interpretation

Cost (example pricing):
- $1.00 per 1M input tokens
- $3.00 per 1M output tokens

Per Interpretation:
= (1500/1M × $1) + (800/1M × $3)
= $0.0015 + $0.0024
= $0.0039 (~$0.004 or ~Rp 65)

Per Participant (2 categories):
= $0.004 × 2
= $0.008 (~Rp 130)
```

#### Monthly Cost Projection

```
Scenario 1: Small Event (100 participants)
- Interpretations needed: 100 × 2 = 200
- Cost: 200 × $0.004 = $0.80 (~Rp 13,000)

Scenario 2: Medium Event (1,000 participants)
- Interpretations needed: 1,000 × 2 = 2,000
- Cost: 2,000 × $0.004 = $8.00 (~Rp 130,000)

Scenario 3: Large Event (5,000 participants)
- Interpretations needed: 5,000 × 2 = 10,000
- Cost: 10,000 × $0.004 = $40.00 (~Rp 650,000)

With Caching (assume 30% cache hit rate):
- Actual API calls: 10,000 × 0.7 = 7,000
- Cost: 7,000 × $0.004 = $28.00 (~Rp 455,000)
```

### Latency Analysis

```
Per API Call:
- Network latency: 100-300ms
- Model inference: 2-5 seconds
- Total: ~3-8 seconds per interpretation

With Caching:
- Cache hit: <50ms (database query)
- Cache miss: ~3-8 seconds (API call)
- Average (70% hit rate): ~1 second

User Experience:
- First view: 3-8s wait (show loading indicator)
- Second view (same baseline): <50ms (instant!)
- Baseline change: 3-8s wait (if new config)
```

### Optimization Strategies

#### 1. Batch Generation (Background Jobs)

```php
// Generate interpretations in background after assessment completion

namespace App\Jobs;

use App\Models\Participant;
use App\Services\InterpretationGeneratorService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenerateParticipantInterpretations implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        protected int $participantId,
        protected ?int $customStandardId = null
    ) {}

    public function handle(InterpretationGeneratorService $service): void
    {
        $participant = Participant::find($this->participantId);

        if (!$participant) {
            return;
        }

        // If custom standard specified, temporarily set it
        if ($this->customStandardId) {
            $templateId = $participant->positionFormation->template_id;
            Session::put("selected_standard.{$templateId}", $this->customStandardId);
        }

        // Generate interpretations (will be cached for future views)
        $service->generateForDisplay($participant);
    }
}

// Dispatch after assessment completion
AssessmentCompleted::dispatch($participant);
GenerateParticipantInterpretations::dispatch($participant->id);
```

#### 2. Parallel Generation

```php
// Generate both categories in parallel

use Illuminate\Support\Facades\Concurrency;

public function generateBothCategoriesParallel(Participant $participant): array
{
    $template = $participant->positionFormation->template;
    $categories = $template->categoryTypes;

    // Run both API calls concurrently
    [$potensi, $kompetensi] = Concurrency::run([
        fn() => $this->generateCategoryInterpretation($participant, $categories->where('code', 'potensi')->first()),
        fn() => $this->generateCategoryInterpretation($participant, $categories->where('code', 'kompetensi')->first()),
    ]);

    return [
        'potensi' => $potensi,
        'kompetensi' => $kompetensi,
    ];
}
```

#### 3. Smart Cache Warming

```php
// Pre-generate for likely scenarios

namespace App\Console\Commands;

class WarmPopularBaselines extends Command
{
    protected $signature = 'interpretation:warm-popular';

    public function handle(): void
    {
        // Get top 10 most frequently used custom standards
        $popularStandards = CustomStandard::withCount('usageLog')
            ->orderBy('usage_log_count', 'desc')
            ->limit(10)
            ->get();

        // Get active events
        $activeEvents = AssessmentEvent::where('status', 'active')->get();

        foreach ($activeEvents as $event) {
            $participants = $event->participants()->limit(100)->get();

            foreach ($popularStandards as $standard) {
                // Warm cache for popular standard
                GenerateParticipantInterpretations::dispatch(
                    $participants->random()->id,
                    $standard->id
                );
            }
        }
    }
}
```

#### 4. Rate Limiting

```php
// Prevent API abuse

namespace App\Http\Middleware;

use Illuminate\Cache\RateLimiter;

class ThrottleAIInterpretationRequests
{
    public function handle($request, Closure $next)
    {
        $key = 'ai-interpretation:' . $request->user()->id;

        if (RateLimiter::tooManyAttempts($key, $perMinute = 10)) {
            return response()->json([
                'message' => 'Too many interpretation requests. Please try again later.',
            ], 429);
        }

        RateLimiter::hit($key, $decayMinutes = 1);

        return $next($request);
    }
}
```

---

## 🚀 Future Enhancements

### Phase 2 Features

#### 1. Multi-Model Support
```php
// Support multiple AI providers

config/ai.php:
'providers' => [
    'zai' => [...],
    'openai' => [...],
    'anthropic' => [...],
],

'interpretation' => [
    'provider' => 'zai', // Can switch dynamically
    'model_priority' => ['glm-4.6', 'gpt-4o', 'claude-3.5-sonnet'],
],
```

#### 2. Fine-Tuning
- Train custom model on historical interpretations
- Improve context understanding for Indonesian govt recruitment
- Reduce cost with smaller, specialized model

#### 3. Interactive Regeneration
```php
// User can adjust tone/focus

User clicks: "Regenerate with more focus on Integritas"
→ System adjusts prompt
→ Regenerates interpretation
```

#### 4. Quality Feedback Loop
```php
// Collect user feedback

User rates interpretation: ⭐⭐⭐⭐⭐
→ Store feedback
→ Analyze patterns
→ Improve prompts
```

#### 5. Multi-Language Support
- English interpretations for international candidates
- Bilingual reports (ID + EN)

#### 6. Export to PDF
- Generate professional PDF reports
- Include interpretations, charts, recommendations

---

## 📚 References

### Documentation
- [SPSP Business Concepts](./SPSP_BUSINESS_CONCEPTS.md)
- [Existing Interpretation System](./INTERPRETATION_SYSTEM.md)
- [3-Layer Priority System](./DYNAMIC_STANDARD_ANALYSIS.md)
- [Custom Standard Feature](./CUSTOM_STANDARD_FEATURE.md)

### External Resources
- [GLM-4.6 Model Card](https://platform.z.ai/docs/models/glm-4.6)
- [Z.ai API Documentation](https://platform.z.ai/docs/api-reference)
- [Prompt Engineering Best Practices](https://www.anthropic.com/prompt-engineering)

---

**Version:** 1.0
**Last Updated:** December 2025
**Author:** SPSP Development Team
**Status:** Draft - Pending Implementation
