<?php

namespace Tests\Unit;

use App\Models\PsychologicalTest;
use Tests\TestCase;

class PsychologicalTestTest extends TestCase
{
    public function test_psikogram_formatted_accessor_with_null(): void
    {
        $test = new PsychologicalTest();
        $test->psikogram = null;

        $this->assertEquals('-', $test->psikogram_formatted);
    }

    public function test_psikogram_formatted_accessor_with_string(): void
    {
        $test = new PsychologicalTest();
        $test->psikogram = "Ini adalah psikogram dalam bentuk string.";

        $this->assertEquals("Ini adalah psikogram dalam bentuk string.", $test->psikogram_formatted);
    }

    public function test_psikogram_formatted_accessor_with_indexed_array(): void
    {
        $test = new PsychologicalTest();
        $test->psikogram = ["Poin Pertama", "Poin Kedua"];

        $this->assertEquals("Poin Pertama\nPoin Kedua", $test->psikogram_formatted);
    }

    public function test_psikogram_formatted_accessor_with_associative_array(): void
    {
        $test = new PsychologicalTest();
        $test->psikogram = [
            "Kepemimpinan" => "Sangat Baik",
            "Adaptasi" => "Cukup"
        ];

        $this->assertEquals("Kepemimpinan: Sangat Baik\nAdaptasi: Cukup", $test->psikogram_formatted);
    }
}
