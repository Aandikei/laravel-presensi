<x-layouts.admin>
    <x-slot:title>Preview Naik Kelas</x-slot:title>

    <div class="container px-6 mx-auto">
        <div class="my-6">
            <h2 class="text-2xl font-semibold text-gray-700 dark:text-gray-200">Preview Naik Kelas</h2>
            <a href="{{ route('admin.naik-kelas.index') }}"
                class="text-sm text-purple-600 hover:underline dark:text-purple-400">← Kembali</a>
        </div>

        {{-- Info --}}
        <div class="mb-4 p-4 bg-white dark:bg-gray-800 rounded-lg shadow-xs dark:shadow-none dark:border dark:border-gray-700 flex items-center gap-6 text-sm">
            <div>
                <p class="text-gray-500 dark:text-gray-400">Dari</p>
                <p class="font-semibold text-gray-700 dark:text-gray-200">
                    {{ $tahunAsal->nama_tahun }} - {{ $tahunAsal->semester }}
                </p>
            </div>
            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
            </svg>
            <div>
                <p class="text-gray-500 dark:text-gray-400">Ke</p>
                <p class="font-semibold text-gray-700 dark:text-gray-200">
                    {{ $tahunTujuan->nama_tahun }} - {{ $tahunTujuan->semester }}
                </p>
            </div>
        </div>

        @if($kelasAsal->isEmpty())
            <div class="p-8 text-center bg-white dark:bg-gray-800 rounded-lg shadow-xs dark:shadow-none dark:border dark:border-gray-700 text-gray-500">
                Tidak ada kelas di tahun ajaran asal.
            </div>
        @else

        <form method="POST" action="{{ route('admin.naik-kelas.proses') }}">
            @csrf
            <input type="hidden" name="tahun_asal_id" value="{{ $tahunAsal->id_tahun }}">
            <input type="hidden" name="tahun_tujuan_id" value="{{ $tahunTujuan->id_tahun }}">

            @foreach($kelasAsal as $kelas)
                @php
                    $isLulus           = $kelas->tingkat >= $tingkatMaks;
                    $tingkatTujuan     = $kelas->tingkat + 1;
                    $kelasTujuanNaik   = $semuaKelas[$tingkatTujuan] ?? collect();
                    $kelasTujuanTetap  = $semuaKelas[$kelas->tingkat] ?? collect();
                @endphp

                <div class="mb-6 bg-white dark:bg-gray-800 rounded-lg shadow-xs dark:shadow-none dark:border dark:border-gray-700 overflow-hidden">
                    {{-- Header Kelas --}}
                    <div class="px-5 py-3 border-b border-gray-200 dark:border-gray-700 flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <h3 class="font-semibold text-gray-700 dark:text-gray-200">
                                {{ $kelas->nama_kelas }}
                                <span class="text-sm font-normal text-gray-500">(Tingkat {{ $kelas->tingkat }})</span>
                            </h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                {{ $kelas->registrasiAkademik->count() }} siswa
                                @if($isLulus)
                                    • <span class="text-orange-600 font-medium">Tingkat tertinggi → default Lulus</span>
                                @endif
                            </p>
                        </div>

                        {{-- Bulk Actions --}}
                        <div class="flex flex-wrap items-center gap-2">
                            @if(!$isLulus)
                                {{-- Bulk pilih kelas naik --}}
                                @if($kelasTujuanNaik->isNotEmpty())
                                    <select onchange="bulkSetKelas('{{ $kelas->id_kelas }}', 'naik', this.value)"
                                        class="text-xs dark:bg-gray-700 dark:text-gray-300 py-1">
                                        <option value="">Bulk Naik → Pilih Kelas</option>
                                        @foreach($kelasTujuanNaik as $kt)
                                            <option value="{{ $kt->id_kelas }}">{{ $kt->nama_kelas }}</option>
                                        @endforeach
                                    </select>
                                @endif

                                {{-- Bulk pilih kelas tidak naik --}}
                                @if($kelasTujuanTetap->isNotEmpty())
                                    <select onchange="bulkSetKelas('{{ $kelas->id_kelas }}', 'tidak_naik', this.value)"
                                        class="text-xs dark:bg-gray-700 dark:text-gray-300 py-1">
                                        <option value="">Bulk Tidak Naik → Pilih Kelas</option>
                                        @foreach($kelasTujuanTetap as $kt)
                                            <option value="{{ $kt->id_kelas }}">{{ $kt->nama_kelas }}</option>
                                        @endforeach
                                    </select>
                                @endif
                            @endif

                            <button type="button"
                                onclick="bulkSetAction('{{ $kelas->id_kelas }}', '{{ $isLulus ? 'lulus' : 'naik' }}')"
                                class="px-3 py-1 text-xs font-medium text-white rounded
                                {{ $isLulus ? 'bg-orange-500 hover:bg-orange-600' : 'bg-green-600 hover:bg-green-700' }}">
                                {{ $isLulus ? 'Set Semua Lulus' : 'Set Semua Naik' }}
                            </button>

                            @if(!$isLulus)
                                <button type="button"
                                    onclick="bulkSetAction('{{ $kelas->id_kelas }}', 'tidak_naik')"
                                    class="px-3 py-1 text-xs font-medium text-white bg-red-600 rounded hover:bg-red-700">
                                    Set Semua Tidak Naik
                                </button>
                                <button type="button"
                                    onclick="bulkSetAction('{{ $kelas->id_kelas }}', 'lulus')"
                                    class="px-3 py-1 text-xs font-medium text-white bg-orange-500 rounded hover:bg-orange-600">
                                    Set Semua Lulus
                                </button>
                            @endif
                        </div>
                    </div>

                    {{-- Daftar Siswa --}}
                    <table class="w-full">
                        <thead>
                            <tr class="text-xs font-semibold tracking-wide text-left text-gray-500 uppercase border-b dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50">
                                <th class="px-4 py-3">No</th>
                                <th class="px-4 py-3">Nama Siswa</th>
                                <th class="px-4 py-3">NISN</th>
                                <th class="px-4 py-3">Status</th>
                                <th class="px-4 py-3">Kelas Tujuan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y dark:divide-gray-700">
                            @forelse($kelas->registrasiAkademik as $i => $reg)
                                @php
                                    $sudah = in_array($reg->siswa_id, $sudahTerdaftar);
                                @endphp
                                <tr class="{{ $sudah ? 'opacity-50' : 'hover:bg-gray-50 dark:hover:bg-gray-700/70' }}">
                                    <td class="px-4 py-3 text-sm text-gray-500">{{ $i + 1 }}</td>
                                    <td class="px-4 py-3 font-medium text-gray-700 dark:text-gray-200">
                                        {{ $reg->siswa->nama_siswa }}
                                        @if($sudah)
                                            <span class="ml-1 px-1.5 py-0.5 text-xs bg-blue-100 text-blue-700 rounded">
                                                Sudah terdaftar
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-500">{{ $reg->siswa->nisn }}</td>

                                    {{-- Kolom Status --}}
                                    <td class="px-4 py-3">
                                        @if($sudah)
                                            <span class="text-xs text-gray-400">—</span>
                                        @else
                                            <select
                                                name="siswa[{{ $reg->siswa_id }}][action]"
                                                class="action-select action-select-{{ $kelas->id_kelas }} text-xs dark:bg-gray-700 dark:text-gray-300 py-1"
                                                data-siswa-id="{{ $reg->siswa_id }}"
                                                data-tingkat="{{ $kelas->tingkat }}"
                                                onchange="onActionChange(this)">
                                                @if($isLulus)
                                                    <option value="lulus" selected>🎓 Lulus</option>
                                                    <option value="naik">⬆️ Naik Kelas</option>
                                                    <option value="tidak_naik">⬇️ Tidak Naik</option>
                                                @else
                                                    <option value="naik">⬆️ Naik Kelas</option>
                                                    <option value="tidak_naik">⬇️ Tidak Naik</option>
                                                    <option value="lulus">🎓 Lulus</option>
                                                @endif
                                            </select>
                                        @endif
                                    </td>

                                    {{-- Kolom Kelas Tujuan --}}
                                    <td class="px-4 py-3" id="td-kelas-{{ $reg->siswa_id }}">
                                        @if($sudah)
                                            <span class="text-xs text-gray-400">—</span>
                                        @elseif($isLulus)
                                            <span class="text-xs text-orange-600">Alumni</span>
                                            <input type="hidden" name="siswa[{{ $reg->siswa_id }}][kelas_tujuan_id]" value="">
                                        @else
                                            {{-- Default: dropdown kelas naik --}}
                                            <select
                                                name="siswa[{{ $reg->siswa_id }}][kelas_tujuan_id]"
                                                class="kelas-select kelas-select-{{ $kelas->id_kelas }} text-xs dark:bg-gray-700 dark:text-gray-300 py-1"
                                                data-siswa-id="{{ $reg->siswa_id }}">
                                                <option value="">-- Pilih Kelas --</option>
                                                @foreach($kelasTujuanNaik as $kt)
                                                    <option value="{{ $kt->id_kelas }}">{{ $kt->nama_kelas }}</option>
                                                @endforeach
                                            </select>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-6 text-center text-gray-500">
                                        Tidak ada siswa di kelas ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            @endforeach

            {{-- Submit --}}
            <div class="sticky bottom-0 bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 p-4 flex items-center justify-between shadow-lg">
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Pastikan semua siswa sudah dipilih status dan kelas tujuannya.
                </p>
                <button type="submit"
                    onclick="return confirm('Yakin proses naik kelas? Aksi ini tidak bisa dibatalkan.')"
                    class="px-6 py-2.5 text-sm font-medium text-white bg-purple-600 rounded-lg hover:bg-purple-700">
                    Proses Naik Kelas
                </button>
            </div>
        </form>

        @endif
    </div>

    @push('scripts')
    <script>
        // Data kelas tujuan per tingkat dari server
        const kelasTujuanData = @json(
            $semuaKelas->map(fn($group) =>
                $group->map(fn($k) => [
                    'id_kelas'   => $k->id_kelas,
                    'nama_kelas' => $k->nama_kelas,
                ])->values()
            )->toArray()
        );

        // Saat action berubah per siswa
        function onActionChange(selectEl) {
            const siswaId = selectEl.dataset.siswaId;
            const tingkat = parseInt(selectEl.dataset.tingkat);
            const action  = selectEl.value;
            const td      = document.getElementById(`td-kelas-${siswaId}`);

            if (action === 'lulus') {
                td.innerHTML = `<span class="text-xs text-orange-600">Alumni</span>
                    <input type="hidden" name="siswa[${siswaId}][kelas_tujuan_id]" value="">`;
                return;
            }

            const tingkatTujuan = action === 'tidak_naik' ? tingkat : tingkat + 1;
            const opsi = kelasTujuanData[tingkatTujuan] || [];

            if (opsi.length === 0) {
                td.innerHTML = `<span class="text-xs text-red-500">Belum ada kelas di tingkat ${tingkatTujuan}</span>
                    <input type="hidden" name="siswa[${siswaId}][kelas_tujuan_id]" value="">`;
                return;
            }

            let html = `<select name="siswa[${siswaId}][kelas_tujuan_id]"
                class="text-xs dark:bg-gray-700 dark:text-gray-300 py-1">
                <option value="">-- Pilih Kelas --</option>`;
            opsi.forEach(k => {
                html += `<option value="${k.id_kelas}">${k.nama_kelas}</option>`;
            });
            html += `</select>`;
            td.innerHTML = html;
        }

        // Bulk set action untuk semua siswa di kelas
        function bulkSetAction(kelasId, action) {
            document.querySelectorAll(`.action-select-${kelasId}`).forEach(select => {
                select.value = action;
                select.dispatchEvent(new Event('change'));
            });
        }

        // Bulk set kelas tujuan untuk semua siswa di kelas
        function bulkSetKelas(kelasId, action, kelasTujuanId) {
            if (!kelasTujuanId) return;

            // Set semua action dulu
            document.querySelectorAll(`.action-select-${kelasId}`).forEach(select => {
                select.value = action;
                select.dispatchEvent(new Event('change'));
            });

            // Delay sedikit biar DOM update dulu
            setTimeout(() => {
                document.querySelectorAll(`[id^="td-kelas-"] select`).forEach(select => {
                    // Cek apakah select ini milik siswa di kelas ini
                    const actionSelect = document.querySelector(
                        `.action-select-${kelasId}[data-siswa-id="${select.name.match(/\[(\d+)\]/)[1]}"]`
                    );
                    if (actionSelect) {
                        select.value = kelasTujuanId;
                    }
                });
            }, 100);
        }
    </script>
    @endpush
</x-layouts.admin>