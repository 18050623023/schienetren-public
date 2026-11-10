<?php

namespace App\Filament\Tenant\Resources\EmployeeAssignments\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Tables\Filters\TrashedFilter;
use Illuminate\Support\Facades\Auth;

class EmployeeAssignmentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->sortable(),
                TextColumn::make('employee.name')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('roles.name')
                    ->badge()
                    ->sortable()
                    ->searchable(),

                TextColumn::make('branch.name')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('start_date')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('end_date')
                    ->placeholder('Masih Aktif')
                    ->badge()
                    ->color(fn ($state) => $state ? 'gray' : 'success')
                    ->sortable()
                    ->searchable(),
            ])
            ->emptyStateHeading(fn () => Auth::user()?->hasRole(settingTenant('tenant_bypass_roles', ['super_admin', 'owner', 'admin'])) ? 'No Employee Assignments' : 'Penugasan Belum Dilakukan')
            ->emptyStateDescription(fn () => Auth::user()?->hasRole(settingTenant('tenant_bypass_roles', ['super_admin', 'owner', 'admin'])) ? 'You may create an employee assignment using the button below.' : 'Anda belum memiliki penugasan atau penugasan Anda belum diatur oleh admin.')
            ->emptyStateIcon(fn () => Auth::user()?->hasRole(settingTenant('tenant_bypass_roles', ['super_admin', 'owner', 'admin'])) ? 'heroicon-o-x-mark' : 'heroicon-o-exclamation-triangle')
            ->filters([
                TrashedFilter::make(),
                //
            ])
            ->recordActions([
                RestoreAction::make(),
                ForceDeleteAction::make(),
                ViewAction::make(),
                // EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    RestoreBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
