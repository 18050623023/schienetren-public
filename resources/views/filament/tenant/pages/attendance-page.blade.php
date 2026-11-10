<x-filament-panels::page>
    {{-- Page content --}}

    {{ $this->form }}
    {{ $this->table }}
      {{-- <script>const modal = document.querySelector('[x-show="modalOpen"]');


                console.log(getComputedStyle(modal).position);
                console.log(getComputedStyle(modal).zIndex);</script> --}}




    {{-- Kumpulan Modal --}}

{{-- Modal Not Working Day --}}
    <x-filament::modal

        id="not-workingDay"

        alignment="center"

        icon="heroicon-o-exclamation-triangle"
        icon-color="warning"
        >

        {{-- Modal content --}}
        <x-slot name="heading">
            Hari ini bukan hari kerja
        </x-slot>

        <x-slot name="description">
            Hari ini merupakan <b>hari libur</b>.
            Attendance akan dianggap <b>lembur</b> dan
            memerlukan <b>persetujuan</b>.
            {{-- Anda berada diluar radius absensi <br> --}}
            {{-- Sejauh <b>{{ (/100) ?? '-' }} meter</b> --}}
            {{-- Sejauh <b> meter</b> --}}
        </x-slot>


        <x-slot name="footer">
            {{-- Modal footer content --}}
        </x-slot>

    </x-filament::modal>



    {{-- Modal View Attendance --}}
    @if ($this->selectedAttendance)

        @php
            // dd($this->selectedAttendance?->logs?->where('type', 'in')->first()->photo);
            $attendance = $this->selectedAttendance;
            // dd($attendance);
            $logIn = $attendance?->logs?->where('type', 'in')->first();

            $color = match ($attendance->review_status) {
                "approved" => "success",
                "rejected" => "danger",
                default => "warning",
            };
            // dd($this->selectedAttendance);
        @endphp
        <x-filament::modal id="attendance-photo"
                icon="{{ $attendance?->requires_schedule_review ? 'heroicon-o-exclamation-triangle' : '' }}"
                icon-color="primary"
                width="5xl"
        >

            {{-- Modal content --}}
            <x-slot name="heading">
                Attendance Tanggal {{ $this->selectedAttendance->date }}
            </x-slot>

            <x-slot name="description">
                @if ($attendance?->requires_schedule_review)
                    Attendance ini memerlukan persetujuan. Status Persetujuan:

                    <x-filament::badge color="{{ $color }}">
                        {{ $attendance->review_status }}
                    </x-filament::badge>
                @endif

                {{-- Anda berada diluar radius absensi <br> --}}
                {{-- Sejauh <b>{{ (/100) ?? '-' }} meter</b> --}}
                {{-- Sejauh <b> meter</b> --}}
            </x-slot>

            {{ $this->formModal }}

            {{-- <img
                src="{{ Storage::temporaryUrl(
                        $logIn->photo,
                        now()->addMinutes(5)
                    ) }}"
                class="w-full rounded-lg"
            > --}}
        </x-filament::modal>

    @endif
</x-filament-panels::page>
