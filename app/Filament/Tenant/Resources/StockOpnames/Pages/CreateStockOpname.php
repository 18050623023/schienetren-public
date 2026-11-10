<?php

namespace App\Filament\Tenant\Resources\StockOpnames\Pages;

use App\Filament\Tenant\Resources\StockOpnames\Pages\Audit\Support\AuditStockOpnameHelper;
use App\Filament\Tenant\Resources\StockOpnames\Pages\Audit\Support\ModalDescriptions;
use App\Filament\Tenant\Resources\StockOpnames\StockOpnameResource;
use App\Models\Tenant\Product;
use App\Models\Tenant\ProductSerial;
use App\Models\Tenant\Stock;
use App\Models\Tenant\StockOpname;
use App\Models\Tenant\StockOpnameItem;
use App\Models\Tenant\StockOpnameItemSerial;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CreateStockOpname extends CreateRecord
{
    protected static string $resource = StockOpnameResource::class;

    protected static bool $canCreateAnother = false;

    public function getSubNavigation(): array
    {
        return static::getResource()::getSubNavigation();
    }

    public function mount(): void
    {
        $branchId = session('current_branch_id');
        if ($branchId) {
            $hasStock = Stock::where('branch_id', $branchId)->where('stock', '>', 0)->exists();
            $alreadyInitial = StockOpname::where('branch_id', $branchId)->where('type', 'initial')->exists();

            if ($hasStock || $alreadyInitial) {
                Notification::make()
                    ->title('Initial Stock Tidak Tersedia')
                    ->body('Cabang ini sudah memiliki saldo stok berjalan atau sudah pernah melakukan Initial Stock sebelumnya.')
                    ->warning()
                    ->send();

                redirect(static::getResource()::getUrl('index'));
                return;
            }
        } else {
            $anyEligible = \App\Models\Tenant\Branch::where('is_active', true)
                ->whereDoesntHave('stockOpnames', fn ($q) => $q->where('type', 'initial'))
                ->whereDoesntHave('stocks', fn ($q) => $q->where('stock', '>', 0))
                ->exists();

            if (! $anyEligible) {
                Notification::make()
                    ->title('Initial Stock Tidak Tersedia')
                    ->body('Seluruh cabang telah memiliki saldo stok. Silakan gunakan menu Penyesuaian Stok atau Pembelian.')
                    ->warning()
                    ->send();

                redirect(static::getResource()::getUrl('index'));
                return;
            }
        }

        parent::mount();
    }

    // public function create(bool $another = false): void
    // {
    //     try {
    //         parent::create();
    //         \Illuminate\Support\Facades\Log::info('OIJBASDFIHUBASIJFBHIASFUDHB');
    //     } catch (\Illuminate\Validation\ValidationException $e) {
    //         // paksa tutup modal
    //         $this->dispatch('close-modal');

    //         // tetap kirim error ke form
    //         $this->setErrorBag($e->validator->errors());

    //         $this->halt();

    //     }
    // }

    protected function getCreateFormAction(): Action
    {
        // return parent::getCreateFormAction()
        //     ->before(function () {
        //         $this->validate();
        //         $actionId = str('fi-')
        //             ->append($this->getId())
        //             ->append('-action-0')
        //             ->toString();

        //         $this->dispatch('close-modal', id: $actionId);
        //     })
        //     ->submit(null)
        //     ->requiresConfirmation()
        //     ->action(function () {
        //         $this->validate();

        //         $this->create();
        //     });
        return Action::make('submit')
            // ->icon('heroicon-s-shopping-cart')
            ->label('Submit Stock Opname')
            ->requiresConfirmation()
            ->modalHeading('Confirm Initial Stock')
            ->modalDescription(ModalDescriptions::initialStockSubmit())
            ->modalCancelActionLabel('Cancel')
            ->modalSubmitActionLabel('Confirm')            
            // ->action(fn (CreateStockOpname $livewire) => $livewire->create())
            ->action(function (CreateStockOpname $livewire) {
                try {
                    $livewire->create();
                } catch (\Illuminate\Validation\ValidationException $e) {
                    // Menangkap ValidationException agar action dianggap "selesai" oleh Filament,
                    // sehingga modal konfirmasi akan tertutup secara otomatis.
                    // Kemudian kita kembalikan error ke form agar user tahu field mana yang salah.
                    $livewire->setErrorBag($e->validator->errors());
                }
            })
            // ->action(function (CreateStockOpname $livewire) {
            //     // $livewire->validate(); // validasi form
            //     $validator = Validator::make(
            //         $livewire->form->getState(),
            //         $livewire->getRules()
            //     );

            //     if ($validator->fails()) {

            //         // inject error ke form
            //         $livewire->setErrorBag($validator->errors());

            //         // kasih notifikasi (optional)
            //         Notification::make()
            //             ->title('Validasi gagal')
            //             ->danger()
            //             ->send();

            //         return; // ⬅️ stop tanpa exception
            //     }
            //     $livewire->create();
            // })
            ->keyBindings(['mod+s']);
    }

    protected function handleRecordCreation(array $data): Model
    {
        // $data structure: [ 'branch_id', 'date', 'items' => [ [ 'product_id', 'qty', 'serials' => [ ['serial_number'] ] ] ] ]

        return DB::transaction(function () use ($data) {

            $alreadyInitial = StockOpname::where('branch_id', $data['branch_id'])
                ->where('type', 'initial')
                ->exists();

            /** @var Builder $query */
            $queryStock = Stock::query();
            $hasStock = $queryStock->where('branch_id', '=', $data['branch_id'])
                ->where('stock', '>', 0)
                ->exists();

            if ($alreadyInitial || $hasStock) {
                abort(403, 'Initial stock hanya boleh dilakukan satu kali dan saat stok masih 0.');
                // throw new \Exception(
                //     'Initial stock hanya boleh dilakukan satu kali dan saat stok masih 0.'
                // );
            }

            // create header
            $header = StockOpname::create([
                'branch_id' => $data['branch_id'],
                'reference' => AuditStockOpnameHelper::generateReference(),
                'date' => $data['date'] ?? now(),
                'notes' => $data['notes'] ?? null,
                'type' => 'initial',
                'status' => 'draft',
                'created_by' => Auth::user()->id,
            ]);

            foreach ($data['items'] as $item) {

                $opnameItem = StockOpnameItem::create([
                    'stock_opname_id' => $header->id,
                    'branch_id' => $data['branch_id'],
                    'product_id' => $item['product_id'],
                    'product_variant_id' => $item['product_variant_id'] ?? null,
                    'system_qty' => $item['qty'],
                    'actual_qty' => $item['qty'],
                    'note' => $item['note'] ?? null,
                ]);

                // If product uses serials, insert serials
                $productAll = Product::all();
                $product = $productAll->find($item['product_id']);

                if ($product && $product->has_sn && ! empty($item['serials'])) {
                    foreach ($item['serials'] as $s) {
                        StockOpnameItemSerial::create([
                            'stock_opname_item_id' => $opnameItem->id,
                            'product_id' => $item['product_id'],
                            'product_variant_id' => $item['product_variant_id'] ?? null,
                            'serial_number' => $s['serial_number'],
                            'status' => 'EXTRA',
                        ]);
                    }
                }
            }

            return $header;
            // return null;
        });
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
