<?php

namespace App\Services;

use App\Models\Tenant\FinanceAccount;
use App\Models\Tenant\FinanceAccountBalance;
use App\Models\Tenant\FinanceJournal;
use App\Models\Tenant\FinanceJournalItem;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FinanceAccountingService
{
    /**
     * Create a new balanced journal entry
     *
     * @param int $branchId (ID Cabang / Cost Center)
     * @param string $date (Y-m-d)
     * @param string $description
     * @param array $items Array of ['finance_account_id' => int, 'debit' => float, 'credit' => float, 'description' => string|null]
     * @param Model|null $journalable (Opsional: Model asal transaksi misal RepairPayment)
     * @param int|null $userId
     * @return FinanceJournal
     * @throws Exception
     */
    public function createJournalEntry(int $branchId, string $date, string $description, array $items, ?Model $journalable = null, ?int $userId = null): FinanceJournal
    {
        $totalDebit = 0;
        $totalCredit = 0;

        foreach ($items as $item) {
            $totalDebit += (float) ($item['debit'] ?? 0);
            $totalCredit += (float) ($item['credit'] ?? 0);
        }

        // Validasi Balance (Double-Entry check)
        // Menggunakan abs() dan epsilon 0.001 untuk menghindari isu presisi desimal
        if (abs($totalDebit - $totalCredit) > 0.001) {
            throw new Exception("Jurnal tidak seimbang (Unbalanced). Total Debit: {$totalDebit}, Total Kredit: {$totalCredit}");
        }

        if ($totalDebit == 0) {
            throw new Exception("Nilai transaksi tidak boleh nol.");
        }

        return DB::transaction(function () use ($branchId, $date, $description, $items, $journalable, $userId) {
            // ponytail: Idempotency guard untuk relasi polymorphic 1-to-1 (e.g. RepairPayment, HrPayroll)
            if ($journalable && $journalable->exists) {
                $existingJournal = FinanceJournal::where('journalable_type', $journalable->getMorphClass())
                    ->where('journalable_id', $journalable->getKey())
                    ->lockForUpdate()
                    ->first();

                if ($existingJournal) {
                    return $existingJournal;
                }
            }

            // Generate Nomor Jurnal Otomatis (Contoh: JRN-202610-0001)
            $prefixCode = rtrim(settingTenant('journal_prefix', 'JRN'), '-');
            $prefix = $prefixCode . '-' . Carbon::parse($date)->format('Ym');
            $lastJournal = FinanceJournal::where('reference_number', 'like', "{$prefix}-%")->orderBy('id', 'desc')->lockForUpdate()->first();

            $nextSeq = 1;
            if ($lastJournal) {
                $lastSeq = (int) substr($lastJournal->reference_number, -4);
                $nextSeq = $lastSeq + 1;
            }

            $referenceNumber = $prefix . '-' . str_pad($nextSeq, 4, '0', STR_PAD_LEFT);

            // Insert Header
            $journal = new FinanceJournal();
            $journal->branch_id = $branchId;
            $journal->date = $date;
            $journal->reference_number = $referenceNumber;
            $journal->description = $description;
            $journal->created_by = $userId ?? Auth::id();

            if ($journalable) {
                $journal->journalable()->associate($journalable);
            }

            $journal->save();

            // Insert Details (Items)
            foreach ($items as $item) {
                $journal->items()->create([
                    'branch_id' => $branchId,
                    'finance_account_id' => $item['finance_account_id'],
                    'debit' => $item['debit'] ?? 0,
                    'credit' => $item['credit'] ?? 0,
                    'description' => $item['description'] ?? null,
                ]);
            }

            return $journal;
        });
    }

    /**
     * Hitung saldo suatu akun secara real-time berdasarkan riwayat mutasinya.
     * Logika Arah Saldo (Normal Balance) diterapkan di sini.
     */
    public function getAccountBalance($accountId, $branchId = null, $startDate = null, $endDate = null): float
    {
        $account = FinanceAccount::with('category')->findOrFail($accountId);

        $query = FinanceJournalItem::where('finance_account_id', $accountId);

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        if ($startDate) {
            $query->whereHas('journal', function ($q) use ($startDate) {
                $q->where('date', '>=', $startDate);
            });
        }

        if ($endDate) {
            $query->whereHas('journal', function ($q) use ($endDate) {
                $q->where('date', '<=', $endDate);
            });
        }

        $sums = $query->selectRaw('SUM(debit) as total_debit, SUM(credit) as total_credit')->first();

        $totalDebit = (float) ($sums->total_debit ?? 0);
        $totalCredit = (float) ($sums->total_credit ?? 0);

        // Aturan Akuntansi Dasar:
        // Aset & Beban bertambah di Debit -> Saldo = Debit - Kredit
        // Kewajiban, Ekuitas & Pendapatan bertambah di Kredit -> Saldo = Kredit - Debit
        $type = $account->category->type ?? 'asset';

        if (in_array($type, ['asset', 'expense'])) {
            return $totalDebit - $totalCredit;
        } else {
            return $totalCredit - $totalDebit;
        }
    }

    /**
     * Proses Tutup Buku Bulanan (Mengunci Saldo Akhir)
     * @param string $period Format 'Y-m' contoh: '2026-10'
     */
    public function closeAccountingPeriod(string $period)
    {
        // 1. Tentukan rentang tanggal bulan tersebut
        $carbonDate = Carbon::createFromFormat('Y-m', $period);
        $startDate = $carbonDate->copy()->startOfMonth()->format('Y-m-d');
        $endDate = $carbonDate->copy()->endOfMonth()->format('Y-m-d');

        // 2. Tentukan periode sebelumnya untuk tarik saldo awal
        $previousPeriod = $carbonDate->copy()->subMonth()->format('Y-m');

        return DB::transaction(function () use ($period, $previousPeriod, $startDate, $endDate) {
            $accounts = FinanceAccount::with('category')->get();
            $results = [];

            foreach ($accounts as $account) {
                // Ambil Saldo Akhir bulan lalu sebagai Saldo Awal bulan ini
                $prevBalance = FinanceAccountBalance::where('finance_account_id', $account->id)
                    ->where('period', $previousPeriod)
                    ->first();

                $openingBalance = $prevBalance ? $prevBalance->closing_balance : 0;

                // Hitung total mutasi selama bulan INI saja
                $sums = FinanceJournalItem::where('finance_account_id', $account->id)
                    ->whereHas('journal', function ($q) use ($startDate, $endDate) {
                        $q->whereBetween('date', [$startDate, $endDate]);
                    })
                    ->selectRaw('SUM(debit) as total_debit, SUM(credit) as total_credit')
                    ->first();

                $totalDebit = (float) ($sums->total_debit ?? 0);
                $totalCredit = (float) ($sums->total_credit ?? 0);

                // Hitung Saldo Akhir bulan ini
                $type = $account->category->type ?? 'asset';
                if (in_array($type, ['asset', 'expense'])) {
                    $mutation = $totalDebit - $totalCredit;
                } else {
                    $mutation = $totalCredit - $totalDebit;
                }

                $closingBalance = $openingBalance + $mutation;

                // Simpan atau Update ke tabel balance
                $balanceRecord = FinanceAccountBalance::updateOrCreate(
                    [
                        'finance_account_id' => $account->id,
                        'period' => $period
                    ],
                    [
                        'opening_balance' => $openingBalance,
                        'total_debit' => $totalDebit,
                        'total_credit' => $totalCredit,
                        'closing_balance' => $closingBalance
                    ]
                );

                $results[] = $balanceRecord;
            }

            return $results;
        });
    }
}
