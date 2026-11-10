<?php

namespace App\Services\Stock;

use App\Models\Tenant\EmployeeAssignment;
use App\Models\Tenant\StockMovement;
use App\Models\Tenant\User;
use Carbon\Carbon;
use Exception;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class StockMovementService
{
    public static function create(
        Model $source,
        array $attributes,
        array $serialIds = []
    ): StockMovement {

        $movement = $source->stockMovements()->create($attributes);

        if (! empty($serialIds)) {
            $movement->productSerials()
                ->syncWithoutDetaching($serialIds);
        }

        return $movement;
    }

    public static function updateOrCreate(
        Model $source,
        array $attributes,
        array $serialIds = []
    ): StockMovement {
        
        $movement = $source->stockMovements()->first();

        if ($movement) {
            $movement->update($attributes);
            
            if (! empty($serialIds)) {
                $movement->productSerials()->sync($serialIds);
            } else {
                $movement->productSerials()->detach();
            }
        } else {
            $movement = self::create($source, $attributes, $serialIds);
        }

        return $movement;
    }


}

?>
