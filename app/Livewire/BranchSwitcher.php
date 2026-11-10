<?php

namespace App\Livewire;

use App\Models\Tenant\StockOpname;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Livewire\Component;

class BranchSwitcher extends Component
{
    public $setCurrentBranchId;

    // public function mount()
    // {
    //     $this->setCurrentBranchId = session('current_branch_id');
    // }

    // public function switchBranch($branchId)
    // {
    //     session(['current_branch_id' => $branchId]);
    //     $this->setCurrentBranchId = $branchId;
    // }

    public function updated($property)
    {
        // $audit = StockOpname::where('status', 'in_progress')->exists();
        // if($audit) {
        //     Notification::make()
        //         ->title('Selesaikan Audit stock opname sebelum ganti cabang')
        //         ->warning()
        //         ->send();
        //     redirect(request()->header('Referer'));
        //     return;
        // }
        if ($property === 'setCurrentBranchId') {
            session(['current_branch_id' => filled($this->setCurrentBranchId) ? (int) $this->setCurrentBranchId : null]);

            Notification::make()
                ->title('Berhasil')
                ->body('Berhasil ganti cabang')
                ->success()
                ->send();

            redirect(request()->header('Referer'));
        }
    }

    public function render()
    {
        $branches = \App\Models\Tenant\Branch::all();
        $user = Auth::user();
        // $this->setCurrentBranchId = ($user->hasRole('owner')) ? Session::get('current_branch_id') : $user->branch_id;
        $this->setCurrentBranchId = Session::get('current_branch_id');
        $tenant = tenant();
        // dd($currentBranchId);

        $hasBypassRole = $user->hasRole(settingTenant('tenant_bypass_roles', 'owner'));

        return view('livewire.branch-switcher', [
            'branches' => $branches,
            'user' => $user,
            'tenant' => $tenant,
            'hasBypassRole' => $hasBypassRole,
        ]);
    }
}
