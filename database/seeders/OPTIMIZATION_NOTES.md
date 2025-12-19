# DynamicAssessmentSeeder Optimization Notes

## Tanggal: 2025-12-19

### Masalah Awal
- Kecepatan seeding sangat lambat (2-4 participant/detik) untuk dataset ribuan participant
- Nested transactions dalam loop menyebabkan overhead besar
- N+1 query problem pada setiap participant
- Memory buildup karena Eloquent models tidak di-clear

### Solusi Optimasi

#### 1. **Batch Insert Strategy**
- Participants di-insert dalam batch (50-200 sekaligus) menggunakan `DB::table()->insert()`
- Psychological tests dan interpretations juga di-batch insert
- Mengurangi overhead INSERT query dari ribuan menjadi puluhan

#### 2. **Adaptive Chunk Sizing**
```php
$chunkSize = match (true) {
    $totalParticipants < 500 => 50,
    $totalParticipants < 2000 => 100,
    $totalParticipants < 5000 => 150,
    default => 200
};
```
- Chunk size otomatis menyesuaikan dengan total participants
- Dataset kecil: chunk kecil untuk responsiveness
- Dataset besar: chunk besar untuk throughput maksimal

#### 3. **Transaction Scope Reduction**
- **Sebelum**: Transaction per 50 participants
- **Sesudah**: Transaction per chunk (50-200 participants)
- Mengurangi commit overhead secara signifikan

#### 4. **Memory Management**
- Garbage collection setiap 5 chunks (bukan 10)
- Clear entity manager cache lebih sering
- Menghindari memory leak pada dataset besar

### Alur Kerja Baru

1. **seedEvent()**: Setup event, batches, positions (tidak berubah)
2. **seedParticipantsOptimized()**: Koordinator utama
   - Hitung adaptive chunk size
   - Loop per chunk dengan transaction terpisah
   - Progress tracking detail
3. **processParticipantChunk()**: Proses satu chunk
   - Generate semua participant data dalam array
   - **Bulk insert participants** (1 query untuk 50-200 rows)
   - Fetch inserted participants
   - Loop untuk assessments (masih perlu individual processing)
   - **Bulk insert psych tests** (1 query)
   - **Bulk insert interpretations** (1 query)

### Estimasi Peningkatan Performa

| Dataset Size | Sebelum | Sesudah | Improvement |
|--------------|---------|---------|-------------|
| 100 participants | ~30 detik | ~5 detik | **6x lebih cepat** |
| 1,000 participants | ~7 menit | ~45 detik | **9x lebih cepat** |
| 10,000 participants | ~70 menit | ~7 menit | **10x lebih cepat** |

### Catatan Penting

1. **Assessment Calculations**: Masih menggunakan service layer individual karena kompleksitas business logic
   - AspectAssessment, SubAspectAssessment membutuhkan calculation per item
   - CategoryAssessment dan FinalAssessment butuh aggregation
   - Tidak bisa di-bulk insert tanpa refactor major ke service layer

2. **Performance Level Distribution**: Tetap random per participant untuk data realistis

3. **Unique Identifiers**: Menggunakan static counter untuk memastikan username, email, test_number unique

### File Backup
- Original file: `database/seeders/DynamicAssessmentSeeder.php.backup`
- Untuk rollback: `cp DynamicAssessmentSeeder.php.backup DynamicAssessmentSeeder.php`

### Testing
```bash
# Test dengan dataset kecil
php artisan db:seed --class=DynamicAssessmentSeeder

# Monitor memory usage
php -d memory_limit=2G artisan db:seed --class=DynamicAssessmentSeeder

# Test dengan participants_count=5000 untuk validasi performa
```

### Potential Future Optimizations

1. **Bulk Insert Assessments**: Refactor service layer untuk support batch processing
2. **Queue-based Processing**: Untuk dataset >50k, gunakan queue jobs
3. **Database Indexing**: Ensure proper indexes on foreign keys
4. **Parallel Processing**: Multiple workers untuk different events
