# Assessment Calculation Services

## Overview

This directory contains services responsible for calculating all assessment-related values from raw data received via API.

## Service Architecture

```
AssessmentCalculationService (Orchestrator)
    ├─ SubAspectService (Level 1: Store raw data)
    ├─ AspectService (Level 2: Calculate aspect ratings & scores)
    ├─ CategoryService (Level 3: Aggregate category totals)
    └─ FinalAssessmentService (Level 4: Calculate final weighted scores)
```

## Services

### 1. AssessmentCalculationService
**Purpose:** Main orchestrator that coordinates all calculation services
**Responsibility:**
- Entry point for calculations
- Calls other services in correct order
- Handles transactions

### 2. SubAspectService
**Purpose:** Store raw sub-aspect assessment data (Potensi only)
**Responsibility:**
- Store individual_rating (INTEGER 1-5) from API
- Snapshot standard_rating from master

### 3. AspectService
**Purpose:** Calculate aspect assessments
**Responsibility:**
- **Potensi:** Calculate individual_rating as AVG from sub-aspects (DECIMAL)
- **Kompetensi:** Store individual_rating from API (INTEGER 1-5)
- Calculate standard_score, individual_score
- Calculate gaps (gap_rating, gap_score)
- Calculate percentage_score for spider chart
- Determine conclusion codes

### 4. CategoryService
**Purpose:** Aggregate category assessments (Potensi/Kompetensi)
**Responsibility:**
- SUM all aspect scores per category
- Calculate category-level gaps
- Determine category conclusion codes

### 5. FinalAssessmentService
**Purpose:** Calculate final weighted assessment score
**Responsibility:**
- Weighted calculation: (Potensi × weight%) + (Kompetensi × weight%)
- Calculate achievement percentage
- Determine final conclusion code

## Key Principles

1. ✅ **No Hard-Coding:** All weights & standards fetched from database
2. ✅ **Fully Dynamic:** Support multiple templates with different structures
3. ✅ **Snapshot Pattern:** Standard ratings preserved at calculation time
4. ✅ **Single Responsibility:** Each service handles one calculation level
5. ✅ **Reusable:** Can be called from API controller or seeder

## Usage Example

```php
use App\Services\Assessment\AssessmentCalculationService;

// In Controller or Seeder
$calculationService = app(AssessmentCalculationService::class);

// Calculate for single participant
$calculationService->calculateParticipant($participant);

// Or calculate for entire event
$calculationService->calculateEvent($event);
```

## Calculation Flow

```
1. API sends raw data (sub-aspect ratings, aspect ratings)
   ↓
2. SubAspectService: Store raw sub-aspect assessments
   ↓
3. AspectService: Calculate aspect ratings & scores
   ↓
4. CategoryService: Aggregate category totals
   ↓
5. FinalAssessmentService: Calculate final weighted score
   ↓
6. Done! All derived values calculated and stored
```
