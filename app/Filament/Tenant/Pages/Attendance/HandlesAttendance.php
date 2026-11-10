<?php

namespace App\Filament\Tenant\Pages\Attendance;

use App\Models\Tenant\Attendance;
use App\Models\Tenant\EmployeeAssignment;
use App\Services\AttendanceService;
use App\Services\GeoLocationService;
use Carbon\Carbon;
use Exception;
use InvalidArgumentException;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use App\Enums\TenantFeature;

/**
 * @property \Filament\Forms\Form $form
 * @property \Carbon\Carbon|null $loggedAt
 * @method mixed dispatch(string $event, ...$params)
 * @method void fillAttendanceForm()
 */
trait HandlesAttendance
{
    protected ?Attendance $attendance = null;
    protected ?Attendance $currentAttendance = null;
    protected ?EmployeeAssignment $currentAssignment = null;


    public function submitAttendance(): bool
    {
        //Initialize Variable

        // $data['logged_at'] = now();
        // $data['logged_at'] = Carbon::parse('07:59:00');
        // dd($this->loggedAt);


        $attendanceService = app(AttendanceService::class);
        $geoLocationService = app(GeoLocationService::class);

        // Validasi aktif dan mengambil data Assignment
        $employeeAssignment = $this->getCurrentAssignment();

        $data = $this->form->getState();

        // dd($data);

        // Validation Data

        // Validasi request (tanpa try-catch agar otomatis berhenti jika gagal)
        $this->validateRequest($data);

        $data['logged_at'] = $this->loggedAt;


        // Inisialize Variable untuk Photo attendance
        $employeeName = $employeeAssignment->employee->user->name;
        $folder = Str::slug($employeeName);
        $path = "images/attendance/{$folder}/";
        $fileName = 'attendance-' . $data['date'] . '-' . $data['type'] . '.jpg';
        $data['filePath'] = $path . $fileName;


        // double validate distance dengan aman jika fitur geofence aktif
        if (tenant()->hasFeatureCode(TenantFeature::ATTENDANCE_GEOFENCE)) {
            $distance = data_get($data, 'map.distance', 999999);
            if ($distance > $data['attendance_radius'] && $data['type'] == 'in') {
                $this->dispatch('open-modal', id: 'distance-modal');
                Notification::make()
                    ->danger()
                    ->title('You are outside the attendance radius')
                    ->body('Jarak anda ' . $geoLocationService->formatDistance($data['map']['distance']))
                    ->send();
                return false;
            }
        }

        // Resolve Context
        $context = $attendanceService->resolveAttendanceContext($employeeAssignment, $data);



        // Resolve Status
        $status = [
            'status' => 'off',
            'difference_minute' => 0,
        ];

        if ($context['working_day'] && $context['work_shift']) {

            $status = $attendanceService->validateStatus(
                $context['work_shift'],
                $data['logged_at'],
                $data['type']
            );
        }

        if ($status['status'] == 'late') {
            Notification::make()
                ->danger()
                ->title('Kamu terlambat')
                ->body('')
                ->send();
        }


        // dd($data,$context,$status);

        // Execution

        DB::beginTransaction();

        try {
            // upload photo
            $returnPhoto = $attendanceService->uploadPhoto($data['photo'], $data['filePath']);

            if (!$returnPhoto) {
                throw new Exception('gagal upload photo');
            }


            // Execute attendance
            if ($context['working_day']) {
                $attendanceService->handleWorkingDay($status, $data, $context);
            } else {
                $attendanceService->handleNonWorkingDay($status, $data, $context);
            }
            // match ($context['attendance_type']) {

            //     'schedule' => $attendanceService->handleWorkingDay(
            //         status: $status,
            //         data: $data,
            //         context: $context,
            //     ),

            //     'holiday' => $attendanceService->handleNonWorkingDay(
            //         status: $status,
            //         data: $data,
            //         context: $context,
            //     ),

            //     // 'override' => $this->handleOverride(
            //     //     status: $status,
            //     //     data: $data,
            //     //     context: $context,
            //     // ),


            //     default => throw new InvalidArgumentException(
            //         "Unsupported attendance type: {$context['attendance_type']}"
            //     ),
            // };


            // dd($data);
            // if($context['working_day']) {
            //     // $this->dispatch('open-modal', id: 'absent-modal');
            //     $attendanceService->handleWorkingDay($status, $data, $context);
            //     // return false;
            // };
            $this->resetCurrentAssignmentAndAttendance();
            // $this->refreshAttendance();


            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            if ($data['filePath'] && Storage::exists($data['filePath'])) {
                Storage::delete($data['filePath']);
            }

            throw $e;
        }

        Notification::make()
            ->success()
            ->title('Attendance submitted successfully')
            ->body('')
            ->send();

        return true;
    }

    protected function validateRequest(array $data): void
    {
        $isGeofence = tenant()->hasFeatureCode(TenantFeature::ATTENDANCE_GEOFENCE);

        Validator::make($data, [
            'type' => ['required', Rule::in(['in', 'out'])],

            'employee_assignment_id' => [
                'required',
                'exists:employee_assignments,id',
            ],

            'date' => ['required', 'date'],

            'latitude' => ['required', 'numeric'],
            'longitude' => ['required', 'numeric'],

            // 'map.latitude' => ['required', 'numeric'],
            // 'map.longitude' => ['required', 'numeric'],
            // 'map.distance' => ['required', 'numeric'],

            'map.latitude' => [$isGeofence ? 'required' : 'nullable', 'numeric'],
            'map.longitude' => [$isGeofence ? 'required' : 'nullable', 'numeric'],
            'map.distance' => [$isGeofence ? 'required' : 'nullable', 'numeric'],
            'map.accuracy' => ['nullable', 'numeric'],

            'photo' => ['required', 'string'],
        ])->validate();
    }

    public function resetCurrentAssignmentAndAttendance()
    {
        $this->currentAssignment = null;
        $this->currentAttendance = null;
        $this->attendance = $this->attendance?->fresh();
        $this->dispatch('$refresh');
        $this->fillAttendanceForm();
    }


    public function hasCheckInLog(string $type): bool
    {
        // dd($this->getCurrentAttendance());
        return $this->getCurrentAttendance()?->logs
            ->contains('type', $type) ?? false;
    }

    public function getCurrentAttendance(): ?Attendance
    {
        // dd($this->getCurrentAssignment()->attendance());
        return $this->currentAttendance ??= $this->getCurrentAssignment()
            ?->attendance()
            ->whereDate('date', $this->loggedAt)
            ->first();
    }


    public function getCurrentAssignment(): ?EmployeeAssignment
    {
        $logged_date = $this->loggedAt->toDateString();
        // dd(Auth::user()->employees?->id);
        $assignment = $this->currentAssignment ??= EmployeeAssignment::with([
            'employee',
            'branch',
            // 'attendance' => function ($query) {
            //     $query
            //         ->whereDate('created_at', $this->loggedAt);
            // }
        ])
            ->where('employee_id', Auth::user()->employees?->id)
            ->where('start_date', '<=', $logged_date)
            ->where(function ($query) use ($logged_date) {
                $query->whereNull('end_date')
                    ->orWhereDate('end_date', '>=', $logged_date);
            })
            ->first();

        abort_if(! $assignment, 403, 'Anda belum ditugaskan.');

        return $assignment;
    }
}
