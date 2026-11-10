<?php

namespace App\Filament\Tenant\Resources\PaymentMethods\Pages;

use App\Filament\Tenant\Resources\PaymentMethods\PaymentMethodResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePaymentMethod extends CreateRecord
{
    protected static string $resource = PaymentMethodResource::class;
}
