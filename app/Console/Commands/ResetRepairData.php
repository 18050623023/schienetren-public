<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Tenant;
use App\Models\Tenant\StockMovement;
use App\Models\Tenant\Stock;
use App\Models\Tenant\ProductSerial;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ResetRepairData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'repair:reset-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Revert stocks and truncate all repair related tables for all tenants';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("Memulai proses revert stok dan reset data repair...");

        Tenant::all()->runForEach(function () {
            $tenantId = tenant('id');
            $this->info("Memproses tenant: {$tenantId}...");

            DB::transaction(function () {
                // 1. Ambil semua stock_movements dari RepairItem
                $movements = StockMovement::with('productSerials')
                                ->where('source_type', 'App\Models\Tenant\RepairItem')
                                ->get();
                
                foreach ($movements as $movement) {
                    // Karena movement dari repair umumnya "out", kita tambahkan (kembalikan) ke stok
                    if ($movement->direction === 'out') {
                        Stock::where('branch_id', $movement->branch_id)
                             ->where('product_variant_id', $movement->product_variant_id)
                             ->increment('stock', $movement->quantity);
                    } elseif ($movement->direction === 'in') {
                        Stock::where('branch_id', $movement->branch_id)
                             ->where('product_variant_id', $movement->product_variant_id)
                             ->decrement('stock', $movement->quantity);
                    }
                    
                    // 2. Kembalikan status serial number menjadi "available"
                    if ($movement->productSerials->isNotEmpty()) {
                        foreach ($movement->productSerials as $serial) {
                            $serial->update(['status' => 'available']);
                        }
                    }
                    
                    // 3. Hapus movement-nya
                    $movement->delete();
                }
            });

            // 4. Truncate tabel-tabel repair (di luar transaction karena memicu implicit commit)
            Schema::disableForeignKeyConstraints();
            
            DB::table('repair_technician_commissions')->truncate();
            DB::table('repair_warranty_claims')->truncate();
            DB::table('repair_technicians')->truncate();
            DB::table('repair_payments')->truncate();
            DB::table('repair_invoices')->truncate();
            DB::table('repair_item_warranties')->truncate();
            DB::table('repair_items')->truncate();
            DB::table('repair_photos')->truncate();
            DB::table('repair_devices')->truncate();
            DB::table('repair_handovers')->truncate();
            DB::table('repairs')->truncate();
            
            Schema::enableForeignKeyConstraints();

            $this->info("Data repair berhasil direset untuk tenant: {$tenantId}.");
        });

        $this->info("Selesai! Semua stok terkait repair telah dikembalikan dan tabel telah di-truncate.");
    }
}
