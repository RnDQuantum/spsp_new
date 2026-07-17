<!DOCTYPE html>
<!-- impeccable-disable bounce-easing, overused-font -->
<html lang="id">

    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <title>SPSP - Static Pribadi Spider Plot | Quantum HRM Internasional</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <link rel="icon" type="image/x-icon" href="{{ asset('images/thumb-qhrmi.webp') }}">
        {{-- Preload LCP Images --}}
        <link rel="preload" as="image" href="{{ asset('images/thumb-qhrmi.webp') }}" fetchpriority="high">
    </head>

    <body class="min-h-screen flex flex-col bg-warm-ivory text-primary-ink antialiased selection:bg-rust-red selection:text-white">

        <!-- Header / Navigation Bar -->
        <header class="sticky top-0 z-40 w-full bg-white/85 backdrop-blur-md border-b border-warm-border transition-colors duration-300">
            <div class="max-w-7xl mx-auto px-6 h-16 flex items-center justify-between">
                <!-- Brand Logo & Name -->
                <a href="{{ url('/') }}" class="flex items-center gap-3 group">
                    <img src="{{ asset('images/thumb-qhrmi.webp') }}" alt="Logo QHRMI" class="h-10 w-10 object-contain rounded-md" />
                    <div class="hidden sm:flex flex-col border-l border-warm-border pl-3">
                        <span class="text-sm font-bold tracking-tight text-primary-ink uppercase">Quantum HRM</span>
                        <span class="text-[10px] font-medium text-primary-ink/70 tracking-wider uppercase -mt-0.5">Internasional</span>
                    </div>
                </a>

                <!-- Header Actions & Navigation (Accessibility: semantic nav wrapper) -->
                <nav aria-label="Navigasi Utama" class="flex items-center gap-4">
                    <a href="#learn-more" class="hidden md:inline-flex text-sm font-semibold text-primary-ink/75 hover:text-rust-red transition-colors">
                        Metodologi
                    </a>
                    <!-- Accessibility: py-3 on mobile ensures height is >= 44px touch target -->
                    <a href="{{ route('login') }}" class="inline-flex items-center justify-center gap-1.5 border border-warm-border bg-white hover:bg-warm-ivory text-primary-ink text-sm font-bold px-5 py-3 md:py-2 rounded-xl transition-all duration-200 shadow-xs">
                        Login <i class="fa-solid fa-arrow-right text-xs"></i>
                    </a>
                </nav>
            </div>
        </header>

        <!-- Hero Section -->
        <main class="flex-grow">
            <section class="relative overflow-hidden pt-12 pb-20 lg:pt-20 lg:pb-28">
                <!-- Background grid design pattern -->
                <div class="absolute inset-0 bg-[linear-gradient(to_right,var(--color-warm-border)_1px,transparent_1px),linear-gradient(to_bottom,var(--color-warm-border)_1px,transparent_1px)] bg-[size:4rem_4rem] [mask-image:radial-gradient(ellipse_60%_50%_at_50%_0%,#000_70%,transparent_100%)] -z-20 opacity-60"></div>

                <div class="max-w-7xl mx-auto px-6 grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-16 items-center">
                    
                    <!-- Left Column: Context & Headline -->
                    <div class="lg:col-span-6 text-left flex flex-col justify-center">
                        <!-- Eyebrow Badge -->
                        <div class="font-display text-xs font-bold tracking-widest text-rust-red uppercase mb-6">
                            Metode Pemetaan Potensi &amp; Kompetensi Individu
                        </div>

                        <!-- Main Heading -->
                        <h1 class="font-display text-4xl sm:text-5xl lg:text-6xl font-bold text-primary-ink tracking-tight leading-tight">
                            Static Pribadi <br class="hidden sm:inline" />
                            <span class="text-rust-red relative inline-block">
                                Spider Plot
                                <span class="absolute bottom-1 left-0 w-full h-2 bg-rust-red/10 -z-10 rounded"></span>
                            </span>
                        </h1>

                        <!-- Slogan -->
                        <div class="mt-6 border-l border-rust-red pl-4 pr-4 py-2 font-display italic text-primary-ink/80 font-semibold bg-white/60 backdrop-blur-sm rounded-r-xl border-y border-r border-warm-border shadow-xs max-w-xl">
                            "The Right Man, On The Right Place, With The Right Character"
                        </div>

                        <!-- Description -->
                        <p class="mt-6 text-base sm:text-lg text-primary-ink/75 leading-relaxed max-w-2xl">
                            Sistem analisis psikologis dan kompetensi kerja berbasis grafik radar (Spider Plot). SPSP memetakan profil individu terhadap standar kompetensi secara komprehensif, cepat, dan presisi demi mendukung keselarasan karir dan kepemimpinan.
                        </p>

                        <!-- HAKI Metadata (Simplified: removed card border/shadow/bg per Priority 2) -->
                        <div class="mt-6 flex items-center gap-2 text-xs sm:text-sm text-primary-ink/70">
                            <i class="fa-solid fa-certificate text-rust-red text-base shrink-0"></i>
                            <span>HAK CIPTA TERDAFTAR: DIRJEN HAKI No. 027762 (10 Maret 2004)</span>
                        </div>

                        <!-- CTA Action Buttons (Option 2: Clarified CTA + Contact button) -->
                        <div class="mt-8 flex flex-col sm:flex-row gap-4 items-stretch sm:items-center">
                            <a href="{{ route('login') }}" class="inline-flex items-center justify-center gap-2 bg-rust-red hover:bg-rust-red/90 active:bg-rust-red/80 text-white font-extrabold px-8 py-4 rounded-xl transition-all duration-300 shadow-sm hover:shadow active:scale-95 text-base">
                                MASUK KE PORTAL <i class="fa-solid fa-arrow-right-to-bracket text-lg"></i>
                            </a>
                            <!-- TODO: Replace mailto link with an actual contact route once it exists -->
                            <a href="mailto:support@quantum-hrmi.com" class="inline-flex items-center justify-center gap-2 border border-warm-border bg-white hover:bg-warm-ivory text-primary-ink/85 font-semibold px-6 py-4 rounded-xl transition-all duration-300 text-base shadow-xs">
                                Hubungi Kami <i class="fa-solid fa-envelope text-sm text-primary-ink/65"></i>
                            </a>
                        </div>
                    </div>

                    <!-- Right Column: SPSP Diagram Card -->
                    <!-- Right Column: SPSP Diagram (Clean: floating vector chart) -->
                    <div class="lg:col-span-6 relative flex flex-col justify-center items-center group w-full max-w-2xl mx-auto">
                        <!-- Decorative glow -->
                        <div class="absolute -inset-4 bg-gradient-to-tr from-rust-red/8 to-accent-amber/8 rounded-3xl blur-2xl opacity-75 -z-10 animate-pulse" style="animation-duration: 8s;"></div>

                        <!-- SVG Chart -->
                        <div class="w-full flex items-center justify-center">
                            <svg viewBox="25 80 790 565" xmlns="http://www.w3.org/2000/svg" class="w-full h-auto transition-transform duration-500 group-hover:scale-[1.03] origin-center" role="img" aria-label="Grafik radar perbandingan skor individu terhadap standar kompetensi">
                              <title>Grafik radar SPSP</title>
                              <desc>Contoh perbandingan profil individu terhadap standar dan toleransi pada 10 aspek kompetensi</desc>

                              <!-- Grid rings (value 1-4, thinned to 1.0px for background subtlety) -->
                              <polygon points="390,332.5 417.9,341.6 435.2,365.3 435.2,394.7 417.9,418.4 390,427.5 362.1,418.4 344.8,394.7 344.8,365.3 362.1,341.6" fill="none" stroke="var(--color-warm-border)" stroke-width="1"/>
                              <polygon points="390,285 445.8,303.1 480.4,350.6 480.4,409.4 445.8,456.9 390,475 334.2,456.9 299.7,409.4 299.7,350.6 334.2,303.1" fill="none" stroke="var(--color-warm-border)" stroke-width="1"/>
                              <polygon points="390,237.5 473.8,264.7 525.5,336.0 525.5,424.0 473.8,495.3 390,522.5 306.2,495.3 254.5,424.0 254.5,336.0 306.2,264.7" fill="none" stroke="var(--color-warm-border)" stroke-width="1"/>
                              <polygon points="390,190 501.7,226.3 570.7,321.3 570.7,438.7 501.7,533.7 390,570 278.3,533.7 209.3,438.7 209.3,321.3 278.3,226.3" fill="none" stroke="var(--color-warm-border)" stroke-width="1"/>

                              <!-- Axis lines (thinned to 1.0px) -->
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

                              <!-- Scale ticks (1-4) along top axis (Enlarged to 12px) -->
                              <g fill="var(--color-primary-ink)" fill-opacity="0.45" font-size="12" font-family="Instrument Sans, sans-serif" font-weight="bold">
                                <text x="402" y="194">4</text>
                                <text x="402" y="241.5">3</text>
                                <text x="402" y="289">2</text>
                                <text x="402" y="336.5">1</text>
                              </g>

                              <!-- Toleransi (Thickened to 2.5px and set to 0.8 opacity for clear visibility) -->
                              <polygon points="390,261.3 459.8,283.9 502.9,343.3 502.9,416.7 459.8,476.1 390,498.8 320.2,476.1 277.1,416.7 277.1,343.3 320.2,283.9"
                                fill="none" stroke="var(--color-primary-ink)" stroke-opacity="0.8" stroke-width="2.5" stroke-dasharray="2,4"/>

                              <!-- Standar (solid) -->
                              <polygon points="390,237.5 473.8,264.7 525.5,336.0 525.5,424.0 473.8,495.3 390,522.5 306.2,495.3 254.5,424.0 254.5,336.0 306.2,264.7"
                                fill="none" stroke="var(--color-primary-ink)" stroke-width="2.5"/>

                              <!-- Individu (dashed red) -->
                              <polygon points="390,228.0 468.2,272.4 548.1,328.6 507.5,418.2 473.8,495.3 390,494.0 309.0,491.4 240.9,428.4 268.0,340.4 303.4,260.9"
                                fill="none" stroke="var(--color-rust-red)" stroke-width="2.5" stroke-dasharray="6,4"/>

                              <!-- Individu vertex markers -->
                              <g fill="var(--color-rust-red)">
                                <circle cx="390" cy="228.0" r="4.5"/>
                                <circle cx="468.2" cy="272.4" r="4.5"/>
                                <circle cx="548.1" cy="328.6" r="4.5"/>
                                <circle cx="507.5" cy="418.2" r="4.5"/>
                                <circle cx="473.8" cy="495.3" r="4.5"/>
                                <circle cx="390" cy="494.0" r="4.5"/>
                                <circle cx="309.0" cy="491.4" r="4.5"/>
                                <circle cx="240.9" cy="428.4" r="4.5"/>
                                <circle cx="268.0" cy="340.4" r="4.5"/>
                                <circle cx="303.4" cy="260.9" r="4.5"/>
                              </g>

                              <!-- Individu value labels (Enlarged to 14px) -->
                              <g fill="var(--color-rust-red)" font-size="14" font-weight="bold" font-family="Instrument Sans, sans-serif">
                                <text x="390" y="210" text-anchor="middle">3.2</text>
                                <text x="480.4" y="261.1" text-anchor="start">2.8</text>
                                <text x="563.4" y="324.3" text-anchor="start">3.5</text>
                                <text x="524.8" y="422.5" text-anchor="start">2.6</text>
                                <text x="486.0" y="506.6" text-anchor="start">3.0</text>
                                <text x="390" y="514" text-anchor="middle">2.4</text>
                                <text x="296.8" y="502.8" text-anchor="end">2.9</text>
                                <text x="225.6" y="432.8" text-anchor="end">3.3</text>
                                <text x="252.7" y="336.0" text-anchor="end">2.7</text>
                                <text x="291.2" y="249.6" text-anchor="end">3.1</text>
                              </g>

                              <!-- Axis category labels (Enlarged to 16px and opaque for contrast) -->
                              <g fill="var(--color-primary-ink)" font-size="16" font-weight="bold" font-family="Instrument Sans, sans-serif">
                                <text x="390" y="112" text-anchor="middle">General Ability</text>
                                <text x="528.2" y="189.9" text-anchor="start">Willingness to do more</text>
                                <text x="613.5" y="307.4" text-anchor="start">Creative Leadership</text>
                                <text x="613.5" y="452.6" text-anchor="start">Goal &amp; Objective Setting</text>
                                <text x="528.2" y="570.1" text-anchor="start">Managerial</text>
                                <text x="390" y="619" text-anchor="middle">Emotional Quotient (EQ)</text>
                                <text x="251.8" y="570.1" text-anchor="end">Personality</text>
                                <text x="166.5" y="452.6" text-anchor="end">Human Relation</text>
                                <text x="166.5" y="307.4" text-anchor="end">Work Style</text>
                                <text x="251.8" y="189.9" text-anchor="end">Loyalty</text>
                              </g>
                            </svg>
                        </div>
                        
                        <!-- Custom Legend Info -->
                        <div class="mt-6 w-full grid grid-cols-3 gap-3 text-center text-xs sm:text-sm font-bold text-primary-ink/80">
                            <div class="flex items-center justify-center gap-2 bg-white border border-warm-border py-2 px-3 rounded-md shadow-xs">
                                <svg class="w-6 h-1 shrink-0" viewBox="0 0 24 4" xmlns="http://www.w3.org/2000/svg">
                                    <line x1="0" y1="2" x2="24" y2="2" stroke="var(--color-primary-ink)" stroke-width="2.5" />
                                </svg>
                                <span>Standar</span>
                            </div>
                            <div class="flex items-center justify-center gap-2 bg-white border border-warm-border py-2 px-3 rounded-md shadow-xs">
                                <svg class="w-6 h-1 shrink-0" viewBox="0 0 24 4" xmlns="http://www.w3.org/2000/svg">
                                    <line x1="0" y1="2" x2="24" y2="2" stroke="var(--color-rust-red)" stroke-width="2.5" stroke-dasharray="5,3" />
                                </svg>
                                <span>Individu</span>
                            </div>
                            <div class="flex items-center justify-center gap-2 bg-white border border-warm-border py-2 px-3 rounded-md shadow-xs">
                                <svg class="w-6 h-1 shrink-0" viewBox="0 0 24 4" xmlns="http://www.w3.org/2000/svg">
                                    <line x1="0" y1="2" x2="24" y2="2" stroke="var(--color-primary-ink)" stroke-opacity="0.8" stroke-width="2.5" stroke-dasharray="2,4" />
                                </svg>
                                <span>Toleransi</span>
                            </div>
                        </div>
                    </div>

                </div>
            </section>

            <!-- Methodology / Highlights Section -->
            <section id="learn-more" class="bg-white py-20 border-y border-warm-border scroll-mt-16">
                <div class="max-w-7xl mx-auto px-6">
                    <!-- Section Header -->
                    <div class="text-center max-w-3xl mx-auto mb-16">
                        <span class="text-rust-red font-bold text-xs uppercase tracking-wider mb-2 block">Pilar Utama SPSP</span>
                        <h2 class="font-display text-3xl font-bold text-primary-ink sm:text-4xl tracking-tight">
                            Metodologi Pemetaan Objektif & Terpadu
                        </h2>
                        <p class="mt-4 text-base sm:text-lg text-primary-ink/70 leading-relaxed">
                            Static Pribadi Spider Plot memetakan kompetensi dalam visualisasi radar 360 derajat untuk menghasilkan pemetaan yang holistik.
                        </p>
                    </div>

                    <!-- 3 Column Feature Grid -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                        
                        <!-- Feature 1 -->
                        <div class="bg-warm-ivory/40 hover:bg-warm-ivory/80 border border-warm-border/50 hover:border-warm-border p-8 rounded-xl transition-all duration-300 group shadow-xs">
                            <div class="text-rust-red mb-6 text-2xl">
                                <i class="fa-solid fa-sliders"></i>
                            </div>
                            <h3 class="font-display text-lg font-bold text-primary-ink mb-3">Kriteria Standar Jabatan</h3>
                            <p class="text-primary-ink/75 text-sm leading-relaxed">
                                Menentukan nilai acuan (standard baseline) yang diperlukan bagi suatu peran kerja untuk menjamin kontribusi optimal bagi sasaran organisasi.
                            </p>
                        </div>

                        <!-- Feature 2 -->
                        <div class="bg-warm-ivory/40 hover:bg-warm-ivory/80 border border-warm-border/50 hover:border-warm-border p-8 rounded-xl transition-all duration-300 group shadow-xs">
                            <div class="text-rust-red mb-6 text-2xl">
                                <i class="fa-solid fa-user-gear"></i>
                            </div>
                            <h3 class="font-display text-lg font-bold text-primary-ink mb-3">Kesesuaian Kompetensi</h3>
                            <p class="text-primary-ink/75 text-sm leading-relaxed">
                                Memetakan profil aktual individu meliputi aspek kemampuan umum, kepribadian, gaya kerja, hubungan manusia, hingga kecerdasan emosional (EQ).
                            </p>
                        </div>

                        <!-- Feature 3 -->
                        <div class="bg-warm-ivory/40 hover:bg-warm-ivory/80 border border-warm-border/50 hover:border-warm-border p-8 rounded-xl transition-all duration-300 group shadow-xs">
                            <div class="text-rust-red mb-6 text-2xl">
                                <i class="fa-solid fa-chart-line"></i>
                            </div>
                            <h3 class="font-display text-lg font-bold text-primary-ink mb-3">Batas Toleransi Deviasi</h3>
                            <p class="text-primary-ink/75 text-sm leading-relaxed">
                                Mengidentifikasi tingkat deviasi antara profil individu dan standar jabatan untuk mengevaluasi kelayakan penempatan secara rasional dan adil.
                            </p>
                        </div>

                    </div>
                </div>
            </section>
        </main>

        <!-- Footer (Expanded Multi-Column with high contrast text for WCAG AA compliance) -->
        <footer class="bg-primary-ink text-warm-ivory/70 py-12 border-t border-warm-border/20">
            <div class="max-w-7xl mx-auto px-6 grid grid-cols-1 md:grid-cols-12 gap-8 items-start">
                
                <!-- Column 1: Branding -->
                <div class="md:col-span-4 flex flex-col gap-3">
                    <div class="flex items-center gap-3">
                        <img src="{{ asset('images/thumb-qhrmi.webp') }}" alt="Logo QHRMI" class="h-8 w-8 object-contain rounded-md" />
                        <span class="text-xs tracking-wider uppercase font-semibold text-warm-ivory/60">SPSP System</span>
                    </div>
                    <p class="text-xs text-warm-ivory/65 leading-relaxed max-w-sm mt-2">
                        Sistem Pemetaan & Statistik Psikologi (SPSP) merupakan platform analisis potensi dan kompetensi individu terstandarisasi dari PT. Quantum HRM Internasional.
                    </p>
                </div>

                <!-- Column 2: Alamat Kantor -->
                <div class="md:col-span-4 flex flex-col gap-2">
                    <span class="text-xs font-bold uppercase tracking-wider text-warm-ivory/85">Kantor Pusat</span>
                    <p class="text-xs text-warm-ivory/65 leading-relaxed">
                        Jl. Sidosermo I No. 10, Surabaya, 60239,<br>
                        Jawa Timur, Indonesia
                    </p>
                    <div class="flex flex-col gap-1 mt-1 text-xs text-warm-ivory/65">
                        <span class="flex items-center gap-1.5"><i class="fa-solid fa-phone text-warm-ivory/50 text-[10px]"></i> 031-8436700</span>
                        <span class="flex items-center gap-1.5"><i class="fa-solid fa-envelope text-warm-ivory/50 text-[10px]"></i> support@quantum-hrmi.com</span>
                    </div>
                </div>

                <!-- Column 3: Tautan Penting -->
                <div class="md:col-span-4 flex flex-col gap-2">
                    <span class="text-xs font-bold uppercase tracking-wider text-warm-ivory/85">Tautan Penting</span>
                    <nav aria-label="Navigasi Footer" class="flex flex-col gap-1.5 text-xs text-warm-ivory/65">
                        <a href="#learn-more" class="hover:text-rust-red transition-colors w-fit">Metodologi SPSP</a>
                        <a href="{{ route('login') }}" class="hover:text-rust-red transition-colors w-fit">Masuk ke Portal</a>
                        <!-- TODO: Replace hash links with actual contact and privacy pages once created -->
                        <a href="mailto:support@quantum-hrmi.com" class="hover:text-rust-red transition-colors w-fit">Hubungi Kami (Kontak)</a>
                        <a href="#" class="hover:text-rust-red transition-colors w-fit">Kebijakan Privasi</a>
                    </nav>
                </div>

            </div>

            <!-- Bottom Copyright bar (Ensuring contrast ratio >= 4.5:1 using text-warm-ivory/60) -->
            <div class="max-w-7xl mx-auto px-6 mt-12 pt-6 border-t border-warm-border/10 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-warm-ivory/60">
                <div>
                    &copy; 2026 PT. Quantum HRM Internasional. All rights reserved.
                </div>
                <div class="tracking-wide">
                    HAK CIPTA TERDAFTAR DIRJEN HAKI NO. 027762
                </div>
            </div>
        </footer>

    </body>
</html>
