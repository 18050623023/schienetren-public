<?php

namespace App\Filament\Tenant\Resources\Discounts\Pages;

use App\Filament\Tenant\Resources\Discounts\DiscountResource;
use Filament\Resources\Pages\CreateRecord;

class CreateDiscount extends CreateRecord
{
    protected static string $resource = DiscountResource::class;
}
