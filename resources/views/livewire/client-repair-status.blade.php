<div class="min-h-screen bg-gray-50 dark:bg-gray-900 py-8 px-4 sm:px-6 lg:px-8">
    @if(!$searched || !$repair)
        <div class="max-w-md mx-auto w-full space-y-8 bg-white dark:bg-gray-800 p-8 rounded-2xl shadow-xl">
            <div class="text-center">
                <h2 class="mt-6 text-3xl font-extrabold text-gray-900 dark:text-white">
                    Cek Status Reparasi
                </h2>
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                    @if ($this->hasValidToken)
                        Masukkan nomor telepon Anda untuk melihat detail data reparasi.
                    @else
                        Masukkan kode reparasi dan nomor telepon Anda untuk melihat detail data reparasi.
                    @endif
                </p>
            </div>

            <form wire:submit="search" class="mt-8 space-y-6">
                {{ $this->form }}

                <div>
                    <button type="submit" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                        Cek Status
                    </button>
                </div>
            </form>

            @if($searched && !$repair)
                <div class="mt-8 border-t border-gray-200 dark:border-gray-700 pt-6">
                    <div class="bg-red-50 dark:bg-red-900/30 border-l-4 border-red-400 p-4 rounded-md">
                        <div class="flex">
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-red-800 dark:text-red-200">
                                    Data tidak ditemukan
                                </h3>
                                <div class="mt-2 text-sm text-red-700 dark:text-red-300">
                                    <p>Pastikan kode reparasi dan nomor telepon sudah benar.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    @else
        <!-- Header Toko -->
        <div class="max-w-6xl mx-auto bg-white dark:bg-gray-800 rounded-2xl shadow-sm p-6 mb-6 text-center border border-gray-100 dark:border-gray-700">
            <h2 class="text-2xl font-extrabold text-gray-900 dark:text-white uppercase tracking-tight">{{ $repair->branch?->name ?? 'SCHIENE TREN KOMPUTER' }}</h2>
            <p class="text-gray-500 dark:text-gray-400 text-sm mt-2 flex items-center justify-center gap-1">
                <x-heroicon-o-map-pin class="w-4 h-4 text-danger-500" /> {{ $repair->branch?->address ?? 'Pusat Layanan Servis' }}
            </p>
            
            <div class="flex flex-wrap justify-center gap-3 mt-4">
                <a href="https://api.whatsapp.com/send?phone={{ $repair->branch?->whatsapp ?? '6283831389500' }}" target="_blank" class="inline-flex items-center gap-2 px-4 py-2 bg-success-500 hover:bg-success-600 text-white text-sm font-medium rounded-lg transition-colors">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51a12.8 12.8 0 0 0-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413Z"/></svg>
                    Hubungi Admin
                </a>
                <button wire:click="resetSearch" class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 text-sm font-medium rounded-lg transition-colors">
                    <x-heroicon-o-arrow-left class="w-4 h-4" /> Cari Lainnya
                </button>
            </div>
        </div>

        <div class="max-w-6xl mx-auto flex justify-between items-center mb-4 px-2">
            <h4 class="text-xl font-bold text-gray-900 dark:text-white">Tracking Progres Servis</h4>
            <span class="bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 text-sm px-3 py-1.5 rounded-full font-semibold">ID Nota: <b>{{ $repair->code }}</b></span>
        </div>

        <!-- Render Infolist -->
        <div class="max-w-6xl mx-auto">
            {{ $this->infolist }}
        </div>
        
        <!-- Render Filament Modals (for Actions) -->
        <x-filament-actions::modals />

        <div class="max-w-6xl mx-auto mt-6">
            <div class="bg-white dark:bg-gray-800 rounded-xl p-4 text-sm text-gray-500 dark:text-gray-400 border border-gray-100 dark:border-gray-700 shadow-sm">
                <div class="font-bold text-danger-500 mb-2 text-xs flex items-center gap-1 uppercase tracking-wider">
                    <x-heroicon-o-information-circle class="w-4 h-4" /> PERATURAN GARANSI & KETENTUAN !!!
                </div>
                <p>*S&K Berlaku Garansi Batal Jika Nota Hilang & Segel Rusak - No Fix No Fee</p>
                <p class="font-bold text-gray-800 dark:text-gray-200 mt-1">Dengan melakukan pengecekan status di sini, berarti Anda menyetujui dan sepenuhnya percaya kepada pihak toko.</p>
            </div>
        </div>
    @endif
</div>
