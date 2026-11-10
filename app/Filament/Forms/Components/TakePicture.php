<?php

namespace App\Filament\Forms\Components;

use Closure;
use Filament\Forms\Components\Field;

class TakePicture extends Field
{
    // protected string $view = 'filament.forms.components.take-picture-pop-up';
    protected string $view = 'filament.forms.components.take-picture';

    protected mixed $config = [];
    protected bool $showCameraSelector = true;
    protected bool $showPreview = true;



    /* Config Array
        [
            'label' => 'Take Picture',
            'header' => 'Take Picture',
            'placeholder' => 'Open Camera',
            'capture' => 'Capture Photo',
            'aspectRatio' => '16:9',
            ]
    */
    /* Method Avilable
        ->showPreview(closure, any)
        ->showCameraSelector(closure, any)
    */
    public function config(array | Closure $config): static
    {
        $this->config = $config;

        return $this;
    }

    public function getConfig(?string $key = null): array|string|null
    {
        $this->config['label'] = $this->getLabel();
        $this->config['header'] = 'Take Picture';
        $this->config['placeholder'] = 'Open Camera';
        $this->config['capture'] = 'Capture Photo';
        $this->config['aspectRatio'] = '16:9';

        if ($key === null) {
            return $this->config;
        }

        return $this->evaluate($this->config[$key] ?? null);
    }

    public function showCameraSelector(bool $show = true): static
    {
        $this->showCameraSelector = $show;
        return $this;
    }

    public function getShowCameraSelector(): bool
    {
        return $this->showCameraSelector;
    }


    public function showPreview(bool $show = true): static
    {
        $this->showPreview = $show;
        return $this;
    }

    public function getShowPreview(): bool
    {
        return $this->showPreview;
    }


    // 3. Masukkan properti ke dalam viewData agar dikirim ke Blade
    public function getViewData(): array
    {

        return array_merge(parent::getViewData(), [
            'config' => $this->getConfig(),
            // 'label' => $this->getLabel(),
        ]);
    }

}
