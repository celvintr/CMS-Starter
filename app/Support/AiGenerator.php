<?php

namespace App\Support;

use App\Models\SiteSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Genera módulos y plantillas con IA a partir de una descripción en lenguaje natural.
 * El resultado se valida y se sanea antes de convertirse en un pack importable.
 */
class AiGenerator
{
    public const MODULE_TYPES = ['generico', 'tienda', 'galeria', 'equipo', 'servicios', 'faq', 'formulario'];
    public const FIELD_TYPES = ['text', 'textarea', 'richtext', 'email', 'number', 'boolean', 'date', 'select', 'image', 'gallery'];
    public const BLOCK_TYPES = ['hero', 'richtext', 'features', 'image_text', 'cta', 'contact', 'gallery', 'module_list', 'form', 'testimonios', 'precios', 'faq', 'mapa', 'video', 'stats'];
    public const FEATURE_ICONS = ['bolt', 'shield', 'sparkles', 'check', 'truck', 'clock', 'heart', 'star', 'chat', 'phone', 'tag', 'cube'];

    /**
     * @return array El pack (envelope) listo para Pack::importModule/importPage.
     */
    public static function generate(string $kind, string $prompt): array
    {
        $settings = SiteSetting::current();
        $key = $settings->ai_api_key;

        if (! $key) {
            throw new RuntimeException('Primero configura tu API de IA en Ajustes del sitio.');
        }

        $provider = $settings->ai_provider ?: 'openrouter';
        $model = $settings->ai_model ?: self::defaultModel($provider);
        [$endpoint, $headers] = self::endpoint($provider, $key);

        $response = Http::withHeaders($headers)
            ->timeout(90)
            ->acceptJson()
            ->post($endpoint, [
                'model' => $model,
                'temperature' => 0.4,
                'response_format' => ['type' => 'json_object'],
                'messages' => [
                    ['role' => 'system', 'content' => self::systemPrompt($kind)],
                    ['role' => 'user', 'content' => trim($prompt)],
                ],
            ]);

        if ($response->status() === 401) {
            throw new RuntimeException('La API rechazó tu llave (401). Revísala en Ajustes.');
        }
        if (! $response->successful()) {
            throw new RuntimeException('Error de la IA (' . $response->status() . '). Intenta de nuevo.');
        }

        $content = data_get($response->json(), 'choices.0.message.content');
        if (! is_string($content) || $content === '') {
            throw new RuntimeException('La IA no devolvió contenido utilizable.');
        }

        $data = self::extractJson($content);
        if (! is_array($data)) {
            throw new RuntimeException('La IA no devolvió un JSON válido. Intenta reformular.');
        }

        return $kind === 'page-template'
            ? self::toTemplatePack($data)
            : self::toModulePack($data);
    }

    // ---------- Proveedores ----------

    protected static function endpoint(string $provider, string $key): array
    {
        return match ($provider) {
            'openai' => ['https://api.openai.com/v1/chat/completions', ['Authorization' => "Bearer {$key}"]],
            default => ['https://openrouter.ai/api/v1/chat/completions', [
                'Authorization' => "Bearer {$key}",
                'X-Title' => 'CMS Starter',
            ]],
        };
    }

    protected static function defaultModel(string $provider): string
    {
        return $provider === 'openai' ? 'gpt-4o-mini' : 'openai/gpt-4o-mini';
    }

    // ---------- Prompts ----------

    protected static function systemPrompt(string $kind): string
    {
        if ($kind === 'page-template') {
            $blocks = <<<TXT
- hero: {"heading","subheading","image":null,"button_text","button_url"}
- richtext: {"content": "HTML"}
- features: {"heading","items":[{"icon","title","text"}]} — icon uno de: bolt, shield, sparkles, check, truck, clock, heart, star, chat, phone, tag, cube
- image_text: {"image":null,"image_side":"left" u "right","heading","text":"HTML"}
- cta: {"heading","text","button_text","button_url"}
- contact: {"heading","subheading"}
TXT;

            return "Eres un generador de plantillas de página para un CMS por bloques. "
                . "Devuelve ÚNICAMENTE un objeto JSON válido (sin markdown ni explicaciones) con la forma: "
                . '{"title": string, "content": [ bloques ]}. '
                . "Cada bloque es {\"type\": string, \"data\": { ... }}. Tipos de bloque y su data:\n{$blocks}\n"
                . "Reglas: pon todo campo de imagen en null. Usa 3 items en features. "
                . "Escribe todos los textos en español. No inventes tipos de bloque distintos a los listados.";
        }

        $types = implode(', ', self::MODULE_TYPES);
        $fieldTypes = implode(', ', self::FIELD_TYPES);

        return "Eres un generador de módulos de contenido para un CMS. "
            . "Devuelve ÚNICAMENTE un objeto JSON válido (sin markdown ni explicaciones) con la forma: "
            . '{"name","slug","type","singular_label","plural_label","icon","is_public","fields":[{"key","label","type","required","options"}]}. '
            . "Valores permitidos: type uno de [{$types}]. "
            . "Cada field.type uno de [{$fieldTypes}]. "
            . "field.key en snake_case sin espacios. field.options solo para type 'select' (opciones separadas por coma), si no, cadena vacía. "
            . "icon es un nombre de Heroicons v2 outline (ej: heroicon-o-shopping-bag). "
            . "Usa 'tienda' para catálogos con precio, 'formulario' para captura de datos, 'galeria' para imágenes, 'faq' para preguntas. "
            . "Escribe los label en español. slug en kebab-case.";
    }

    // ---------- Parseo y saneo ----------

    protected static function extractJson(string $content): mixed
    {
        $content = trim($content);

        // Quitar ```json ... ``` si viene con fences
        if (Str::startsWith($content, '```')) {
            $content = preg_replace('/^```[a-zA-Z]*\s*/', '', $content);
            $content = preg_replace('/```\s*$/', '', $content);
            $content = trim($content);
        }

        $decoded = json_decode($content, true);
        if (is_array($decoded)) {
            return $decoded;
        }

        // Último recurso: recortar del primer { al último }
        $start = strpos($content, '{');
        $end = strrpos($content, '}');
        if ($start !== false && $end !== false && $end > $start) {
            return json_decode(substr($content, $start, $end - $start + 1), true);
        }

        return null;
    }

    protected static function toModulePack(array $d): array
    {
        $name = trim((string) ($d['name'] ?? 'Módulo')) ?: 'Módulo';
        $type = in_array($d['type'] ?? '', self::MODULE_TYPES, true) ? $d['type'] : 'generico';

        $fields = [];
        foreach ($d['fields'] ?? [] as $f) {
            $label = trim((string) ($f['label'] ?? ($f['key'] ?? '')));
            if ($label === '' && empty($f['key'])) {
                continue;
            }
            $key = Str::slug((string) ($f['key'] ?? $label), '_') ?: 'campo';
            $ftype = in_array($f['type'] ?? '', self::FIELD_TYPES, true) ? $f['type'] : 'text';

            $fields[] = [
                'key' => $key,
                'label' => $label ?: $key,
                'type' => $ftype,
                'required' => (bool) ($f['required'] ?? false),
                'options' => is_string($f['options'] ?? null) ? $f['options'] : '',
            ];
        }

        if (empty($fields)) {
            $fields[] = ['key' => 'descripcion', 'label' => 'Descripción', 'type' => 'richtext', 'required' => false, 'options' => ''];
        }

        $data = [
            'name' => $name,
            'slug' => Str::slug((string) ($d['slug'] ?? $name)) ?: 'modulo',
            'type' => $type,
            'singular_label' => $d['singular_label'] ?? Str::singular($name),
            'plural_label' => $d['plural_label'] ?? $name,
            'icon' => is_string($d['icon'] ?? null) && Str::startsWith($d['icon'], 'heroicon-') ? $d['icon'] : \App\Models\Module::iconFor($type),
            'is_public' => (bool) ($d['is_public'] ?? false),
            'fields' => $fields,
        ];

        return ['cms' => 'cms-starter', 'type' => 'module', 'version' => 1, 'data' => $data];
    }

    protected static function toTemplatePack(array $d): array
    {
        $blocks = [];
        foreach ($d['content'] ?? [] as $block) {
            $type = $block['type'] ?? null;
            if (! in_array($type, self::BLOCK_TYPES, true)) {
                continue; // descarta bloques desconocidos
            }
            $blocks[] = ['type' => $type, 'data' => is_array($block['data'] ?? null) ? $block['data'] : []];
        }

        if (empty($blocks)) {
            throw new RuntimeException('La IA no generó bloques válidos. Intenta reformular.');
        }

        $data = [
            'title' => trim((string) ($d['title'] ?? 'Página')) ?: 'Página',
            'content' => $blocks,
        ];

        return ['cms' => 'cms-starter', 'type' => 'page-template', 'version' => 1, 'data' => $data];
    }
}
