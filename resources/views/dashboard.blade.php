<!DOCTYPE html>
<html lang="id">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Dashboard</title>
        {{-- @vite(['resources/css/app.css', 'resources/js/app.js']) --}}
        <script src="https://cdn.tailwindcss.com"></script>
        @livewireStyles
    </head>

    <body class="bg-gray-100">

        <livewire:components.sidebar />

        <div class="ml-64 p-8">
            <h1 class="text-3xl font-bold text-gray-800">Dashboard</h1>
            <p class="text-gray-600 mt-2">Selamat datang di dashboard</p>

            <div class="grid grid-cols-3 gap-6 mt-8">
                <div class="bg-white p-6 rounded-lg shadow">
                    <h3 class="text-gray-500">Total Users</h3>
                    <p class="text-3xl font-bold mt-2">1,234</p>
                </div>
                <div class="bg-white p-6 rounded-lg shadow">
                    <h3 class="text-gray-500">Active</h3>
                    <p class="text-3xl font-bold mt-2">892</p>
                </div>
                <div class="bg-white p-6 rounded-lg shadow">
                    <h3 class="text-gray-500">Pending</h3>
                    <p class="text-3xl font-bold mt-2">45</p>
                </div>
            </div>
        </div>

        @livewireScripts
    </body>

</html>
