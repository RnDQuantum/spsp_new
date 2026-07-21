{{--
    Pure Alpine modal — Zero Livewire round-trip on open/search/sort/page.
    Data is received via browser event 'open-talent-box-modal' dispatched
    by TalentPool\Index::openBoxModal() or client-side chart click.
--}}
<div
    x-data="talentBoxModal()"
    x-on:open-talent-box-modal.window="openModal($event.detail)"
    x-on:keydown.esc.window="closeModal()">

    {{-- Modal Overlay --}}
    <div x-cloak x-show="show" x-transition.opacity.duration.200ms
        x-on:click.self="closeModal()"
        class="fixed inset-0 z-50 flex items-end justify-center bg-black/50 p-4 pb-8 backdrop-blur-sm sm:items-center lg:p-8"
        role="dialog" aria-modal="true" aria-labelledby="modalTitle">

        {{-- Modal Dialog --}}
        <div x-show="show"
            x-transition:enter="transition ease-out duration-200 motion-reduce:transition-opacity"
            x-transition:enter-start="opacity-0 translate-y-4 scale-95" x-transition:enter-end="opacity-100 translate-y-0 scale-100"
            x-transition:leave="transition ease-in duration-100"
            x-transition:leave-start="opacity-100 translate-y-0 scale-100" x-transition:leave-end="opacity-0 translate-y-4 scale-95"
            class="flex w-full max-w-5xl flex-col overflow-hidden rounded-xl border border-warm-border dark:border-[#25211e] bg-white dark:bg-[#171412] text-primary-ink dark:text-neutral-100 shadow-2xl font-sans"
            style="max-height: 90vh;"
            x-trap="show">

            {{-- Dialog Header --}}
            <div class="flex shrink-0 items-center justify-between border-b border-warm-border dark:border-[#25211e] bg-warm-ivory dark:bg-[#1f1b18] px-6 py-4">
                <div class="flex items-center gap-4">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full shadow-md border border-white/20 text-white font-bold font-mono-data text-sm"
                        :style="'background: ' + (boxInfo.color || '#9E9E9E')">
                        <span x-text="boxInfo.code || 'K-?'"></span>
                    </div>
                    <div>
                        <h3 id="modalTitle" class="text-xl font-bold font-display tracking-tight text-primary-ink dark:text-neutral-100"
                            x-text="boxInfo.label || 'Daftar Peserta Talent Pool'"></h3>
                        <p class="text-xs text-primary-ink/70 dark:text-neutral-400 font-mono-data">
                            Total: <span x-text="filteredTotal" class="font-bold"></span> peserta ditemukan
                        </p>
                    </div>
                </div>
                <button x-on:click="closeModal()" aria-label="close modal"
                    class="text-primary-ink/70 dark:text-neutral-400 hover:text-accent-amber transition-colors p-1.5 rounded-lg hover:bg-black/5 dark:hover:bg-white/5">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" aria-hidden="true" stroke="currentColor"
                        fill="none" stroke-width="2" class="h-5 w-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            {{-- Search & Controls Bar --}}
            <div class="shrink-0 bg-white dark:bg-[#171412] border-b border-warm-border dark:border-[#25211e] px-6 py-3">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div class="relative flex-1 min-w-[220px]">
                        <input type="text" x-model.debounce.200ms="search"
                            x-on:input="currentPage = 1"
                            placeholder="Cari nama atau nomor tes..."
                            class="w-full rounded-lg border border-warm-border dark:border-[#25211e] bg-warm-ivory/50 dark:bg-[#1f1b18] px-4 py-2 pl-10 text-sm text-primary-ink dark:text-neutral-100 focus:outline-none focus:border-accent-amber transition-colors">
                        <svg class="absolute left-3 top-2.5 h-4 w-4 text-primary-ink/50 dark:text-neutral-400 pointer-events-none"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="text-xs font-mono-data text-primary-ink/70 dark:text-neutral-400">
                            Tampilkan:
                            <select x-model.number="perPage" x-on:change="currentPage = 1"
                                class="ml-1 rounded-md border border-warm-border dark:border-[#25211e] bg-warm-ivory dark:bg-[#1f1b18] px-2 py-1 text-xs font-mono-data font-bold text-primary-ink dark:text-neutral-100 focus:outline-none focus:border-accent-amber">
                                <option value="10">10</option>
                                <option value="25">25</option>
                                <option value="50">50</option>
                            </select>
                        </div>
                        <div class="text-xs font-mono-data text-primary-ink/70 dark:text-neutral-400 border-l border-warm-border dark:border-[#25211e] pl-3">
                            <span x-text="pageStart"></span>–<span x-text="pageEnd"></span> / <span x-text="filteredTotal"></span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Dialog Body / Table --}}
            <div class="flex-1 overflow-auto bg-white dark:bg-[#171412] relative">
                <table class="w-full border-collapse text-sm text-primary-ink dark:text-neutral-200">
                    <thead class="sticky top-0 z-10 bg-warm-ivory dark:bg-[#1f1b18] border-b border-warm-border dark:border-[#25211e]">
                        <tr>
                            <th class="border border-warm-border dark:border-[#25211e] px-3 py-2.5 text-center font-bold text-xs w-12">No</th>
                            <th class="border border-warm-border dark:border-[#25211e] px-4 py-2.5 text-left font-bold text-xs cursor-pointer hover:bg-warm-border/30 dark:hover:bg-[#25211e]/60 transition-colors select-none"
                                x-on:click="toggleSort('test_number')">
                                <div class="flex items-center gap-1.5">
                                    Nomor Tes
                                    <template x-if="sortKey === 'test_number'">
                                        <svg class="h-3.5 w-3.5 text-accent-amber" fill="currentColor" viewBox="0 0 20 20">
                                            <path x-show="sortDir === 'asc'" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" />
                                            <path x-show="sortDir === 'desc'" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" />
                                        </svg>
                                    </template>
                                </div>
                            </th>
                            <th class="border border-warm-border dark:border-[#25211e] px-4 py-2.5 text-left font-bold text-xs cursor-pointer hover:bg-warm-border/30 dark:hover:bg-[#25211e]/60 transition-colors select-none"
                                x-on:click="toggleSort('name')">
                                <div class="flex items-center gap-1.5">
                                    Nama Peserta
                                    <template x-if="sortKey === 'name'">
                                        <svg class="h-3.5 w-3.5 text-accent-amber" fill="currentColor" viewBox="0 0 20 20">
                                            <path x-show="sortDir === 'asc'" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" />
                                            <path x-show="sortDir === 'desc'" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" />
                                        </svg>
                                    </template>
                                </div>
                            </th>
                            <th class="border border-warm-border dark:border-[#25211e] px-4 py-2.5 text-center font-bold text-xs cursor-pointer hover:bg-warm-border/30 dark:hover:bg-[#25211e]/60 transition-colors select-none w-32"
                                x-on:click="toggleSort('potensi_rating')">
                                <div class="flex items-center justify-center gap-1.5">
                                    Potensi
                                    <template x-if="sortKey === 'potensi_rating'">
                                        <svg class="h-3.5 w-3.5 text-accent-amber" fill="currentColor" viewBox="0 0 20 20">
                                            <path x-show="sortDir === 'asc'" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" />
                                            <path x-show="sortDir === 'desc'" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" />
                                        </svg>
                                    </template>
                                </div>
                            </th>
                            <th class="border border-warm-border dark:border-[#25211e] px-4 py-2.5 text-center font-bold text-xs cursor-pointer hover:bg-warm-border/30 dark:hover:bg-[#25211e]/60 transition-colors select-none w-32"
                                x-on:click="toggleSort('kinerja_rating')">
                                <div class="flex items-center justify-center gap-1.5">
                                    Kompetensi
                                    <template x-if="sortKey === 'kinerja_rating'">
                                        <svg class="h-3.5 w-3.5 text-accent-amber" fill="currentColor" viewBox="0 0 20 20">
                                            <path x-show="sortDir === 'asc'" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" />
                                            <path x-show="sortDir === 'desc'" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" />
                                        </svg>
                                    </template>
                                </div>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-[#171412]">
                        <template x-for="(row, idx) in paginatedRows" :key="String(row.test_number) + '_' + idx">
                            <tr class="hover:bg-warm-ivory/50 dark:hover:bg-[#1f1b18]/50 transition-colors duration-100">
                                <td class="border border-warm-border dark:border-[#25211e] px-3 py-2 text-center font-mono-data text-xs text-primary-ink/70 dark:text-neutral-400"
                                    x-text="(currentPage - 1) * perPage + idx + 1"></td>
                                <td class="border border-warm-border dark:border-[#25211e] px-4 py-2 font-mono-data text-xs font-semibold text-primary-ink dark:text-neutral-200"
                                    x-text="row.test_number"></td>
                                <td class="border border-warm-border dark:border-[#25211e] px-4 py-2 font-semibold text-primary-ink dark:text-neutral-100"
                                    x-text="row.name"></td>
                                <td class="border border-warm-border dark:border-[#25211e] px-4 py-2 text-center">
                                    <span class="inline-flex items-center rounded-md bg-warm-ivory dark:bg-[#25211e] border border-warm-border dark:border-[#25211e] px-2.5 py-0.5 text-xs font-mono-data font-bold text-primary-ink dark:text-neutral-200"
                                        x-text="parseFloat(row.potensi_rating || 0).toFixed(2)">
                                    </span>
                                </td>
                                <td class="border border-warm-border dark:border-[#25211e] px-4 py-2 text-center">
                                    <span class="inline-flex items-center rounded-md bg-warm-ivory dark:bg-[#25211e] border border-warm-border dark:border-[#25211e] px-2.5 py-0.5 text-xs font-mono-data font-bold text-primary-ink dark:text-neutral-200"
                                        x-text="parseFloat(row.kinerja_rating || 0).toFixed(2)">
                                    </span>
                                </td>
                            </tr>
                        </template>

                        <tr x-show="filteredTotal === 0">
                            <td colspan="5" class="border border-warm-border dark:border-[#25211e] px-4 py-8 text-center text-primary-ink/60 dark:text-neutral-400 text-sm">
                                <span x-show="search.length > 0">Tidak ada hasil untuk "<span x-text="search" class="font-semibold"></span>"</span>
                                <span x-show="search.length === 0">Tidak ada data peserta di kotak ini</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="shrink-0 border-t border-warm-border dark:border-[#25211e] bg-warm-ivory/50 dark:bg-[#1f1b18]/50 px-6 py-3"
                x-show="lastPage > 1">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div class="text-xs font-mono-data text-primary-ink/70 dark:text-neutral-400">
                        Menampilkan <span x-text="pageStart"></span>–<span x-text="pageEnd"></span>
                        dari <span x-text="filteredTotal"></span> peserta
                    </div>
                    <div class="flex gap-1.5 items-center">
                        <button x-on:click="currentPage = Math.max(1, currentPage - 1)"
                            :disabled="currentPage <= 1"
                            class="rounded-md border border-warm-border dark:border-[#25211e] bg-white dark:bg-[#1f1b18] px-3 py-1 text-xs font-bold text-primary-ink dark:text-neutral-200 transition hover:bg-warm-ivory disabled:cursor-not-allowed disabled:opacity-40 cursor-pointer">
                            ← Prev
                        </button>

                        <template x-for="(p, i) in pageNumbers" :key="i">
                            <span class="inline-flex items-center">
                                <button x-show="p !== '...'" x-on:click="currentPage = p"
                                    :class="p === currentPage ? 'border-accent-amber bg-accent-amber text-white' : 'border-warm-border dark:border-[#25211e] bg-white dark:bg-[#1f1b18] text-primary-ink dark:text-neutral-200 hover:bg-warm-ivory'"
                                    class="rounded-md border px-3 py-1 text-xs font-bold font-mono-data transition-colors cursor-pointer"
                                    x-text="p">
                                </button>
                                <span x-show="p === '...'" class="px-2 py-1 text-xs font-mono-data text-primary-ink/50 dark:text-neutral-500">…</span>
                            </span>
                        </template>

                        <button x-on:click="currentPage = Math.min(lastPage, currentPage + 1)"
                            :disabled="currentPage >= lastPage"
                            class="rounded-md border border-warm-border dark:border-[#25211e] bg-white dark:bg-[#1f1b18] px-3 py-1 text-xs font-bold text-primary-ink dark:text-neutral-200 transition hover:bg-warm-ivory disabled:cursor-not-allowed disabled:opacity-40 cursor-pointer">
                            Next →
                        </button>
                    </div>
                </div>
            </div>

            {{-- Dialog Footer --}}
            <div class="shrink-0 flex justify-end border-t border-warm-border dark:border-[#25211e] bg-warm-ivory dark:bg-[#1f1b18] px-6 py-3">
                <button x-on:click="closeModal()" type="button"
                    class="rounded-lg bg-accent-amber px-5 py-2 text-center text-xs font-bold uppercase tracking-wider text-white transition hover:bg-amber-700 active:scale-95 cursor-pointer">
                    Tutup
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function talentBoxModal() {
    return {
        show: false,
        boxInfo: {
            number: 1,
            code: '',
            label: '',
            color: '#9E9E9E'
        },
        allData: [],
        search: '',
        sortKey: 'name',
        sortDir: 'asc',
        currentPage: 1,
        perPage: 25,

        openModal(detail) {
            if (Array.isArray(detail)) {
                detail = detail[0] || {};
            }
            this.boxInfo = {
                number: detail.boxNumber || 1,
                code: detail.boxCode || ('K-' + (detail.boxNumber || 1)),
                label: detail.boxLabel || 'Daftar Peserta',
                color: detail.boxColor || '#9E9E9E'
            };
            this.allData = detail.participants || [];
            this.search = '';
            this.currentPage = 1;
            this.sortKey = 'name';
            this.sortDir = 'asc';
            this.show = true;
        },

        closeModal() {
            this.show = false;
            setTimeout(() => {
                this.allData = [];
            }, 150);
        },

        toggleSort(key) {
            if (this.sortKey === key) {
                this.sortDir = this.sortDir === 'asc' ? 'desc' : 'asc';
            } else {
                this.sortKey = key;
                this.sortDir = 'asc';
            }
            this.currentPage = 1;
        },

        get filteredData() {
            let data = [...this.allData];
            const q = this.search.toLowerCase().trim();
            if (q) {
                data = data.filter(r =>
                    (r.name && r.name.toLowerCase().includes(q)) ||
                    (r.test_number && String(r.test_number).toLowerCase().includes(q))
                );
            }
            const key = this.sortKey;
            const dir = this.sortDir === 'asc' ? 1 : -1;
            data.sort((a, b) => {
                const isNumeric = key === 'potensi_rating' || key === 'kinerja_rating';
                const av = isNumeric ? parseFloat(a[key] || 0) : String(a[key] || '').toLowerCase();
                const bv = isNumeric ? parseFloat(b[key] || 0) : String(b[key] || '').toLowerCase();
                if (av < bv) return -1 * dir;
                if (av > bv) return 1 * dir;
                return 0;
            });
            return data;
        },

        get filteredTotal() { return this.filteredData.length; },
        get lastPage() { return Math.max(1, Math.ceil(this.filteredTotal / this.perPage)); },
        get pageStart() { return this.filteredTotal === 0 ? 0 : (this.currentPage - 1) * this.perPage + 1; },
        get pageEnd() { return Math.min(this.currentPage * this.perPage, this.filteredTotal); },

        get paginatedRows() {
            const start = (this.currentPage - 1) * this.perPage;
            return this.filteredData.slice(start, start + this.perPage);
        },

        get pageNumbers() {
            const pages = [];
            const total = this.lastPage;
            const cur = this.currentPage;
            if (total <= 7) {
                for (let i = 1; i <= total; i++) pages.push(i);
            } else {
                pages.push(1);
                if (cur > 3) pages.push('...');
                for (let i = Math.max(2, cur - 1); i <= Math.min(total - 1, cur + 1); i++) pages.push(i);
                if (cur < total - 2) pages.push('...');
                pages.push(total);
            }
            return pages;
        },
    };
}
</script>
