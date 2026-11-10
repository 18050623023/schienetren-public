<div>
    @php
        $photos = collect();
        foreach($repair->repairDevices as $device) {
            if($device->repairPhotos) {
                foreach($device->repairPhotos as $photo) {
                    $photos->push($photo);
                }
            }
        }
    @endphp
    
    @if($photos->isEmpty())
        <div class="text-center py-8">
            <x-heroicon-o-camera class="w-12 h-12 mx-auto text-gray-300 dark:text-gray-600 mb-3" />
            <p class="text-gray-500 dark:text-gray-400 font-medium">Belum ada dokumen foto untuk perangkat ini.</p>
        </div>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
            @foreach($photos as $photo)
                <div class="relative group rounded-xl overflow-hidden border border-gray-200 dark:border-gray-700 aspect-square bg-gray-100 dark:bg-gray-800 flex items-center justify-center">
                    @if($photo->photo_path)
                        <img src="{{ Storage::url($photo->photo_path) }}" alt="Foto Fisik" class="object-cover w-full h-full" />
                    @else
                        <x-heroicon-o-photo class="w-8 h-8 text-gray-400" />
                    @endif
                </div>
            @endforeach
        </div>
    @endif
</div>
