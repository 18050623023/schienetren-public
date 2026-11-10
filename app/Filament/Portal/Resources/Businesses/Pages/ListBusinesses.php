<?php

namespace App\Filament\Portal\Resources\Businesses\Pages;

use App\Filament\Portal\Resources\Businesses\BusinessResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;

class ListBusinesses extends ListRecords
{
    protected static string $resource = BusinessResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Buat Bisnis Baru'),
        ];
    }
}
