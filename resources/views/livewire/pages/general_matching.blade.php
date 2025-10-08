<x-layouts.app title="General Matching Report - Aspek Psikologi">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Ensure equal width for Range Scale columns */
        .range-scale {
            width: 8%;
            /* Equal width for columns 1-5 (5 columns, 40% total, 8% each) */
        }

        /* Ensure progress bar spans the full Range Scale section */
        .progress-container {
            position: relative;
            width: 100%;
            /* Span all 5 columns (40% of table width) */
            height: 1.5rem;
            /* Match h-6 */
        }
    </style>
    <div class="bg-white rounded-lg shadow-md overflow-hidden max-w-7xl mx-auto my-8">
        <!-- Header -->
        <div class="border-b-4 border-black py-3">
            <h1 class="text-center text-lg font-bold uppercase tracking-wide text-black">GENERAL MATCHING - ASPEK
                PSIKOLOGI</h1>
        </div>

        <!-- Info Section -->
        <div class="grid grid-cols-2 border-b border-gray-300">
            <!-- Left Column -->
            <div class="border-r border-gray-300">
                <div class="grid grid-cols-3 border-b border-gray-300">
                    <div class="px-4 py-2 text-sm text-black">Nomor Tes</div>
                    <div class="px-4 py-2 text-sm col-span-2 text-black">: 1</div>
                </div>
                <div class="grid grid-cols-3 border-b border-gray-300">
                    <div class="px-4 py-2 text-sm text-black">NIP</div>
                    <div class="px-4 py-2 text-sm col-span-2 text-black">: 19780923 198803 1 003</div>
                </div>
                <div class="grid grid-cols-3 border-b border-gray-300">
                    <div class="px-4 py-2 text-sm text-black">Nama</div>
                    <div class="px-4 py-2 text-sm col-span-2 text-black">: Badrus Samsu Daruat, S.Sos., M.Si.</div>
                </div>
                <div class="grid grid-cols-3 border-b border-gray-300">
                    <div class="px-4 py-2 text-sm text-black">Jabatan Saat Ini</div>
                    <div class="px-4 py-2 text-sm col-span-2 text-black">: Sekretaris Inspektorat Daerah</div>
                </div>
                <div class="grid grid-cols-3">
                    <div class="px-4 py-2 text-sm text-black">Tanggal Tes</div>
                    <div class="px-4 py-2 text-sm col-span-2 text-black">: 15 Maret 2023</div>
                </div>
            </div>

            <!-- Right Column - JOB PERSON MATCH -->
            <div class="flex flex-col">
                <div class="grid grid-cols-2 border-b border-gray-300">
                    <div class="px-4 py-2 text-sm text-black">Standar/Standard</div>
                    <div class="px-4 py-2 text-sm text-black">: JPT Pratama</div>
                </div>
                <div class="grid grid-cols-2 border-b border-gray-300">
                    <div class="px-4 py-2 text-sm text-black">Matrix</div>
                    <div class="px-4 py-2 text-sm text-black">: 1</div>
                </div>
                <div class="px-4 py-2 text-center font-bold text-sm border-b border-gray-300 text-black">
                    JOB PERSON MATCH
                </div>
                <div class="flex-grow flex items-center px-4 py-2">
                    <div class="w-full h-8 relative">
                        <div class="h-full bg-green-600 rounded" style="width: 94%;"></div>
                        <div class="absolute right-0 top-0 bottom-0 flex items-center pr-2">
                            <span class="text-sm font-bold text-black">94%</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Table -->
        <div class="overflow-x-auto">
            <table class="w-full border-collapse">
                <thead>
                    <tr class="bg-blue-100">
                        <th class="border border-gray-300 px-4 py-2 text-left text-sm font-bold text-black"
                            colspan="2">ATRIBUT & INDIKATOR</th>
                        <th class="border border-gray-300 px-2 py-2 text-center text-xs font-bold text-black range-scale"
                            colspan="5">Range Scale</th>
                    </tr>
                    <tr class="bg-blue-100">
                        <th class="border border-gray-300 px-4 py-2 text-left text-sm font-bold text-black"
                            colspan="2"></th>
                        <th class="border border-gray-300 px-2 py-1 text-center text-xs text-black range-scale">1</th>
                        <th class="border border-gray-300 px-2 py-1 text-center text-xs text-black range-scale">2</th>
                        <th class="border border-gray-300 px-2 py-1 text-center text-xs text-black range-scale">3</th>
                        <th class="border border-gray-300 px-2 py-1 text-center text-xs text-black range-scale">4</th>
                        <th class="border border-gray-300 px-2 py-1 text-center text-xs text-black range-scale">5</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- ASPEK PSIKOLOGI -->
                    <tr class="bg-gray-100">
                        <td class="border border-gray-300 px-4 py-2 font-bold text-sm text-black" colspan="7">ASPEK
                            PSIKOLOGI</td>
                    </tr>
                    <!-- I. Kecerdasan -->
                    <tr>
                        <td class="border border-gray-300 px-4 py-2 text-sm font-bold text-black" colspan="2">
                            I&nbsp;&nbsp;&nbsp;&nbsp;Kecerdasan</td>
                        <td class="border border-gray-300 range-scale" colspan="5">
                            <div class="progress-container">
                                <div class="h-full bg-green-600 rounded" style="width: 100%;"></div>
                                <div class="absolute right-0 top-0 bottom-0 flex items-center pr-2">
                                    <span class="text-xs font-bold text-black">100%</span>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr class="bg-gray-50">
                        <td class="border border-gray-300 px-4 py-1 text-xs text-black">1.</td>
                        <td class="border border-gray-300 px-4 py-1 text-xs text-black">Kecerdasan Umum</td>
                        <td class="border border-gray-300 range-scale"></td>
                        <td class="border border-gray-300 range-scale"></td>
                        <td class="border border-gray-300 range-scale"></td>
                        <td class="border border-gray-300 range-scale text-center text-black">X</td>
                        <td class="border border-gray-300 range-scale"></td>
                    </tr>
                    <tr class="bg-gray-50">
                        <td class="border border-gray-300 px-4 py-1 text-xs text-black">2.</td>
                        <td class="border border-gray-300 px-4 py-1 text-xs text-black">Daya Tangkap</td>
                        <td class="border border-gray-300 range-scale"></td>
                        <td class="border border-gray-300 range-scale"></td>
                        <td class="border border-gray-300 range-scale"></td>
                        <td class="border border-gray-300 range-scale text-center text-black">X</td>
                        <td class="border border-gray-300 range-scale"></td>
                    </tr>
                    <tr class="bg-gray-50">
                        <td class="border border-gray-300 px-4 py-1 text-xs text-black">3.</td>
                        <td class="border border-gray-300 px-4 py-1 text-xs text-black">Daya Analisa</td>
                        <td class="border border-gray-300 range-scale"></td>
                        <td class="border border-gray-300 range-scale"></td>
                        <td class="border border-gray-300 range-scale"></td>
                        <td class="border border-gray-300 range-scale text-center text-black">X</td>
                        <td class="border border-gray-300 range-scale"></td>
                    </tr>
                    <tr class="bg-gray-50">
                        <td class="border border-gray-300 px-4 py-1 text-xs text-black">4.</td>
                        <td class="border border-gray-300 px-4 py-1 text-xs text-black">Berpikir Konseptual</td>
                        <td class="border border-gray-300 range-scale"></td>
                        <td class="border border-gray-300 range-scale"></td>
                        <td class="border border-gray-300 range-scale"></td>
                        <td class="border border-gray-300 range-scale text-center text-black">X</td>
                        <td class="border border-gray-300 range-scale"></td>
                    </tr>
                    <tr class="bg-gray-50">
                        <td class="border border-gray-300 px-4 py-1 text-xs text-black">5.</td>
                        <td class="border border-gray-300 px-4 py-1 text-xs text-black">Kemampuan Logika</td>
                        <td class="border border-gray-300 range-scale"></td>
                        <td class="border border-gray-300 range-scale"></td>
                        <td class="border border-gray-300 range-scale"></td>
                        <td class="border border-gray-300 range-scale text-center text-black">X</td>
                        <td class="border border-gray-300 range-scale"></td>
                    </tr>
                    <tr class="bg-gray-50">
                        <td class="border border-gray-300 px-4 py-1 text-xs text-black">6.</td>
                        <td class="border border-gray-300 px-4 py-1 text-xs text-black">Kreativitas</td>
                        <td class="border border-gray-300 range-scale"></td>
                        <td class="border border-gray-300 range-scale"></td>
                        <td class="border border-gray-300 range-scale text-center text-black">X</td>
                        <td class="border border-gray-300 range-scale"></td>
                        <td class="border border-gray-300 range-scale"></td>
                    </tr>
                    <!-- II. Cara Kerja -->
                    <tr>
                        <td class="border border-gray-300 px-4 py-2 text-sm font-bold text-black" colspan="2">
                            II&nbsp;&nbsp;&nbsp;Cara Kerja</td>
                        <td class="border border-gray-300 range-scale" colspan="5">
                            <div class="progress-container">
                                <div class="h-full bg-green-600 rounded" style="width: 100%;"></div>
                                <div class="absolute right-0 top-0 bottom-0 flex items-center pr-2">
                                    <span class="text-xs font-bold text-black">100%</span>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr class="bg-gray-50">
                        <td class="border border-gray-300 px-4 py-1 text-xs text-black">1.</td>
                        <td class="border border-gray-300 px-4 py-1 text-xs text-black">Sistematika Kerja</td>
                        <td class="border border-gray-300 range-scale"></td>
                        <td class="border border-gray-300 range-scale"></td>
                        <td class="border border-gray-300 range-scale"></td>
                        <td class="border border-gray-300 range-scale text-center text-black">X</td>
                        <td class="border border-gray-300 range-scale"></td>
                    </tr>
                    <tr class="bg-gray-50">
                        <td class="border border-gray-300 px-4 py-1 text-xs text-black">2.</td>
                        <td class="border border-gray-300 px-4 py-1 text-xs text-black">Ketelitian</td>
                        <td class="border border-gray-300 range-scale"></td>
                        <td class="border border-gray-300 range-scale"></td>
                        <td class="border border-gray-300 range-scale text-center text-black">X</td>
                        <td class="border border-gray-300 range-scale"></td>
                        <td class="border border-gray-300 range-scale"></td>
                    </tr>
                    <tr class="bg-gray-50">
                        <td class="border border-gray-300 px-4 py-1 text-xs text-black">3.</td>
                        <td class="border border-gray-300 px-4 py-1 text-xs text-black">Kerjasama</td>
                        <td class="border border-gray-300 range-scale"></td>
                        <td class="border border-gray-300 range-scale"></td>
                        <td class="border border-gray-300 range-scale text-center text-black">X</td>
                        <td class="border border-gray-300 range-scale"></td>
                        <td class="border border-gray-300 range-scale"></td>
                    </tr>
                    <tr class="bg-gray-50">
                        <td class="border border-gray-300 px-4 py-1 text-xs text-black">4.</td>
                        <td class="border border-gray-300 px-4 py-1 text-xs text-black">Kedisiplinan</td>
                        <td class="border border-gray-300 range-scale"></td>
                        <td class="border border-gray-300 range-scale"></td>
                        <td class="border border-gray-300 range-scale"></td>
                        <td class="border border-gray-300 range-scale text-center text-black">X</td>
                        <td class="border border-gray-300 range-scale"></td>
                    </tr>
                    <tr class="bg-gray-50">
                        <td class="border border-gray-300 px-4 py-1 text-xs text-black">5.</td>
                        <td class="border border-gray-300 px-4 py-1 text-xs text-black">Tanggung Jawab</td>
                        <td class="border border-gray-300 range-scale"></td>
                        <td class="border border-gray-300 range-scale"></td>
                        <td class="border border-gray-300 range-scale"></td>
                        <td class="border border-gray-300 range-scale text-center text-black">X</td>
                        <td class="border border-gray-300 range-scale"></td>
                    </tr>
                    <!-- III. Potensi Kerja -->
                    <tr>
                        <td class="border border-gray-300 px-4 py-2 text-sm font-bold text-black" colspan="2">
                            III&nbsp;&nbsp;Potensi Kerja</td>
                        <td class="border border-gray-300 range-scale" colspan="5">
                            <div class="progress-container">
                                <div class="h-full bg-green-600 rounded" style="width: 94%;"></div>
                                <div class="absolute right-0 top-0 bottom-0 flex items-center pr-2">
                                    <span class="text-xs font-bold text-black">94%</span>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr class="bg-gray-50">
                        <td class="border border-gray-300 px-4 py-1 text-xs text-black">1.</td>
                        <td class="border border-gray-300 px-4 py-1 text-xs text-black">Motivasi Berprestasi</td>
                        <td class="border border-gray-300 range-scale"></td>
                        <td class="border border-gray-300 range-scale"></td>
                        <td class="border border-gray-300 range-scale"></td>
                        <td class="border border-gray-300 range-scale text-center text-black">X</td>
                        <td class="border border-gray-300 range-scale"></td>
                    </tr>
                    <tr class="bg-gray-50">
                        <td class="border border-gray-300 px-4 py-1 text-xs text-black">2.</td>
                        <td class="border border-gray-300 px-4 py-1 text-xs text-black">Inisiatif</td>
                        <td class="border border-gray-300 range-scale"></td>
                        <td class="border border-gray-300 range-scale"></td>
                        <td class="border border-gray-300 range-scale text-center text-black">X</td>
                        <td class="border border-gray-300 range-scale"></td>
                        <td class="border border-gray-300 range-scale"></td>
                    </tr>
                    <tr class="bg-gray-50">
                        <td class="border border-gray-300 px-4 py-1 text-xs text-black">3.</td>
                        <td class="border border-gray-300 px-4 py-1 text-xs text-black">Semangat Kerja</td>
                        <td class="border border-gray-300 range-scale"></td>
                        <td class="border border-gray-300 range-scale"></td>
                        <td class="border border-gray-300 range-scale"></td>
                        <td class="border border-gray-300 range-scale text-center text-black">X</td>
                        <td class="border border-gray-300 range-scale"></td>
                    </tr>
                    <tr class="bg-gray-50">
                        <td class="border border-gray-300 px-4 py-1 text-xs text-black">4.</td>
                        <td class="border border-gray-300 px-4 py-1 text-xs text-black">Kestabilan Kerja</td>
                        <td class="border border-gray-300 range-scale"></td>
                        <td class="border border-gray-300 range-scale"></td>
                        <td class="border border-gray-300 range-scale text-center text-black">X</td>
                        <td class="border border-gray-300 range-scale"></td>
                        <td class="border border-gray-300 range-scale"></td>
                    </tr>
                    <!-- IV. Hubungan Sosial -->
                    <tr>
                        <td class="border border-gray-300 px-4 py-2 text-sm font-bold text-black" colspan="2">
                            IV&nbsp;&nbsp;&nbsp;Hubungan Sosial</td>
                        <td class="border border-gray-300 range-scale" colspan="5">
                            <div class="progress-container">
                                <div class="h-full bg-green-600 rounded" style="width: 83%;"></div>
                                <div class="absolute right-0 top-0 bottom-0 flex items-center pr-2">
                                    <span class="text-xs font-bold text-black">83%</span>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr class="bg-gray-50">
                        <td class="border border-gray-300 px-4 py-1 text-xs text-black">1.</td>
                        <td class="border border-gray-300 px-4 py-1 text-xs text-black">Kepekaan Interpersonal</td>
                        <td class="border border-gray-300 range-scale"></td>
                        <td class="border border-gray-300 range-scale text-center text-black">X</td>
                        <td class="border border-gray-300 range-scale"></td>
                        <td class="border border-gray-300 range-scale"></td>
                        <td class="border border-gray-300 range-scale"></td>
                    </tr>
                    <tr class="bg-gray-50">
                        <td class="border border-gray-300 px-4 py-1 text-xs text-black">2.</td>
                        <td class="border border-gray-300 px-4 py-1 text-xs text-black">Komunikasi</td>
                        <td class="border border-gray-300 range-scale"></td>
                        <td class="border border-gray-300 range-scale"></td>
                        <td class="border border-gray-300 range-scale text-center text-black">X</td>
                        <td class="border border-gray-300 range-scale"></td>
                        <td class="border border-gray-300 range-scale"></td>
                    </tr>
                    <tr class="bg-gray-50">
                        <td class="border border-gray-300 px-4 py-1 text-xs text-black">3.</td>
                        <td class="border border-gray-300 px-4 py-1 text-xs text-black">Kontak Sosial</td>
                        <td class="border border-gray-300 range-scale"></td>
                        <td class="border border-gray-300 range-scale text-center text-black">X</td>
                        <td class="border border-gray-300 range-scale"></td>
                        <td class="border border-gray-300 range-scale"></td>
                        <td class="border border-gray-300 range-scale"></td>
                    </tr>
                    <tr class="bg-gray-50">
                        <td class="border border-gray-300 px-4 py-1 text-xs text-black">4.</td>
                        <td class="border border-gray-300 px-4 py-1 text-xs text-black">Penyesuaian Diri</td>
                        <td class="border border-gray-300 range-scale"></td>
                        <td class="border border-gray-300 range-scale"></td>
                        <td class="border border-gray-300 range-scale text-center text-black">X</td>
                        <td class="border border-gray-300 range-scale"></td>
                        <td class="border border-gray-300 range-scale"></td>
                    </tr>
                    <!-- V. Kepribadian -->
                    <tr>
                        <td class="border border-gray-300 px-4 py-2 text-sm font-bold text-black" colspan="2">
                            V&nbsp;&nbsp;&nbsp;&nbsp;Kepribadian</td>
                        <td class="border border-gray-300 range-scale" colspan="5">
                            <div class="progress-container">
                                <div class="h-full bg-green-600 rounded" style="width: 100%;"></div>
                                <div class="absolute right-0 top-0 bottom-0 flex items-center pr-2">
                                    <span class="text-xs font-bold text-black">100%</span>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr class="bg-gray-50">
                        <td class="border border-gray-300 px-4 py-1 text-xs text-black">1.</td>
                        <td class="border border-gray-300 px-4 py-1 text-xs text-black">Kepercayaan Diri</td>
                        <td class="border border-gray-300 range-scale"></td>
                        <td class="border border-gray-300 range-scale"></td>
                        <td class="border border-gray-300 range-scale text-center text-black">X</td>
                        <td class="border border-gray-300 range-scale"></td>
                        <td class="border border-gray-300 range-scale"></td>
                    </tr>
                    <tr class="bg-gray-50">
                        <td class="border border-gray-300 px-4 py-1 text-xs text-black">2.</td>
                        <td class="border border-gray-300 px-4 py-1 text-xs text-black">Stabilitas Emosi</td>
                        <td class="border border-gray-300 range-scale"></td>
                        <td class="border border-gray-300 range-scale"></td>
                        <td class="border border-gray-300 range-scale text-center text-black">X</td>
                        <td class="border border-gray-300 range-scale"></td>
                        <td class="border border-gray-300 range-scale"></td>
                    </tr>
                    <tr class="bg-gray-50">
                        <td class="border border-gray-300 px-4 py-1 text-xs text-black">3.</td>
                        <td class="border border-gray-300 px-4 py-1 text-xs text-black">Daya Tahan Stress</td>
                        <td class="border border-gray-300 range-scale"></td>
                        <td class="border border-gray-300 range-scale"></td>
                        <td class="border border-gray-300 range-scale text-center text-black">X</td>
                        <td class="border border-gray-300 range-scale"></td>
                        <td class="border border-gray-300 range-scale"></td>
                    </tr>
                    <tr class="bg-gray-50">
                        <td class="border border-gray-300 px-4 py-1 text-xs text-black">4.</td>
                        <td class="border border-gray-300 px-4 py-1 text-xs text-black">Kepemimpinan</td>
                        <td class="border border-gray-300 range-scale"></td>
                        <td class="border border-gray-300 range-scale"></td>
                        <td class="border border-gray-300 range-scale"></td>
                        <td class="border border-gray-300 range-scale text-center text-black">X</td>
                        <td class="border border-gray-300 range-scale"></td>
                    </tr>

                    {{-- I. Integritas --}}
                    <tr>
                        <td class="border border-gray-300 px-4 py-2 text-sm font-bold text-black" colspan="2">
                            I&nbsp;&nbsp;&nbsp;&nbsp;Integritas</td>
                        <td class="border border-gray-300 range-scale" colspan="5">
                            <div class="progress-container">
                                <div class="h-full bg-green-600 rounded" style="width: 100%;"></div>
                                <div class="absolute right-0 top-0 bottom-0 flex items-center pr-2">
                                    <span class="text-xs font-bold text-black">100%</span>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr class="bg-gray-50">
                        <td class="border border-gray-300 px-4 py-1 text-xs text-black">1.</td>
                        <td class="border border-gray-300 px-4 py-1 text-xs text-black">Konsisten berperilaku selaras
                            dengan nilai, norma dan/atau etika organisasi, dan jujur
                            dalam hubungan dengan manajemen, rekan kerja, bawahan langsung, dan pemangku
                            kepentingan, menciptakan budaya etika tinggi, bettanggungjawab atas tindakan atau
                            keputusan besetta risiko yang menyertainya.
                            Level 4
                            Mampu menciptakan situasi kerja yang mendorong kepatuhan pada nilai, norma, dan etika
                            organisasi.</td>
                        <td class="border border-gray-300 range-scale"></td>
                        <td class="border border-gray-300 range-scale"></td>
                        <td class="border border-gray-300 range-scale"></td>
                        <td class="border border-gray-300 range-scale text-center text-black">X</td>
                        <td class="border border-gray-300 range-scale"></td>
                    </tr>

                    {{-- II. Kerjasama --}}
                    <tr>
                        <td class="border border-gray-300 px-4 py-2 text-sm font-bold text-black" colspan="2">
                            II&nbsp;&nbsp;&nbsp;&nbsp;Kerjasama</td>
                        <td class="border border-gray-300 range-scale" colspan="5">
                            <div class="progress-container">
                                <div class="h-full bg-green-600 rounded" style="width: 75%;"></div>
                                <div class="absolute right-0 top-0 bottom-0 flex items-center pr-2">
                                    <span class="text-xs font-bold text-black">75%</span>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr class="bg-gray-50">
                        <td class="border border-gray-300 px-4 py-1 text-xs text-black">1.</td>
                        <td class="border border-gray-300 px-4 py-1 text-xs text-black">Kemampuan menjalin, membina,
                            mempettahankan hubungan kerja yang efektif, memiliki
                            komitmen saling membantu dalam penyelesaian tugas, dan mengoptimalkan segala
                            sumberdaya untuk mencapai tujuan strategis organisasi.
                            Level 4
                            Membangun komitmen tim, sinergi.</td>
                        <td class="border border-gray-300 range-scale"></td>
                        <td class="border border-gray-300 range-scale"></td>
                        <td class="border border-gray-300 range-scale text-center text-black">X</td>
                        <td class="border border-gray-300 range-scale"></td>
                        <td class="border border-gray-300 range-scale"></td>
                    </tr>

                    {{-- III. Komunikasi --}}
                    <tr>
                        <td class="border border-gray-300 px-4 py-2 text-sm font-bold text-black" colspan="2">
                            III&nbsp;&nbsp;&nbsp;&nbsp;Komunikasi</td>
                        <td class="border border-gray-300 range-scale" colspan="5">
                            <div class="progress-container">
                                <div class="h-full bg-green-600 rounded" style="width: 75%;"></div>
                                <div class="absolute right-0 top-0 bottom-0 flex items-center pr-2">
                                    <span class="text-xs font-bold text-black">75%</span>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr class="bg-gray-50">
                        <td class="border border-gray-300 px-4 py-1 text-xs text-black">1.</td>
                        <td class="border border-gray-300 px-4 py-1 text-xs text-black">Kemampuan untuk menerangkan
                            pandangan dan gagasan secara jelas, sistematis disertai
                            argumentasi yang logis dengan cara-cara yang sesuai baik secara lisan maupun tertulis;
                            memastikan pemahaman; mendengarkan secara aktif dan efektif; mempersuasi, meyakinkan
                            dan membujuk orang lain dalam rangka mencapai tujuan organisasi.
                            Level 4
                            Mampu mengemukakan pemikiran multidimensi secara lisan dan tertulis untuk mendorong
                            kesepakatan dengan tujuan meningkatkan kinerja secara keseluruhan.</td>
                        <td class="border border-gray-300 range-scale"></td>
                        <td class="border border-gray-300 range-scale"></td>
                        <td class="border border-gray-300 range-scale text-center text-black">X</td>
                        <td class="border border-gray-300 range-scale"></td>
                        <td class="border border-gray-300 range-scale"></td>
                    </tr>

                    {{-- IV. Orientasi Pada Hasil --}}
                    <tr>
                        <td class="border border-gray-300 px-4 py-2 text-sm font-bold text-black" colspan="2">
                            IV&nbsp;&nbsp;&nbsp;&nbsp;Orientasi Pada Hasil</td>
                        <td class="border border-gray-300 range-scale" colspan="5">
                            <div class="progress-container">
                                <div class="h-full bg-green-600 rounded" style="width: 100%;"></div>
                                <div class="absolute right-0 top-0 bottom-0 flex items-center pr-2">
                                    <span class="text-xs font-bold text-black">100%</span>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr class="bg-gray-50">
                        <td class="border border-gray-300 px-4 py-1 text-xs text-black">1.</td>
                        <td class="border border-gray-300 px-4 py-1 text-xs text-black">Kemampuan mempettahankan
                            komitmen pribadi yang tinggi untuk menyelesaikan tugas,
                            dapat diandalkan, bettanggung jawab, mampu secara sistimatis mengidentifikasi risiko dan
                            peluang dengan memperhatikan keterhubungan antara perencanaan dan hasil, untuk
                            keberhasilan organisasi.
                            Level 4
                            Mendorong unit kerja mencapai target yang ditetapkan atau melebihi hasil kerja
                            sebelumnya.</td>
                        <td class="border border-gray-300 range-scale"></td>
                        <td class="border border-gray-300 range-scale"></td>
                        <td class="border border-gray-300 range-scale"></td>
                        <td class="border border-gray-300 range-scale text-center text-black">X</td>
                        <td class="border border-gray-300 range-scale"></td>
                    </tr>

                    {{-- V. Pelayanan Publik --}}
                    <tr>
                        <td class="border border-gray-300 px-4 py-2 text-sm font-bold text-black" colspan="2">
                            V&nbsp;&nbsp;&nbsp;&nbsp;Pelayanan Publik</td>
                        <td class="border border-gray-300 range-scale" colspan="5">
                            <div class="progress-container">
                                <div class="h-full bg-green-600 rounded" style="width: 75%;"></div>
                                <div class="absolute right-0 top-0 bottom-0 flex items-center pr-2">
                                    <span class="text-xs font-bold text-black">75%</span>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr class="bg-gray-50">
                        <td class="border border-gray-300 px-4 py-1 text-xs text-black">1.</td>
                        <td class="border border-gray-300 px-4 py-1 text-xs text-black">Kemampuan dalam melaksanakan
                            tugas-tugas pemerintahan, pembangunan dan kegiatan
                            pemenuhan kebutuhan pelayanan publik secara profesional, transparan, mengikuti standar
                            pelayanan yang objektif, netral, tidak memihak, tidak diskriminatif, serta tidak terpengaruh
                            kepentingan pribadi/kelompok/golongan/partai politik
                            Level 4
                            Mampu memonitor mengevaluasi, memperhitungkan dan mengantisipasi dampak dari isu-
                            isu jangka panjang, kesempatan, atau kekuatan politik dalam hal pelayanan kebutuhan
                            pemangku kepentingan yang transparan, objektif, dan profesional.</td>
                        <td class="border border-gray-300 range-scale"></td>
                        <td class="border border-gray-300 range-scale"></td>
                        <td class="border border-gray-300 range-scale text-center text-black">X</td>
                        <td class="border border-gray-300 range-scale"></td>
                        <td class="border border-gray-300 range-scale"></td>
                    </tr>

                    {{-- VI. Pengembangan Diri dan Orang Lain --}}
                    <tr>
                        <td class="border border-gray-300 px-4 py-2 text-sm font-bold text-black" colspan="2">
                            VI&nbsp;&nbsp;&nbsp;&nbsp;Pengembangan Diri dan Orang Lain</td>
                        <td class="border border-gray-300 range-scale" colspan="5">
                            <div class="progress-container">
                                <div class="h-full bg-green-600 rounded" style="width: 100%;"></div>
                                <div class="absolute right-0 top-0 bottom-0 flex items-center pr-2">
                                    <span class="text-xs font-bold text-black">100%</span>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr class="bg-gray-50">
                        <td class="border border-gray-300 px-4 py-1 text-xs text-black">1.</td>
                        <td class="border border-gray-300 px-4 py-1 text-xs text-black">Kemampuan untuk meningkatkan
                            pengetahuan dan menyempurnakan keterampilan diri;
                            menginspirasi orang lain untuk mengembangkan dan menyempurnakan pengetahuan dan
                            keterampilan yang relevan dengan pekerjaan dan pengembangan karir jangka panjang,
                            mendorong kemauan belajar sepanjang hidup, memberikan saran/bantuan, umpan balik,
                            bimbingan untuk membantu orang lain untuk mengembangkan potensi dirinya.
                            Level 4
                            Menyusun program pengembangan jangka panjang dalam rangka mendorong manajemen
                            pembelajaran.</td>
                        <td class="border border-gray-300 range-scale"></td>
                        <td class="border border-gray-300 range-scale"></td>
                        <td class="border border-gray-300 range-scale"></td>
                        <td class="border border-gray-300 range-scale text-center text-black">X</td>
                        <td class="border border-gray-300 range-scale"></td>
                    </tr>

                    {{-- VII. Mengelola Perubahan --}}
                    <tr>
                        <td class="border border-gray-300 px-4 py-2 text-sm font-bold text-black" colspan="2">
                            VII&nbsp;&nbsp;&nbsp;&nbsp;Mengelola Perubahan</td>
                        <td class="border border-gray-300 range-scale" colspan="5">
                            <div class="progress-container">
                                <div class="h-full bg-green-600 rounded" style="width: 75%;"></div>
                                <div class="absolute right-0 top-0 bottom-0 flex items-center pr-2">
                                    <span class="text-xs font-bold text-black">75%</span>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr class="bg-gray-50">
                        <td class="border border-gray-300 px-4 py-1 text-xs text-black">1.</td>
                        <td class="border border-gray-300 px-4 py-1 text-xs text-black">Kemampuan dalam menyesuaikan
                            diri dengan situasi yang baru atau berubah dan tidak
                            bergantung secara berlebihan pada metode dan proses lama, mengambil tindakan untuk
                            mendukung dan melaksanakan insiatif perubahan, memimpin usaha perubahan, mengambil
                            tanggung jawab pribadi untuk memastikan perubahan berhasil diimplementasikan secara
                            efektif.
                            Level 4
                            Memimpin perubahan pada unit kerja.</td>
                        <td class="border border-gray-300 range-scale"></td>
                        <td class="border border-gray-300 range-scale"></td>
                        <td class="border border-gray-300 range-scale"></td>
                        <td class="border border-gray-300 range-scale text-center text-black">X</td>
                        <td class="border border-gray-300 range-scale"></td>
                    </tr>

                    {{-- VIII. Pengambilan Keputusan --}}
                    <tr>
                        <td class="border border-gray-300 px-4 py-2 text-sm font-bold text-black" colspan="2">
                            VIII&nbsp;&nbsp;&nbsp;&nbsp;Pengambilan Keputusan</td>
                        <td class="border border-gray-300 range-scale" colspan="5">
                            <div class="progress-container">
                                <div class="h-full bg-green-600 rounded" style="width: 100%;"></div>
                                <div class="absolute right-0 top-0 bottom-0 flex items-center pr-2">
                                    <span class="text-xs font-bold text-black">100%</span>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr class="bg-gray-50">
                        <td class="border border-gray-300 px-4 py-1 text-xs text-black">1.</td>
                        <td class="border border-gray-300 px-4 py-1 text-xs text-black">Kemampuan membuat keputusan
                            yang baik secara tepat waktu dan dengan keyakinan diri
                            setelah mempettimbangkan prinsip kehati-hatian, dirumuskan secara sistematis dan
                            seksama berdasarkan berbagai informasi, alternatif pemecahan masalah dan
                            konsekuensinya, serta bettanggung jawab atas keputusan yang diambil.
                            Level 4
                            Menyelesaikan masalah yang mengandung risiko tinggi, mengantisipasi dampak keputusan,
                            membuat tindakan pengamanan; mitigasi risiko.</td>
                        <td class="border border-gray-300 range-scale"></td>
                        <td class="border border-gray-300 range-scale"></td>
                        <td class="border border-gray-300 range-scale text-center text-black">X</td>
                        <td class="border border-gray-300 range-scale"></td>
                        <td class="border border-gray-300 range-scale"></td>
                    </tr>

                    {{-- IX. Perekat Bangsa --}}
                    <tr>
                        <td class="border border-gray-300 px-4 py-2 text-sm font-bold text-black" colspan="2">
                            IX&nbsp;&nbsp;&nbsp;&nbsp;Perekat Bangsa</td>
                        <td class="border border-gray-300 range-scale" colspan="5">
                            <div class="progress-container">
                                <div class="h-full bg-green-600 rounded" style="width: 100%;"></div>
                                <div class="absolute right-0 top-0 bottom-0 flex items-center pr-2">
                                    <span class="text-xs font-bold text-black">100%</span>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr class="bg-gray-50">
                        <td class="border border-gray-300 px-4 py-1 text-xs text-black">1.</td>
                        <td class="border border-gray-300 px-4 py-1 text-xs text-black">Kemampuan dalam mempromosikan
                            sikap toleransi, keterbukaan, peka terhadap perbedaan
                            individu/kelompok masyarakat; mampu menjadi perpanjangan tangan pemerintah dalam
                            mempersatukan masyarakat dan membangun hubungan sosial psikologis dengan
                            masyarakat ditengah kemajemukan Indonesia sehingga menciptakan kelekatan yang kuat
                            antara ASN dan para pemangku kepentingan serta diantara para pemangku kepentingan itu
                            sendiri; menjaga, mengembangkan, dan mewujudkan rasa persatuan dan kesatuan dalam
                            kehidupan bermasyarakat, berbangsa dan bernegara Indonesia
                            Level 4
                            Mendayagunakan perbedaan secara konstruktif dan kreatif untuk meningkatkan efektifitas
                            organisasi.</td>
                        <td class="border border-gray-300 range-scale"></td>
                        <td class="border border-gray-300 range-scale"></td>
                        <td class="border border-gray-300 range-scale"></td>
                        <td class="border border-gray-300 range-scale text-center text-black">X</td>
                        <td class="border border-gray-300 range-scale"></td>
                    </tr>
                </tbody>
            </table>
        </div>

    </div>
</x-layouts.app>
