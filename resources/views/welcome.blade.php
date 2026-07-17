<!DOCTYPE html>
<html lang="id">

    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <title>SPSP - Static Pribadi Spider Plot | Quantum HRM Internasional</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <link rel="icon" type="image/x-icon" href="{{ asset('images/thumb-qhrmi.webp') }}">
        {{-- Preload LCP Images --}}
        <link rel="preload" as="image" href="{{ asset('images/spsp.webp') }}" fetchpriority="high">
        <link rel="preload" as="image" href="{{ asset('images/logo-qhrmi.webp') }}" fetchpriority="high">
    </head>

    <body class="min-h-screen flex flex-col bg-warm-ivory text-primary-ink antialiased selection:bg-rust-red selection:text-white">

        <!-- Header / Navigation Bar -->
        <header class="sticky top-0 z-40 w-full bg-white/85 backdrop-blur-md border-b border-warm-border transition-colors duration-300">
            <div class="max-w-7xl mx-auto px-6 h-16 flex items-center justify-between">
                <!-- Brand Logo & Name -->
                <a href="{{ url('/') }}" class="flex items-center gap-3 group">
                    <img src="{{ asset('images/logo-qhrmi.webp') }}" alt="Logo QHRMI" class="h-10 w-auto object-contain transition-transform duration-300 group-hover:scale-[1.02]" />
                    <div class="hidden sm:flex flex-col border-l border-warm-border pl-3">
                        <span class="text-sm font-bold tracking-tight text-primary-ink uppercase">Quantum HRM</span>
                        <span class="text-[10px] font-medium text-primary-ink/60 tracking-wider uppercase -mt-0.5">Internasional</span>
                    </div>
                </a>

                <!-- Header Actions -->
                <div class="flex items-center gap-4">
                    <a href="#learn-more" class="hidden md:inline-flex text-sm font-semibold text-primary-ink/70 hover:text-rust-red transition-colors">
                        Metodologi
                    </a>
                    <a href="{{ route('login') }}" class="inline-flex items-center justify-center gap-1.5 border border-warm-border bg-white hover:bg-warm-ivory text-primary-ink text-sm font-bold px-5 py-2 rounded-xl transition-all duration-200 shadow-xs">
                        Login <i class="fa-solid fa-arrow-right text-xs"></i>
                    </a>
                </div>
            </div>
        </header>

        <!-- Hero Section -->
        <main class="flex-grow">
            <section class="relative overflow-hidden pt-12 pb-20 lg:pt-20 lg:pb-28">
                <!-- Background grid design pattern (uses theme's warm-border) -->
                <div class="absolute inset-0 bg-[linear-gradient(to_right,var(--color-warm-border)_1px,transparent_1px),linear-gradient(to_bottom,var(--color-warm-border)_1px,transparent_1px)] bg-[size:4rem_4rem] [mask-image:radial-gradient(ellipse_60%_50%_at_50%_0%,#000_70%,transparent_100%)] -z-20 opacity-60"></div>

                <div class="max-w-7xl mx-auto px-6 grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-16 items-center">
                    
                    <!-- Left Column: Context & Headline -->
                    <div class="lg:col-span-7 text-left flex flex-col justify-center">
                        <!-- Eyebrow Badge -->
                        <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-xs font-bold bg-rust-red/5 text-rust-red border border-rust-red/20 mb-6 w-fit shadow-xs">
                            <span class="w-1.5 h-1.5 rounded-full bg-rust-red animate-pulse"></span>
                            METODE PEMETAAN POTENSI & KOMPETENSI INDIVIDU
                        </div>

                        <!-- Main Heading (uses editorial font-display / Lora) -->
                        <h1 class="font-display text-4xl sm:text-5xl lg:text-6xl font-bold text-primary-ink tracking-tight leading-tight">
                            Static Pribadi <br class="hidden sm:inline" />
                            <span class="text-rust-red relative inline-block">
                                Spider Plot
                                <span class="absolute bottom-1 left-0 w-full h-2 bg-rust-red/10 -z-10 rounded"></span>
                            </span>
                        </h1>

                        <!-- Slogan -->
                        <div class="mt-6 border-l-4 border-rust-red pl-4 py-1.5 italic text-primary-ink/80 font-semibold bg-white/60 backdrop-blur-sm rounded-r-xl border-y border-r border-warm-border shadow-xs max-w-xl">
                            "The Right Man, On The Right Place, With The Right Character"
                        </div>

                        <!-- Description -->
                        <p class="mt-6 text-base sm:text-lg text-primary-ink/75 leading-relaxed max-w-2xl">
                            Sistem analisis psikologis dan kompetensi kerja berbasis grafik radar (Spider Plot). SPSP memetakan profil individu terhadap standar kompetensi secara komprehensif, cepat, dan presisi demi mendukung keselarasan karir dan kepemimpinan.
                        </p>

                        <!-- HAKI Metadata -->
                        <div class="mt-8 flex items-center gap-3 text-xs sm:text-sm text-primary-ink/60 bg-white border border-warm-border rounded-xl px-4 py-3 shadow-xs w-fit">
                            <i class="fa-solid fa-certificate text-rust-red text-lg"></i>
                            <div>
                                <span class="font-bold text-primary-ink/80">HAK CIPTA TERDAFTAR</span>
                                <span class="mx-1.5 text-warm-border">|</span>
                                <span>DIRJEN HAKI No. 027762 (10 Maret 2004)</span>
                            </div>
                        </div>

                        <!-- CTA Action Buttons -->
                        <div class="mt-8 flex flex-col sm:flex-row gap-4 items-stretch sm:items-center">
                            <a href="{{ route('login') }}" class="inline-flex items-center justify-center gap-2 bg-rust-red hover:bg-rust-red/90 active:bg-rust-red/80 text-white font-extrabold px-8 py-4 rounded-xl transition-all duration-300 shadow-sm hover:shadow active:scale-95 text-base">
                                START ASSESSMENT <i class="fa-solid fa-circle-play text-lg"></i>
                            </a>
                            <a href="#learn-more" class="inline-flex items-center justify-center gap-2 border border-warm-border bg-white hover:bg-warm-ivory text-primary-ink/85 font-semibold px-6 py-4 rounded-xl transition-all duration-300 text-base shadow-xs">
                                Pelajari Metodologi
                            </a>
                        </div>
                    </div>

                    <!-- Right Column: SPSP Diagram Card -->
                    <div class="lg:col-span-5 relative flex justify-center items-center">
                        <!-- Decorative glow -->
                        <div class="absolute -inset-4 bg-gradient-to-tr from-rust-red/8 to-accent-amber/8 rounded-3xl blur-2xl opacity-75 -z-10 animate-pulse" style="animation-duration: 8s;"></div>

                        <!-- The Image Card (conforms to max 12px rounding for cards) -->
                        <div class="w-full max-w-md bg-white p-6 rounded-xl border border-warm-border shadow-xs overflow-hidden transition-all duration-300 hover:shadow-sm group">
                            <div class="relative overflow-hidden rounded-lg bg-warm-ivory p-2 flex items-center justify-center">
                                <img src="{{ asset('images/spsp.webp') }}" fetchpriority="high" alt="SPSP Diagram" class="w-full h-auto object-contain transition-transform duration-500 group-hover:scale-105" />
                            </div>
                            
                            <!-- Custom Legend Info -->
                            <div class="mt-5 pt-4 border-t border-warm-border/50 grid grid-cols-3 gap-2 text-center text-[11px] font-semibold text-primary-ink/60">
                                <div class="flex items-center justify-center gap-1.5 bg-warm-ivory py-1.5 px-2 rounded-md">
                                    <span class="w-2.5 h-2.5 bg-black rounded-xs inline-block shrink-0"></span> 
                                    <span>Standar</span>
                                </div>
                                <div class="flex items-center justify-center gap-1.5 bg-warm-ivory py-1.5 px-2 rounded-md">
                                    <span class="w-2.5 h-0.5 border-t border-dashed border-rust-red inline-block shrink-0"></span> 
                                    <span>Individu</span>
                                </div>
                                <div class="flex items-center justify-center gap-1.5 bg-warm-ivory py-1.5 px-2 rounded-md">
                                    <span class="w-2.5 h-0.5 border-t border-dotted border-black inline-block shrink-0"></span> 
                                    <span>Toleransi</span>
                                </div>
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
                            <div class="w-12 h-12 rounded-lg bg-rust-red/10 flex items-center justify-center text-rust-red mb-6 group-hover:scale-110 transition-transform duration-300">
                                <i class="fa-solid fa-sliders text-xl"></i>
                            </div>
                            <h3 class="text-lg font-bold text-primary-ink mb-3">Kriteria Standar Jabatan</h3>
                            <p class="text-primary-ink/75 text-sm leading-relaxed">
                                Menentukan nilai acuan (standard baseline) yang diperlukan bagi suatu peran kerja untuk menjamin kontribusi optimal bagi sasaran organisasi.
                            </p>
                        </div>

                        <!-- Feature 2 -->
                        <div class="bg-warm-ivory/40 hover:bg-warm-ivory/80 border border-warm-border/50 hover:border-warm-border p-8 rounded-xl transition-all duration-300 group shadow-xs">
                            <div class="w-12 h-12 rounded-lg bg-rust-red/10 flex items-center justify-center text-rust-red mb-6 group-hover:scale-110 transition-transform duration-300">
                                <i class="fa-solid fa-user-gear text-xl"></i>
                            </div>
                            <h3 class="text-lg font-bold text-primary-ink mb-3">Kesesuaian Kompetensi</h3>
                            <p class="text-primary-ink/75 text-sm leading-relaxed">
                                Memetakan profil aktual individu meliputi aspek kemampuan umum, kepribadian, gaya kerja, hubungan manusia, hingga kecerdasan emosional (EQ).
                            </p>
                        </div>

                        <!-- Feature 3 -->
                        <div class="bg-warm-ivory/40 hover:bg-warm-ivory/80 border border-warm-border/50 hover:border-warm-border p-8 rounded-xl transition-all duration-300 group shadow-xs">
                            <div class="w-12 h-12 rounded-lg bg-rust-red/10 flex items-center justify-center text-rust-red mb-6 group-hover:scale-110 transition-transform duration-300">
                                <i class="fa-solid fa-chart-line text-xl"></i>
                            </div>
                            <h3 class="text-lg font-bold text-primary-ink mb-3">Batas Toleransi Deviasi</h3>
                            <p class="text-primary-ink/75 text-sm leading-relaxed">
                                Mengidentifikasi tingkat deviasi antara profil individu dan standar jabatan untuk mengevaluasi kelayakan penempatan secara rasional dan adil.
                            </p>
                        </div>

                    </div>
                </div>
            </section>
        </main>

        <!-- Footer -->
        <footer class="bg-primary-ink text-warm-ivory/60 py-12 border-t border-warm-border/20">
            <div class="max-w-7xl mx-auto px-6 flex flex-col md:flex-row items-center justify-between gap-6">
                <!-- Left: Branding -->
                <div class="flex items-center gap-3">
                    <img src="{{ asset('images/logo-qhrmi.webp') }}" alt="Logo QHRMI" class="h-8 w-auto object-contain brightness-0 invert opacity-45" />
                    <span class="text-xs tracking-wider uppercase font-semibold text-warm-ivory/40">SPSP Assessment System</span>
                </div>

                <!-- Right: Copyright -->
                <div class="text-sm text-center md:text-right text-warm-ivory/45">
                    &copy; 2026 Quantum HRM Internasional. All rights reserved.
                </div>
            </div>
        </footer>

    </body>
</html>
