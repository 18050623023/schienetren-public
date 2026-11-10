<?php

namespace App\Services;

use App\Models\Tenant\Attendance;
use App\Models\Tenant\EmployeeAssignment;
use App\Models\Tenant\Holiday;
use App\Models\Tenant\LeaveRequest;
use App\Models\Tenant\User;
use App\Models\Tenant\WorkShift;
use Carbon\Carbon;
use Exception;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AttendanceService
{
    public function validateStatus(WorkShift $workShift, Carbon $time, string $type): array
    {

        if (! $workShift) {
            throw new Exception('Work shift not found');
        }

        $time = Carbon::parse($time);

        $referenceTime = match ($type) {
            'in' => Carbon::parse($workShift->start_time),
            'out' => Carbon::parse($workShift->end_time),
            default => throw new Exception('Type not found or null'),
        };

        $difference = $referenceTime->diffInMinutes($time);

        return [
            'status' => $type === 'in'
                ? match (true) {
                    $difference < 0 => 'early',
                    $difference > $workShift->late_tolerance => 'late',
                    default => 'present',
                }
                : match (true) {
                    $difference < 0 => 'early',
                    $difference > 0 => 'overtime',
                    default => 'present',
                },
            'difference_minute' => max(abs($difference), 0),
        ];
    }
    public function resolveAttendanceContext(EmployeeAssignment $assignment, array $data = []): array
    {

        $assignment->loadMissing([
            'workSchedule.workScheduleToday.workShift',
            'employee.user'
        ]);

        // $workScheduleOverride = \App\Models\Tenant\WorkScheduleOverride::approvedForAssignmentAndDate($assignment->id, $data['logged_at'])->first();
        $hasShiftManagement = tenant()->hasFeatureCode(\App\Enums\TenantFeature::SHIFT_MANAGEMENT);

        $workScheduleOverride = $hasShiftManagement
            ? \App\Models\Tenant\WorkScheduleOverride::approvedForAssignmentAndDate($assignment->id, $data['logged_at'])->first()
            : null;
        $holiday = Holiday::forBranchAndDate($assignment->branch_id, $data['logged_at'])->get();

        if ($hasShiftManagement) {
            $workShift = $assignment->workSchedule?->workScheduleToday?->workShift;
            $isAssignmentWorkingDay = $assignment->isWorkingDay($data['logged_at']);
        } else {
            $defaultSchedule = \App\Models\Tenant\WorkSchedule::getDefaultSchedule();
            $workShift = $defaultSchedule?->workScheduleToday?->workShift;
            $loggedAtCarbon = $data['logged_at'] instanceof Carbon ? $data['logged_at'] : Carbon::parse($data['logged_at']);
            $isAssignmentWorkingDay = (int) $loggedAtCarbon->dayOfWeekIso <= 6;
        }

        // Penentuan Working Day
        $isEffectiveWorkingDay = $isAssignmentWorkingDay;

        // Penetapan attendance type
        $attendanceType = 'working_day';

        $isHoliday = $holiday->isNotEmpty();

        if ($isHoliday) {
            $attendanceType = 'holiday';
        }

        if ($workScheduleOverride) {
            $workShift = $workScheduleOverride->workShift;
            $isEffectiveWorkingDay = $workScheduleOverride->is_working_days;
            $attendanceType = 'override';
        }


        $leave = null;

        if ($isEffectiveWorkingDay) {
            $leave = LeaveRequest::query()
                ->where('employee_id', $assignment->employee_id)
                ->where('status', 'approved')
                ->whereDate('start_date', '<=', $data['date'])
                ->whereDate('end_date', '>=', $data['date'])
                ->first();
            // dd($data);

        }

        if($leave) {
            $attendanceType = 'leave';
        }

        if(
            !$isAssignmentWorkingDay &&
            !$isHoliday &&
            !$workScheduleOverride
        )
        {
            $attendanceType = 'off_day';
        }


        // Normalize context
        if (in_array($attendanceType, ['leave', 'off_day', 'holiday'])) {
            $workShift = null;
            $isEffectiveWorkingDay = false;
        }

        // dd($leave);

        return [
                'attendance_type' => $attendanceType,
                'working_day'     => $isEffectiveWorkingDay,
                'is_holiday'      => $isHoliday,
                'holiday'         => $holiday,
                'override'        => $workScheduleOverride,
                'work_shift'      => $workShift,
                'leave_request'   => $leave,
                'is_leave'        => $leave !== null,
            ];

    }



    public function uploadPhoto(string $file, string $filePath): bool
    {
        if (!str_starts_with($file, 'data:image/')) {
            throw new Exception('File type is based on data:image/');
            return false;
        }

        $parts = explode(',', $file, 2);

        if (count($parts) !== 2) {
            return false;
        }

        [, $content] = $parts;

        $data = base64_decode($content, true);

        if ($data === false) {
            throw new Exception('Invalid base64 data');

            return false;
        }

        return Storage::put(
            $filePath,
            $data
        );

    }

    public function handleWorkingDay(array $status , array $data = [], array $context = [] ): void
    {

        // execute Database
        $attendance = Attendance::firstOrNew([
            'employee_assignment_id' => $data['employee_assignment_id'],
            'date' => $data['date'],
        ]);


        if ($data['type'] === 'in') {
            $attendance->status = $status['status'] ;
            $attendance->attendance_type = $context['attendance_type'];
            $attendance->notes =  match ($context['attendance_type']) {
                    'override' => 'Override Working',
                    default => null,
                };
        }


        if ($data['type'] === 'out') {

            $attendance->overtime = null;
            $attendance->requires_schedule_review = false;

            if ($status['status'] === 'overtime') {
                $workShift = $context['work_shift'] ?? null;

                $attendance->overtime = $status['difference_minute'];
                $attendance->requires_schedule_review =
                    $status['difference_minute'] >
                    ($workShift?->overtime_tolerance ?? 0);
            }
        }


        // dd($);

        $attendance->save();

        $attendance->logs()->updateOrCreate([
                'attendance_id' => $attendance->id,
                'type'          => $data['type'],
            ],[
                'employee_assignment_id' => $data['employee_assignment_id'] ?? null,
                'logged_at'     => $data['logged_at'] ?? null,
                'photo'         => $data['filePath'] ?? null,
                'latitude'      => $data['map']['latitude'] ?? null,
                'longitude'     => $data['map']['longitude'] ?? null,
                'accuracy'      => $data['map']['accuracy'] ?? null,
                'distance'      => $data['map']['distance'] ?? null,
                'late_minutes'  => $status['status'] === 'late' ? $status ['difference_minute'] : null,
                'early_minutes' => $status['status'] === 'early' ? $status['difference_minute'] : null,
            ]);

    }



    public function handleNonWorkingDay(array $status, array $data = [], array $context = []): void
    {
        $attendance = Attendance::firstOrNew([
            'employee_assignment_id' => $data['employee_assignment_id'],
            'date' => $data['date'],
        ]);

        if ($data['type'] === 'in') {
            $attendance->status = $status['status'];
            $attendance->attendance_type = $context['attendance_type'];
            $attendance->requires_schedule_review = true;
            $attendance->notes =  match ($context['attendance_type']) {
                    'holiday' => 'Holiday',
                    'weekend' => 'Weekend',
                    'leave' => 'Leave',
                    'override' => 'Override Non Working',
                    default => null,
                };
        }

        $attendance->save();

        $attendance->logs()->updateOrCreate(
            [
                'attendance_id' => $attendance->id,
                'type' => $data['type'],
            ],
            [
                'employee_assignment_id' => $data['employee_assignment_id'],
                'logged_at' => $data['logged_at'] ?? null,
                'photo' => $data['filePath'] ?? null,
                'latitude' => $data['map']['latitude'] ?? null,
                'longitude' => $data['map']['longitude'] ?? null,
                'accuracy' => $data['map']['accuracy'] ?? null,
                'distance' => $data['map']['distance'] ?? null,
            ]
        );
    }


}
