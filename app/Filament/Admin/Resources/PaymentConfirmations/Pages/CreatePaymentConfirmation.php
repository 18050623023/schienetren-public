<?php

namespace App\Filament\Admin\Resources\PaymentConfirmations\Pages;

use App\Filament\Admin\Resources\PaymentConfirmations\PaymentConfirmationResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePaymentConfirmation extends CreateRecord
{
    protected static string $resource = PaymentConfirmationResource::class;
}
