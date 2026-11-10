

<x-dynamic-component
:component="$getFieldWrapperView()"
:field="$field"
>
    <div
        id="camera-field"
        x-data="cameraField({
            statePath: '{{$getStatePath()}}',
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
        @if ($field->isDisabled())
            <div class="cursor-not-allowed opacity-50 p-4 w-full text-center" @click.stop="">
        @else
            <div class="cursor-pointer p-4 w-full text-center"
                 @click="
                    showingPreview = false;

                    if (webcamActive) {
                        stopCamera();
                    } else if (!isDisabled) {
                        startCamera();
                    }
                "
                :class="{
                    'cursor-not-allowed opacity-50': isDisabled,
                    'cursor-pointer': !isDisabled,
                }"
            >
        @endif
            <template x-if="!webcamActive">
                <span>📷</span>
            </template>

            <template x-if="webcamActive">
                <span>❌</span>
            </template>

            <span
                x-text="
                    webcamActive
                        ? 'Tutup Kamera'
                        : @js($field->getConfig('placeholder'))
                "
            ></span>

        </div>




        <template x-if="webcamActive">
            <div>
                <video
                    x-ref="video"
                    :id="'video-' + id"
                    autoplay
                    playsinline
                    :style="mirroredView ? 'transform: scaleX(-1)' : ''"
                    class="w-full rounded mb-5"
                ></video>

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

                <div class="flex justify-between">

                    <!-- Flip camera -->
                    <div class="flex items-center gap-2">
                        <div x-show="webcamActive && !webcamError && isMobile && availableCameras.length > 1 && !showingPreview"
                            class="">
                            <button type="button" @click="flipCamera()"
                                class="w-10 h-10 rounded-full bg-gray-900/70 text-white flex items-center justify-center hover:bg-gray-900/90 transition-colors"
                                title="Switch Camera">
                                <x-filament::icon icon="heroicon-o-arrow-path" class="h-5 w-5" />
                            </button>
                        </div>

                        <!-- Camera type indicator (front/back) -->
                        <div x-show="webcamActive && !webcamError && !showingPreview"
                            class="">
                            <span class="px-2 py-1 rounded-full bg-gray-900/70 text-white text-xs">
                                <span
                                    x-text="isBackCamera
                                        ? 'Back Camera'
                                        : 'Front Camera'"></span>
                            </span>
                        </div>

                    </div>

                    <div>
                        <button
                            @click="capture()"
                            class="bg-primary-400 hover:bg-primary-300 dark:bg-primary-600 dark:hover:bg-primary-500 text-primary-900 hover:text-primary-800 dark:text-primary-950 dark:hover:text-primary-950 font-medium px-4 py-2 rounded"
                        >
                            {{ $field->getConfig('capture') }}
                        </button>
                    </div>

                    <!-- Mirror toggle -->

                    <div>
                        <div x-show="webcamActive && !webcamError && !showingPreview"
                            class="">
                            <button type="button" @click="toggleMirror()"
                                class="w-10 h-10 rounded-full bg-gray-900/70 text-white flex items-center justify-center hover:bg-gray-900/90 transition-colors"
                                :class="{ 'ring-2 ring-primary-500': mirroredView }"
                                :title="mirroredView
                                    ? 'Disable Miror'
                                    : 'Enable Miror'">
                                <x-filament::icon icon="heroicon-o-arrows-right-left" class="h-5 w-5" />
                            </button>
                        </div>
                    </div>

                </div>
            </div>
        </template>







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
        {{-- <input type="hidden" {{ $applyStateBindingModifiers('wire:model') }}="{{ $getStatePath() }}" x-model="photoData"> --}}
    </div>
</x-dynamic-component>


<script>

    document.addEventListener('alpine:init', () => {
        Alpine.data('cameraField', ({statePath, config, id, isDisabled }) => ({
            statePath,
            config,
            id,
            aspectRatio: config.aspectRatio,

            photoData: null,
            selectedCameraId: null,
            showingPreview: false,
            webcamActive: false,
            webcamError: null,
            availableCameras: [],
            mirroredView: false,
            isBackCamera: false,
            isDisabled: isDisabled,
            isMobile: /iPhone|iPad|iPod|Android/i.test(navigator.userAgent),

            cameraStream: null,
            loading: false,


            // ... (state dan variabel awal tetap sama)

            // 1. Fungsi Pembantu Baru (Satu Pintu untuk Mengurus Stream Video)
            async applyVideoStream(constraints) {
                // Matikan stream lama jika ada (tanpa menyentuh UI webcamActive)
                if (this.cameraStream) {
                    this.cameraStream.getTracks().forEach(track => track.stop());
                    this.cameraStream = null;
                }

                try {
                    this.loading = true;
                    const stream = await navigator.mediaDevices.getUserMedia(constraints);
                    this.cameraStream = stream;

                    await this.$nextTick();

                    // Suntikkan stream ke DOM
                    if (this.$refs.video) {
                        this.$refs.video.srcObject = stream;
                    } else {
                        const videoElement = document.getElementById('video-' + this.id);
                        if (videoElement) {
                            videoElement.srcObject = stream;
                        }
                    }

                    // Deteksi tipe kamera dari track (Depan/Belakang)
                    const videoTrack = stream.getVideoTracks()[0];
                    if (videoTrack) {
                        const settings = videoTrack.getSettings();
                        if (settings.facingMode) {
                            this.isBackCamera = settings.facingMode === 'environment';
                            this.mirroredView = !this.isBackCamera;
                        }
                    }
                } catch (error) {
                    console.error('Error applying video stream:', error);
                    this.handleWebcamError(error);
                } finally {
                    this.loading = false;
                }
            },

            // 2. startCamera Menjadi Sangat Ringkas
            async startCamera() {
                // console.log(this.statePath);
                if (this.isDisabled) return;
                if (this.webcamActive) return;

                this.webcamActive = true;
                this.webcamError = null;

                let aspectWidth = 16, aspectHeight = 9;
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

                if (this.selectedCameraId) constraints.video.deviceId = { exact: this.selectedCameraId };

                // Panggil helper
                await this.applyVideoStream(constraints);

                // Load list kamera untuk dropdown jika diaktifkan
                if ({{ $getShowCameraSelector() ? 'true' : 'false' }}) {
                    await this.getCameras();
                }
            },

            // 3. changeCamera Menjadi Sangat Ringkas
            async changeCamera(cameraId) {
                this.selectedCameraId = cameraId;
                const camera = this.availableCameras.find(c => c.deviceId === cameraId);
                this.detectCameraType(camera);

                if (!this.webcamActive) return;

                let aspectWidth = 16, aspectHeight = 9;
                if (this.aspectRatio) {
                    const parts = this.aspectRatio.split(':');
                    if (parts.length === 2) {
                        aspectWidth = parseInt(parts[0]);
                        aspectHeight = parseInt(parts[1]);
                    }
                }

                const constraints = {
                    video: {
                        deviceId: { exact: this.selectedCameraId },
                        width: { ideal: aspectWidth * 120 },
                        height: { ideal: aspectHeight * 120 },
                    },
                    audio: false
                };

                // Panggil helper
                await this.applyVideoStream(constraints);
            },

            // 4. flipCamera Menjadi Sangat Ringkas
            async flipCamera() {
                if (this.availableCameras.length < 2) return;

                const currentIndex = this.availableCameras.findIndex(cam => cam.deviceId === this.selectedCameraId);
                const nextIndex = (currentIndex + 1) % this.availableCameras.length;
                const nextCamera = this.availableCameras[nextIndex];

                this.selectedCameraId = nextCamera.deviceId;
                this.detectCameraType(nextCamera);

                let aspectWidth = 16, aspectHeight = 9;
                if (this.aspectRatio) {
                    const parts = this.aspectRatio.split(':');
                    if (parts.length === 2) {
                        aspectWidth = parseInt(parts[0]);
                        aspectHeight = parseInt(parts[1]);
                    }
                }

                const constraints = {
                    video: {
                        deviceId: { exact: nextCamera.deviceId },
                        width: { ideal: aspectWidth * 120 },
                        height: { ideal: aspectHeight * 120 },
                    },
                    audio: false
                };

                // Panggil helper
                await this.applyVideoStream(constraints);
            },

            stopCamera() {
                // console.log('Stop Camera');
                this.webcamActive = false;
                if (this.cameraStream) {
                    this.cameraStream.getTracks().forEach(track => track.stop());
                    this.cameraStream = null;
                }

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


            detectCameraType(camera) {

                // console.log(this.availableCameras);


                if (!camera) return;

                const label = (camera.label || '').toLowerCase();

                this.isBackCamera = label.includes('back') ||
                    label.includes('rear') ||
                    label.includes('environment') ||
                    label.includes('0, facing back');

                this.mirroredView = !this.isBackCamera;
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
                
                this.$wire.$set(this.statePath, imageData);
                this.$wire.call('submitAttendance');
                
                // this.$wire.call(
                //     'submit'
                // )
                // this.$dispatch('close-modal', {
                //     id: 'camera-modal'
                // });
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

