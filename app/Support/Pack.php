<?php

namespace App\Support;

use App\Models\Module;
use App\Models\Page;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Empaqueta (exporta) e integra (importa) módulos y plantillas de página
 * como archivos JSON portables entre sitios. La base de un ecosistema de packs.
 */
class Pack
{
    public const VERSION = 1;

    protected static function envelope(string $type, array $data): array
    {
        return [
            'cms' => 'cms-starter',
            'type' => $type,
            'version' => self::VERSION,
            'exported_at' => now()->toIso8601String(),
            'data' => $data,
        ];
    }

    public static function toJson(array $pack): string
    {
        return json_encode($pack, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    // ---------- Módulos ----------

    public static function exportModule(Module $module, bool $withEntries = false): array
    {
        $data = [
            'name' => $module->name,
            'slug' => $module->slug,
            'type' => $module->type,
            'singular_label' => $module->singular_label,
            'plural_label' => $module->plural_label,
            'icon' => $module->icon,
            'is_public' => $module->is_public,
            'fields' => $module->fields,
        ];

        if ($withEntries) {
            $data['entries'] = $module->entries()
                ->get(['title', 'slug', 'data', 'is_published', 'sort_order'])
                ->toArray();
        }

        return self::envelope('module', $data);
    }

    public static function importModule(array $pack): Module
    {
        $d = $pack['data'] ?? [];

        $module = Module::create([
            'name' => $d['name'] ?? 'Módulo importado',
            'slug' => self::uniqueSlug(Module::class, $d['slug'] ?? Str::slug($d['name'] ?? 'modulo')),
            'type' => $d['type'] ?? 'generico',
            'singular_label' => $d['singular_label'] ?? null,
            'plural_label' => $d['plural_label'] ?? null,
            'icon' => $d['icon'] ?? 'heroicon-o-rectangle-stack',
            'is_public' => $d['is_public'] ?? false,
            'fields' => $d['fields'] ?? [],
        ]);

        foreach ($d['entries'] ?? [] as $entry) {
            $module->entries()->create([
                'title' => $entry['title'] ?? 'Registro',
                'slug' => $entry['slug'] ?? Str::slug($entry['title'] ?? 'registro'),
                'data' => $entry['data'] ?? [],
                'is_published' => $entry['is_published'] ?? true,
                'sort_order' => $entry['sort_order'] ?? 0,
            ]);
        }

        return $module;
    }

    // ---------- Plantillas de página ----------

    public static function exportPage(Page $page): array
    {
        return self::envelope('page-template', [
            'title' => $page->title,
            'content' => $page->content ?? [],
        ]);
    }

    public static function importPage(array $pack, ?string $title = null): Page
    {
        $d = $pack['data'] ?? [];
        $title = $title ?: ($d['title'] ?? 'Página importada');

        return Page::create([
            'title' => $title,
            'slug' => self::uniqueSlug(Page::class, Str::slug($title)),
            'content' => $d['content'] ?? [],
            'is_published' => true,
            'show_in_menu' => true,
        ]);
    }

    // ---------- Utilidades ----------

    /**
     * Valida el sobre de un pack y devuelve el array, o null si no es válido.
     */
    public static function parse(?string $json, string $expectedType): ?array
    {
        $pack = json_decode((string) $json, true);

        if (! is_array($pack) || ($pack['type'] ?? null) !== $expectedType) {
            return null;
        }

        return $pack;
    }

    protected static function uniqueSlug(string $model, string $base): string
    {
        $base = $base ?: 'item';
        $slug = $base;
        $i = 1;

        while ($model::where('slug', $slug)->exists()) {
            $slug = $base . '-' . $i++;
        }

        return $slug;
    }
}
