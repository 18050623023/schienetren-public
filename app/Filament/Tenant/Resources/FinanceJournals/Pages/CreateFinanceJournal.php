<?php

namespace App\Filament\Tenant\Resources\FinanceJournals\Pages;

use App\Filament\Tenant\Resources\FinanceJournals\FinanceJournalResource;
use Filament\Resources\Pages\CreateRecord;

use App\Models\Tenant\FinanceJournal;
use Carbon\Carbon;

class CreateFinanceJournal extends CreateRecord
{
    protected static string $resource = FinanceJournalResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (empty($data['reference_number'])) {
            // $prefix = 'JRN-' . Carbon::parse($data['date'] ?? now())->format('Ym');
            $prefixCode = rtrim(settingTenant('journal_prefix', 'JRN'), '-');
            $prefix = $prefixCode . '-' . Carbon::parse($data['date'] ?? now())->format('Ym');
            $lastJournal = FinanceJournal::where('reference_number', 'like', "{$prefix}-%")->orderBy('id', 'desc')->first();

            $nextSeq = 1;
            if ($lastJournal) {
                $lastSeq = (int) substr($lastJournal->reference_number, -4);
                $nextSeq = $lastSeq + 1;
            }

            $data['reference_number'] = $prefix . '-' . str_pad($nextSeq, 4, '0', STR_PAD_LEFT);
        }

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
