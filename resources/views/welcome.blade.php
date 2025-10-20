<!DOCTYPE html>
<html lang="id">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>STATIC PRIBADI SPIDER PLOT</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>

    <body class="bg-gray-50 min-h-screen flex items-center justify-center p-4">
        <div class="text-center max-w-3xl mx-auto">
            <!-- Logo/Gambar -->
            <div class="mb-8">
                <img src="{{ asset('images/logo-qhrmi.png') }}" alt="Logo Static Pribadi Spider Plot"
                    class="mx-auto max-w-md md:max-w-lg rounded-lg shadow-md">
            </div>

            <!-- Judul Utama -->
            <h1 class="text-4xl md:text-5xl font-bold text-red-600 underline mb-6">
                STATIC PRIBADI SPIDER PLOT
            </h1>

            <!-- Subjudul -->
            <h2 class="text-xl md:text-2xl text-gray-700 mb-4 font-semibold">
                METODE PEMETAAN POTENSI INDIVIDU KARYAWAN
            </h2>

            <!-- Hak Cipta -->
            <p class="text-sm md:text-base text-gray-600 mb-8">
                HAK CIPTA: DIRJEN HAKI No. 027762 TANGGAL 10 MARET 2004
            </p>

            <!-- Tombol Mulai -->
            <a href="{{ route('login') }}"
                class="inline-block bg-red-600 hover:bg-red-700 text-white font-semibold px-8 py-3 rounded-lg transition duration-300 shadow-lg hover:shadow-xl">
                Mulai
            </a>
        </div>
    </body>

</html>
