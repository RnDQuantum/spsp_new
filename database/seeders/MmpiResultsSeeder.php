<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MmpiResultsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = [
            // Data 1
            [
                'event_id' => 1,
                'participant_id' => 101,
                'kode_proyek' => 'PROJ-A2023',
                'no_test' => '02-1-3-27-047',
                'username' => 'jkt01-047',
                'validitas' => 'Hasil tes ini konsisten, akurat dan dapat dipercaya.',
                'internal' => "1. Kejujuran: cukup\n2. Tanggung jawab: bagus\n3. Ketaatan pada peraturan: cukup\n4. Percaya diri: cukup\n5. Kemampuan beradaptasi: cukup\n6. Kemampuan Mengendalikan emosi: cukup\n7. Kemandirian: sangat kurang",
                'interpersonal' => "1. Sosialisasi: Sedang\n2. Hubungan dalam keluarga: cukup\n3. Kemampuan membina hubungan akrab: bagus\n4. Kemampuan mempercayai orang lain: cukup",
                'kap_kerja' => "1. Kemampuan mengatasi kendala sikap (bekerja): bagus\n2. Kemampuan mengatasi permasalahan: sangat kurang\n3. Kemampuan mengambil keputusan: cukup\n4. Motivasi: cukup",
                'klinik' => 'Klien mungkin mengalami stres berat.',
                'kesimpulan' => "1. Klien memiliki Fungsi Psikologik Menyeluruh (Overall Psychological Function) yang sedang.(PQ=46)\n2. Saat ini klien mengalami stres berat.\n3. Klien memiliki kapasitas kerja yang sedang.\n4. Hubungan interpersonal klien: cukup.\n5. Kemampuan klien mengembangkan/merubah potensi diri: kurang.",
                'psikogram' => "1. K.Mengatasi Masalah 10\n2. Kepemimpinan 10\n3. Integritas 70\n4. Disiplin 60\n5. Percaya Diri 60\n6. Motivasi 60\n7. Kapasitas Kerja 50\n8. Hub. Interpersonal 60\n9. Membina Hubungn Akrab 77\n10. Kemampuan Beradaptasi 61\n11. K.Mengendalikan Emosi 62\n12. K.Mengembangkan Diri 36",
                'nilai_pq' => 46.00,
                'tingkat_stres' => 'berat',
                'created_at' => Carbon::now()->subDays(30),
                'updated_at' => Carbon::now()->subDays(30),
            ],

            // Data 2
            [
                'event_id' => 1,
                'participant_id' => 102,
                'kode_proyek' => 'PROJ-A2023',
                'no_test' => '02-1-3-28-063',
                'username' => 'jkt01-063',
                'validitas' => 'Hasil tes ini akurat dan valid untuk dijadikan dasar penilaian.',
                'internal' => "1. Kejujuran: bagus\n2. Tanggung jawab: bagus\n3. Ketaatan pada peraturan: bagus\n4. Percaya diri: bagus\n5. Kemampuan beradaptasi: cukup\n6. Kemampuan Mengendalikan emosi: bagus\n7. Kemandirian: cukup",
                'interpersonal' => "1. Sosialisasi: Bagus\n2. Hubungan dalam keluarga: bagus\n3. Kemampuan membina hubungan akrab: bagus\n4. Kemampuan mempercayai orang lain: cukup",
                'kap_kerja' => "1. Kemampuan mengatasi kendala sikap (bekerja): bagus\n2. Kemampuan mengatasi permasalahan: cukup\n3. Kemampuan mengambil keputusan: bagus\n4. Motivasi: bagus",
                'klinik' => 'Klien memiliki tingkat stres ringan.',
                'kesimpulan' => "1. Klien memiliki Fungsi Psikologik Menyeluruh (Overall Psychological Function) yang bagus.(PQ=72)\n2. Saat ini klien mengalami stres ringan.\n3. Klien memiliki kapasitas kerja yang baik.\n4. Hubungan interpersonal klien: bagus.\n5. Kemampuan klien mengembangkan/merubah potensi diri: cukup.",
                'psikogram' => "1. K.Mengatasi Masalah 70\n2. Kepemimpinan 65\n3. Integritas 75\n4. Disiplin 80\n5. Percaya Diri 75\n6. Motivasi 80\n7. Kapasitas Kerja 75\n8. Hub. Interpersonal 70\n9. Membina Hubungn Akrab 72\n10. Kemampuan Beradaptasi 70\n11. K.Mengendalikan Emosi 70\n12. K.Mengembangkan Diri 65",
                'nilai_pq' => 72.00,
                'tingkat_stres' => 'ringan',
                'created_at' => Carbon::now()->subDays(28),
                'updated_at' => Carbon::now()->subDays(28),
            ],

            // Data 3
            [
                'event_id' => 2,
                'participant_id' => 201,
                'kode_proyek' => 'PROJ-B2023',
                'no_test' => '03-2-4-15-028',
                'username' => 'bdg02-028',
                'validitas' => 'Hasil tes cukup konsisten dan dapat dijadikan rujukan penilaian.',
                'internal' => "1. Kejujuran: cukup\n2. Tanggung jawab: cukup\n3. Ketaatan pada peraturan: cukup\n4. Percaya diri: kurang\n5. Kemampuan beradaptasi: cukup\n6. Kemampuan Mengendalikan emosi: kurang\n7. Kemandirian: kurang",
                'interpersonal' => "1. Sosialisasi: Cukup\n2. Hubungan dalam keluarga: kurang\n3. Kemampuan membina hubungan akrab: cukup\n4. Kemampuan mempercayai orang lain: kurang",
                'kap_kerja' => "1. Kemampuan mengatasi kendala sikap (bekerja): cukup\n2. Kemampuan mengatasi permasalahan: kurang\n3. Kemampuan mengambil keputusan: kurang\n4. Motivasi: cukup",
                'klinik' => 'Klien mengalami stres sedang.',
                'kesimpulan' => "1. Klien memiliki Fungsi Psikologik Menyeluruh (Overall Psychological Function) yang kurang.(PQ=55)\n2. Saat ini klien mengalami stres sedang.\n3. Klien memiliki kapasitas kerja yang cukup.\n4. Hubungan interpersonal klien: kurang.\n5. Kemampuan klien mengembangkan/merubah potensi diri: kurang.",
                'psikogram' => "1. K.Mengatasi Masalah 45\n2. Kepemimpinan 40\n3. Integritas 60\n4. Disiplin 55\n5. Percaya Diri 45\n6. Motivasi 55\n7. Kapasitas Kerja 60\n8. Hub. Interpersonal 45\n9. Membina Hubungn Akrab 60\n10. Kemampuan Beradaptasi 55\n11. K.Mengendalikan Emosi 45\n12. K.Mengembangkan Diri 40",
                'nilai_pq' => 55.00,
                'tingkat_stres' => 'sedang',
                'created_at' => Carbon::now()->subDays(15),
                'updated_at' => Carbon::now()->subDays(15),
            ],

            // Data 4
            [
                'event_id' => 2,
                'participant_id' => 202,
                'kode_proyek' => 'PROJ-B2023',
                'no_test' => '03-2-4-16-039',
                'username' => 'bdg02-039',
                'validitas' => 'Hasil tes sangat konsisten dan dapat diandalkan.',
                'internal' => "1. Kejujuran: sangat bagus\n2. Tanggung jawab: sangat bagus\n3. Ketaatan pada peraturan: sangat bagus\n4. Percaya diri: bagus\n5. Kemampuan beradaptasi: sangat bagus\n6. Kemampuan Mengendalikan emosi: bagus\n7. Kemandirian: bagus",
                'interpersonal' => "1. Sosialisasi: Sangat bagus\n2. Hubungan dalam keluarga: bagus\n3. Kemampuan membina hubungan akrab: sangat bagus\n4. Kemampuan mempercayai orang lain: bagus",
                'kap_kerja' => "1. Kemampuan mengatasi kendala sikap (bekerja): sangat bagus\n2. Kemampuan mengatasi permasalahan: bagus\n3. Kemampuan mengambil keputusan: sangat bagus\n4. Motivasi: sangat bagus",
                'klinik' => 'Klien memiliki tingkat stres normal.',
                'kesimpulan' => "1. Klien memiliki Fungsi Psikologik Menyeluruh (Overall Psychological Function) yang sangat bagus.(PQ=85)\n2. Saat ini klien mengalami tingkat stres normal.\n3. Klien memiliki kapasitas kerja yang sangat baik.\n4. Hubungan interpersonal klien: sangat bagus.\n5. Kemampuan klien mengembangkan/merubah potensi diri: bagus.",
                'psikogram' => "1. K.Mengatasi Masalah 85\n2. Kepemimpinan 90\n3. Integritas 90\n4. Disiplin 85\n5. Percaya Diri 80\n6. Motivasi 90\n7. Kapasitas Kerja 90\n8. Hub. Interpersonal 85\n9. Membina Hubungn Akrab 85\n10. Kemampuan Beradaptasi 85\n11. K.Mengendalikan Emosi 80\n12. K.Mengembangkan Diri 75",
                'nilai_pq' => 85.00,
                'tingkat_stres' => 'normal',
                'created_at' => Carbon::now()->subDays(14),
                'updated_at' => Carbon::now()->subDays(14),
            ],

            // Data 5
            [
                'event_id' => 3,
                'participant_id' => 301,
                'kode_proyek' => 'PROJ-C2023',
                'no_test' => '04-3-5-21-015',
                'username' => 'sby03-015',
                'validitas' => 'Hasil tes cukup valid namun terdapat beberapa inkonsistensi minor.',
                'internal' => "1. Kejujuran: cukup\n2. Tanggung jawab: cukup\n3. Ketaatan pada peraturan: kurang\n4. Percaya diri: sangat kurang\n5. Kemampuan beradaptasi: kurang\n6. Kemampuan Mengendalikan emosi: sangat kurang\n7. Kemandirian: sangat kurang",
                'interpersonal' => "1. Sosialisasi: Kurang\n2. Hubungan dalam keluarga: kurang\n3. Kemampuan membina hubungan akrab: sangat kurang\n4. Kemampuan mempercayai orang lain: sangat kurang",
                'kap_kerja' => "1. Kemampuan mengatasi kendala sikap (bekerja): kurang\n2. Kemampuan mengatasi permasalahan: sangat kurang\n3. Kemampuan mengambil keputusan: sangat kurang\n4. Motivasi: kurang",
                'klinik' => 'Klien mengalami stres sangat berat.',
                'kesimpulan' => "1. Klien memiliki Fungsi Psikologik Menyeluruh (Overall Psychological Function) yang sangat kurang.(PQ=35)\n2. Saat ini klien mengalami stres sangat berat.\n3. Klien memiliki kapasitas kerja yang kurang.\n4. Hubungan interpersonal klien: sangat kurang.\n5. Kemampuan klien mengembangkan/merubah potensi diri: sangat kurang.",
                'psikogram' => "1. K.Mengatasi Masalah 15\n2. Kepemimpinan 20\n3. Integritas 50\n4. Disiplin 40\n5. Percaya Diri 25\n6. Motivasi 30\n7. Kapasitas Kerja 35\n8. Hub. Interpersonal 25\n9. Membina Hubungn Akrab 20\n10. Kemampuan Beradaptasi 30\n11. K.Mengendalikan Emosi 15\n12. K.Mengembangkan Diri 20",
                'nilai_pq' => 35.00,
                'tingkat_stres' => 'sangat berat',
                'created_at' => Carbon::now()->subDays(7),
                'updated_at' => Carbon::now()->subDays(7),
            ],
        ];

        DB::table('mmpi_results')->insert($data);
    }
}
