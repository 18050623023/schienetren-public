<?php

namespace App\Filament\Admin\Resources\Users\RelationManagers;

use App\Filament\Admin\Resources\Subscriptions\Schemas\SubscriptionForm;
use App\Filament\Admin\Resources\Subscriptions\Tables\SubscriptionsTable;
use Filament\Actions\CreateAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class SubscriptionsRelationManager extends RelationManager
{
    protected static string $relationship = 'subscriptions';

    public function form(Schema $form): Schema
    {
        return SubscriptionForm::configure($form);
    }

    public function table(Table $table): Table
    {
        return SubscriptionsTable::configure($table)
            ->headerActions([
                CreateAction::make(),
            ]);
    }
}
