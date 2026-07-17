<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <meta name="description" content="SPSP (Static Pribadi Spider Plot) - Sistem Pemetaan Potensi & Kompetensi SDM Terstandarisasi dan Terdaftar HAKI Sejak 2004 dari PT. Quantum HRM Internasional." />
        
        <!-- Open Graph Tags -->
        <meta property="og:title" content="SPSP - Static Pribadi Spider Plot | Quantum HRM Internasional" />
        <meta property="og:description" content="Sistem analisis psikologis & kompetensi kerja berbasis grafik radar (Spider Plot) presisi tinggi untuk keselarasan karir dan kepemimpinan." />
        <meta property="og:image" content="{{ asset('images/thumb-qhrmi.webp') }}" />
        <meta property="og:type" content="website" />
        <meta property="og:url" content="{{ url('/') }}" />

        <title>SPSP - Static Pribadi Spider Plot | Quantum HRM Internasional</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <link rel="icon" type="image/x-icon" href="{{ asset('images/thumb-qhrmi.webp') }}">
        
        {{-- Preload LCP Images --}}
        <link rel="preload" as="image" href="{{ asset('images/thumb-qhrmi.webp') }}" fetchpriority="high">
        
        <!-- Google Fonts: Lora, IBM Plex Sans, IBM Plex Mono -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;500;600;700&family=IBM+Plex+Sans:wght@300;400;500;600;700&family=Lora:ital,wght@0,400..700;1,400..700&display=swap" rel="stylesheet">

        <style>
            .btn-profile {
                transition: all 0.2s ease-in-out;
            }
            .btn-profile:not(.active):hover {
                background-color: #faf8f5 !important; /* warm-ivory */
                color: #171412 !important; /* primary-ink */
            }
            .btn-profile.active {
                background-color: #b91c1c !important; /* rust-red */
                color: #ffffff !important;
            }
            .btn-profile.active:hover {
                background-color: #991b1b !important; /* rust-red/90 */
                color: #ffffff !important;
            }
        </style>
    </head>

    <body class="min-h-screen flex flex-col bg-warm-ivory text-primary-ink font-technical antialiased selection:bg-rust-red/20">

        <!-- Header / Navigation Bar -->
        <header class="sticky top-0 z-40 w-full bg-white/90 backdrop-blur-md border-b border-warm-border transition-colors duration-300">
            <div class="max-w-7xl mx-auto px-6 h-16 flex items-center justify-between">
                <!-- Brand Logo & Name -->
                <a href="{{ url('/') }}" class="flex items-center gap-3 group">
                    <img src="{{ asset('images/thumb-qhrmi.webp') }}" alt="Logo QHRMI" class="h-10 w-10 object-contain rounded-md" />
                    <div class="flex flex-col border-l border-warm-border pl-3">
                        <span class="text-sm font-bold tracking-tight text-primary-ink uppercase">Quantum HRM</span>
                        <span class="text-[10px] font-semibold text-primary-ink/70 tracking-wider uppercase -mt-0.5">Internasional</span>
                    </div>
                </a>

                <!-- Header Actions & Navigation (Accessibility: semantic nav wrapper) -->
                <nav aria-label="Navigasi Utama" class="flex items-center gap-6">
                    <a href="#metodologi" class="hidden md:inline-flex text-xs font-bold text-primary-ink/80 hover:text-rust-red transition-colors">
                        Metodologi
                    </a>
                    <a href="#kredibilitas" class="hidden md:inline-flex text-xs font-bold text-primary-ink/80 hover:text-rust-red transition-colors">
                        Kredibilitas
                    </a>
                    <a href="#demo" class="hidden sm:inline-flex text-xs font-bold text-primary-ink/80 hover:text-rust-red transition-colors">
                        Jadwalkan Konsultasi
                    </a>
                    
                    <a href="{{ route('login') }}" class="inline-flex items-center justify-center gap-1.5 border border-warm-border bg-white hover:bg-warm-ivory text-primary-ink text-xs font-bold px-4 py-2.5 rounded-md transition-all duration-200 shadow-xs">
                        Login Portal <i class="fa-solid fa-arrow-right-to-bracket text-[10px] text-rust-red"></i>
                    </a>
                </nav>
            </div>
        </header>

        <!-- Hero Section -->
        <main class="flex-grow">
            <section class="relative overflow-hidden pt-6 pb-8 lg:pt-10 lg:pb-12 border-b border-warm-border bg-white animate-none">
                <div class="max-w-7xl mx-auto px-6 grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-16 items-center">
                    
                    <!-- Left Column: Context & Headline -->
                    <div class="lg:col-span-6 text-left flex flex-col justify-center">
                        <!-- Eyebrow Badge -->
                        <div class="font-mono-data text-xs font-bold tracking-widest text-rust-red uppercase mb-2">
                            PEMETAAN POTENSI &amp; KOMPETENSI SDM
                        </div>

                        <!-- Main Heading (Lora Serif) -->
                        <h1 class="font-display text-4xl sm:text-5xl lg:text-[46px] font-bold text-primary-ink tracking-tight leading-[1.15] mb-3">
                            Static Pribadi <br class="hidden sm:inline" />
                            Spider Plot
                        </h1>

                        <!-- Slogan -->
                        <div class="border-l-2 border-rust-red pl-4 py-1 font-display italic text-primary-ink/80 font-medium mb-3 text-sm">
                            "The Right Man, On The Right Place, With The Right Character"
                        </div>

                        <!-- Description -->
                        <p class="text-xs sm:text-sm text-primary-ink/75 leading-relaxed max-w-xl mb-4">
                            Sistem analisis psikologis dan kompetensi kerja berbasis grafik radar (Spider Plot). SPSP secara presisi memetakan profil individu terhadap standar kompetensi jabatan untuk mendukung keputusan rekrutmen, penempatan, dan promosi kepemimpinan secara objektif.
                        </p>

                        <!-- HAKI Metadata (Professional scientific certificate block) -->
                        <div class="inline-flex items-center gap-3 bg-warm-ivory border border-warm-border rounded px-3.5 py-2 max-w-fit mb-4">
                            <i class="fa-solid fa-shield-halved text-rust-red text-base shrink-0"></i>
                            <div class="flex flex-col">
                                <span class="text-[9px] font-bold text-primary-ink/50 uppercase tracking-wider font-mono-data">Kredibilitas Hukum Terdaftar</span>
                                <span class="text-xs font-bold text-primary-ink/80 font-mono-data">DIRJEN HAKI No. 027762 (Sejak 10 Maret 2004)</span>
                            </div>
                        </div>

                        <!-- CTA Action Buttons -->
                        <div class="flex flex-col sm:flex-row gap-3 items-stretch sm:items-center">
                            <a href="#demo" class="inline-flex items-center justify-center gap-2 bg-rust-red hover:bg-rust-red/90 text-white font-bold text-xs px-5 py-3 rounded transition-all duration-200 shadow-sm active:scale-98">
                                AJUKAN DEMO &amp; KONSULTASI <i class="fa-solid fa-calendar-check text-[11px]"></i>
                            </a>
                            <a href="#metodologi" class="inline-flex items-center justify-center gap-2 border border-warm-border bg-white hover:bg-warm-ivory text-primary-ink font-bold text-xs px-5 py-3 rounded transition-all duration-200">
                                PELAJARI METODOLOGI
                            </a>
                        </div>
                    </div>

                    <!-- Right Column: SPSP Interactive Assessment Console -->
                    <div class="lg:col-span-6 w-full max-w-2xl mx-auto">
                        <!-- Assessment Card Console -->
                        <div class="bg-white border border-warm-border rounded-lg shadow-sm overflow-hidden flex flex-col">
                            <!-- SVG Interactive Radar Chart Container (Constrained Height) -->
                            <div class="p-2 bg-white flex items-center justify-center h-[380px] overflow-hidden">
                                <svg viewBox="0 80 810 570" xmlns="http://www.w3.org/2000/svg" class="h-full w-auto" role="img" aria-label="Simulasi Spider Plot interaktif membandingkan individu terhadap standar">
                                    <title>Simulator Radar Chart SPSP</title>
                                    
                                    <!-- Grid rings (Values 1-4) -->
                                    <polygon points="390,332.5 417.9,341.6 435.2,365.3 435.2,394.7 417.9,418.4 390,427.5 362.1,418.4 344.8,394.7 344.8,365.3 362.1,341.6" fill="none" stroke="var(--color-warm-border)" stroke-width="1.5"/>
                                    <polygon points="390,285 445.8,303.1 480.4,350.6 480.4,409.4 445.8,456.9 390,475 334.2,456.9 299.7,409.4 299.7,350.6 334.2,303.1" fill="none" stroke="var(--color-warm-border)" stroke-width="1.5"/>
                                    <polygon points="390,237.5 473.8,264.7 525.5,336.0 525.5,424.0 473.8,495.3 390,522.5 306.2,495.3 254.5,424.0 254.5,336.0 306.2,264.7" fill="none" stroke="var(--color-warm-border)" stroke-width="1.5"/>
                                    <polygon points="390,190 501.7,226.3 570.7,321.3 570.7,438.7 501.7,533.7 390,570 278.3,533.7 209.3,438.7 209.3,321.3 278.3,226.3" fill="none" stroke="var(--color-warm-border)" stroke-width="1.5"/>

                                    <!-- Axis lines -->
                                    <g stroke="var(--color-warm-border)" stroke-width="1">
                                        <line x1="390" y1="380" x2="390" y2="190"/>
                                        <line x1="390" y1="380" x2="501.7" y2="226.3"/>
                                        <line x1="390" y1="380" x2="570.7" y2="321.3"/>
                                        <line x1="390" y1="380" x2="570.7" y2="438.7"/>
                                        <line x1="390" y1="380" x2="501.7" y2="533.7"/>
                                        <line x1="390" y1="380" x2="390" y2="570"/>
                                        <line x1="390" y1="380" x2="278.3" y2="533.7"/>
                                        <line x1="390" y1="380" x2="209.3" y2="438.7"/>
                                        <line x1="390" y1="380" x2="209.3" y2="321.3"/>
                                        <line x1="390" y1="380" x2="278.3" y2="226.3"/>
                                    </g>

                                    <!-- Grid Ring Scale Labels -->
                                    <g fill="var(--color-primary-ink)" fill-opacity="0.35" font-size="12" font-family="IBM Plex Mono, monospace" font-weight="bold">
                                        <text x="400" y="194">4.0</text>
                                        <text x="400" y="241.5">3.0</text>
                                        <text x="400" y="289">2.0</text>
                                        <text x="400" y="336.5">1.0</text>
                                    </g>

                                    <!-- Toleransi (Dashed Primary Ink) -->
                                    <polygon points="390,261.3 459.8,283.9 502.9,343.3 502.9,416.7 459.8,476.1 390,498.8 320.2,476.1 277.1,416.7 277.1,343.3 320.2,283.9"
                                        fill="none" stroke="var(--color-primary-ink)" stroke-width="2" stroke-opacity="0.7" stroke-dasharray="2,4"/>

                                    <!-- Standar Jabatan (Solid Primary Ink) -->
                                    <polygon points="390,237.5 473.8,264.7 525.5,336.0 525.5,424.0 473.8,495.3 390,522.5 306.2,495.3 254.5,424.0 254.5,336.0 306.2,264.7"
                                        fill="none" stroke="var(--color-primary-ink)" stroke-width="2"/>

                                    <!-- Individu (Actual Score Line - Animated Rust Red Dashed) -->
                                    <polygon id="individual-polygon" points="390,228.0 468.2,272.4 548.1,328.6 507.5,418.2 473.8,495.3 390,494.0 309.0,491.4 240.9,428.4 268.0,340.4 303.4,260.9"
                                        fill="var(--color-rust-red)" fill-opacity="0.08" stroke="var(--color-rust-red)" stroke-width="3" stroke-dasharray="6,4"/>

                                    <!-- Individu vertex markers -->
                                    <g fill="var(--color-rust-red)">
                                        <circle id="marker-0" cx="390" cy="228.0" r="5.5"/>
                                        <circle id="marker-1" cx="468.2" cy="272.4" r="5.5"/>
                                        <circle id="marker-2" cx="548.1" cy="328.6" r="5.5"/>
                                        <circle id="marker-3" cx="507.5" cy="418.2" r="5.5"/>
                                        <circle id="marker-4" cx="473.8" cy="495.3" r="5.5"/>
                                        <circle id="marker-5" cx="390" cy="494.0" r="5.5"/>
                                        <circle id="marker-6" cx="309.0" cy="491.4" r="5.5"/>
                                        <circle id="marker-7" cx="240.9" cy="428.4" r="5.5"/>
                                        <circle id="marker-8" cx="268.0" cy="340.4" r="5.5"/>
                                        <circle id="marker-9" cx="303.4" cy="260.9" r="5.5"/>
                                    </g>

                                    <!-- Individu value labels (Enlarged Monospace values) -->
                                    <g fill="var(--color-rust-red)" font-size="15" font-weight="bold" font-family="IBM Plex Mono, monospace">
                                        <text id="value-0" x="390" y="210" text-anchor="middle">3.2</text>
                                        <text id="value-1" x="480.4" y="261.1" text-anchor="start">2.8</text>
                                        <text id="value-2" x="563.4" y="324.3" text-anchor="start">3.5</text>
                                        <text id="value-3" x="524.8" y="422.5" text-anchor="start">2.6</text>
                                        <text id="value-4" x="486.0" y="506.6" text-anchor="start">3.0</text>
                                        <text id="value-5" x="390" y="514" text-anchor="middle">2.4</text>
                                        <text id="value-6" x="296.8" y="502.8" text-anchor="end">2.9</text>
                                        <text id="value-7" x="225.6" y="432.8" text-anchor="end">3.3</text>
                                        <text id="value-8" x="252.7" y="336.0" text-anchor="end">2.7</text>
                                        <text id="value-9" x="291.2" y="249.6" text-anchor="end">3.1</text>
                                    </g>

                                    <!-- Indonesian Category Labels -->
                                    <g fill="var(--color-primary-ink)" fill-opacity="0.9" font-size="18" font-weight="bold" font-family="IBM Plex Sans, sans-serif">
                                        <text x="390" y="112" text-anchor="middle">Kemampuan Umum</text>
                                        <text x="528.2" y="189.9" text-anchor="start">Inisiatif Kerja</text>
                                        <text x="613.5" y="307.4" text-anchor="start">Kepemimpinan Kreatif</text>
                                        <text x="613.5" y="452.6" text-anchor="start">Penetapan Sasaran</text>
                                        <text x="528.2" y="570.1" text-anchor="start">Kemampuan Manajerial</text>
                                        <text x="390" y="619" text-anchor="middle">Kecerdasan Emosional</text>
                                        <text x="251.8" y="570.1" text-anchor="end">Profil Kepribadian</text>
                                        <text x="166.5" y="452.6" text-anchor="end">Hubungan Interpersonal</text>
                                        <text x="166.5" y="307.4" text-anchor="end">Gaya Kerja</text>
                                        <text x="251.8" y="189.9" text-anchor="end">Loyalitas</text>
                                    </g>
                                </svg>
                            </div>

                            <!-- Legend -->
                            <div class="px-4 pb-4 pt-2 border-b border-warm-border grid grid-cols-3 gap-3 text-center text-[10px] font-bold uppercase font-mono-data text-primary-ink/75">
                                <div class="flex items-center justify-center gap-2 bg-warm-ivory border border-warm-border py-1.5 rounded">
                                    <svg class="w-5 h-1 shrink-0" viewBox="0 0 20 4" xmlns="http://www.w3.org/2000/svg">
                                        <line x1="0" y1="2" x2="20" y2="2" stroke="var(--color-primary-ink)" stroke-width="3" />
                                    </svg>
                                    <span>Standar (3.0)</span>
                                </div>
                                <div class="flex items-center justify-center gap-2 bg-warm-ivory border border-warm-border py-1.5 rounded">
                                    <svg class="w-5 h-1 shrink-0" viewBox="0 0 20 4" xmlns="http://www.w3.org/2000/svg">
                                        <line x1="0" y1="2" x2="20" y2="2" stroke="var(--color-primary-ink)" stroke-opacity="0.8" stroke-width="3" stroke-dasharray="2,3" />
                                    </svg>
                                    <span>Toleransi (2.5)</span>
                                </div>
                                <div class="flex items-center justify-center gap-2 bg-warm-ivory border border-warm-border py-1.5 rounded">
                                    <svg class="w-5 h-1 shrink-0" viewBox="0 0 20 4" xmlns="http://www.w3.org/2000/svg">
                                        <line x1="0" y1="2" x2="20" y2="2" stroke="var(--color-rust-red)" stroke-width="3" stroke-dasharray="5,2" />
                                    </svg>
                                    <span>Individu</span>
                                </div>
                            </div>

                            <!-- Interactive Simulator Controls -->
                            <div class="p-4 bg-warm-ivory flex flex-col gap-3">
                                <div class="text-[10px] font-bold text-primary-ink/65 uppercase tracking-wider">
                                    Simulasi Profil Asesi:
                                </div>
                                <div class="grid grid-cols-3 gap-2">
                                    <button onclick="selectProfile('manager')" id="btn-manager" class="btn-profile border border-warm-border bg-white text-primary-ink font-bold text-[10px] py-1.5 px-2 rounded cursor-pointer shadow-xs">
                                        Kandidat Direktur
                                    </button>
                                    <button onclick="selectProfile('tech')" id="btn-tech" class="btn-profile border border-warm-border bg-white text-primary-ink font-bold text-[10px] py-1.5 px-2 rounded cursor-pointer shadow-xs">
                                        Spesialis IT
                                    </button>
                                    <button onclick="selectProfile('admin')" id="btn-admin" class="btn-profile border border-warm-border bg-white text-primary-ink font-bold text-[10px] py-1.5 px-2 rounded cursor-pointer shadow-xs">
                                        Staf Administrasi
                                    </button>
                                </div>
                                <div class="p-2.5 bg-white border border-warm-border rounded text-[11px] leading-relaxed text-primary-ink/75" id="profile-description">
                                    Pilih salah satu tombol profil di atas untuk mensimulasikan pemetaan skor kompetensi nyata asesi terhadap standar jabatan secara real-time.
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </section>

            <!-- Methodology Section (Sequence Flow) -->
            <section id="metodologi" class="py-20 border-b border-warm-border bg-warm-ivory scroll-mt-16">
                <div class="max-w-7xl mx-auto px-6">
                    
                    <div class="max-w-3xl mx-auto text-center mb-16">
                        <span class="font-mono-data text-xs font-bold text-rust-red tracking-widest uppercase mb-3 block">
                            PILAR UTAMA SPSP
                        </span>
                        <h2 class="font-display text-3xl md:text-4xl font-bold text-primary-ink tracking-tight">
                            Metodologi Pemetaan Secara Terpadu &amp; Objektif
                        </h2>
                        <p class="mt-4 text-sm sm:text-base text-primary-ink/75 leading-relaxed">
                            Static Pribadi Spider Plot (SPSP) bekerja secara linier dan terstruktur untuk menghasilkan pemetaan psikologis serta kompetensi karyawan yang tidak bias.
                        </p>
                    </div>

                    <!-- Flow Sequence Layout -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-8 items-start relative">
                        
                        <!-- Step 1 -->
                        <div class="bg-white border border-warm-border p-8 rounded-lg shadow-xs flex flex-col relative">
                            <span class="font-mono-data text-5xl font-bold text-slate-100 absolute top-4 right-6 select-none">01</span>
                            <div class="font-mono-data text-[10px] font-bold text-rust-red tracking-wider uppercase mb-6">Tahap Penetapan</div>
                            <h3 class="text-lg font-bold text-primary-ink mb-3">1. Kriteria Standar Jabatan</h3>
                            <p class="text-primary-ink/75 text-xs sm:text-sm leading-relaxed">
                                Organisasi menentukan syarat kompetensi minimum untuk sebuah peran kerja. Nilai acuan ini (standard baseline) menjadi patokan garis pemetaan (solid line) pada grafik SPSP.
                            </p>
                        </div>

                        <!-- Step 2 -->
                        <div class="bg-white border border-warm-border p-8 rounded-lg shadow-xs flex flex-col relative">
                            <span class="font-mono-data text-5xl font-bold text-slate-100 absolute top-4 right-6 select-none">02</span>
                            <div class="font-mono-data text-[10px] font-bold text-rust-red tracking-wider uppercase mb-6">Tahap Pengukuran</div>
                            <h3 class="text-lg font-bold text-primary-ink mb-3">2. Pengukuran Profil Individu</h3>
                            <p class="text-primary-ink/75 text-xs sm:text-sm leading-relaxed">
                                Asesi (karyawan/kandidat) melaksanakan evaluasi psikometri dan kompetensi secara mandiri. Hasil pengukuran diplot sebagai skor aktual (colored line) di radar chart.
                            </p>
                        </div>

                        <!-- Step 3 -->
                        <div class="bg-white border border-warm-border p-8 rounded-lg shadow-xs flex flex-col relative">
                            <span class="font-mono-data text-5xl font-bold text-slate-100 absolute top-4 right-6 select-none">03</span>
                            <div class="font-mono-data text-[10px] font-bold text-rust-red tracking-wider uppercase mb-6">Tahap Keputusan</div>
                            <h3 class="text-lg font-bold text-primary-ink mb-3">3. Batas Toleransi Deviasi</h3>
                            <p class="text-primary-ink/75 text-xs sm:text-sm leading-relaxed">
                                Sistem menghitung tingkat kesenjangan (gap) antara standar dan profil individu. Batas toleransi deviasi (dashed line) menjadi pedoman kelayakan penempatan asesi secara objektif.
                            </p>
                        </div>

                    </div>
                </div>
            </section>

            <!-- Social Proof & Statistics -->
            <section id="kredibilitas" class="py-20 border-b border-warm-border bg-white scroll-mt-16">
                <div class="max-w-7xl mx-auto px-6">
                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
                        
                        <!-- Text -->
                        <div class="lg:col-span-5 flex flex-col justify-center">
                            <span class="font-mono-data text-xs font-bold text-rust-red tracking-widest uppercase mb-3">
                                REKAM JEJAK &amp; KREDIBILITAS
                            </span>
                            <h2 class="font-display text-3xl font-bold text-primary-ink tracking-tight mb-6">
                                Dipercaya oleh Lembaga Negara &amp; Korporasi Terkemuka
                            </h2>
                            <p class="text-primary-ink/75 text-sm sm:text-base leading-relaxed mb-6">
                                Sejak terdaftar HAKI pada tahun 2004, metodologi pemetaan SPSP telah teruji dalam berbagai proyek restrukturisasi, promosi jabatan eselon, dan rekrutmen massal secara andal.
                            </p>
                            
                            <div class="flex flex-col gap-3 font-mono-data text-xs text-primary-ink/70">
                                <div class="flex items-center gap-2">
                                    <i class="fa-solid fa-circle-check text-rust-red text-sm shrink-0"></i>
                                    <span>Memenuhi standar UU PDP untuk data psikologis</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <i class="fa-solid fa-circle-check text-rust-red text-sm shrink-0"></i>
                                    <span>Metodologi berstandar ilmiah &amp; berlisensi hukum</span>
                                </div>
                            </div>
                        </div>

                        <!-- Stats and Client Categories -->
                        <div class="lg:col-span-7 flex flex-col gap-8">
                            <!-- Statistics -->
                            <div class="grid grid-cols-3 gap-4">
                                <div class="p-6 bg-warm-ivory border border-warm-border rounded flex flex-col text-center">
                                    <span class="font-mono-data text-3xl font-bold text-rust-red mb-1">22+</span>
                                    <span class="text-[10px] font-bold text-primary-ink/55 uppercase tracking-wider">Tahun Riset &amp; Penggunaan</span>
                                </div>
                                <div class="p-6 bg-warm-ivory border border-warm-border rounded flex flex-col text-center">
                                    <span class="font-mono-data text-3xl font-bold text-rust-red mb-1">500K+</span>
                                    <span class="text-[10px] font-bold text-primary-ink/55 uppercase tracking-wider">Profil Asesi Terpetakan</span>
                                </div>
                                <div class="p-6 bg-warm-ivory border border-warm-border rounded flex flex-col text-center">
                                    <span class="font-mono-data text-3xl font-bold text-rust-red mb-1">150+</span>
                                    <span class="text-[10px] font-bold text-primary-ink/55 uppercase tracking-wider">Mitra BUMN &amp; Instansi</span>
                                </div>
                            </div>

                            <!-- Client Category Badges -->
                            <div class="border border-warm-border rounded p-6">
                                <h4 class="text-xs font-bold text-primary-ink/60 uppercase tracking-wider mb-4 font-mono-data">
                                    Kategori Klien Pengguna SPSP:
                                </h4>
                                <div class="flex flex-wrap gap-2 text-xs font-bold text-primary-ink/80">
                                    <span class="bg-warm-ivory border border-warm-border px-3.5 py-2 rounded">Kementerian &amp; Lembaga Pemerintah</span>
                                    <span class="bg-warm-ivory border border-warm-border px-3.5 py-2 rounded">Badan Usaha Milik Negara (BUMN)</span>
                                    <span class="bg-warm-ivory border border-warm-border px-3.5 py-2 rounded">Korporasi Swasta Nasional</span>
                                    <span class="bg-warm-ivory border border-warm-border px-3.5 py-2 rounded">Lembaga Pendidikan &amp; TNI/Polri</span>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </section>

            <!-- Inquiry Form & Demo request -->
            <section id="demo" class="py-20 border-b border-warm-border bg-warm-ivory scroll-mt-16">
                <div class="max-w-3xl mx-auto px-6">
                    <div class="bg-white border border-warm-border rounded-lg shadow-sm p-8 md:p-10 flex flex-col">
                        <div class="text-center mb-8">
                            <span class="font-mono-data text-[10px] font-bold text-rust-red tracking-widest uppercase mb-2 block">
                                FORMULIR LAYANAN
                            </span>
                            <h2 class="font-display text-2xl md:text-3xl font-bold text-primary-ink tracking-tight">
                                Jadwalkan Demo &amp; Konsultasi SPSP
                            </h2>
                            <p class="mt-3 text-xs sm:text-sm text-primary-ink/75 leading-relaxed">
                                Silakan isi formulir di bawah ini. Tim analis Quantum HRM Internasional akan segera menghubungi Anda untuk mendiskusikan kebutuhan pemetaan kompetensi di instansi Anda.
                            </p>
                        </div>

                        <!-- Inquiry form submission handler (Dynamic UI) -->
                        <form id="inquiry-form" onsubmit="handleInquirySubmit(event)" class="space-y-4">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div class="flex flex-col gap-1.5">
                                    <label for="name" class="text-[11px] font-bold text-primary-ink/70 uppercase font-mono-data">Nama Lengkap</label>
                                    <input type="text" id="name" required class="bg-white border border-warm-border rounded px-3 py-2 text-sm focus:outline-none focus:border-rust-red font-technical" placeholder="Contoh: Budi Santoso" />
                                </div>
                                <div class="flex flex-col gap-1.5">
                                    <label for="company" class="text-[11px] font-bold text-primary-ink/70 uppercase font-mono-data">Nama Perusahaan/Instansi</label>
                                    <input type="text" id="company" required class="bg-white border border-warm-border rounded px-3 py-2 text-sm focus:outline-none focus:border-rust-red font-technical" placeholder="Contoh: PT. Maju Sentosa" />
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div class="flex flex-col gap-1.5">
                                    <label for="email" class="text-[11px] font-bold text-primary-ink/70 uppercase font-mono-data">Email Kerja</label>
                                    <input type="email" id="email" required class="bg-white border border-warm-border rounded px-3 py-2 text-sm focus:outline-none focus:border-rust-red font-mono-data" placeholder="budi@perusahaan.com" />
                                </div>
                                <div class="flex flex-col gap-1.5">
                                    <label for="phone" class="text-[11px] font-bold text-primary-ink/70 uppercase font-mono-data">Nomor Telepon/WhatsApp</label>
                                    <input type="tel" id="phone" required class="bg-white border border-warm-border rounded px-3 py-2 text-sm focus:outline-none focus:border-rust-red font-mono-data" placeholder="0812XXXXXXXX" />
                                </div>
                            </div>

                            <div class="flex flex-col gap-1.5">
                                <label for="message" class="text-[11px] font-bold text-primary-ink/70 uppercase font-mono-data">Kebutuhan Pemetaan/Pesan Tambahan</label>
                                <textarea id="message" rows="4" required class="bg-white border border-warm-border rounded px-3 py-2 text-sm focus:outline-none focus:border-rust-red font-technical" placeholder="Jelaskan secara singkat rencana pemetaan atau kebutuhan konsultasi asesi..."></textarea>
                            </div>

                            <button type="submit" class="w-full bg-rust-red hover:bg-rust-red/90 text-white font-bold text-xs py-3 rounded transition-all duration-200 shadow-xs uppercase tracking-wider">
                                Kirim Pengajuan Konsultasi
                            </button>
                        </form>

                        <!-- Submission Success State -->
                        <div id="form-success-card" class="hidden bg-emerald-50 border border-emerald-200 text-emerald-900 rounded p-6 text-center space-y-3">
                            <i class="fa-solid fa-circle-check text-emerald-600 text-3xl"></i>
                            <h3 class="font-bold text-base">Pengajuan Berhasil Dikirim</h3>
                            <p class="text-xs text-emerald-800 leading-relaxed">
                                Terima kasih atas minat Anda pada SPSP. Tim analis Quantum HRM Internasional akan mengirimkan proposal demo dan menghubungi Anda melalui email/WhatsApp dalam waktu 1x24 jam kerja.
                            </p>
                        </div>
                    </div>
                </div>
            </section>
        </main>

        <!-- Footer -->
        <footer class="bg-primary-ink text-slate-400 py-12 border-t border-warm-border/10">
            <div class="max-w-7xl mx-auto px-6 grid grid-cols-1 md:grid-cols-12 gap-8 items-start">
                
                <!-- Column 1: Branding -->
                <div class="md:col-span-4 flex flex-col gap-3">
                    <div class="flex items-center gap-3">
                        <img src="{{ asset('images/thumb-qhrmi.webp') }}" alt="Logo QHRMI" class="h-8 w-8 object-contain rounded-md animate-none" />
                        <span class="text-xs font-bold tracking-wider uppercase text-white font-mono-data">SPSP System</span>
                    </div>
                    <p class="text-xs text-slate-400 leading-relaxed max-w-sm">
                        Static Pribadi Spider Plot (SPSP) merupakan sistem pemetaan psikologi dan kompetensi terstandarisasi yang dikembangkan dan didaftarkan secara sah oleh PT. Quantum HRM Internasional.
                    </p>
                </div>

                <!-- Column 2: Alamat Kantor -->
                <div class="md:col-span-4 flex flex-col gap-2">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-200 font-mono-data">Kantor Pusat</span>
                    <p class="text-xs text-slate-400 leading-relaxed">
                        Jl. Sidosermo I No. 10, Surabaya, 60239,<br>
                        Jawa Timur, Indonesia
                    </p>
                    <div class="flex flex-col gap-1 mt-1 text-xs text-slate-400 font-mono-data">
                        <span class="flex items-center gap-1.5"><i class="fa-solid fa-phone text-slate-500 text-[10px]"></i> 031-8436700</span>
                        <span class="flex items-center gap-1.5"><i class="fa-solid fa-envelope text-slate-500 text-[10px]"></i> support@quantum-hrmi.com</span>
                    </div>
                </div>

                <!-- Column 3: Tautan Penting -->
                <div class="md:col-span-4 flex flex-col gap-2">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-200 font-mono-data">Tautan Resmi</span>
                    <nav aria-label="Navigasi Footer" class="flex flex-col gap-1.5 text-xs text-slate-400 font-mono-data">
                        <a href="#metodologi" class="hover:text-white transition-colors w-fit">Metodologi SPSP</a>
                        <a href="#kredibilitas" class="hover:text-white transition-colors w-fit">Kredibilitas Mitra</a>
                        <a href="{{ route('login') }}" class="hover:text-white transition-colors w-fit">Login Portal</a>
                        <a href="{{ route('privacy') }}" class="hover:text-white transition-colors w-fit font-bold text-rust-red">Kebijakan Privasi SPSP</a>
                    </nav>
                </div>

            </div>

            <!-- Bottom Copyright bar (Ensuring contrast ratio >= 4.5:1 using text-slate-400) -->
            <div class="max-w-7xl mx-auto px-6 mt-12 pt-6 border-t border-warm-border/10 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs font-mono-data">
                <div>
                    &copy; 2026 PT. Quantum HRM Internasional. Hak Cipta Dilindungi Undang-Undang.
                </div>
                <div class="tracking-wide">
                    HAK CIPTA TERDAFTAR DIRJEN HAKI NO. 027762
                </div>
            </div>
        </footer>

        <!-- Vanilla JS Simulator & Form Scripts -->
        <script>
            // Data Profiles for Simulator
            const simulatorProfiles = {
                manager: {
                    name: "Kandidat Direktur",
                    scores: [3.8, 3.5, 3.7, 3.6, 3.8, 3.6, 3.2, 3.4, 3.5, 3.8],
                    desc: "Profil Kandidat Direktur: Menunjukkan keunggulan mutlak pada Kemampuan Manajerial (3.8), Kepemimpinan Kreatif (3.7), dan Kecerdasan Emosional (3.6) yang sangat solid di atas baseline standar."
                },
                tech: {
                    name: "Spesialis IT",
                    scores: [3.9, 3.2, 2.4, 2.8, 2.5, 3.0, 2.7, 2.6, 3.9, 3.6],
                    desc: "Profil Spesialis IT: Menonjol tinggi pada Kemampuan Umum (3.9) dan Gaya Kerja (3.9) yang teliti-fokus, namun bersifat spesifik pada fungsi teknis individual."
                },
                admin: {
                    name: "Staf Administrasi",
                    scores: [2.8, 3.0, 2.2, 3.2, 2.4, 3.1, 3.4, 3.2, 3.6, 3.8],
                    desc: "Profil Staf Administrasi: Memiliki Loyalitas (3.8) dan Gaya Kerja (3.6) yang teratur, menjaga kepatuhan operasional tinggi dengan toleransi deviasi yang aman."
                }
            };

            let activeScores = [3.2, 2.8, 3.5, 2.6, 3.0, 2.4, 2.9, 3.3, 2.7, 3.1];
            let radarAnimId = null;

            // Coordinate generation logic
            function calculatePointsStr(scores) {
                const Cx = 390;
                const Cy = 380;
                const step = 47.5; // 190 max radius / 4 value steps
                return scores.map((val, i) => {
                    const R = val * step;
                    const rad = (i * 36) * Math.PI / 180;
                    const x = (Cx + R * Math.sin(rad)).toFixed(1);
                    const y = (Cy - R * Math.cos(rad)).toFixed(1);
                    return `${x},${y}`;
                }).join(' ');
            }

            // Update SVG Chart elements
            function renderChartState(scores) {
                const polygon = document.getElementById('individual-polygon');
                polygon.setAttribute('points', calculatePointsStr(scores));
                
                const Cx = 390;
                const Cy = 380;
                const step = 47.5;
                
                scores.forEach((val, i) => {
                    const R = val * step;
                    const rad = (i * 36) * Math.PI / 180;
                    const x = (Cx + R * Math.sin(rad)).toFixed(1);
                    const y = (Cy - R * Math.cos(rad)).toFixed(1);
                    
                    const circle = document.getElementById(`marker-${i}`);
                    if (circle) {
                        circle.setAttribute('cx', x);
                        circle.setAttribute('cy', y);
                    }
                    
                    const label = document.getElementById(`value-${i}`);
                    if (label) {
                        const labelR = R + 20;
                        const labelX = (Cx + labelR * Math.sin(rad)).toFixed(1);
                        const labelY = (Cy - labelR * Math.cos(rad)).toFixed(1);
                        label.setAttribute('x', labelX);
                        label.setAttribute('y', labelY);
                        label.textContent = val.toFixed(1);
                    }
                });
            }

            // Animate points shift
            function transitionToScores(targetScores) {
                const duration = 400; // ms
                const start = performance.now();
                const initialScores = [...activeScores];
                
                if (radarAnimId) cancelAnimationFrame(radarAnimId);
                
                function frame(timestamp) {
                    const elapsed = timestamp - start;
                    const progress = Math.min(elapsed / duration, 1);
                    const ease = progress * (2 - progress); // Ease out quad
                    
                    activeScores = initialScores.map((val, i) => {
                        return val + (targetScores[i] - val) * ease;
                    });
                    
                    renderChartState(activeScores);
                    
                    if (progress < 1) {
                        radarAnimId = requestAnimationFrame(frame);
                    } else {
                        radarAnimId = null;
                    }
                }
                
                radarAnimId = requestAnimationFrame(frame);
            }

            // Profile selection trigger
            function selectProfile(key) {
                const profile = simulatorProfiles[key];
                if (!profile) return;
                
                // Animate radar chart transition
                transitionToScores(profile.scores);
                
                // Update description UI
                document.getElementById('profile-description').textContent = profile.desc;
                
                // Toggle active buttons style using CSS active class
                document.querySelectorAll('.btn-profile').forEach(btn => {
                    btn.classList.remove('active');
                });
                
                const activeBtn = document.getElementById(`btn-${key}`);
                if (activeBtn) {
                    activeBtn.classList.add('active');
                }
            }

            // Handle Contact Form Submission
            function handleInquirySubmit(event) {
                event.preventDefault();
                
                const form = document.getElementById('inquiry-form');
                const successCard = document.getElementById('form-success-card');
                
                // Animate hiding form and showing success message
                form.classList.add('hidden');
                successCard.classList.remove('hidden');
            }
        </script>
    </body>
</html>
