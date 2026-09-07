<?php

namespace App\Filament\Concerns;

use App\Models\Module;
use Livewire\Attributes\Url;

/**
 * Mantiene el módulo activo (por slug) en la URL y a través de las
 * peticiones de Livewire, para que el formulario/tabla dinámicos sepan
 * qué módulo están gestionando.
 */
trait HasModuleContext
{
    #[Url]
    public ?string $module = null;

    public function currentModule(): ?Module
    {
        return $this->module ? Module::firstWhere('slug', $this->module) : null;
    }
}
