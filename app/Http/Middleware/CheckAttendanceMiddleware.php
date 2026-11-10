<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Filament\Facades\Filament;
use App\Models\Tenant\EmployeeAssignment;
use App\Models\Tenant\Attendance;

class CheckAttendanceMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $panel = Filament::getCurrentPanel();
        
        if ($panel && $panel->getId() === 'tenant') {
            $user = Filament::auth()->user();

            if ($user) {
                $currentRoute = $request->route()?->getName();
                
                // 1. Bypass pengecekan jika sedang berada di rute-rute berikut:
                // - attendance-page: agar tidak terjadi redirect loop
                // - logout: agar user tetap bisa keluar dari aplikasi (logout)
                // - livewire: agar user tetap bisa klik tombol submit di AttendancePage
                if ($currentRoute && !str($currentRoute)->contains(['attendance-page', 'logout', 'livewire'])) {
                    
                    // 2. Bypass pengecekan jika role user masuk dalam pengecualian
                    if (!$user->hasRole(settingTenant('tenant_bypass_roles', ['owner']))) {
                        
                        // 3. Cek apakah ada penugasan (assignment) aktif hari ini
                        $assignment = EmployeeAssignment::where('employee_id', $user->employees?->id)
                            ->where('start_date', '<=', today())
                            ->where(function ($query) {
                                $query->whereNull('end_date')
                                    ->orWhereDate('end_date', '>=', today());
                            })
                            ->first();

                        $hasCheckedIn = false;

                        if ($assignment) {
                            // 4. Cek apakah sudah absen masuk hari ini
                            $hasCheckedIn = Attendance::where('employee_assignment_id', $assignment->id)
                                ->whereDate('date', today())
                                ->whereHas('logs', function ($q) {
                                    $q->where('type', 'in');
                                })
                                ->exists();
                        }

                        // 5. Jika belum absen, atau tidak punya assignment sama sekali, tendang kembali ke AttendancePage
                        if (!$hasCheckedIn) {
                            return redirect()->to(\App\Filament\Tenant\Pages\AttendancePage::getUrl());
                        }

                        // Set session branch untuk user (selain owner) berdasarkan assignment
                        if (!session()->has('current_branch_id') || session('current_branch_id') !== $assignment->branch_id) {
                            session(['current_branch_id' => $assignment->branch_id]);
                        }
                    }
                }
            }
        }

        return $next($request);
    }
}
