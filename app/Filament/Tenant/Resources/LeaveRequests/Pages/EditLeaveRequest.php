<?php

namespace App\Filament\Tenant\Resources\LeaveRequests\Pages;

use App\Filament\Tenant\Resources\LeaveRequests\LeaveRequestResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class EditLeaveRequest extends EditRecord
{
    protected static string $resource = LeaveRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    // mutate data before edit
    protected function mutateFormDataBeforeFill(array $data): array
    {
        return $data;
    }

    // protected function afterSave(): void
    // {
    //     $files = $this->data['attachments'] ?? [];
    //     $names = $this->data['original_names'] ?? [];

    //     $this->record->attachments()->delete();

    //     $this->record->attachments()->createMany(
    //         collect($files)->map(fn ($path) => [
    //             'file_name' => $names[$path] ?? null,
    //             'file_path' => $path,
    //             'created_by' => Auth::user()->id,
    //         ])->toArray()
    //     );
    //     // dd($this->record->attachments()->get());
    // }

    protected function afterSave(): void
    {
        $newFiles = $this->data['attachments'] ?? [];
        $names = $this->data['original_names'] ?? [];

        $oldFiles = $this->record
            ->attachments()
            ->pluck('file_path')
            ->toArray();

        $deleted = array_diff($oldFiles, $newFiles);
        $added = array_diff($newFiles, $oldFiles);

        // hapus file yang dibuang user
        foreach ($deleted as $path) {
            Storage::delete($path);

            $this->record
                ->attachments()
                ->where('file_path', $path)
                ->delete();
        }

        // tambah file baru
        $this->record
            ->attachments()
            ->createMany(
                collect($added)->map(fn ($path) => [
                    'file_name' => $names[$path] ?? null,
                    'file_path' => $path,
                    'created_by' => Auth::id(),
                ])->toArray()
            );
    }
}
