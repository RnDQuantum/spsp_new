<!DOCTYPE html>
<html lang="id">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>STATIC PRIBADI SPIDER PLOT</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>

    <body class="bg-gray-50 min-h-screen flex items-center justify-center p-4">
        <div class="max-w-7xl mx-auto w-full">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 items-center lg:divide-x lg:divide-gray-500">

                <!-- Kolom Kiri - Gambar SPSP -->
                <div class="flex items-center justify-center lg:pr-8">
                    <img src="{{ asset('images/spsp.png') }}" alt="SPSP Diagram" class="w-full max-w-lg">
                </div>

                <!-- Kolom Kanan - Konten Utama -->
                <div class="text-center lg:pl-8">
                    <!-- Logo/Gambar -->
                    <div class="mb-8">
                        <img src="{{ asset('images/logo-qhrmi.png') }}" alt="Logo Static Pribadi Spider Plot"
                            class="mx-auto max-w-md">
                    </div>

                    <!-- Judul Utama -->
                    <h1 class="text-4xl md:text-4xl font-bold text-red-600 underline mb-4">
                        STATIC PRIBADI SPIDER PLOT
                    </h1>

                    <!-- Subjudul -->
                    <h2 class="text-lg md:text-lg text-gray-700 mb-2 font-semibold">
                        METODE PEMETAAN POTENSI DAN KOMPETENSI INDIVIDU KARYAWAN
                    </h2>

                    <!-- Hak Cipta -->
                    <p class="text-sm md:text-base text-gray-600 mb-4">
                        HAK CIPTA: DIRJEN HAKI No. 027762 TANGGAL 10 MARET 2004
                    </p>

                    <!-- Tombol Mulai -->
                    {{-- TEMPORARY: Bypass authentication - redirect to dashboard directly --}}
                    {{-- TODO: Restore authentication by changing 'dashboard' back to 'login' --}}
                    <a href="{{ route('login') }}"
                        class="inline-block bg-red-600 hover:bg-red-700 text-white font-semibold px-8 py-3 rounded-lg transition duration-300 shadow-lg hover:shadow-xl">
                        START
                    </a>
                    {{-- ORIGINAL (with auth): <a href="{{ route('login') }}"> --}}
                </div>

            </div>
        </div>
    </body>

</html>
