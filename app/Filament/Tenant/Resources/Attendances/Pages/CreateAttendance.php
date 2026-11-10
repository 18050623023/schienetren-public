<?php

namespace App\Filament\Tenant\Resources\Attendances\Pages;

use App\Filament\Tenant\Resources\Attendances\AttendanceResource;
use Carbon\Carbon;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateAttendance extends CreateRecord
{
    protected static string $resource = AttendanceResource::class;


    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // dd($data);

        return $data;
    }

    protected function handleRecordCreation(array $data): Model
    {
        // Ambil notes jika tipe absen adalah 'in'.
        // Jika 'out', kita biarkan notes lama di database tetap terjaga (tidak dioverwrite null)
        $notes = ($data['type'] === 'in') ? ($data['notes'] ?? null) : null;

        $updateData = [
            'overtime' => $data['overtime'] ?? 0,
            'status' => $data['status'] ?? 'present',
            'requires_schedule_review' => false,
        ];

        // OPTIMASI: Jangan timpa notes masuk (pagi) menjadi null saat pulang (sore)
        if ($data['type'] === 'in') {
            $updateData['notes'] = $notes;
        }

        // Menggunakan updateOrCreate untuk mengambil data induk lama atau membuat baru
        return $this->getModel()::updateOrCreate(
            [
                'employee_assignment_id' => $data['employee_assignment_id'],
                'date' => $data['date'],
            ],
            $updateData
        );
    }

    protected function afterCreate(): void
    {

        // 1. Ambil instance model Attendance yang baru saja dibuat/didapatkan dari langkah 1
        $attendance = $this->getRecord();

        // 2. Ambil data mentah dari form state
        $formData = $this->form->getState();


        $logNotes = ($formData['type'] === 'out') ? ($formData['notes'] ?? null) : ($formData['notes'] ?? null);

        // 3. Masukkan data log detil ke tabel attendance_logs memanfaatkan relasihasMany
        $attendance->logs()->create([
            'employee_assignment_id' => $formData['employee_assignment_id'],
            'logged_at' => Carbon::today()->setTimeFromTimeString($formData['time']),
            'photo' => $formData['photo'] ?? null,
            'latitude' => $formData['latitude'] ?? null,
            'longitude' => $formData['longitude'] ?? null,
            'accuracy' => $formData['accuracy'] ?? null,
            // 'distance_meter' => $formData['distance_meter'] ?? null,
            // 'late_minute' => $formData['late_minute'] ?? 0,
            // 'early_minute' => $formData['early_minute'] ?? 0,
            // 'type' => $formData['type'], // check_in atau check_out sesuai pilihan form
            'distance' => $formData['distance'] ?? $formData['distance_meter'] ?? null,
            'late_minutes' => $formData['late_minutes'] ?? $formData['late_minute'] ?? 0,
            'early_minutes' => $formData['early_minutes'] ?? $formData['early_minute'] ?? 0,
            'type' => $formData['type'], // in atau out sesuai pilihan form
            'notes' => $logNotes,
            // 'status' => $formData['status'] ?? 'present',
        ]);
    }
}
