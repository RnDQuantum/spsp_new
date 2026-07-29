<?php

namespace App\Services\Lsp;

class LspNormEngineService
{
    /**
     * Cache in-memory static untuk norma JSON (IST, Kostik, Personality)
     */
    protected static ?array $normCache = null;

    /**
     * Cache in-memory instance untuk query master DB LSP
     */
    protected array $masterCache = [];

    /**
     * Read norm JSON files with static in-memory caching
     */
    public function loadNormData(): array
    {
        if (static::$normCache !== null) {
            return static::$normCache;
        }

        $norms = ['ist' => null, 'kostik' => null, 'personality' => null];

        $paths = [
            'ist' => resource_path('data/lsp_norms/ist.json'),
            'kostik' => resource_path('data/lsp_norms/kostik.json'),
            'personality' => resource_path('data/lsp_norms/personality.json'),
        ];

        foreach ($paths as $key => $path) {
            if (file_exists($path)) {
                $content = file_get_contents($path);
                $norms[$key] = json_decode($content, true);
            }
        }

        static::$normCache = $norms;

        return static::$normCache;
    }

    /**
     * Process IST Raw Scores to Standard Scores and IQ
     */
    public function processIstNorms(?string $rawIst, string $pendidikan, int $usia, ?array $istNorm = null): array
    {
        if ($istNorm === null) {
            $allNorms = $this->loadNormData();
            $istNorm = $allNorms['ist'] ?? null;
        }

        $defaultResult = [
            'iq' => 100,
            'kategori' => 'Rata-rata',
            'scores' => ['SE' => 10, 'WA' => 10, 'AN' => 10, 'GE' => 10, 'ME' => 10, 'RA' => 10, 'ZR' => 10, 'FA' => 10, 'WU' => 10],
        ];

        if (empty($rawIst)) {
            return $defaultResult;
        }

        $arrayIst = explode(',', $rawIst);
        if (count($arrayIst) < 9) {
            return $defaultResult;
        }

        $rsSum = array_sum(array_slice($arrayIst, 0, 9));
        $rsSE = (int) $arrayIst[0];
        $rsWA = (int) $arrayIst[1];
        $rsAN = (int) $arrayIst[2];
        $rsGE = (int) $arrayIst[3];
        $rsRA = (int) $arrayIst[4];
        $rsZR = (int) $arrayIst[5];
        $rsFA = (int) $arrayIst[6];
        $rsWU = (int) $arrayIst[7];
        $rsME = (int) $arrayIst[8];

        if (! $istNorm) {
            $iqEst = 90 + min(40, (int) ($rsSum / 3));

            return [
                'iq' => $iqEst,
                'kategori' => $iqEst >= 110 ? 'Tinggi' : ($iqEst >= 90 ? 'Rata-rata' : 'Rendah'),
                'scores' => [
                    'IQ' => $iqEst, 'SE' => $rsSE, 'WA' => $rsWA, 'AN' => $rsAN, 'GE' => $rsGE,
                    'ME' => $rsME, 'RA' => $rsRA, 'ZR' => $rsZR, 'FA' => $rsFA, 'WU' => $rsWU,
                ],
            ];
        }

        $pendidikanUpper = strtoupper($pendidikan);
        if (in_array($pendidikanUpper, ['SMA', 'SMK'])) {
            $iqIst = $istNorm['hasil_iq_pendidikan'][$istNorm['sw_sma'][$rsSum] ?? 0] ?? 100;
            $ssSE = $istNorm['aspek_pendidikan']['SMA']['SE'][$rsSE] ?? 10;
            $ssWA = $istNorm['aspek_pendidikan']['SMA']['WA'][$rsWA] ?? 10;
            $ssAN = $istNorm['aspek_pendidikan']['SMA']['AN'][$rsAN] ?? 10;
            $ssGE = $istNorm['aspek_pendidikan']['SMA']['GE'][$rsGE] ?? 10;
            $ssME = $istNorm['aspek_pendidikan']['SMA']['ME'][$rsME] ?? 10;
            $ssRA = $istNorm['aspek_pendidikan']['SMA']['RA'][$rsRA] ?? 10;
            $ssZR = $istNorm['aspek_pendidikan']['SMA']['ZR'][$rsZR] ?? 10;
            $ssFA = $istNorm['aspek_pendidikan']['SMA']['FA'][$rsFA] ?? 10;
            $ssWU = $istNorm['aspek_pendidikan']['SMA']['WU'][$rsWU] ?? 10;
        } elseif (in_array($pendidikanUpper, ['S1', 'D3', 'D4', 'S2', 'S3'])) {
            $iqIst = $istNorm['hasil_iq_pendidikan'][$istNorm['sw_si'][$rsSum] ?? 0] ?? 100;
            $ssSE = $istNorm['aspek_pendidikan']['SI']['SE'][$rsSE] ?? 10;
            $ssWA = $istNorm['aspek_pendidikan']['SI']['WA'][$rsWA] ?? 10;
            $ssAN = $istNorm['aspek_pendidikan']['SI']['AN'][$rsAN] ?? 10;
            $ssGE = $istNorm['aspek_pendidikan']['SI']['GE'][$rsGE] ?? 10;
            $ssME = $istNorm['aspek_pendidikan']['SI']['ME'][$rsME] ?? 10;
            $ssRA = $istNorm['aspek_pendidikan']['SI']['RA'][$rsRA] ?? 10;
            $ssZR = $istNorm['aspek_pendidikan']['SI']['ZR'][$rsZR] ?? 10;
            $ssFA = $istNorm['aspek_pendidikan']['SI']['FA'][$rsFA] ?? 10;
            $ssWU = $istNorm['aspek_pendidikan']['SI']['WU'][$rsWU] ?? 10;
        } else {
            $umurKey = $this->getIstAgeKey($usia);
            $iqIst = $istNorm['hasil_iq'][$istNorm['aspek'][$umurKey]['GESAMT'][$rsSum] ?? 0] ?? 100;
            $ssSE = $istNorm['aspek'][$umurKey]['SE'][$rsSE] ?? 10;
            $ssWA = $istNorm['aspek'][$umurKey]['WA'][$rsWA] ?? 10;
            $ssAN = $istNorm['aspek'][$umurKey]['AN'][$rsAN] ?? 10;
            $ssGE = $istNorm['aspek'][$umurKey]['GE'][$rsGE] ?? 10;
            $ssME = $istNorm['aspek'][$umurKey]['ME'][$rsME] ?? 10;
            $ssRA = $istNorm['aspek'][$umurKey]['RA'][$rsRA] ?? 10;
            $ssZR = $istNorm['aspek'][$umurKey]['ZR'][$rsZR] ?? 10;
            $ssFA = $istNorm['aspek'][$umurKey]['FA'][$rsFA] ?? 10;
            $ssWU = $istNorm['aspek'][$umurKey]['WU'][$rsWU] ?? 10;
        }

        $kategoriCode = '4';
        if ($iqIst <= 89) {
            $kategoriCode = '5';
        } elseif ($iqIst <= 109) {
            $kategoriCode = '4';
        } elseif ($iqIst <= 119) {
            $kategoriCode = '3';
        } elseif ($iqIst <= 129) {
            $kategoriCode = '2';
        } elseif ($iqIst >= 130) {
            $kategoriCode = '1';
        }

        $kategoriText = $istNorm['kategori'][$kategoriCode] ?? 'Rata-rata';

        return [
            'iq' => $iqIst,
            'kategori' => $kategoriText,
            'scores' => [
                'IQ' => $iqIst, 'SE' => $ssSE, 'WA' => $ssWA, 'AN' => $ssAN, 'GE' => $ssGE,
                'ME' => $ssME, 'RA' => $ssRA, 'ZR' => $ssZR, 'FA' => $ssFA, 'WU' => $ssWU,
            ],
        ];
    }

    public function getIstAgeKey(int $usia): string
    {
        if ($usia <= 12) {
            return '12';
        }
        if ($usia <= 18) {
            return (string) $usia;
        }
        if ($usia <= 20) {
            return '19-20';
        }
        if ($usia <= 25) {
            return '21-25';
        }
        if ($usia <= 30) {
            return '26-30';
        }
        if ($usia <= 35) {
            return '31-35';
        }
        if ($usia <= 40) {
            return '36-40';
        }
        if ($usia <= 45) {
            return '41-45';
        }
        if ($usia <= 50) {
            return '46-50';
        }

        return '51-60';
    }

    /**
     * Process PAPI Kostik raw scores
     */
    public function processKostikNorms(?string $rawKostik, ?array $kostikNorm = null): array
    {
        $factors = ['A', 'G', 'N', 'R', 'C', 'D', 'T', 'V', 'F', 'W', 'L', 'P', 'I', 'S', 'O', 'B', 'X', 'E', 'K', 'Z'];
        $result = array_fill_keys($factors, 1);

        if (empty($rawKostik)) {
            return $result;
        }

        $arr = explode(',', $rawKostik);
        foreach ($factors as $idx => $factor) {
            if (isset($arr[$idx])) {
                $result[$factor] = (int) $arr[$idx];
            }
        }

        return $result;
    }

    /**
     * Process 16PF raw scores with MD adjustment & Sten lookups
     */
    public function process16pfNorms(?string $rawPersonality, int $usia, ?array $personalityNorm = null): array
    {
        if ($personalityNorm === null) {
            $allNorms = $this->loadNormData();
            $personalityNorm = $allNorms['personality'] ?? null;
        }

        $factors = ['A', 'B', 'C', 'E', 'F', 'G', 'H', 'I', 'L', 'M', 'N', 'O', 'Q1', 'Q2', 'Q3', 'Q4'];
        $result = array_fill_keys($factors, 5);

        if (empty($rawPersonality)) {
            return $result;
        }

        $arr = explode(',', $rawPersonality);
        $md = isset($arr[0]) ? (int) $arr[0] : 0;

        $stenScoreGroup = '30';
        if ($usia <= 19) {
            $stenScoreGroup = '17';
        } elseif ($usia <= 29) {
            $stenScoreGroup = '20';
        }

        $allFactors = ['MD', 'A', 'B', 'C', 'E', 'F', 'G', 'H', 'I', 'L', 'M', 'N', 'O', 'Q1', 'Q2', 'Q3', 'Q4'];

        for ($i = 1; $i < count($arr); $i++) {
            if (! isset($allFactors[$i])) {
                continue;
            }
            $fName = $allFactors[$i];
            $rawVal = (int) $arr[$i];

            if ($personalityNorm && isset($personalityNorm['sten_score'][$stenScoreGroup][$fName][$rawVal])) {
                $baseSten = (int) $personalityNorm['sten_score'][$stenScoreGroup][$fName][$rawVal];
            } else {
                $baseSten = min(10, max(1, $rawVal));
            }

            if ($md == 10) {
                if (in_array($fName, ['O', 'Q4'])) {
                    $baseSten += 2;
                } elseif (in_array($fName, ['C', 'Q3'])) {
                    $baseSten -= 2;
                } elseif (in_array($fName, ['L', 'N', 'Q2'])) {
                    $baseSten += 1;
                } elseif (in_array($fName, ['A', 'G', 'H'])) {
                    $baseSten -= 1;
                }
            } elseif ($md == 8 || $md == 9) {
                if (in_array($fName, ['L', 'N', 'O', 'Q2', 'Q4'])) {
                    $baseSten += 1;
                } elseif (in_array($fName, ['A', 'C', 'G', 'H', 'Q3'])) {
                    $baseSten -= 1;
                }
            } elseif ($md == 7) {
                if (in_array($fName, ['O', 'Q4'])) {
                    $baseSten += 1;
                } elseif (in_array($fName, ['C', 'Q3'])) {
                    $baseSten -= 1;
                }
            }

            $result[$fName] = max(1, min(10, $baseSten));
        }

        return $result;
    }

    /**
     * Calculate Potensi Profile with Master Query Caching
     */
    public function calculateProfilPotensiCached($db, string $standarJabatan, string $standarPenilaian, array $ist, array $kostik, array $pf16): array
    {
        $toleransiPct = 10;

        $cacheKeyPotensi = "potensi_rows_{$standarJabatan}";
        if (! isset($this->masterCache[$cacheKeyPotensi])) {
            $this->masterCache[$cacheKeyPotensi] = $db->table('standar_potensi')
                ->join('standar_aspek', 'standar_aspek.kode_aspek', '=', 'standar_potensi.aspek')
                ->join('standar_atribute', 'standar_atribute.kode_atribute', '=', 'standar_potensi.atribut')
                ->where('standar_potensi.standar_jabatan', $standarJabatan)
                ->where('standar_potensi.level', 'potensi')
                ->select('standar_potensi.*', 'standar_aspek.aspek_penilaian', 'standar_atribute.nama_atribute')
                ->orderBy('standar_potensi.id', 'asc')
                ->orderBy('standar_potensi.urutan', 'asc')
                ->get();
        }
        $standarPotensiRows = $this->masterCache[$cacheKeyPotensi];

        $cacheKeyMappings = "tool_mappings_{$standarJabatan}_{$standarPenilaian}";
        if (! isset($this->masterCache[$cacheKeyMappings])) {
            $this->masterCache[$cacheKeyMappings] = $db->table('standar_atribute_alat_ukur')
                ->join('standar_potensi', 'standar_atribute_alat_ukur.kode_atribute', '=', 'standar_potensi.atribut')
                ->where('standar_potensi.standar_jabatan', $standarJabatan)
                ->where('standar_atribute_alat_ukur.standard', $standarPenilaian)
                ->orderBy('standar_potensi.urutan', 'asc')
                ->get();
        }
        $toolMappings = $this->masterCache[$cacheKeyMappings];

        $aspekAtributRatings = [];
        foreach ($toolMappings as $mapping) {
            $x = 0;
            if ($mapping->alat_ukur === 'ist') {
                $x = $ist['scores'][$mapping->komponen] ?? 0;
            } elseif ($mapping->alat_ukur === 'kostik') {
                $x = $kostik[$mapping->komponen] ?? 0;
            } elseif ($mapping->alat_ukur === '16pf') {
                $x = $pf16[$mapping->komponen] ?? 0;
            }

            $rating = 1;
            if ($mapping->tingkat === '+') {
                if ($x <= $mapping->skala_1) {
                    $rating = 1;
                } elseif ($x <= $mapping->skala_2) {
                    $rating = 2;
                } elseif ($x <= $mapping->skala_3) {
                    $rating = 3;
                } elseif ($x <= $mapping->skala_4) {
                    $rating = 4;
                } elseif ($x >= $mapping->skala_5) {
                    $rating = 5;
                }
            } elseif ($mapping->tingkat === '-') {
                if ($x >= $mapping->skala_1) {
                    $rating = 1;
                } elseif ($x >= $mapping->skala_2) {
                    $rating = 2;
                } elseif ($x >= $mapping->skala_3) {
                    $rating = 3;
                } elseif ($x >= $mapping->skala_4) {
                    $rating = 4;
                } elseif ($x <= $mapping->skala_5) {
                    $rating = 5;
                }
            }

            $aspekAtributRatings[$mapping->aspek][$mapping->atribut][] = $rating;
        }

        $actualAttributeRating = [];
        foreach ($aspekAtributRatings as $aspek => $atributs) {
            foreach ($atributs as $atrib => $ratings) {
                $actualAttributeRating[$aspek][$atrib] = (int) round(array_sum($ratings) / count($ratings));
            }
        }

        $aspekSummary = [];
        $totalStandardRating = 0;
        $totalIndividualRating = 0;
        $totalStandardScore = 0;
        $totalIndividualScore = 0;

        $groupedAspects = [];
        foreach ($standarPotensiRows as $row) {
            $groupedAspects[$row->aspek][] = $row;
        }

        foreach ($groupedAspects as $aspekKode => $rows) {
            $namaAspek = $rows[0]->aspek_penilaian;
            $bobot = (float) $rows[0]->bobot;

            $stdRatings = array_map(fn ($r) => (float) $r->standar_rating, $rows);
            $avgStdRating = array_sum($stdRatings) / count($stdRatings);
            $stdRatingTol = $avgStdRating - ($avgStdRating * ($toleransiPct / 100));

            $indivRatings = [];
            foreach ($rows as $r) {
                $indivRatings[] = $actualAttributeRating[$aspekKode][$r->atribut] ?? (int) $r->standar_rating;
            }
            $avgIndivRating = count($indivRatings) > 0 ? (array_sum($indivRatings) / count($indivRatings)) : $avgStdRating;

            $stdScoreTol = $stdRatingTol * $bobot;
            $indivScore = $avgIndivRating * $bobot;

            $gapRating = $avgIndivRating - $stdRatingTol;
            $gapScore = $indivScore - $stdScoreTol;

            if ($gapScore > 0) {
                $kesimpulan = 'Sangat Memenuhi Standard';
            } elseif ($gapScore == 0) {
                $kesimpulan = 'Memenuhi Standard';
            } else {
                $kesimpulan = ($avgIndivRating >= $stdRatingTol) ? 'Masih Memenuhi Standard' : 'Kurang Memenuhi Standard';
            }

            $totalStandardRating += $stdRatingTol;
            $totalIndividualRating += $avgIndivRating;
            $totalStandardScore += $stdScoreTol;
            $totalIndividualScore += $indivScore;

            $aspekSummary[$aspekKode] = [
                'kode_aspek' => $aspekKode,
                'nama_aspek' => $namaAspek,
                'bobot' => $bobot,
                'standard_rating' => round($avgStdRating, 2),
                'standard_rating_toleransi' => round($stdRatingTol, 2),
                'standard_score_toleransi' => round($stdScoreTol, 2),
                'individual_rating' => round($avgIndivRating, 2),
                'individual_score' => round($indivScore, 2),
                'gap_rating' => round($gapRating, 2),
                'gap_score' => round($gapScore, 2),
                'kesimpulan' => $kesimpulan,
                'atributs' => array_map(fn ($r) => [
                    'kode_atribut' => $r->atribut,
                    'nama_atribut' => $r->nama_atribute,
                    'standard_rating' => (int) $r->standar_rating,
                    'individual_rating' => $actualAttributeRating[$aspekKode][$r->atribut] ?? (int) $r->standar_rating,
                ], $rows),
            ];
        }

        $overallGapRating = $totalIndividualRating - $totalStandardRating;
        $overallGapScore = $totalIndividualScore - $totalStandardScore;

        if ($overallGapScore > 0) {
            $kesimpulanAkhir = 'Memenuhi Standard';
        } elseif ($overallGapScore == 0) {
            $kesimpulanAkhir = 'Memenuhi Standard';
        } else {
            $kesimpulanAkhir = 'Di Bawah Standard';
        }

        return [
            'aspek_list' => $aspekSummary,
            'total_standard_rating' => round($totalStandardRating, 2),
            'total_individual_rating' => round($totalIndividualRating, 2),
            'total_standard_score' => round($totalStandardScore, 2),
            'total_individual_score' => round($totalIndividualScore, 2),
            'gap_total_rating' => round($overallGapRating, 2),
            'gap_total_score' => round($overallGapScore, 2),
            'kesimpulan_akhir' => $kesimpulanAkhir,
        ];
    }
}
