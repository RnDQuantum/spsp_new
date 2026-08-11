<?php

namespace Tests\Unit;

use App\Models\Mmpi;
use Tests\TestCase;

class MmpiTest extends TestCase
{
    public function test_psikogram_formatted_accessor_with_null(): void
    {
        $test = new Mmpi;
        $test->psikogram = null;

        $this->assertEquals('-', $test->psikogram_formatted);
    }

    public function test_psikogram_formatted_accessor_with_string(): void
    {
        $test = new Mmpi;
        $test->psikogram = 'Ini adalah psikogram dalam bentuk string.';

        $this->assertEquals('Ini adalah psikogram dalam bentuk string.', $test->psikogram_formatted);
    }

    public function test_psikogram_formatted_accessor_with_indexed_array(): void
    {
        $test = new Mmpi;
        $test->psikogram = ['Poin Pertama', 'Poin Kedua'];

        $this->assertEquals("Poin Pertama\nPoin Kedua", $test->psikogram_formatted);
    }

    public function test_psikogram_formatted_accessor_with_associative_array(): void
    {
        $test = new Mmpi;
        $test->psikogram = [
            'Kepemimpinan' => 'Sangat Baik',
            'Adaptasi' => 'Cukup',
        ];

        $this->assertEquals("Kepemimpinan: Sangat Baik\nAdaptasi: Cukup", $test->psikogram_formatted);
    }
}
