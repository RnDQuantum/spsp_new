<div>
    <!-- Main Container -->
    <div class="bg-white dark:bg-gray-900 mx-auto my-8 shadow-lg dark:shadow-gray-800/50 overflow-hidden"
        style="max-width: 1400px;">

        <!-- Header - DARK MODE READY -->
        <div class="border-b-4 border-black dark:border-gray-300 py-3 bg-gray-300 dark:bg-gray-600">
            <h1 class="text-center text-lg font-bold uppercase tracking-wide text-gray-900 dark:text-gray-100">
                DAFTAR KLIEN
            </h1>
            <p class="text-center text-sm font-semibold text-gray-700 dark:text-gray-300 mt-1">
                Manajemen Data Instansi & Perusahaan
            </p>
        </div>

        <!-- Action Buttons -->
        <div class="p-6 bg-gray-50 dark:bg-gray-800 border-b border-gray-200 dark:border-gray-600">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <!-- Action Buttons -->
                <div class="flex gap-2">
                    <button
                        class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-700 text-white font-semibold rounded-lg shadow hover:bg-gray-700 dark:hover:bg-gray-600 transition-colors">
                        <i class="fa-solid fa-plus mr-2"></i>
                        Tambah Klien
                    </button>
                    <button
                        class="inline-flex items-center px-4 py-2 bg-green-600 dark:bg-green-700 text-white font-semibold rounded-lg shadow hover:bg-green-700 dark:hover:bg-green-600 transition-colors">
                        <i class="fa-solid fa-file-excel mr-2"></i>
                        Export Excel
                    </button>
                </div>
            </div>
        </div>

        <!-- Filters & Search Section -->
        <div class="p-6 bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <!-- Search -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Pencarian</label>
                    <div class="relative">
                        <input type="text" id="searchInput"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 bg-white dark:bg-gray-700 dark:border-gray-600 dark:text-white p-2 pl-10 border"
                            placeholder="Cari nama instansi...">
                        <i
                            class="fa-solid fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                    </div>
                </div>

                <!-- Filter Kategori -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Kategori</label>
                    <select id="categoryFilter"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 bg-white dark:bg-gray-700 dark:border-gray-600 dark:text-white p-2 border">
                        <option value="">Semua Kategori</option>
                        <option value="Kementerian">Kementerian</option>
                        <option value="BUMN">BUMN</option>
                        <option value="Swasta">Swasta</option>
                        <option value="Pendidikan">Pendidikan</option>
                        <option value="UMKM">UMKM</option>
                    </select>
                </div>

                <!-- Filter Status -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status</label>
                    <select id="statusFilter"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 bg-white dark:bg-gray-700 dark:border-gray-600 dark:text-white p-2 border">
                        <option value="">Semua Status</option>
                        <option value="Aktif">Aktif</option>
                        <option value="Pending">Pending</option>
                        <option value="Selesai">Selesai</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Table Section -->
        <div class="p-6 bg-white dark:bg-gray-900">
            <!-- Table Info -->
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-4 gap-2">
                <div class="text-sm text-gray-600 dark:text-gray-400">
                    Menampilkan <span id="showingStart">1</span> - <span id="showingEnd">10</span> dari <span
                        id="totalEntries">50</span> data
                </div>
                <div class="flex items-center gap-2">
                    <label class="text-sm text-gray-600 dark:text-gray-400">Tampilkan:</label>
                    <select id="entriesPerPage"
                        class="rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 bg-white dark:bg-gray-700 dark:border-gray-600 dark:text-white p-1 border text-sm">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                </div>
            </div>

            <!-- Table -->
            <div
                class="overflow-x-auto bg-white dark:bg-gray-800 rounded-lg shadow border border-gray-200 dark:border-gray-600">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700" id="clientTable">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th scope="col"
                                class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600"
                                onclick="sortTable(0)">
                                No. <i class="fa-solid fa-sort ml-1"></i>
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600"
                                onclick="sortTable(1)">
                                Nama Instansi <i class="fa-solid fa-sort ml-1"></i>
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600"
                                onclick="sortTable(2)">
                                Kategori <i class="fa-solid fa-sort ml-1"></i>
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600"
                                onclick="sortTable(3)">
                                Ditambahkan Pada <i class="fa-solid fa-sort ml-1"></i>
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600"
                                onclick="sortTable(4)">
                                Status Klien <i class="fa-solid fa-sort ml-1"></i>
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Aksi
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700"
                        id="tableBody">
                        <!-- Data will be populated by JavaScript -->
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="flex flex-col md:flex-row justify-between items-center mt-4 gap-4">
                <div class="text-sm text-gray-600 dark:text-gray-400">
                    Halaman <span id="currentPage">1</span> dari <span id="totalPages">5</span>
                </div>
                <nav class="inline-flex rounded-md shadow-sm" id="pagination">
                    <!-- Pagination buttons will be populated by JavaScript -->
                </nav>
            </div>
        </div>
    </div>
</div>

<script>
    // Sample Data - 50 entries
    const clientsData = [
        { name: 'Kementerian Keuangan', category: 'Kementerian', date: '2024-11-28', status: 'Aktif' },
        { name: 'PT. Telkom Indonesia', category: 'BUMN', date: '2024-11-25', status: 'Aktif' },
        { name: 'Universitas Indonesia', category: 'Pendidikan', date: '2024-11-22', status: 'Pending' },
        { name: 'PT. Bank Mandiri', category: 'BUMN', date: '2024-11-20', status: 'Aktif' },
        { name: 'Kementerian BUMN', category: 'Kementerian', date: '2024-11-18', status: 'Selesai' },
        { name: 'PT. Pertamina', category: 'BUMN', date: '2024-11-15', status: 'Aktif' },
        { name: 'Universitas Gadjah Mada', category: 'Pendidikan', date: '2024-11-12', status: 'Pending' },
        { name: 'Kementerian Pendidikan', category: 'Kementerian', date: '2024-11-10', status: 'Aktif' },
        { name: 'PT. Unilever Indonesia', category: 'Swasta', date: '2024-11-08', status: 'Aktif' },
        { name: 'Institut Teknologi Bandung', category: 'Pendidikan', date: '2024-11-05', status: 'Selesai' },
        { name: 'Kementerian Kesehatan', category: 'Kementerian', date: '2024-11-03', status: 'Aktif' },
        { name: 'PT. Garuda Indonesia', category: 'BUMN', date: '2024-11-01', status: 'Pending' },
        { name: 'Universitas Airlangga', category: 'Pendidikan', date: '2024-10-30', status: 'Aktif' },
        { name: 'PT. Astra International', category: 'Swasta', date: '2024-10-28', status: 'Aktif' },
        { name: 'Kementerian Dalam Negeri', category: 'Kementerian', date: '2024-10-25', status: 'Selesai' },
        { name: 'PT. PLN', category: 'BUMN', date: '2024-10-22', status: 'Aktif' },
        { name: 'Universitas Padjadjaran', category: 'Pendidikan', date: '2024-10-20', status: 'Aktif' },
        { name: 'PT. BCA', category: 'Swasta', date: '2024-10-18', status: 'Pending' },
        { name: 'Kementerian Perhubungan', category: 'Kementerian', date: '2024-10-15', status: 'Aktif' },
        { name: 'PT. Indofood', category: 'Swasta', date: '2024-10-12', status: 'Aktif' },
        { name: 'Universitas Diponegoro', category: 'Pendidikan', date: '2024-10-10', status: 'Selesai' },
        { name: 'PT. Wijaya Karya', category: 'BUMN', date: '2024-10-08', status: 'Aktif' },
        { name: 'Kementerian Perindustrian', category: 'Kementerian', date: '2024-10-05', status: 'Pending' },
        { name: 'PT. Semen Indonesia', category: 'BUMN', date: '2024-10-03', status: 'Aktif' },
        { name: 'Universitas Brawijaya', category: 'Pendidikan', date: '2024-10-01', status: 'Aktif' },
        { name: 'PT. BNI', category: 'BUMN', date: '2024-09-28', status: 'Aktif' },
        { name: 'Kementerian Pertanian', category: 'Kementerian', date: '2024-09-25', status: 'Selesai' },
        { name: 'PT. Adaro Energy', category: 'Swasta', date: '2024-09-22', status: 'Aktif' },
        { name: 'Universitas Hasanuddin', category: 'Pendidikan', date: '2024-09-20', status: 'Pending' },
        { name: 'PT. Angkasa Pura', category: 'BUMN', date: '2024-09-18', status: 'Aktif' },
        { name: 'Kementerian Pariwisata', category: 'Kementerian', date: '2024-09-15', status: 'Aktif' },
        { name: 'PT. BRI', category: 'BUMN', date: '2024-09-12', status: 'Aktif' },
        { name: 'Universitas Sebelas Maret', category: 'Pendidikan', date: '2024-09-10', status: 'Selesai' },
        { name: 'PT. Gudang Garam', category: 'Swasta', date: '2024-09-08', status: 'Aktif' },
        { name: 'Kementerian Ketenagakerjaan', category: 'Kementerian', date: '2024-09-05', status: 'Pending' },
        { name: 'PT. Pelni', category: 'BUMN', date: '2024-09-03', status: 'Aktif' },
        { name: 'Universitas Andalas', category: 'Pendidikan', date: '2024-09-01', status: 'Aktif' },
        { name: 'PT. HM Sampoerna', category: 'Swasta', date: '2024-08-28', status: 'Aktif' },
        { name: 'Kementerian Sosial', category: 'Kementerian', date: '2024-08-25', status: 'Selesai' },
        { name: 'PT. Jasa Marga', category: 'BUMN', date: '2024-08-22', status: 'Aktif' },
        { name: 'Warung Kopi Kenangan', category: 'UMKM', date: '2024-08-20', status: 'Pending' },
        { name: 'Toko Elektronik Maju', category: 'UMKM', date: '2024-08-18', status: 'Aktif' },
        { name: 'CV. Berkah Jaya', category: 'UMKM', date: '2024-08-15', status: 'Aktif' },
        { name: 'PT. Mayora Indah', category: 'Swasta', date: '2024-08-12', status: 'Selesai' },
        { name: 'Kementerian Energi', category: 'Kementerian', date: '2024-08-10', status: 'Aktif' },
        { name: 'UD. Sumber Rezeki', category: 'UMKM', date: '2024-08-08', status: 'Pending' },
        { name: 'PT. Kalbe Farma', category: 'Swasta', date: '2024-08-05', status: 'Aktif' },
        { name: 'Toko Baju Fashion', category: 'UMKM', date: '2024-08-03', status: 'Aktif' },
        { name: 'PT. Bukalapak', category: 'Swasta', date: '2024-08-01', status: 'Aktif' },
        { name: 'Kedai Nasi Padang Sederhana', category: 'UMKM', date: '2024-07-28', status: 'Selesai' }
    ];

    let currentPage = 1;
    let entriesPerPage = 10;
    let filteredData = [...clientsData];
    let sortDirection = {};

    function formatDate(dateString) {
        const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Oct', 'Nov', 'Des'];
        const date = new Date(dateString);
        return `${date.getDate()} ${months[date.getMonth()]} ${date.getFullYear()}`;
    }

    function getStatusBadge(status) {
        if (status === 'Aktif') {
            return '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">Aktif</span>';
        } else if (status === 'Pending') {
            return '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">Pending</span>';
        } else {
            return '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200">Selesai</span>';
        }
    }

    function renderTable() {
        const start = (currentPage - 1) * entriesPerPage;
        const end = start + entriesPerPage;
        const paginatedData = filteredData.slice(start, end);

        const tbody = document.getElementById('tableBody');
        tbody.innerHTML = '';

        paginatedData.forEach((client, index) => {
            const row = `
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-900 dark:text-gray-100">
                        ${start + index + 1}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                        ${client.name}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                        ${client.category}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                        ${formatDate(client.date)}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        ${getStatusBadge(client.status)}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                        <button class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300 mr-3" title="Lihat Detail">
                            <i class="fa-solid fa-eye"></i>
                        </button>
                        <button class="text-yellow-600 hover:text-yellow-900 dark:text-yellow-400 dark:hover:text-yellow-300 mr-3" title="Edit">
                            <i class="fa-solid fa-edit"></i>
                        </button>
                        <button class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300" title="Hapus">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `;
            tbody.innerHTML += row;
        });

        updatePaginationInfo();
        renderPagination();
    }

    function updatePaginationInfo() {
        const start = (currentPage - 1) * entriesPerPage + 1;
        const end = Math.min(start + entriesPerPage - 1, filteredData.length);
        const totalPages = Math.ceil(filteredData.length / entriesPerPage);

        document.getElementById('showingStart').textContent = filteredData.length > 0 ? start : 0;
        document.getElementById('showingEnd').textContent = end;
        document.getElementById('totalEntries').textContent = filteredData.length;
        document.getElementById('currentPage').textContent = currentPage;
        document.getElementById('totalPages').textContent = totalPages;
    }

    function renderPagination() {
        const totalPages = Math.ceil(filteredData.length / entriesPerPage);
        const pagination = document.getElementById('pagination');
        pagination.innerHTML = '';

        // Previous button
        const prevBtn = `
            <button onclick="changePage(${currentPage - 1})" ${currentPage === 1 ? 'disabled' : ''} 
                class="px-3 py-2 text-sm font-medium text-gray-700 bg-white dark:bg-gray-700 dark:text-gray-200 border border-gray-300 dark:border-gray-600 rounded-l-md hover:bg-gray-50 dark:hover:bg-gray-600 ${currentPage === 1 ? 'opacity-50 cursor-not-allowed' : ''}">
                <i class="fa-solid fa-chevron-left"></i>
            </button>
        `;
        pagination.innerHTML += prevBtn;

        // Page numbers
        let startPage = Math.max(1, currentPage - 2);
        let endPage = Math.min(totalPages, currentPage + 2);

        if (currentPage <= 3) {
            endPage = Math.min(5, totalPages);
        }
        if (currentPage >= totalPages - 2) {
            startPage = Math.max(1, totalPages - 4);
        }

        for (let i = startPage; i <= endPage; i++) {
            const pageBtn = `
                <button onclick="changePage(${i})" 
                    class="px-3 py-2 text-sm font-medium ${i === currentPage ? 'text-white bg-gray-800 dark:bg-gray-600' : 'text-gray-700 bg-white dark:bg-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600'} border border-gray-300 dark:border-gray-600">
                    ${i}
                </button>
            `;
            pagination.innerHTML += pageBtn;
        }

        // Next button
        const nextBtn = `
            <button onclick="changePage(${currentPage + 1})" ${currentPage === totalPages ? 'disabled' : ''} 
                class="px-3 py-2 text-sm font-medium text-gray-700 bg-white dark:bg-gray-700 dark:text-gray-200 border border-gray-300 dark:border-gray-600 rounded-r-md hover:bg-gray-50 dark:hover:bg-gray-600 ${currentPage === totalPages ? 'opacity-50 cursor-not-allowed' : ''}">
                <i class="fa-solid fa-chevron-right"></i>
            </button>
        `;
        pagination.innerHTML += nextBtn;
    }

    function changePage(page) {
        const totalPages = Math.ceil(filteredData.length / entriesPerPage);
        if (page < 1 || page > totalPages) return;
        currentPage = page;
        renderTable();
    }

    function filterData() {
        const searchTerm = document.getElementById('searchInput').value.toLowerCase();
        const categoryFilter = document.getElementById('categoryFilter').value;
        const statusFilter = document.getElementById('statusFilter').value;

        filteredData = clientsData.filter(client => {
            const matchSearch = client.name.toLowerCase().includes(searchTerm);
            const matchCategory = !categoryFilter || client.category === categoryFilter;
            const matchStatus = !statusFilter || client.status === statusFilter;
            return matchSearch && matchCategory && matchStatus;
        });

        currentPage = 1;
        renderTable();
    }

    function sortTable(columnIndex) {
        const columns = ['number', 'name', 'category', 'date', 'status'];
        const column = columns[columnIndex];

        if (!sortDirection[column]) sortDirection[column] = 'asc';
        else sortDirection[column] = sortDirection[column] === 'asc' ? 'desc' : 'asc';

        filteredData.sort((a, b) => {
            let aVal, bVal;

            if (column === 'number') {
                aVal = clientsData.indexOf(a);
                bVal = clientsData.indexOf(b);
            } else if (column === 'date') {
                aVal = new Date(a.date);
                bVal = new Date(b.date);
            } else {
                aVal = a[column].toLowerCase();
                bVal = b[column].toLowerCase();
            }

            if (sortDirection[column] === 'asc') {
                return aVal > bVal ? 1 : -1;
            } else {
                return aVal < bVal ? 1 : -1;
            }
        });

        renderTable();
    }

    // Event Listeners
    document.getElementById('searchInput').addEventListener('keyup', filterData);
    document.getElementById('categoryFilter').addEventListener('change', filterData);
    document.getElementById('statusFilter').addEventListener('change', filterData);
    document.getElementById('entriesPerPage').addEventListener('change', function () {
        entriesPerPage = parseInt(this.value);
        currentPage = 1;
        renderTable();
    });

    // Initial render
    renderTable();
</script>