<?php

namespace App\Livewire\Pages;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.app', ['title' => 'Daftar Klien'])]
class ClientList extends Component
{
    use WithPagination;

    public $search = '';
    public $categoryFilter = '';
    public $statusFilter = '';
    public $perPage = 10;
    public $sortField = 'name';
    public $sortDirection = 'asc';

    protected $queryString = [
        'search' => ['except' => ''],
        'categoryFilter' => ['except' => ''],
        'statusFilter' => ['except' => ''],
    ];

    // Sample data - nanti bisa diganti dengan data dari database
    protected function getClientsData()
    {
        return collect([
            ['name' => 'Kementerian Keuangan', 'category' => 'Kementerian', 'date' => '2024-11-28', 'status' => 'Aktif'],
            ['name' => 'PT. Telkom Indonesia', 'category' => 'BUMN', 'date' => '2024-11-25', 'status' => 'Aktif'],
            ['name' => 'Universitas Indonesia', 'category' => 'Pendidikan', 'date' => '2024-11-22', 'status' => 'Pending'],
            ['name' => 'PT. Bank Mandiri', 'category' => 'BUMN', 'date' => '2024-11-20', 'status' => 'Aktif'],
            ['name' => 'Kementerian BUMN', 'category' => 'Kementerian', 'date' => '2024-11-18', 'status' => 'Selesai'],
            ['name' => 'PT. Pertamina', 'category' => 'BUMN', 'date' => '2024-11-15', 'status' => 'Aktif'],
            ['name' => 'Universitas Gadjah Mada', 'category' => 'Pendidikan', 'date' => '2024-11-12', 'status' => 'Pending'],
            ['name' => 'Kementerian Pendidikan', 'category' => 'Kementerian', 'date' => '2024-11-10', 'status' => 'Aktif'],
            ['name' => 'PT. Unilever Indonesia', 'category' => 'Swasta', 'date' => '2024-11-08', 'status' => 'Aktif'],
            ['name' => 'Institut Teknologi Bandung', 'category' => 'Pendidikan', 'date' => '2024-11-05', 'status' => 'Selesai'],
            ['name' => 'Kementerian Kesehatan', 'category' => 'Kementerian', 'date' => '2024-11-03', 'status' => 'Aktif'],
            ['name' => 'PT. Garuda Indonesia', 'category' => 'BUMN', 'date' => '2024-11-01', 'status' => 'Pending'],
            ['name' => 'Universitas Airlangga', 'category' => 'Pendidikan', 'date' => '2024-10-30', 'status' => 'Aktif'],
            ['name' => 'PT. Astra International', 'category' => 'Swasta', 'date' => '2024-10-28', 'status' => 'Aktif'],
            ['name' => 'Kementerian Dalam Negeri', 'category' => 'Kementerian', 'date' => '2024-10-25', 'status' => 'Selesai'],
            ['name' => 'PT. PLN', 'category' => 'BUMN', 'date' => '2024-10-22', 'status' => 'Aktif'],
            ['name' => 'Universitas Padjadjaran', 'category' => 'Pendidikan', 'date' => '2024-10-20', 'status' => 'Aktif'],
            ['name' => 'PT. BCA', 'category' => 'Swasta', 'date' => '2024-10-18', 'status' => 'Pending'],
            ['name' => 'Kementerian Perhubungan', 'category' => 'Kementerian', 'date' => '2024-10-15', 'status' => 'Aktif'],
            ['name' => 'PT. Indofood', 'category' => 'Swasta', 'date' => '2024-10-12', 'status' => 'Aktif'],
            ['name' => 'Universitas Diponegoro', 'category' => 'Pendidikan', 'date' => '2024-10-10', 'status' => 'Selesai'],
            ['name' => 'PT. Wijaya Karya', 'category' => 'BUMN', 'date' => '2024-10-08', 'status' => 'Aktif'],
            ['name' => 'Kementerian Perindustrian', 'category' => 'Kementerian', 'date' => '2024-10-05', 'status' => 'Pending'],
            ['name' => 'PT. Semen Indonesia', 'category' => 'BUMN', 'date' => '2024-10-03', 'status' => 'Aktif'],
            ['name' => 'Universitas Brawijaya', 'category' => 'Pendidikan', 'date' => '2024-10-01', 'status' => 'Aktif'],
            ['name' => 'PT. BNI', 'category' => 'BUMN', 'date' => '2024-09-28', 'status' => 'Aktif'],
            ['name' => 'Kementerian Pertanian', 'category' => 'Kementerian', 'date' => '2024-09-25', 'status' => 'Selesai'],
            ['name' => 'PT. Adaro Energy', 'category' => 'Swasta', 'date' => '2024-09-22', 'status' => 'Aktif'],
            ['name' => 'Universitas Hasanuddin', 'category' => 'Pendidikan', 'date' => '2024-09-20', 'status' => 'Pending'],
            ['name' => 'PT. Angkasa Pura', 'category' => 'BUMN', 'date' => '2024-09-18', 'status' => 'Aktif'],
            ['name' => 'Kementerian Pariwisata', 'category' => 'Kementerian', 'date' => '2024-09-15', 'status' => 'Aktif'],
            ['name' => 'PT. BRI', 'category' => 'BUMN', 'date' => '2024-09-12', 'status' => 'Aktif'],
            ['name' => 'Universitas Sebelas Maret', 'category' => 'Pendidikan', 'date' => '2024-09-10', 'status' => 'Selesai'],
            ['name' => 'PT. Gudang Garam', 'category' => 'Swasta', 'date' => '2024-09-08', 'status' => 'Aktif'],
            ['name' => 'Kementerian Ketenagakerjaan', 'category' => 'Kementerian', 'date' => '2024-09-05', 'status' => 'Pending'],
            ['name' => 'PT. Pelni', 'category' => 'BUMN', 'date' => '2024-09-03', 'status' => 'Aktif'],
            ['name' => 'Universitas Andalas', 'category' => 'Pendidikan', 'date' => '2024-09-01', 'status' => 'Aktif'],
            ['name' => 'PT. HM Sampoerna', 'category' => 'Swasta', 'date' => '2024-08-28', 'status' => 'Aktif'],
            ['name' => 'Kementerian Sosial', 'category' => 'Kementerian', 'date' => '2024-08-25', 'status' => 'Selesai'],
            ['name' => 'PT. Jasa Marga', 'category' => 'BUMN', 'date' => '2024-08-22', 'status' => 'Aktif'],
            ['name' => 'Warung Kopi Kenangan', 'category' => 'UMKM', 'date' => '2024-08-20', 'status' => 'Pending'],
            ['name' => 'Toko Elektronik Maju', 'category' => 'UMKM', 'date' => '2024-08-18', 'status' => 'Aktif'],
            ['name' => 'CV. Berkah Jaya', 'category' => 'UMKM', 'date' => '2024-08-15', 'status' => 'Aktif'],
            ['name' => 'PT. Mayora Indah', 'category' => 'Swasta', 'date' => '2024-08-12', 'status' => 'Selesai'],
            ['name' => 'Kementerian Energi', 'category' => 'Kementerian', 'date' => '2024-08-10', 'status' => 'Aktif'],
            ['name' => 'UD. Sumber Rezeki', 'category' => 'UMKM', 'date' => '2024-08-08', 'status' => 'Pending'],
            ['name' => 'PT. Kalbe Farma', 'category' => 'Swasta', 'date' => '2024-08-05', 'status' => 'Aktif'],
            ['name' => 'Toko Baju Fashion', 'category' => 'UMKM', 'date' => '2024-08-03', 'status' => 'Aktif'],
            ['name' => 'PT. Bukalapak', 'category' => 'Swasta', 'date' => '2024-08-01', 'status' => 'Aktif'],
            ['name' => 'Kedai Nasi Padang Sederhana', 'category' => 'UMKM', 'date' => '2024-07-28', 'status' => 'Selesai'],
        ]);
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingCategoryFilter()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function getClientsProperty()
    {
        $clients = $this->getClientsData();

        // Apply filters
        if ($this->search) {
            $clients = $clients->filter(function ($client) {
                return stripos($client['name'], $this->search) !== false;
            });
        }

        if ($this->categoryFilter) {
            $clients = $clients->where('category', $this->categoryFilter);
        }

        if ($this->statusFilter) {
            $clients = $clients->where('status', $this->statusFilter);
        }

        // Apply sorting
        $clients = $clients->sortBy($this->sortField, SORT_REGULAR, $this->sortDirection === 'desc');

        return $clients->values();
    }

    public function render()
    {
        $clients = $this->clients;
        $total = $clients->count();

        // Manual pagination
        $currentPage = $this->getPage();
        $perPage = $this->perPage;
        $offset = ($currentPage - 1) * $perPage;

        $paginatedClients = $clients->slice($offset, $perPage)->values();

        return view('livewire.pages.list-klien', [
            'clients' => $paginatedClients,
            'total' => $total,
            'from' => $total > 0 ? $offset + 1 : 0,
            'to' => min($offset + $perPage, $total),
            'currentPage' => $currentPage,
            'lastPage' => ceil($total / $perPage),
        ]);
    }
}
