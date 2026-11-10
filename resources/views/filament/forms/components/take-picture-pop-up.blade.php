


<x-dynamic-component
:component="$getFieldWrapperView()"
:field="$field"
>
    <div
        id="camera-field"
        x-data="cameraField({

            state: $wire.$entangle(@js($getStatePath())),
            config: @js($config),
            id: @js($field->getId()),
            isDisabled: @js($field->isDisabled()),

        })"

         {{
            $getExtraAttributeBag()->class([
                'p-4','w-full','border', 'rounded-lg',
                'dark:border-neutral-700', 'dark:bg-neutral-900',
                'text-neutral-900', 'dark:text-neutral-200'
            ])
        }}
        {{-- {{ $attributes }} --}}
    >
        <x-filament::modal
            x-on:modal-closed.window="stopCamera()"
            width="3xl"
        >
            {{-- Modal Trigger --}}
            <x-slot name="trigger">
                @if ($field->isDisabled())
                    <div class="cursor-not-allowed opacity-50 p-4 w-full text-center" @click.stop="">
                @else
                    <div class="cursor-pointer p-4 w-full text-center"
                        @click="
                            showingPreview = false;
                            !webcamActive && !isDisabled && startCamera()
                        "
                        :class="{
                            'cursor-not-allowed opacity-50': isDisabled,
                            'cursor-pointer': !isDisabled,
                        }"
                    >
                @endif

                    {{ $field->getConfig('placeholder') }}

                </div>
            </x-slot>

            {{-- Modal header content --}}
            <x-slot name="heading">
                <div class="flex items-center justify-between">
                    {{ $field->getConfig('header') }}

                    <!-- Flip camera && isMobile -->
                    <div x-show="webcamActive && !webcamError && availableCameras.length > 1 && !showingPreview"
                        class="">
                        <button type="button" @click="flipCamera()"
                            class="w-10 h-10 rounded-full bg-gray-900/70 text-white flex items-center justify-center hover:bg-gray-900/90 transition-colors"
                            title="Switch Camera">
                            <x-filament::icon icon="heroicon-o-arrow-path" class="h-5 w-5" />
                        </button>
                    </div>
                </div>
            </x-slot>



            <!-- Mirror toggle -->
            <div x-show="webcamActive && !webcamError && !showingPreview"
                class="absolute top-4 right-16">
                <button type="button" @click="toggleMirror()"
                    class="w-10 h-10 rounded-full bg-gray-900/70 text-white flex items-center justify-center hover:bg-gray-900/90 transition-colors"
                    :class="{ 'ring-2 ring-primary-500': mirroredView }"
                    :title="mirroredView
                        ? 'Disable Miror'
                        : 'Enable Miror'">
                    <x-filament::icon icon="heroicon-o-arrows-right-left" class="h-5 w-5" />
                </button>
            </div>



            <!-- Camera type indicator (front/back) -->
            <div x-show="webcamActive && !webcamError && !showingPreview"
                class="absolute bottom-4 left-4">
                <span class="px-2 py-1 rounded-full bg-gray-900/70 text-white text-xs">
                    <span
                        x-text="isBackCamera
                            ? 'Back Camera'
                            : 'Front Camera'"></span>
                </span>
            </div>

            {{-- Modal content --}}
            <!-- Video preview (camera active) -->
            {{-- <div x-show="webcamActive && !webcamError"
                class="aspect-video flex items-center justify-center ">
                <video  :id="'video-' + id"  autoplay playsinline
                    :style="mirroredView ? 'transform: scaleX(-1);' : ''"
                    class="max-w-full max-h-[80vh] object-contain rounded border-radius-xl"></video>
            </div> --}}
            <video :id="'video-' + id" autoplay playsinline
                 :style="mirroredView ? 'transform: scaleX(-1);' : ''"  class="w-full rounded"
            ></video>

                            <!-- Preview in modal after capture - (Single Mode) -->
            {{-- <template x-if="showingPreview && photoData && !webcamActive">
                <div class="aspect-video flex items-center justify-center bg-gray-900">
                    <img :src="getImageUrl(photoData)" class="max-w-full max-h-[60vh] object-contain"
                        alt="Captured preview">
                </div>
            </template> --}}

            {{-- MARK: Camera dropdown selector (desktop only) --}}
            <div x-show="{{ $getShowCameraSelector() ? 'true' : 'false' }}&& !isMobile && !showingPreview && webcamActive" class="mb-4">
                <label class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">
                    Select Camera
                </label>

                <select x-model="selectedCameraId" @change="changeCamera($event.target.value)"
                    :disabled="availableCameras.length <= 1"
                    class="
                        block w-full rounded-lg border-none
                        bg-white text-gray-950
                        py-2 pe-10 ps-3 text-sm
                        shadow-sm ring-1 ring-inset ring-gray-950/10
                        focus:ring-2 focus:ring-inset focus:ring-primary-600/40
                        disabled:opacity-60 disabled:cursor-not-allowed
                        dark:bg-white/5 dark:text-white dark:ring-white/10
                        dark:focus:ring-primary-400/40
                    ">
                    <template x-for="(camera, index) in availableCameras" :key="camera.deviceId">
                        <option :value="camera.deviceId"
                            class="bg-white text-gray-950 dark:bg-gray-900 dark:text-white"
                            x-text="camera.label ? camera.label : `Camera ${index + 1}`"></option>
                    </template>
                </select>

                <p x-show="availableCameras.length <= 1"
                    class="mt-2 text-xs text-gray-500 dark:text-gray-300">
                    Only one camera detected.
                </p>
            </div>

            {{-- <template x-if="image">
                <img :src="image" class="w-40 rounded">
            </template> --}}
            {{-- Modal footer content --}}
            <x-slot name="footer">
                <div class="flex justify-center">
                    <button
                        @click="capture()"
                        class="bg-primary-400 hover:bg-primary-300 dark:bg-primary-600 dark:hover:bg-primary-500 text-primary-900 hover:text-primary-800 dark:text-primary-950 dark:hover:text-primary-950 font-medium px-4 py-2 rounded"
                    >
                        {{ $field->getConfig('capture') }}
                    </button>
                </div>
            </x-slot>

        </x-filament::modal>

        <template x-if="photoData ">
            <div class="relative w-full rounded-lg bg-white dark:bg-gray-900 ring-1 ring-gray-950/5 dark:ring-white/10 shadow-sm overflow-hidden"
                @mouseenter="isHovering = true" @mouseleave="isHovering = false">

                <div class="flex flex-col items-center gap-3 p-4 text-center">

                    {{-- Thumbnail (Ukurannya diperbesar sedikit agar proporsional secara vertikal) --}}
                    <div class="relative flex-shrink-0 w-60 h-60 rounded-lg overflow-hidden bg-gray-100 dark:bg-gray-800 ring-1 ring-gray-950/5 dark:ring-white/10">
                        <img :src="photoData ? getImageUrl(photoData) : ''" class="w-full h-full object-cover"
                            alt="Captured photo">
                    </div>

                    {{-- Info --}}
                    <div class="w-full min-w-0">
                        <p class="text-sm font-medium text-gray-700 dark:text-gray-200 truncate">
                            Captured photo
                        </p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                            <span x-text="isBase64Image() ? 'New photo' : 'Saved photo'"></span>
                        </p>
                    </div>

                    {{-- Action buttons (Tombol aksi ditaruh di paling bawah secara horizontal yang rapi) --}}
                    <div class="flex items-center gap-2 mt-1">
                        <button type="button" @click.stop="handleSinglePreviewClick()"
                            class="flex items-center justify-center w-9 h-9 rounded-lg text-gray-400 hover:text-gray-500 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors ring-1 ring-gray-950/5 dark:ring-white/5"
                            title="View full photo">
                            <x-filament::icon icon="heroicon-m-eye" class="h-5 w-5" />
                        </button>

                        <button x-show="!isDisabled" type="button" @click.stop="clearPhoto()"
                            class="flex items-center justify-center w-9 h-9 rounded-lg text-gray-400 hover:text-danger-500 hover:bg-danger-50 dark:hover:bg-danger-500/10 transition-colors ring-1 ring-gray-950/5 dark:ring-white/5"
                            title="Remove photo">
                            <x-filament::icon icon="heroicon-m-trash" class="h-5 w-5" />
                        </button>
                    </div>

                </div>
            </div>
        </template>

    {{-- Interact with the `state` property in Alpine.js --}}
    </div>
</x-dynamic-component>


{{-- @vite('resources/css/component.css') --}}
{{-- <div class="">
    TEST TAILWIND
</div> --}}



{{-- <div
>

    <canvas x-ref="canvas" class="hidden"></canvas>

    <button type="button" @click="startCamera()">
        Start Camera
    </button>

    <button type="button" @click="capture()">
        Capture
    </button>

    <template x-if="image">
        <img :src="image" class="w-40 rounded">
    </template>

    <input type="hidden" {{ $attributes }} x-model="image">
</div> --}}

<script>

    document.addEventListener('alpine:init', () => {
        Alpine.data('cameraField', ({ state, config, id, isDisabled }) => ({
            photoData: state,
            config,
            id,
            aspectRatio: config.aspectRatio,

            selectedCameraId: null,
            showingPreview: false,
            webcamActive: false,
            webcamError: null,
            availableCameras: [],
            mirroredView: false,
            isBackCamera: false,
            isDisabled: isDisabled,
            isMobile: /iPhone|iPad|iPod|Android/i.test(navigator.userAgent),

            stream: null,
            loading: false,


            detectCameraType(camera) {
                if (!camera) return;

                const label = (camera.label || '').toLowerCase();

                this.isBackCamera = label.includes('back') ||
                    label.includes('rear') ||
                    label.includes('environment') ||
                    label.includes('0, facing back');

                this.mirroredView = !this.isBackCamera;
            },

            async startCamera() {
                // console.log(this.webcamError);
                if (this.isDisabled) return;
                if (this.webcamActive) return;

                this.stopCamera();

                await new Promise(r => setTimeout(r, 120));


                this.webcamActive = true;
                this.webcamError = null;

                await this.$nextTick();

                let aspectWidth = 16;
                let aspectHeight = 9;

                if (this.aspectRatio) {
                    const parts = this.aspectRatio.split(':');
                    if (parts.length === 2) {
                        aspectWidth = parseInt(parts[0]);
                        aspectHeight = parseInt(parts[1]);
                    }
                }

                const constraints = {
                    video: {
                        facingMode: this.isMobile ? this.currentFacingMode : 'user',
                        width: { ideal: aspectWidth * 120 },
                        height: { ideal: aspectHeight * 120 },
                    },
                    audio: false
                };

                if (this.selectedCameraId) {
                    constraints.video.deviceId = { exact: this.selectedCameraId };
                }

                try {
                    this.loading = true;

                    const video = document.getElementById('video-' + this.id);;

                    if (!video) {
                        console.error('Video element not found');
                        return;
                    }

                    this.stream = await navigator.mediaDevices.getUserMedia(constraints);

                    video.srcObject = this.stream;


                    if ({{ $getShowCameraSelector() ? 'true' : 'false' }}) {
                        await this.getCameras();
                    }

                    const videoTrack = this.stream.getVideoTracks()[0];
                    if (videoTrack) {
                        const settings = videoTrack.getSettings();
                        if (settings.facingMode) {
                            this.isBackCamera = settings.facingMode === 'environment';
                            this.mirroredView = !this.isBackCamera;
                        }
                    }


                } finally {
                    this.loading = false;
                }
            },

            stopCamera() {
                // console.log('Stop Camera');
                this.webcamActive = false;
                if (!this.stream) return;

                this.stream.getTracks().forEach(track => track.stop());

                this.stream = null;

            },

            async getCameras() {
                try {
                    const devices = await navigator.mediaDevices.enumerateDevices();
                    this.availableCameras = devices.filter(device => device.kind === 'videoinput');

                    if (this.availableCameras.length > 0 && !this.selectedCameraId) {
                        this.selectedCameraId = this.availableCameras[0].deviceId;
                        this.detectCameraType(this.availableCameras[0]);
                    }

                    return this.availableCameras;
                } catch (error) {
                    console.error('Error getting camera devices:', error);
                    this.webcamError = 'Unable to detect available cameras';
                    return [];
                }
            },


            async changeCamera(cameraId) {
                // console.log('Change Camera');
                this.selectedCameraId = cameraId;

                const camera = this.availableCameras.find(c => c.deviceId === cameraId);
                this.detectCameraType(camera);

                if (this.webcamActive) {
                    this.stopCamera();
                    await this.$nextTick();
                    this.startCamera();
                }
            },


            async flipCamera() {
                // console.log('Flip Camera');
                if (this.availableCameras.length < 2) return;

                const currentIndex = this.availableCameras.findIndex(
                    cam => cam.deviceId === this.selectedCameraId
                );

                const nextIndex = (currentIndex + 1) % this.availableCameras.length;
                const nextCamera = this.availableCameras[nextIndex];
                this.selectedCameraId = nextCamera.deviceId;

                this.detectCameraType(nextCamera);

                const constraints = {
                    video: {
                        deviceId: { exact: nextCamera.deviceId },
                        width: { ideal: 1280 },
                        height: { ideal: 720 }
                    },
                    audio: false
                };

                if (this.cameraStream) {
                    this.cameraStream.getTracks().forEach(track => track.stop());
                    this.cameraStream = null;
                }

                try {
                    const stream = await navigator.mediaDevices.getUserMedia(constraints);
                    this.cameraStream = stream;

                    if (document.getElementById('video-' + this.id)) {
                        document.getElementById('video-' + this.id).srcObject = stream;
                    }

                    const videoTrack = stream.getVideoTracks()[0];
                    if (videoTrack) {
                        const settings = videoTrack.getSettings();
                        if (settings.facingMode) {
                            this.isBackCamera = settings.facingMode === 'environment';
                            this.mirroredView = !this.isBackCamera;
                        }
                    }
                } catch (error) {
                    console.error('Error flipping camera:', error);
                    this.handleWebcamError(error);
                    this.selectedCameraId = this.availableCameras[currentIndex].deviceId;
                    this.detectCameraType(this.availableCameras[currentIndex]);
                }
            },

            toggleMirror() {
                this.mirroredView = !this.mirroredView;
            },

            getImageUrl(path) {
                if (!path) return null;

                if (typeof path !== 'string') return null;

                if (path.startsWith('data:image/')) return path;
                if (path.startsWith('http://') || path.startsWith('https://')) return path;

                const cleanPath = path.replace(/^\/+/, '');
                return '/storage/' + cleanPath;
            },


            isBase64Image(value = null) {
                const v = value ?? this.photoData;
                return (typeof v === 'string') && v.startsWith('data:image/');
            },



            captureToDataUrl() {
                const video = document.getElementById('video-' + this.id);
                if (!video || !video.videoWidth || !video.videoHeight) return null;

                const canvas = document.createElement('canvas');

                let targetWidth = video.videoWidth;
                let targetHeight = video.videoHeight;

                if (this.maxWidth || this.maxHeight) {
                    const aspectRatio = video.videoWidth / video.videoHeight;

                    if (this.maxWidth && this.maxHeight) {
                        if (targetWidth > this.maxWidth) {
                            targetWidth = this.maxWidth;
                            targetHeight = Math.round(targetWidth / aspectRatio);
                        }
                        if (targetHeight > this.maxHeight) {
                            targetHeight = this.maxHeight;
                            targetWidth = Math.round(targetHeight * aspectRatio);
                        }
                    } else if (this.maxWidth && targetWidth > this.maxWidth) {
                        targetWidth = this.maxWidth;
                        targetHeight = Math.round(targetWidth / aspectRatio);
                    } else if (this.maxHeight && targetHeight > this.maxHeight) {
                        targetHeight = this.maxHeight;
                        targetWidth = Math.round(targetHeight * aspectRatio);
                    }
                }

                canvas.width = targetWidth;
                canvas.height = targetHeight;

                const context = canvas.getContext('2d');

                if (this.mirroredView) {
                    context.translate(canvas.width, 0);
                    context.scale(-1, 1);
                }

                context.drawImage(video, 0, 0, targetWidth, targetHeight);

                const quality = this.imageQuality / 100;
                return canvas.toDataURL('image/jpeg', quality);
            },


            capture() {

                const imageData = this.captureToDataUrl();
                if (!imageData) return;

                this.photoData = imageData;
                this.stopCamera();
                this.showingPreview = true;

                this.$dispatch('close-modal', {
                    id: 'camera-modal'
                });
            },




            handleSinglePreviewClick() {
                if (!this.photoData) return;
                if (this.isMultiple && typeof this.photoData === 'object') return;
                this.handleMultipleModePreviewClick(this.photoData);
            },

            handleMultipleModePreviewClick(p) {
                if (!p) return;
                if (typeof p === 'string' && p.startsWith('data:image/')) {
                    const byteString = atob(p.split(',')[1]);
                    const mimeString = p.split(',')[0].split(':')[1].split(';')[0];

                    const ab = new ArrayBuffer(byteString.length);
                    const ia = new Uint8Array(ab);
                    for (let i = 0; i < byteString.length; i++) ia[i] = byteString.charCodeAt(i);

                    const blob = new Blob([ab], { type: mimeString });
                    const url = URL.createObjectURL(blob);

                    window.open(url, '_blank')?.focus();
                    return;
                }
                window.open(this.getImageUrl(p), '_blank')?.focus();
            },

            clearPhoto() {

                this.photoData = null;
                this.photoSelected = false;
                this.showingPreview = false;
            },

        }));

    });

</script>
