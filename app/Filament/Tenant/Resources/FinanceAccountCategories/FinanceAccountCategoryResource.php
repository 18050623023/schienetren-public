<?php

namespace App\Filament\Tenant\Resources\FinanceAccountCategories;

use App\Filament\Tenant\Resources\FinanceAccountCategories\Pages;
use App\Models\Tenant\FinanceAccountCategory;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms;
// use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class FinanceAccountCategoryResource extends Resource
{
    protected static ?string $model = FinanceAccountCategory::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-tag';

    // Sembunyikan dari sidebar karena diakses via tombol di halaman Finance Accounts
    protected static bool $shouldRegisterNavigation = false;


    public static function canViewAny(): bool
    {
        return tenant()->hasFeatureCode(\App\Enums\TenantFeature::FINANCE_ADVANCE);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Forms\Components\TextInput::make('name')
                    ->label('Nama Kategori')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('type')
                    ->label('Tipe')
                    ->options([
                        'asset' => 'Asset',
                        'liability' => 'Liability',
                        'equity' => 'Equity',
                        'revenue' => 'Revenue',
                        'expense' => 'Expense',
                    ])
                    ->required(),
                Forms\Components\TextInput::make('code_prefix')
                    ->label('Prefix Kode')
                    ->maxLength(255),
                Forms\Components\TextInput::make('description')
                    ->columnSpanFull()
                    ->label('Deskripsi')
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Kategori')
                    ->searchable(),
                Tables\Columns\TextColumn::make('type')
                    ->label('Tipe')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'asset' => 'success',
                        'liability' => 'danger',
                        'equity' => 'warning',
                        'revenue' => 'info',
                        'expense' => 'danger',
                        default => 'gray',
                    })
                    ->searchable(),
                Tables\Columns\TextColumn::make('code_prefix')
                    ->label('Prefix Kode')
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()
                    ->before(function (DeleteAction $action, FinanceAccountCategory $record) {
                        if ($record->accounts()->exists()) {
                            \Filament\Notifications\Notification::make()
                                ->danger()
                                ->title('Gagal Menghapus')
                                ->body('Kategori ini sedang digunakan oleh akun keuangan.')
                                ->send();

                            $action->cancel();
                        }
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageFinanceAccountCategories::route('/'),
        ];
    }
}
