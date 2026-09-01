<div>
    @if($isOpen)
    <div 
        class="fixed inset-0 z-50 overflow-y-auto bg-primary-ink/70 backdrop-blur-xs flex items-center justify-center p-4 sm:p-6 animate-fadeIn"
        x-data="{ activeTab: @entangle('activeTab') }"
        @keydown.escape.window="$wire.closeEditor()"
    >
        <div 
            class="bg-white dark:bg-[#171412] w-full max-w-4xl rounded-xl shadow-2xl border border-warm-border dark:border-[#25211e] overflow-hidden flex flex-col max-h-[90vh]"
            @click.outside="$wire.closeEditor()"
        >
            <!-- Modal Header -->
            <div class="px-6 py-5 bg-[#1f1b18] text-white border-b border-warm-border/10 flex items-center justify-between shrink-0">
                <div class="flex items-center gap-3.5">
                    <div class="w-10 h-10 rounded-lg bg-accent-amber text-white flex items-center justify-center font-display font-bold text-lg shadow-sm">
                        <i class="fas fa-sliders text-sm"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h2 class="font-display font-semibold text-base text-white">Kelola Data Pelengkap HCA</h2>
                            <span class="text-[10px] bg-accent-amber/20 text-accent-amber border border-accent-amber/30 px-2 py-0.5 rounded-full font-medium">In-Context Editor</span>
                        </div>
                        <p class="text-xs text-slate-400 mt-0.5">
                            {{ $this->participant?->name ?? 'Peserta' }} &bull; {{ $this->participant?->positionFormation?->name ?? 'Formasi Jabatan' }}
                        </p>
                    </div>
                </div>
                <button 
                    type="button" 
                    wire:click="closeEditor"
                    class="p-2 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800 transition-colors cursor-pointer"
                    title="Tutup (Esc)"
                >
                    <i class="fas fa-times text-sm"></i>
                </button>
            </div>

            <!-- Tab Navigation (Executive Journal Style) -->
            <div class="px-6 bg-warm-ivory dark:bg-[#241f1c] border-b border-warm-border dark:border-[#25211e] flex items-center gap-2 overflow-x-auto shrink-0">
                <button 
                    type="button"
                    wire:click="setActiveTab('performance')"
                    class="py-3 px-4 text-xs font-semibold border-b-2 transition-all flex items-center gap-2 cursor-pointer {{ $activeTab === 'performance' ? 'border-accent-amber text-accent-amber font-bold' : 'border-transparent text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white' }}"
                >
                    <i class="fas fa-chart-line text-xs"></i>
                    <span>1. Rekam Kinerja (KPI)</span>
                    <span class="text-[10px] px-1.5 py-0.2 rounded-full {{ $activeTab === 'performance' ? 'bg-accent-amber text-white' : 'bg-slate-200 dark:bg-slate-700 text-slate-600 dark:text-slate-300' }}">
                        {{ count($performanceRecords) }}
                    </span>
                </button>

                <button 
                    type="button"
                    wire:click="setActiveTab('career')"
                    class="py-3 px-4 text-xs font-semibold border-b-2 transition-all flex items-center gap-2 cursor-pointer {{ $activeTab === 'career' ? 'border-accent-amber text-accent-amber font-bold' : 'border-transparent text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white' }}"
                >
                    <i class="fas fa-briefcase text-xs"></i>
                    <span>2. Riwayat Karier</span>
                    <span class="text-[10px] px-1.5 py-0.2 rounded-full {{ $activeTab === 'career' ? 'bg-accent-amber text-white' : 'bg-slate-200 dark:bg-slate-700 text-slate-600 dark:text-slate-300' }}">
                        {{ count($careerHistories) }}
                    </span>
                </button>

                <button 
                    type="button"
                    wire:click="setActiveTab('personal')"
                    class="py-3 px-4 text-xs font-semibold border-b-2 transition-all flex items-center gap-2 cursor-pointer {{ $activeTab === 'personal' ? 'border-accent-amber text-accent-amber font-bold' : 'border-transparent text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white' }}"
                >
                    <i class="fas fa-id-card-clip text-xs"></i>
                    <span>3. Profil Personal</span>
                </button>
            </div>

            <!-- Success Alert Banner -->
            @if($successMessage)
            <div class="mx-6 mt-4 p-3 bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-800/40 rounded-lg text-forest-green dark:text-emerald-400 text-xs flex items-center justify-between shrink-0 animate-fadeIn">
                <div class="flex items-center gap-2">
                    <i class="fas fa-circle-check text-sm"></i>
                    <span>{{ $successMessage }}</span>
                </div>
                <button type="button" wire:click="$set('successMessage', null)" class="text-slate-400 hover:text-slate-600">
                    <i class="fas fa-times text-xs"></i>
                </button>
            </div>
            @endif

            <!-- Modal Body (Scrollable) -->
            <div class="p-6 overflow-y-auto flex-1 space-y-6">
                <!-- TAB 1: REKAM KINERJA & KPI -->
                @if($activeTab === 'performance')
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="font-display font-bold text-sm text-primary-ink dark:text-white">Rekam Realisasi Kinerja & KPI Tahunan</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                                Data ini disinkronkan ke <strong class="text-slate-700 dark:text-slate-300">Section 15 (Dashboard Kinerja)</strong> dan menjadi <strong class="text-slate-700 dark:text-slate-300">Sumbu Kinerja Matriks 9-Box (Section 16)</strong>.
                            </p>
                        </div>
                        <button 
                            type="button" 
                            wire:click="addPerformanceRow"
                            class="px-3 py-1.5 rounded-md bg-accent-amber/10 hover:bg-accent-amber/20 text-accent-amber border border-accent-amber/30 text-xs font-semibold transition-colors flex items-center gap-1.5 cursor-pointer"
                        >
                            <i class="fas fa-plus text-[10px]"></i>
                            Tambah Tahun
                        </button>
                    </div>

                    <div class="space-y-3">
                        @forelse($performanceRecords as $index => $record)
                        <div class="p-4 rounded-lg border border-warm-border dark:border-[#25211e] bg-warm-ivory/30 dark:bg-[#1f1b18]/40 space-y-3 relative group">
                            <div class="flex items-center justify-between border-b border-warm-border/60 dark:border-[#25211e] pb-2.5">
                                <span class="text-xs font-bold text-primary-ink dark:text-white flex items-center gap-2">
                                    <span class="w-5 h-5 rounded-full bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300 flex items-center justify-center text-[10px]">
                                        {{ $index + 1 }}
                                    </span>
                                    Tahun Evaluasi: {{ $record['year'] }}
                                </span>
                                <button 
                                    type="button" 
                                    wire:click="removePerformanceRow({{ $index }})"
                                    class="text-xs text-rose-500 hover:text-rose-700 hover:bg-rose-50 dark:hover:bg-rose-950/30 px-2 py-1 rounded transition-colors cursor-pointer"
                                    title="Hapus baris tahun ini"
                                >
                                    <i class="fas fa-trash-can mr-1"></i> Hapus
                                </button>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3">
                                <div>
                                    <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-400 mb-1">Tahun</label>
                                    <input 
                                        type="number" 
                                        wire:model="performanceRecords.{{ $index }}.year"
                                        class="w-full text-xs px-3 py-2 rounded-md border border-warm-border dark:border-[#25211e] bg-white dark:bg-[#171412] text-primary-ink dark:text-white focus:outline-none focus:ring-1 focus:ring-accent-amber"
                                        placeholder="YYYY"
                                    />
                                </div>
                                <div>
                                    <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-400 mb-1">Capaian KPI (%)</label>
                                    <input 
                                        type="number" 
                                        step="0.01"
                                        wire:model="performanceRecords.{{ $index }}.kpi_score"
                                        class="w-full text-xs px-3 py-2 rounded-md border border-warm-border dark:border-[#25211e] bg-white dark:bg-[#171412] text-primary-ink dark:text-white focus:outline-none focus:ring-1 focus:ring-accent-amber"
                                        placeholder="95.50"
                                    />
                                </div>
                                <div>
                                    <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-400 mb-1">Target (%)</label>
                                    <input 
                                        type="number" 
                                        step="0.01"
                                        wire:model="performanceRecords.{{ $index }}.target_score"
                                        class="w-full text-xs px-3 py-2 rounded-md border border-warm-border dark:border-[#25211e] bg-white dark:bg-[#171412] text-primary-ink dark:text-white focus:outline-none focus:ring-1 focus:ring-accent-amber"
                                        placeholder="100.00"
                                    />
                                </div>
                                <div>
                                    <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-400 mb-1">Predikat / Rating</label>
                                    <select 
                                        wire:model="performanceRecords.{{ $index }}.performance_rating"
                                        class="w-full text-xs px-3 py-2 rounded-md border border-warm-border dark:border-[#25211e] bg-white dark:bg-[#171412] text-primary-ink dark:text-white focus:outline-none focus:ring-1 focus:ring-accent-amber"
                                    >
                                        <option value="Istimewa">Istimewa (Exceeded)</option>
                                        <option value="Sangat Baik">Sangat Baik</option>
                                        <option value="Baik">Baik (Achieved)</option>
                                        <option value="Cukup">Cukup (Needs Improvement)</option>
                                        <option value="Kurang">Kurang (Underperformed)</option>
                                    </select>
                                </div>
                            </div>

                            <div>
                                <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-400 mb-1">
                                    Pencapaian Kunci / Metrik Strategis (1 poin per baris)
                                </label>
                                <textarea 
                                    rows="2"
                                    wire:model="performanceRecords.{{ $index }}.achievements_text"
                                    class="w-full text-xs px-3 py-2 rounded-md border border-warm-border dark:border-[#25211e] bg-white dark:bg-[#171412] text-primary-ink dark:text-white focus:outline-none focus:ring-1 focus:ring-accent-amber"
                                    placeholder="Contoh: Realisasi efisiensi anggaran sebesar 12%&#10;Implementasi sistem digitalisasi operasional"
                                ></textarea>
                            </div>
                        </div>
                        @empty
                        <div class="text-center py-8 border-2 border-dashed border-warm-border dark:border-[#25211e] rounded-lg">
                            <p class="text-xs text-slate-500">Belum ada rekam kinerja tahunan.</p>
                            <button type="button" wire:click="addPerformanceRow" class="mt-2 text-xs text-accent-amber font-semibold">
                                + Tambah Tahun Pertama
                            </button>
                        </div>
                        @endforelse
                    </div>
                </div>
                @endif

                <!-- TAB 2: RIWAYAT KARIER -->
                @if($activeTab === 'career')
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="font-display font-bold text-sm text-primary-ink dark:text-white">Riwayat Jabatan & Rekam Jejak Penugasan</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                                Data ini disinkronkan ke <strong class="text-slate-700 dark:text-slate-300">Section 06 (Riwayat Karier)</strong> dan perhitungan masa kerja efektif (*tenure*).
                            </p>
                        </div>
                        <button 
                            type="button" 
                            wire:click="addCareerRow"
                            class="px-3 py-1.5 rounded-md bg-accent-amber/10 hover:bg-accent-amber/20 text-accent-amber border border-accent-amber/30 text-xs font-semibold transition-colors flex items-center gap-1.5 cursor-pointer"
                        >
                            <i class="fas fa-plus text-[10px]"></i>
                            Tambah Jabatan
                        </button>
                    </div>

                    <div class="space-y-3">
                        @forelse($careerHistories as $index => $career)
                        <div class="p-4 rounded-lg border border-warm-border dark:border-[#25211e] bg-warm-ivory/30 dark:bg-[#1f1b18]/40 space-y-3 relative">
                            <div class="flex items-center justify-between border-b border-warm-border/60 dark:border-[#25211e] pb-2.5">
                                <span class="text-xs font-bold text-primary-ink dark:text-white flex items-center gap-2">
                                    <span class="w-5 h-5 rounded-full bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300 flex items-center justify-center text-[10px]">
                                        {{ $index + 1 }}
                                    </span>
                                    {{ $career['position_title'] ?: 'Posisi Baru' }}
                                    @if(!empty($career['is_current']))
                                        <span class="text-[10px] px-2 py-0.5 rounded bg-emerald-100 text-forest-green font-bold">Posisi Aktif</span>
                                    @endif
                                </span>
                                <button 
                                    type="button" 
                                    wire:click="removeCareerRow({{ $index }})"
                                    class="text-xs text-rose-500 hover:text-rose-700 hover:bg-rose-50 dark:hover:bg-rose-950/30 px-2 py-1 rounded transition-colors cursor-pointer"
                                    title="Hapus baris jabatan ini"
                                >
                                    <i class="fas fa-trash-can mr-1"></i> Hapus
                                </button>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-400 mb-1">Nama Jabatan / Posisi</label>
                                    <input 
                                        type="text" 
                                        wire:model="careerHistories.{{ $index }}.position_title"
                                        class="w-full text-xs px-3 py-2 rounded-md border border-warm-border dark:border-[#25211e] bg-white dark:bg-[#171412] text-primary-ink dark:text-white focus:outline-none focus:ring-1 focus:ring-accent-amber"
                                        placeholder="Contoh: Manager Operasional"
                                    />
                                </div>
                                <div>
                                    <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-400 mb-1">Instansi / Perusahaan / Unit Kerja</label>
                                    <input 
                                        type="text" 
                                        wire:model="careerHistories.{{ $index }}.company_or_institution"
                                        class="w-full text-xs px-3 py-2 rounded-md border border-warm-border dark:border-[#25211e] bg-white dark:bg-[#171412] text-primary-ink dark:text-white focus:outline-none focus:ring-1 focus:ring-accent-amber"
                                        placeholder="Contoh: Direktorat Jenderal Pajak"
                                    />
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 items-center">
                                <div>
                                    <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-400 mb-1">Tahun Mulai</label>
                                    <input 
                                        type="number" 
                                        wire:model="careerHistories.{{ $index }}.start_year"
                                        class="w-full text-xs px-3 py-2 rounded-md border border-warm-border dark:border-[#25211e] bg-white dark:bg-[#171412] text-primary-ink dark:text-white focus:outline-none focus:ring-1 focus:ring-accent-amber"
                                        placeholder="YYYY"
                                    />
                                </div>
                                <div>
                                    <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-400 mb-1">Tahun Selesai</label>
                                    <input 
                                        type="number" 
                                        wire:model="careerHistories.{{ $index }}.end_year"
                                        :disabled="{{ !empty($career['is_current']) ? 'true' : 'false' }}"
                                        class="w-full text-xs px-3 py-2 rounded-md border border-warm-border dark:border-[#25211e] bg-white dark:bg-[#171412] text-primary-ink dark:text-white focus:outline-none focus:ring-1 focus:ring-accent-amber disabled:opacity-50 disabled:bg-slate-100"
                                        placeholder="{{ !empty($career['is_current']) ? 'Sekarang' : 'YYYY' }}"
                                    />
                                </div>
                                <div class="pt-4 flex items-center">
                                    <label class="inline-flex items-center gap-2 cursor-pointer text-xs font-semibold text-primary-ink dark:text-white">
                                        <input 
                                            type="checkbox" 
                                            wire:click="toggleCurrentCareer({{ $index }})"
                                            {{ !empty($career['is_current']) ? 'checked' : '' }}
                                            class="rounded text-accent-amber focus:ring-accent-amber w-4 h-4"
                                        />
                                        <span>Jabatan Saat Ini (Aktif)</span>
                                    </label>
                                </div>
                            </div>

                            <div>
                                <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-400 mb-1">
                                    Pencapaian & Tanggung Jawab Utama (1 poin per baris)
                                </label>
                                <textarea 
                                    rows="2"
                                    wire:model="careerHistories.{{ $index }}.achievements_text"
                                    class="w-full text-xs px-3 py-2 rounded-md border border-warm-border dark:border-[#25211e] bg-white dark:bg-[#171412] text-primary-ink dark:text-white focus:outline-none focus:ring-1 focus:ring-accent-amber"
                                    placeholder="Contoh: Memimpin tim dalam transformasi tata kelola layanan&#10;Meningkatkan kepatuhan SOP divisi hingga 99%"
                                ></textarea>
                            </div>
                        </div>
                        @empty
                        <div class="text-center py-8 border-2 border-dashed border-warm-border dark:border-[#25211e] rounded-lg">
                            <p class="text-xs text-slate-500">Belum ada riwayat jabatan.</p>
                            <button type="button" wire:click="addCareerRow" class="mt-2 text-xs text-accent-amber font-semibold">
                                + Tambah Riwayat Jabatan
                            </button>
                        </div>
                        @endforelse
                    </div>
                </div>
                @endif

                <!-- TAB 3: PROFIL PERSONAL -->
                @if($activeTab === 'personal')
                <div class="space-y-4">
                    <div>
                        <h3 class="font-display font-bold text-sm text-primary-ink dark:text-white">Profil Personal & Informasi Gaya Hidup</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                            Data ini disinkronkan ke <strong class="text-slate-700 dark:text-slate-300">Section 18 (Profil Personal Pelengkap)</strong> untuk memberikan konteks personal yang humanis bagi pimpinan.
                        </p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-400 mb-1">Golongan Darah</label>
                            <select 
                                wire:model="bloodType"
                                class="w-full text-xs px-3 py-2 rounded-md border border-warm-border dark:border-[#25211e] bg-white dark:bg-[#171412] text-primary-ink dark:text-white focus:outline-none focus:ring-1 focus:ring-accent-amber"
                            >
                                <option value="A+">A+</option>
                                <option value="A-">A-</option>
                                <option value="B+">B+</option>
                                <option value="B-">B-</option>
                                <option value="AB+">AB+</option>
                                <option value="AB-">AB-</option>
                                <option value="O+">O+</option>
                                <option value="O-">O-</option>
                                <option value="-">- (Belum Diketahui)</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-400 mb-1">Hobi & Kegemaran</label>
                            <input 
                                type="text" 
                                wire:model="hobbies"
                                class="w-full text-xs px-3 py-2 rounded-md border border-warm-border dark:border-[#25211e] bg-white dark:bg-[#171412] text-primary-ink dark:text-white focus:outline-none focus:ring-1 focus:ring-accent-amber"
                                placeholder="Membaca, Riset Kebijakan, Musik"
                            />
                        </div>

                        <div>
                            <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-400 mb-1">Olahraga Favorit</label>
                            <input 
                                type="text" 
                                wire:model="sports"
                                class="w-full text-xs px-3 py-2 rounded-md border border-warm-border dark:border-[#25211e] bg-white dark:bg-[#171412] text-primary-ink dark:text-white focus:outline-none focus:ring-1 focus:ring-accent-amber"
                                placeholder="Jogging, Bulu Tangkis, Bersepeda"
                            />
                        </div>
                    </div>

                    <div class="space-y-3 pt-2">
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-400 mb-1">Catatan Medis & Kebugaran Fisik</label>
                            <textarea 
                                rows="2"
                                wire:model="medicalNotes"
                                class="w-full text-xs px-3 py-2 rounded-md border border-warm-border dark:border-[#25211e] bg-white dark:bg-[#171412] text-primary-ink dark:text-white focus:outline-none focus:ring-1 focus:ring-accent-amber"
                                placeholder="Kondisi kesehatan umum prima, siap ditugaskan untuk mobilitas tinggi..."
                            ></textarea>
                        </div>

                        <div>
                            <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-400 mb-1">Catatan Budaya & Karakteristik Sosial</label>
                            <textarea 
                                rows="2"
                                wire:model="culturalNotes"
                                class="w-full text-xs px-3 py-2 rounded-md border border-warm-border dark:border-[#25211e] bg-white dark:bg-[#171412] text-primary-ink dark:text-white focus:outline-none focus:ring-1 focus:ring-accent-amber"
                                placeholder="Menjunjung tinggi nilai kejujuran, integritas, dan budaya gotong royong..."
                            ></textarea>
                        </div>

                        <div>
                            <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-400 mb-1">Moto Hidup / Nilai Utama (Core Values)</label>
                            <input 
                                type="text" 
                                wire:model="mottoOrValues"
                                class="w-full text-xs px-3 py-2 rounded-md border border-warm-border dark:border-[#25211e] bg-white dark:bg-[#171412] text-primary-ink dark:text-white focus:outline-none focus:ring-1 focus:ring-accent-amber"
                                placeholder="Integritas dalam Bekerja, Keunggulan dalam Melayani"
                            />
                        </div>
                    </div>
                </div>
                @endif
            </div>

            <!-- Modal Footer (Sticky) -->
            <div class="px-6 py-4 bg-warm-ivory dark:bg-[#1f1b18] border-t border-warm-border dark:border-[#25211e] flex items-center justify-between shrink-0">
                <div class="text-[11px] text-slate-500">
                    Perubahan akan langsung memperbarui grafik & laporan secara otomatis.
                </div>
                <div class="flex items-center gap-2.5">
                    <button 
                        type="button" 
                        wire:click="closeEditor"
                        class="px-4 py-2 rounded-md border border-warm-border hover:bg-slate-100 dark:hover:bg-slate-800 text-xs font-semibold text-slate-700 dark:text-slate-300 transition-colors cursor-pointer"
                    >
                        Batal
                    </button>
                    <button 
                        type="button" 
                        wire:click="save"
                        wire:loading.attr="disabled"
                        class="px-4 py-2 rounded-md bg-[#171412] hover:bg-[#2c2724] text-white text-xs font-semibold transition-all shadow-sm flex items-center gap-2 cursor-pointer disabled:opacity-50"
                    >
                        <span wire:loading.remove wire:target="save">
                            <i class="fas fa-floppy-disk text-accent-amber mr-1"></i> Simpan & Terapkan
                        </span>
                        <span wire:loading wire:target="save">
                            <i class="fas fa-spinner fa-spin mr-1"></i> Menyimpan...
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
