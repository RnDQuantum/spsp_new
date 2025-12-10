<!DOCTYPE html>
<html lang="id">

    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <title>STATIC PRIBADI SPIDER PLOT</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <link rel="icon" type="image/x-icon" href="{{ asset('images/thumb-qhrmi.webp') }}" />
        <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    </head>

    <body class="min-h-screen flex flex-col bg-white">

        <div class="flex-grow flex items-center justify-center p-4 max-w-7xl mx-auto w-full">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 items-center lg:divide-x lg:divide-gray-500">

                <!-- Kolom Kiri - Gambar SPSP -->
                <div class="flex items-center justify-center lg:pr-8">
                    <img src="{{ asset('images/spsp.webp') }}" alt="SPSP Diagram" class="w-full max-w-lg" />
                </div>

                <!-- Kolom Kanan - Konten Utama -->
                <div class="text-center items-center">
                    <!-- Logo/Gambar -->
                    <div class="mb-4">
                        <img src="{{ asset('images/logo-qhrmi.webp') }}" alt="Logo Static Pribadi Spider Plot"
                            class="mx-auto max-w-md" />
                    </div>
                    <!-- Judul Utama -->
                    <h1 class="text-4xl font-bold text-red-600 underline mb-4">
                        STATIC PRIBADI SPIDER PLOT
                    </h1>
                    <!-- Subjudul -->
                    <h2 class="text-lg text-black mb-2 font-semibold">
                        METODE PEMETAAN POTENSI DAN KOMPETENSI INDIVIDU
                    </h2>
                    <!-- Slogan -->
                    <h1 class="text-md font-bold text-black italic mb-2">
                        "The Right Man, On The Right Place, With The Right Character"
                    </h1>
                    <!-- Hak Cipta -->
                    <p class="text-sm text-black mb-4">
                        HAK CIPTA: DIRJEN HAKI No. 027762 TANGGAL 10 MARET 2004
                    </p>
                    <!-- Tombol Mulai -->
                    <a href="{{ route('login') }}"
                        class="inline-block bg-red-600 hover:bg-red-700 text-white font-semibold px-8 py-3 rounded-lg transition duration-300 shadow-lg hover:shadow-xl">
                        START
                    </a>
                </div>
            </div>
        </div>

        <!-- FOOTER DAN MODAL (Alpine.js) -->
        <footer x-data="{ showModal: false }" class="bg-gray-800 text-white text-center py-4 mt-8 relative">
            <a href="#" @click.prevent="showModal = true" class="cursor-pointer">
                &copy; 2025 Quantum HRM Internasional. All rights reserved.
            </a>

            <!-- Modal: Overlay sangat transparan -->
            <div x-show="showModal" x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95"
                class="fixed inset-0 backdrop-blur-md bg-white/30 flex items-center justify-center z-50" x-cloak>
                <div class="bg-white text-gray-800 text-left rounded-lg max-w-md mx-auto p-6 shadow-lg relative">
                    <button @click="showModal = false"
                        class="absolute top-2 right-2 text-gray-500 hover:text-red-600 font-bold text-xl"
                        aria-label="Close">&times;</button>
                    <h3 class="text-lg font-bold mb-4 text-red-700">Tentang Pengembang</h3>
                    <div class="mb-2">
                        <span class="font-semibold">Pencetus/Ide Awal:</span>
                        <br>Prof. Dr. Pribadiyono, Ir., M.S.,
                    </div>
                    <div class="mb-2">
                        <span class="font-semibold">Pengarah Pengembangan & Ketua Tim:</span>
                        <br>Moh. Hafiluddin S.E., M.M.
                    </div>
                    <div class="mb-2">
                        <span class="font-semibold">Pengembang:</span>
                        <br>Bima Bakti Mandala, S.T.
                        <br>Almendaris Shandy Priyatama, S.Kom.
                    </div>
                </div>
            </div>
        </footer>
    </body>

</html>
