<?php

namespace App\Filament\Tenant\Resources\LeaveTypes\Schemas;

use Filament\Actions\Action;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class LeaveTypeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                TextInput::make('name')
                    ->required()
                    ->columnSpan(2),
                TextInput::make('code')
                    ->required(),

                Hidden::make('quota_enabled')
                    ->default(true)
                    ->dehydrated(false)
                    ->afterStateHydrated(function ($state, $record, $set) {
                        if ($record?->is_unlimited ) {
                            $set('quota_enabled', false);

                            return;
                        }
                        $set('quota_enabled', true);
                    }),

                Hidden::make('is_unlimited')
                    ->default(false)
                    ->dehydrated()
                    ->dehydrateStateUsing(fn ($get) => $get('quota_enabled') ? false : true)
                    ->afterStateHydrated(function ($record, $set) {
                        $set('is_unlimited', $record?->is_unlimited);
                    }),

                TextInput::make('default_quota')
                    ->numeric(fn ($get) => $get('quota_enabled'))
                    // ->required()
                    ->disabled(fn ($get) => ! $get('quota_enabled'))

                    ->dehydrated()
                    ->dehydrateStateUsing(fn ($state, $get) => $get('quota_enabled') ? $state : NULL)

                    ->afterStateHydrated(function ($record, $set) {
                        if ($record?->is_unlimited) {
                            $set('default_quota', 'Unlimited Quota');
                        }
                    })

                    ->suffixAction(
                        Action::make('toggleQuota')
                            ->label(fn ($get) => $get('quota_enabled') ? 'Disable Quota / Unlimited' : 'Enable Quota')
                            ->icon(fn ($get) => $get('quota_enabled')
                                ? 'heroicon-m-lock-open'
                                : 'heroicon-m-lock-closed')
                            ->action(function ($get, $set) {
                                $enabled = ! $get('quota_enabled');

                                $set('quota_enabled', $enabled);
                                $set('is_unlimited', !$enabled);

                                $set('default_quota', null);
                                // if ($enabled) {
                                // }
                            })
                    )
                    ->live(),
                textInput::make('quota_per_request')
                    ->numeric()
                    ->default(0)
                    // ->disabled(fn ($get) => ! $get('quota_enabled'))
                    // ->dehydrated()
                    // ->dehydrateStateUsing(fn ($state, $get) => $get('quota_enabled') ? $state : NULL)
                    ,

                Select::make('reset_period')
                    ->options([
                        'yearly' => 'Yearly',
                        'monthly' => 'Monthly',
                        'one_time' => 'One Time',
                        'none' => 'None',
                    ]),
                Radio::make('is_paid')
                    ->inline()
                    ->boolean()
                    ->required(),
                Radio::make('require_approval')
                    ->inline()
                    ->boolean()
                    ->required(),
            ]);
    }
}
