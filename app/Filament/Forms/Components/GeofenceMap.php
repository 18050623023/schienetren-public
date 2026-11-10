<?php

namespace App\Filament\Forms\Components;

use App\Services\AttendanceService;
use App\Services\GeoLocationService;
use Closure;
use Filament\Forms\Components\Field;
use Filament\Support\Assets\Js;
use Filament\Support\Facades\FilamentAsset;
use Illuminate\Support\Facades\Vite;

class GeofenceMap extends Field
{
    protected string $view = 'filament.forms.components.geofence-map';

    // 1. Definisikan properti dengan nilai default
    protected mixed $config = [];

    protected $permanentCircle = false;

    protected $latCircle = null;
    protected $lngCircle = null;

    protected ?string $latitudeField = null;
    protected ?string $longitudeField = null;
    protected ?string $distanceField = null;



    /* Config Array
        [
            'mode' => 'admin || attendance || default',
            'zoom' => int,
            'center' => [lat, lng],
            'height' => int PX
        ]
    */

    public function formatDistance(?float $distance): string
    {
        return app(GeoLocationService::class)->formatDistance($distance);
    }

    public function config(array | Closure $config): static
    {
        $this->config = $config;

        return $this;
    }

    public function getConfig(): array
    {
        return $this->evaluate($this->config);
    }

    public function distanceField(string $field): static
    {
        $this->distanceField = $field;

        return $this;
    }

    public function latitudeField(string $field): static
    {
        $this->latitudeField = $field;

        return $this;
    }

    public function longitudeField(string $field): static
    {

        $this->longitudeField = $field;

        return $this;
    }

    public function permanentCircle(mixed $latOrClosure = true, mixed $lng = null): static
    {
        if ($latOrClosure instanceof Closure) {
            $this->permanentCircle = $latOrClosure;
            $this->latCircle = null;
            $this->lngCircle = null;
        } elseif ($lng !== null) {
            $this->permanentCircle = true;
            $this->latCircle = $latOrClosure;
            $this->lngCircle = $lng;
        } else {
            $this->permanentCircle = $latOrClosure;
            $this->latCircle = null;
            $this->lngCircle = null;
        }

        return $this;
    }

    // 3. Masukkan properti ke dalam viewData agar dikirim ke Blade
    public function getViewData(): array
    {

        // 1. Jalankan/evaluasi closure permanentCircle terlebih dahulu
        $resolvedCircle = $this->evaluate($this->permanentCircle);

        $permanentCircleValue = false;
        $lat = $this->evaluate($this->latCircle) ?? settingTenant('default_latitude', '-7.258137');
        $lng = $this->evaluate($this->lngCircle) ?? settingTenant('default_longitude', '112.749306');
        $rad = null;

        // 2. JIKA CLOSURE MENGEMBALIKAN ARRAY (Berisi Lat & Lng)
        if (is_array($resolvedCircle)) {
            $permanentCircleValue = true; // Otomatis aktifkan lingkaran jika array terisi
            $lat = $resolvedCircle['lat'] ?? $resolvedCircle[0] ?? $lat; // Fallback ke $lat (default tenant) jika null
            $lng = $resolvedCircle['lng'] ?? $resolvedCircle[1] ?? $lng; // Fallback ke $lng (default tenant) jika null
            $rad = $resolvedCircle['rad'] ?? $resolvedCircle[2] ?? null;
            // dd($resolvedCircle);
        }
        // 3. JIKA CLOSURE HANYA MENGEMBALIKAN BOOLEAN (true/false)
        else {
            $permanentCircleValue = (bool) $resolvedCircle;
        }
        // dd(parent::getViewData());
        return array_merge(parent::getViewData(), [
            'config' => $this->getConfig(),
            'rootStatePath' => explode('.', $this->getStatePath())[0],
            'permanentCircle' => $permanentCircleValue,
            'latCircle' => $lat,
            'lngCircle' => $lng,
            'radCircle' => $rad,
            'latitudeField' => $this->latitudeField,
            'longitudeField' => $this->longitudeField,
            'distanceField' => $this->distanceField
        ]);
    }
}
