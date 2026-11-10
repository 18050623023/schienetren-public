<?php

namespace App\Filament\Admin\Resources\PaymentConfirmations\Pages;

use App\Filament\Admin\Resources\PaymentConfirmations\PaymentConfirmationResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPaymentConfirmations extends ListRecords
{
    protected static string $resource = PaymentConfirmationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // CreateAction::make(),
        ];
    }
}
