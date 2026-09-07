<?php

namespace App\Models\Concerns;

/**
 * Devuelve el valor de un campo en el idioma actual, con respaldo al idioma base.
 * Las traducciones se guardan en el JSON `translations` como {locale: {campo: valor}}.
 */
trait HasTranslations
{
    public function t(string $field, ?string $locale = null): mixed
    {
        $locale = $locale ?: app()->getLocale();
        $translations = $this->translations ?? [];
        $value = $translations[$locale][$field] ?? null;

        if ($value === null || $value === '' || $value === []) {
            return $this->{$field};
        }

        return $value;
    }
}
