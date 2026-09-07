<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class Module extends Model
{
    protected $fillable = [
        'name', 'slug', 'type', 'singular_label', 'plural_label',
        'icon', 'is_public', 'fields', 'sort_order',
    ];

    /**
     * Tipos de módulo disponibles (define ícono, campos preset y estilo en el frontend).
     */
    public static function types(): array
    {
        return [
            'generico' => 'Contenido genérico',
            'tienda' => 'Tienda / Catálogo',
            'galeria' => 'Galería',
            'equipo' => 'Equipo / Personas',
            'servicios' => 'Servicios',
            'faq' => 'Preguntas frecuentes',
            'formulario' => 'Formulario (captura datos)',
        ];
    }

    /**
     * Tipos que tienen lógica propia (no son solo contenido para mostrar).
     */
    public function isForm(): bool
    {
        return $this->type === 'formulario';
    }

    /**
     * Íconos sugeridos por tipo.
     */
    public static function iconFor(string $type): string
    {
        return [
            'tienda' => 'heroicon-o-shopping-bag',
            'galeria' => 'heroicon-o-photo',
            'equipo' => 'heroicon-o-user-group',
            'servicios' => 'heroicon-o-briefcase',
            'faq' => 'heroicon-o-question-mark-circle',
            'formulario' => 'heroicon-o-clipboard-document-list',
        ][$type] ?? 'heroicon-o-rectangle-stack';
    }

    /**
     * Campos preset según el tipo, para acelerar la creación.
     */
    public static function fieldPresets(string $type): array
    {
        return match ($type) {
            'tienda' => [
                ['key' => 'imagen', 'label' => 'Imagen', 'type' => 'image', 'required' => false, 'options' => ''],
                ['key' => 'precio', 'label' => 'Precio', 'type' => 'number', 'required' => false, 'options' => ''],
                ['key' => 'categoria', 'label' => 'Categoría', 'type' => 'select', 'required' => false, 'options' => 'General'],
                ['key' => 'descripcion', 'label' => 'Descripción', 'type' => 'textarea', 'required' => false, 'options' => ''],
            ],
            'galeria' => [
                ['key' => 'imagenes', 'label' => 'Imágenes', 'type' => 'gallery', 'required' => false, 'options' => ''],
                ['key' => 'descripcion', 'label' => 'Descripción', 'type' => 'textarea', 'required' => false, 'options' => ''],
            ],
            'equipo' => [
                ['key' => 'foto', 'label' => 'Foto', 'type' => 'image', 'required' => false, 'options' => ''],
                ['key' => 'cargo', 'label' => 'Cargo', 'type' => 'text', 'required' => false, 'options' => ''],
                ['key' => 'bio', 'label' => 'Biografía', 'type' => 'textarea', 'required' => false, 'options' => ''],
            ],
            'servicios' => [
                ['key' => 'icono', 'label' => 'Ícono (emoji)', 'type' => 'text', 'required' => false, 'options' => ''],
                ['key' => 'descripcion', 'label' => 'Descripción', 'type' => 'richtext', 'required' => false, 'options' => ''],
            ],
            'faq' => [
                ['key' => 'respuesta', 'label' => 'Respuesta', 'type' => 'richtext', 'required' => true, 'options' => ''],
            ],
            'formulario' => [
                ['key' => 'nombre', 'label' => 'Nombre', 'type' => 'text', 'required' => true, 'options' => ''],
                ['key' => 'email', 'label' => 'Correo', 'type' => 'email', 'required' => false, 'options' => ''],
                ['key' => 'telefono', 'label' => 'Teléfono', 'type' => 'text', 'required' => false, 'options' => ''],
                ['key' => 'mensaje', 'label' => 'Mensaje', 'type' => 'textarea', 'required' => false, 'options' => ''],
            ],
            default => [
                ['key' => 'descripcion', 'label' => 'Descripción', 'type' => 'richtext', 'required' => false, 'options' => ''],
            ],
        };
    }

    protected $casts = [
        'fields' => 'array',
        'is_public' => 'boolean',
    ];

    public function entries(): HasMany
    {
        return $this->hasMany(Entry::class);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * Lista normalizada de campos (el Repeater los guarda con claves UUID).
     */
    public function fieldList(): Collection
    {
        return collect($this->fields ?? [])->values()->filter(fn ($f) => ! empty($f['key']));
    }

    public function pluralLabel(): string
    {
        return $this->plural_label ?: $this->name;
    }

    public function singularLabel(): string
    {
        return $this->singular_label ?: $this->name;
    }
}
