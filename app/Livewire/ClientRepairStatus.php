<?php

namespace App\Livewire;

use App\Models\Tenant\Repair;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ViewEntry;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class ClientRepairStatus extends Component implements HasForms, \Filament\Infolists\Contracts\HasInfolists, \Filament\Actions\Contracts\HasActions
{
    use InteractsWithForms;
    use \Filament\Infolists\Concerns\InteractsWithInfolists;
    use \Filament\Actions\Concerns\InteractsWithActions;

    public ?array $data = [];
    public ?Repair $repair = null;
    public bool $searched = false;
    public bool $hasValidToken = false;

    public function mount(): void
    {
        $token = request()->query('token');
        if ($token) {
            try {
                $decoded = \Vinkla\Hashids\Facades\Hashids::decode($token);
                if (!empty($decoded)) {
                    $repair = Repair::find($decoded[0]);
                    if ($repair) {
                        $this->data['code'] = $repair->code;
                        $this->hasValidToken = true;
                    } else {
                        \Filament\Notifications\Notification::make()
                            ->title('Data Tidak Ditemukan')
                            ->body('Data reparasi tidak ditemukan atau sudah dihapus.')
                            ->danger()
                            ->send();
                    }
                } else {
                    \Filament\Notifications\Notification::make()
                        ->title('Token Tidak Valid')
                        ->body('Link pelacakan yang Anda gunakan tidak valid.')
                        ->danger()
                        ->send();
                }
            } catch (\Exception $e) {
                \Filament\Notifications\Notification::make()
                    ->title('Terjadi Kesalahan')
                    ->body('Gagal memproses link pelacakan Anda.')
                    ->danger()
                    ->send();
            }
        }

        $this->form->fill($this->data);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                TextInput::make('code')
                    ->label('Kode Reparasi')
                    ->required(fn () => !$this->hasValidToken)
                    ->placeholder('Contoh: REP-12345')
                    ->hidden(fn () => $this->hasValidToken),
                TextInput::make('phone')
                    ->required()
                    ->tel()
                    ->placeholder('8xxxxxxxxxx')
                    ->telRegex('/^8[1-9][0-9]{6,10}$/')
                    ->prefix('+62')
                    ->columnSpan(fn () => $this->hasValidToken ? 2 : 1),
            ])
            ->statePath('data');
    }

    public function search(): void
    {
        $data = $this->form->getState();
        $code = $this->hasValidToken ? $this->data['code'] : $data['code'];
        $phone = $data['phone'];

        $this->repair = Repair::with(['client', 'repairDevices.deviceType', 'branch'])
            ->whereRaw('LOWER(code) = ?', [strtolower($code)])
            ->whereHas('client', function ($query) use ($phone) {
                $query->where('phone', $phone)
                    ->orWhere('whatsapp', $phone);
            })
            ->first();

        $this->searched = true;
    }
    
    public function resetSearch(): void
    {
        $this->searched = false;
        $this->repair = null;
        $this->form->fill();
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->record($this->repair)
            ->components([
                Grid::make(3)
                    ->schema([
                        // Kolom Kiri
                        Grid::make(1)
                            ->columnSpan(['lg' => 2])
                            ->schema([
                                Section::make('Status Unit Saat Ini')
                                    ->schema([
                                        TextEntry::make('status')
                                            ->label('Status Saat Ini')
                                            ->badge(),
                                        TextEntry::make('updated_at')
                                            ->label('Tanggal Update Terakhir')
                                            ->date('d F Y'),
                                    ])->columns(2),
                                    
                                Section::make('Riwayat Pengerjaan Unit')
                                    ->icon('heroicon-o-clock')
                                    ->schema([
                                        ViewEntry::make('timeline')
                                            ->view('livewire.client-repair-status.tracking-timeline')
                                            ->hiddenLabel()
                                    ]),
                            ]),
                            
                        // Kolom Kanan
                        Grid::make(1)
                            ->columnSpan(['lg' => 1])
                            ->schema([
                                Section::make('Data Pemilik & Unit')
                                    ->icon('heroicon-o-computer-desktop')
                                    ->headerActions([
                                        Action::make('detailSpek')
                                            ->label('Spek')
                                            ->icon('heroicon-o-cog-6-tooth')
                                            ->color('primary')
                                            ->modalHeading('Spesifikasi Perangkat')
                                            ->modalContent(fn(Repair $record) => view('livewire.client-repair-status.modal-spek', ['repair' => $record]))
                                            ->modalSubmitAction(false)
                                            ->modalCancelActionLabel('Tutup'),
                                        Action::make('fotoFisik')
                                            ->label('Foto')
                                            ->icon('heroicon-o-camera')
                                            ->color('warning')
                                            ->modalHeading('Dokumen Foto Unit Masuk')
                                            ->modalContent(fn(Repair $record) => view('livewire.client-repair-status.modal-foto', ['repair' => $record]))
                                            ->modalSubmitAction(false)
                                            ->modalCancelActionLabel('Tutup'),
                                    ])
                                    ->schema([
                                        TextEntry::make('client.name')
                                            ->label('Nama Pelanggan')
                                            ->icon('heroicon-o-user')
                                            ->weight(\Filament\Support\Enums\FontWeight::Bold)
                                            ->color('primary'),
                                            
                                        TextEntry::make('device_info')
                                            ->label('Unit Perangkat')
                                            ->getStateUsing(function (Repair $record) {
                                                return $record->repairDevices->map(fn($d) => trim($d->brand?->name . ' ' . $d->model))->implode(', ');
                                            }),
                                            
                                        TextEntry::make('notes')
                                            ->label('Keluhan Kerusakan Awal')
                                            ->color('danger')
                                            ->weight(\Filament\Support\Enums\FontWeight::Bold),
                                            
                                        TextEntry::make('created_at')
                                            ->label('Tanggal Masuk')
                                            ->dateTime('d F Y H:i:s'),
                                    ]),
                                    
                                Section::make('Struk Rincian Biaya')
                                    ->icon('heroicon-o-document-text')
                                    ->schema([
                                        ViewEntry::make('invoice')
                                            ->view('livewire.client-repair-status.cost-rincian')
                                            ->hiddenLabel()
                                    ]),
                            ])
                    ])
            ]);
    }

    public function render()
    {
        return view('livewire.client-repair-status');
    }
}
