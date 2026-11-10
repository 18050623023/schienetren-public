<?php

namespace App\Filament\Tenant\Pages\Auth;

use App\Enums\TenantFeature;
use App\Models\Tenant\Employee;
use Filament\Actions\Action;
use Filament\Auth\Pages\EditProfile;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\EmptyState;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class CustomEditProfile extends EditProfile
{
    public function defaultForm(Schema $schema): Schema
    {
        return parent::defaultForm($schema)->inlineLabel(false);
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $employee = Employee::where('user_id', \Filament\Facades\Filament::auth()->id())->with('skills')->first();
        if ($employee) {
            $data = array_merge($data, $employee->attributesToArray());
            
            $skills = [];
            foreach ($employee->skills as $skill) {
                $skills[(string) \Illuminate\Support\Str::uuid()] = [
                    'skill_specialization_id' => $skill->skill_specialization_id,
                    'skill_level_id' => $skill->skill_level_id,
                ];
            }
            $data['skills'] = $skills;
        }

        return $data;
    }

    public function save(): void
    {
        \Illuminate\Support\Facades\Log::info('CustomEditProfile save() called. Checksum passed!');
        try {
            parent::save();
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Error in save(): ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            throw $e;
        }
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        \Illuminate\Support\Facades\Log::info('handleRecordUpdate called with data', ['data' => $data]);
        
        // Extract employee specific fields before sending to User
        $employeeFields = ['code_pegawai', 'join_date', 'phone', 'whatsapp', 'gender', 'address', 'is_active'];
        $employeeData = [];
        foreach ($employeeFields as $field) {
            if (array_key_exists($field, $data)) {
                $employeeData[$field] = $data[$field];
                unset($data[$field]);
            }
        }
        
        $skillsData = $data['skills'] ?? [];
        unset($data['skills']);

        // Update the User record natively
        $record = parent::handleRecordUpdate($record, $data);

        // Update Employee record
        $employee = Employee::where('user_id', $record->id)->first();
        if ($employee) {
            if (!empty($employeeData)) {
                $employee->update($employeeData);
            }

            // Sync skills only if SKILL_MANAGEMENT feature is enabled
            if (tenant()->hasFeatureCode(TenantFeature::SKILL_MANAGEMENT)) {
                $employee->skills()->delete();
                foreach ($skillsData as $skill) {
                    $employee->skills()->create([
                        'skill_specialization_id' => $skill['skill_specialization_id'],
                        'skill_level_id' => $skill['skill_level_id'],
                    ]);
                }
            }
        }

        return $record;
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Akun')
                    ->schema([
                        $this->getNameFormComponent(),
                        $this->getEmailFormComponent(),
                        $this->getPasswordFormComponent(),
                        $this->getPasswordConfirmationFormComponent(),
                    ])
                    ->columns(2),

                EmptyState::make('Data Kepegawaian Belum Didaftarkan')
                    ->icon('heroicon-o-exclamation-triangle')
                    ->iconColor('warning')
                    ->description('Silakan hubungi HRD atau Admin untuk dibuatkan NIP agar Anda dapat melengkapi alamat dan data pribadi lainnya.')
                    ->visible(fn () => !Employee::where('user_id', \Filament\Facades\Filament::auth()->id())->exists()),

                Group::make()
                    ->columns(2)
                    ->visible(fn() => Employee::where('user_id', \Filament\Facades\Filament::auth()->id())->exists())
                    ->schema([
                        Section::make('Informasi Pribadi (Pegawai)')
                            ->schema([
                                TextInput::make('code_pegawai')
                                    ->label('Kode Pegawai')
                                    ->disabled()
                                    ->dehydrated(false),

                                DatePicker::make('join_date')
                                    ->label('Tanggal Bergabung')
                                    ->disabled()
                                    ->dehydrated(false),

                                TextInput::make('phone')
                                    ->required()
                                    ->tel()
                                    ->placeholder('8xxxxxxxxxx')
                                    ->telRegex('/^8[1-9][0-9]{6,10}$/')
                                    ->prefix('+62')
                                    ->suffixAction(
                                        Action::make('copy')
                                            ->icon('heroicon-o-clipboard')
                                            ->action(fn(Set $set, Get $get) => $set('whatsapp', $get('phone')))
                                            ->tooltip('Copy to WhatsApp')
                                    ),

                                TextInput::make('whatsapp')
                                    ->required()
                                    ->prefix('+62')
                                    ->tel(),

                                Select::make('gender')
                                    ->options([
                                        'male' => 'Laki-laki',
                                        'female' => 'Perempuan',
                                    ])
                                    ->required(),

                                Textarea::make('address')
                                    // ->columnSpan(2)
                                    ->required(),
                            ])
                            ->columns(2),

                        Section::make('Skills')
                            ->visible(fn () => tenant()->hasFeatureCode(TenantFeature::SKILL_MANAGEMENT))
                            ->schema([
                                Repeater::make('skills')
                                    ->hiddenLabel()
                                    ->schema([
                                        Select::make('skill_specialization_id')
                                            ->label('Skill')
                                            ->options(\App\Models\Tenant\SkillSpecialization::pluck('name', 'id'))
                                            ->required()
                                            ->searchable()
                                            ->createOptionForm([
                                                TextInput::make('name')
                                                    ->label('Skill Name')
                                                    ->required(),
                                            ])
                                            ->createOptionUsing(fn (array $data) => \App\Models\Tenant\SkillSpecialization::create($data)->id),

                                        Select::make('skill_level_id')
                                            ->label('Level')
                                            ->options(\App\Models\Tenant\SkillLevel::pluck('name', 'id'))
                                            ->required()
                                            ->searchable()
                                            ->createOptionForm([
                                                TextInput::make('name')
                                                    ->label('Level Name')
                                                    ->required(),
                                            ])
                                            ->createOptionUsing(fn (array $data) => \App\Models\Tenant\SkillLevel::create($data)->id),
                                    ])
                                    ->columns(2),
                            ]),
                    ]),
            ]);
    }
}
