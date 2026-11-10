<?php

namespace App\Filament\Admin\Resources\Users\Pages;

use App\Filament\Admin\Resources\Users\UserResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // password field is dehydrated only when filled (see Resource::form)
        // so if present here it's already hashed by dehydrateStateUsing.
        // but if you prefer to handle it here instead, you can:
        // if (! empty($data['password'])) {
        // // If you didn't hash in the field, hash here:
        // // $data['password'] = Hash::make($data['password']);
        // }


        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
