<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MmpiResult extends Model
{
    use HasFactory;

    /**
     * Nama tabel yang terkait dengan model.
     *
     * @var string
     */
    protected $table = 'mmpi_results';

    /**
     * Atribut yang dapat diisi secara massal.
     *
     * @var array
     */
    protected $fillable = [
        'event_id',
        'participant_id',
        'kode_proyek',
        'no_test',
        'username',
        'validitas',
        'internal',
        'interpersonal',
        'kap_kerja',
        'klinik',
        'kesimpulan',
        'psikogram',
        'nilai_pq',
        'tingkat_stres',
    ];

    /**
     * Atribut yang harus dikonversi ke tipe data tertentu.
     *
     * @var array
     */
    protected $casts = [
        'nilai_pq' => 'decimal:2',
    ];
}
