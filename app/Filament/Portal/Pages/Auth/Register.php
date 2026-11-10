<?php

namespace App\Filament\Portal\Pages\Auth;

use Filament\Forms\Components\Component;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\TextInput;
use Filament\Auth\Pages\Register as BaseRegister;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class Register extends BaseRegister
{
    protected function getForms(): array
    {
        return [
            'form' => $this->form(
                $this->makeForm()
                    ->schema([
                        $this->getNameFormComponent(),
                        $this->getEmailFormComponent(),
                        TextInput::make('phone')
                            ->label('Phone Number')
                            ->tel()
                            ->required()
                            ->maxLength(255),
                        $this->getPasswordFormComponent(),
                        $this->getPasswordConfirmationFormComponent(),
                        Hidden::make('plan_id')
                            ->default(request()->query('plan_id')),
                    ])
                    ->statePath('data'),
            ),
        ];
    }

    protected function handleRegistration(array $data): \Illuminate\Database\Eloquent\Model
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'password' => Hash::make($data['password']),
        ]);

        if (!empty($data['plan_id'])) {
            // Save the plan preference in the user's session so we know what they wanted
            session(['selected_plan_id' => $data['plan_id']]);
        }

        return $user;
    }

    protected function getRedirectUrl(): string
    {
        if (session()->has('selected_plan_id')) {
            $planId = session()->pull('selected_plan_id');
            return route('filament.portal.pages.checkout', ['plan_id' => $planId]);
        }

        return parent::getRedirectUrl();
    }
}
