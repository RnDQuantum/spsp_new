<?php

declare(strict_types=1);

namespace Database\Seeders\Support;

use App\Models\Participant;
use Carbon\Carbon;

/**
 * PersonalProfileGenerator - Generator Profil Personal & Atribut Pelengkap Realistis
 *
 * Menghitung zodiak, shio Tionghoa, dan weton Jawa secara deterministik
 * dari tanggal lahir peserta, serta menghasilkan hobi, olahraga, dan catatan medis.
 */
class PersonalProfileGenerator
{
    private static array $bloodTypes = ['O+', 'A+', 'B+', 'AB+', 'O+'];

    private static array $hobbiesList = [
        'Membaca Literatur Manajemen Strategis & Sejarah',
        'Bermain Catur Strategis & Pemecahan Puzzle',
        'Fotografi Lanskap & Dokumenter',
        'Menulis Esai Kebijakan Publik & Blog Profesional',
        'Berkebun Hidroponik & Tanaman Hias',
        'Mendengarkan Musik Klasik & Akustik',
        'Memasak Kuliner Tradisional Nusantara',
    ];

    private static array $sportsList = [
        'Tenis Lapangan & Bulu Tangkis Mingguan',
        'Jogging & Jalan Santai 5K Rutin',
        'Bersepeda Jarak Menengah (Road Bike)',
        'Berenang Gaya Bebas & Dada',
        'Latihan Kebugaran Fisik (Gym / Fitness)',
        'Golf & Latihan Driving Range',
    ];

    private static array $mottoList = [
        'Integritas tanpa kompromi, dedikasi tanpa batas untuk kemajuan bangsa.',
        'Memimpin dengan keteladanan, melayani dengan kerendahan hati.',
        'Terus belajar, beradaptasi dengan cepat, dan memberikan dampak nyata.',
        'Disiplin dalam eksekusi, visioner dalam menyusun strategi.',
        'Kejujuran adalah modal utama kepemimpinan yang berkelanjutan.',
    ];

    /**
     * Generate personal profile data for a participant.
     *
     * @return array<string, mixed>
     */
    public static function generateForParticipant(Participant $participant): array
    {
        $birthDate = $participant->tanggal_lahir
            ? Carbon::parse($participant->tanggal_lahir)
            : Carbon::create(1985, 5, 15);

        $zodiac = self::calculateZodiac($birthDate);
        $shio = self::calculateShio((int) $birthDate->format('Y'));
        $weton = self::calculateWeton($birthDate);

        $seed = $participant->id;
        $bloodType = self::$bloodTypes[$seed % count(self::$bloodTypes)];
        $hobby = self::$hobbiesList[$seed % count(self::$hobbiesList)];
        $sport = self::$sportsList[($seed + 2) % count(self::$sportsList)];
        $motto = self::$mottoList[($seed + 3) % count(self::$mottoList)];

        $medicalNote = 'Golongan Darah '.$bloodType.'. Hasil skrining kesehatan umum menunjukkan status fisik prima, tekanan darah normal, dan tidak ada riwayat penyakit kronis atau pembatasan aktivitas fisik.';
        $culturalNote = $zodiac.' / '.$shio.'. Memiliki perpaduan karakter yang mandiri, adaptif, dan berorientasi pada ketelitian serta pencapaian target.';

        return [
            'participant_id' => $participant->id,
            'blood_type' => $bloodType,
            'hobbies' => $hobby,
            'sports' => $sport,
            'zodiac' => $zodiac,
            'chinese_zodiac' => $shio,
            'weton' => $weton,
            'medical_notes' => $medicalNote,
            'cultural_notes' => $culturalNote,
            'motto_or_values' => $motto,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    /**
     * Calculate Western Zodiac from date.
     */
    public static function calculateZodiac(Carbon $date): string
    {
        $m = (int) $date->format('n');
        $d = (int) $date->format('j');

        return match (true) {
            ($m === 12 && $d >= 22) || ($m === 1 && $d <= 19) => 'Capricorn',
            ($m === 1 && $d >= 20) || ($m === 2 && $d <= 18) => 'Aquarius',
            ($m === 2 && $d >= 19) || ($m === 3 && $d <= 20) => 'Pisces',
            ($m === 3 && $d >= 21) || ($m === 4 && $d <= 19) => 'Aries',
            ($m === 4 && $d >= 20) || ($m === 5 && $d <= 20) => 'Taurus',
            ($m === 5 && $d >= 21) || ($m === 6 && $d <= 20) => 'Gemini',
            ($m === 6 && $d >= 21) || ($m === 7 && $d <= 22) => 'Cancer',
            ($m === 7 && $d >= 23) || ($m === 8 && $d <= 22) => 'Leo',
            ($m === 8 && $d >= 23) || ($m === 9 && $d <= 22) => 'Virgo',
            ($m === 9 && $d >= 23) || ($m === 10 && $d <= 22) => 'Libra',
            ($m === 10 && $d >= 23) || ($m === 11 && $d <= 21) => 'Scorpio',
            default => 'Sagittarius',
        };
    }

    /**
     * Calculate Chinese Zodiac (Shio) from year.
     */
    public static function calculateShio(int $year): string
    {
        $animals = ['Tikus', 'Kerbau', 'Macan', 'Kelinci', 'Naga', 'Ular', 'Kuda', 'Kambing', 'Monyet', 'Ayam', 'Anjing', 'Babi'];
        $index = ($year - 4) % 12;
        if ($index < 0) {
            $index += 12;
        }

        return $animals[$index];
    }

    /**
     * Calculate Weton Jawa from date.
     */
    public static function calculateWeton(Carbon $date): string
    {
        $days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        $pasarans = ['Legi', 'Pahing', 'Pon', 'Wage', 'Kliwon'];

        $dayName = $days[(int) $date->dayOfWeek];

        // 1970-01-01 was Kamis (4) Wage (3)
        $refEpoch = Carbon::create(1970, 1, 1)->startOfDay();
        $target = $date->copy()->startOfDay();
        $diffDays = (int) floor($refEpoch->diffInSeconds($target, false) / 86400);

        $pasaranIndex = (3 + $diffDays) % 5;
        if ($pasaranIndex < 0) {
            $pasaranIndex += 5;
        }

        return $dayName.' '.$pasarans[$pasaranIndex];
    }
}
