<x-app-layout>

    <div class="max-w-7xl mx-auto px-4 py-6">

        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900">
                Dashboard RTLH Kota Cirebon
            </h1>

            <p class="text-sm text-gray-500 mt-1">
                Ringkasan data Rumah Tidak Layak Huni.
            </p>
        </div>

        {{-- Statistik Utama --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-8">

            <div class="bg-white rounded-xl border p-5">
                <p class="text-sm text-gray-500">Total Rumah</p>
                <p class="text-3xl font-bold text-gray-900 mt-2">
                    {{ number_format($totalHouses) }}
                </p>
            </div>

            <div class="bg-white rounded-xl border p-5">
                <p class="text-sm text-gray-500">Draft</p>
                <p class="text-3xl font-bold text-gray-900 mt-2">
                    {{ number_format($draftHouses) }}
                </p>
            </div>

            <div class="bg-white rounded-xl border p-5">
                <p class="text-sm text-gray-500">Selesai</p>
                <p class="text-3xl font-bold text-gray-900 mt-2">
                    {{ number_format($completedHouses) }}
                </p>
            </div>

            <div class="bg-white rounded-xl border p-5">
                <p class="text-sm text-gray-500">Terverifikasi</p>
                <p class="text-3xl font-bold text-gray-900 mt-2">
                    {{ number_format($verifiedHouses) }}
                </p>
            </div>

            <div class="bg-white rounded-xl border p-5">
                <p class="text-sm text-gray-500">Data Publik</p>
                <p class="text-3xl font-bold text-gray-900 mt-2">
                    {{ number_format($publicHouses) }}
                </p>
            </div>

            <div class="bg-white rounded-xl border p-5">
                <p class="text-sm text-gray-500">Total Assessment</p>
                <p class="text-3xl font-bold text-gray-900 mt-2">
                    {{ number_format($totalAssessments) }}
                </p>
            </div>

        </div>

        {{-- Rekap --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            {{-- Status Rumah --}}
            <div class="bg-white rounded-xl border p-5">
                <h2 class="font-semibold text-gray-900 mb-4">
                    Rekap Status Rumah
                </h2>

                @forelse($housesByStatus as $row)

                    <div class="flex items-center justify-between py-3 border-b last:border-b-0">

                        <span class="text-sm text-gray-600">
                            {{ ucfirst($row->status ?? 'Tidak diketahui') }}
                        </span>

                        <span class="font-semibold text-gray-900">
                            {{ number_format($row->total) }}
                        </span>

                    </div>

                @empty

                    <p class="text-sm text-gray-500">
                        Belum ada data.
                    </p>

                @endforelse
            </div>

            {{-- Tahun Survei --}}
            <div class="bg-white rounded-xl border p-5">
                <h2 class="font-semibold text-gray-900 mb-4">
                    Rekap Tahun Survei
                </h2>

                @forelse($housesByYear as $row)

                    <div class="flex items-center justify-between py-3 border-b last:border-b-0">

                        <span class="text-sm text-gray-600">
                            Tahun {{ $row->survey_year }}
                        </span>

                        <span class="font-semibold text-gray-900">
                            {{ number_format($row->total) }}
                        </span>

                    </div>

                @empty

                    <p class="text-sm text-gray-500">
                        Belum ada data.
                    </p>

                @endforelse
            </div>

        </div>

    </div>

</x-app-layout>