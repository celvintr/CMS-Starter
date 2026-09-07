<?php

namespace App\Support;

/**
 * Biblioteca de packs: catálogo curado de módulos y plantillas listos para
 * importar de un clic. Además lee packs sueltos (.json) de resources/packs/,
 * para que cualquiera pueda ampliar la biblioteca dejando archivos.
 */
class PackLibrary
{
    public static function all(): array
    {
        return array_merge(self::curated(), self::fromDisk());
    }

    public static function find(string $key): ?array
    {
        foreach (self::all() as $entry) {
            if ($entry['key'] === $key) {
                return $entry;
            }
        }

        return null;
    }

    // ---------- Helpers de construcción ----------

    protected static function field(string $key, string $label, string $type, bool $required = false, string $options = ''): array
    {
        return compact('key', 'label', 'type', 'required', 'options');
    }

    protected static function moduleEntry(string $key, string $label, string $desc, string $icon, array $data): array
    {
        return [
            'key' => $key,
            'label' => $label,
            'description' => $desc,
            'category' => 'Módulos',
            'icon' => $icon,
            'type' => 'module',
            'pack' => ['cms' => 'cms-starter', 'type' => 'module', 'version' => 1, 'data' => $data],
        ];
    }

    protected static function templateEntry(string $key, string $label, string $desc, string $icon, string $title, array $blocks): array
    {
        return [
            'key' => $key,
            'label' => $label,
            'description' => $desc,
            'category' => 'Plantillas',
            'icon' => $icon,
            'type' => 'page-template',
            'pack' => ['cms' => 'cms-starter', 'type' => 'page-template', 'version' => 1, 'data' => ['title' => $title, 'content' => $blocks]],
        ];
    }

    // ---------- Catálogo curado ----------

    protected static function curated(): array
    {
        return [
            // === Módulos ===
            self::moduleEntry('lib-productos', 'Productos (Tienda)',
                'Catálogo con imagen, precio y categoría. Funciona con el carrito por WhatsApp.',
                'heroicon-o-shopping-bag', [
                    'name' => 'Productos', 'slug' => 'productos', 'type' => 'tienda',
                    'singular_label' => 'Producto', 'plural_label' => 'Productos',
                    'icon' => 'heroicon-o-shopping-bag', 'is_public' => true,
                    'fields' => [
                        self::field('imagen', 'Imagen', 'image'),
                        self::field('precio', 'Precio', 'number'),
                        self::field('categoria', 'Categoría', 'select', false, 'General'),
                        self::field('descripcion', 'Descripción', 'textarea'),
                    ],
                ]),

            self::moduleEntry('lib-servicios', 'Servicios',
                'Lista de servicios con resumen y descripción con formato.',
                'heroicon-o-briefcase', [
                    'name' => 'Servicios', 'slug' => 'servicios', 'type' => 'servicios',
                    'singular_label' => 'Servicio', 'plural_label' => 'Servicios',
                    'icon' => 'heroicon-o-briefcase', 'is_public' => true,
                    'fields' => [
                        self::field('resumen', 'Resumen', 'text'),
                        self::field('descripcion', 'Descripción', 'richtext'),
                        self::field('imagen', 'Imagen', 'image'),
                    ],
                ]),

            self::moduleEntry('lib-equipo', 'Equipo',
                'Miembros del equipo con foto, cargo y biografía.',
                'heroicon-o-user-group', [
                    'name' => 'Equipo', 'slug' => 'equipo', 'type' => 'equipo',
                    'singular_label' => 'Miembro', 'plural_label' => 'Equipo',
                    'icon' => 'heroicon-o-user-group', 'is_public' => true,
                    'fields' => [
                        self::field('foto', 'Foto', 'image'),
                        self::field('cargo', 'Cargo', 'text'),
                        self::field('bio', 'Biografía', 'textarea'),
                    ],
                ]),

            self::moduleEntry('lib-testimonios', 'Testimonios',
                'Opiniones de clientes con foto, cargo y comentario.',
                'heroicon-o-chat-bubble-left-right', [
                    'name' => 'Testimonios', 'slug' => 'testimonios', 'type' => 'generico',
                    'singular_label' => 'Testimonio', 'plural_label' => 'Testimonios',
                    'icon' => 'heroicon-o-chat-bubble-left-right', 'is_public' => false,
                    'fields' => [
                        self::field('foto', 'Foto', 'image'),
                        self::field('cargo', 'Cargo / Empresa', 'text'),
                        self::field('texto', 'Comentario', 'textarea', true),
                    ],
                ]),

            self::moduleEntry('lib-faq', 'Preguntas frecuentes',
                'Preguntas y respuestas con editor de texto.',
                'heroicon-o-question-mark-circle', [
                    'name' => 'Preguntas frecuentes', 'slug' => 'faq', 'type' => 'faq',
                    'singular_label' => 'Pregunta', 'plural_label' => 'Preguntas frecuentes',
                    'icon' => 'heroicon-o-question-mark-circle', 'is_public' => false,
                    'fields' => [
                        self::field('respuesta', 'Respuesta', 'richtext', true),
                    ],
                ]),

            self::moduleEntry('lib-propiedades', 'Propiedades (Inmobiliaria)',
                'Inmuebles con galería, precio, ubicación, habitaciones y área.',
                'heroicon-o-home-modern', [
                    'name' => 'Propiedades', 'slug' => 'propiedades', 'type' => 'generico',
                    'singular_label' => 'Propiedad', 'plural_label' => 'Propiedades',
                    'icon' => 'heroicon-o-home-modern', 'is_public' => true,
                    'fields' => [
                        self::field('imagen', 'Imagen principal', 'image'),
                        self::field('galeria', 'Galería', 'gallery'),
                        self::field('precio', 'Precio', 'number'),
                        self::field('ubicacion', 'Ubicación', 'text'),
                        self::field('habitaciones', 'Habitaciones', 'number'),
                        self::field('area', 'Área (m²)', 'text'),
                        self::field('descripcion', 'Descripción', 'textarea'),
                    ],
                ]),

            self::moduleEntry('lib-citas', 'Solicitud de cita',
                'Formulario para agendar citas: nombre, teléfono, fecha y servicio.',
                'heroicon-o-calendar-days', [
                    'name' => 'Citas', 'slug' => 'citas', 'type' => 'formulario',
                    'singular_label' => 'Cita', 'plural_label' => 'Citas',
                    'icon' => 'heroicon-o-calendar-days', 'is_public' => false,
                    'fields' => [
                        self::field('nombre', 'Nombre', 'text', true),
                        self::field('telefono', 'Teléfono', 'text', true),
                        self::field('correo', 'Correo', 'email'),
                        self::field('fecha', 'Fecha deseada', 'date'),
                        self::field('servicio', 'Servicio', 'select', false, 'Consulta, Seguimiento, Urgencia'),
                        self::field('nota', 'Comentario', 'textarea'),
                    ],
                ]),

            // === Plantillas de página ===
            self::templateEntry('lib-tpl-restaurante', 'Restaurante',
                'Portada, especialidades, sobre nosotros, reserva y contacto.',
                'heroicon-o-cake', 'Restaurante', [
                    ['type' => 'hero', 'data' => ['heading' => 'Sabor que enamora', 'subheading' => 'Cocina fresca, ambiente cálido y el mejor servicio.', 'image' => null, 'button_text' => 'Reservar mesa', 'button_url' => '#contacto']],
                    ['type' => 'features', 'data' => ['heading' => 'Nuestras especialidades', 'items' => [
                        ['icon' => 'star', 'title' => 'Platos de autor', 'text' => 'Recetas únicas preparadas al momento.'],
                        ['icon' => 'heart', 'title' => 'Ingredientes frescos', 'text' => 'Seleccionamos lo mejor cada día.'],
                        ['icon' => 'clock', 'title' => 'Servicio rápido', 'text' => 'Tu comida lista sin largas esperas.'],
                    ]]],
                    ['type' => 'image_text', 'data' => ['image' => null, 'image_side' => 'left', 'heading' => 'Sobre nosotros', 'text' => '<p>Cuenta la historia de tu restaurante: tradición, chef y ambiente.</p>']],
                    ['type' => 'cta', 'data' => ['heading' => 'Reserva tu mesa hoy', 'text' => 'Vive una experiencia inolvidable.', 'button_text' => 'Reservar', 'button_url' => '#contacto']],
                    ['type' => 'contact', 'data' => ['heading' => 'Reservaciones', 'subheading' => 'Déjanos tus datos y confirmamos tu mesa.']],
                ]),

            self::templateEntry('lib-tpl-clinica', 'Clínica / Consultorio',
                'Portada, especialidades, sobre la clínica, agenda y contacto.',
                'heroicon-o-heart', 'Clínica', [
                    ['type' => 'hero', 'data' => ['heading' => 'Tu salud en buenas manos', 'subheading' => 'Atención profesional y cercana para toda la familia.', 'image' => null, 'button_text' => 'Agendar cita', 'button_url' => '#contacto']],
                    ['type' => 'features', 'data' => ['heading' => 'Especialidades', 'items' => [
                        ['icon' => 'shield', 'title' => 'Atención segura', 'text' => 'Protocolos y equipo de primer nivel.'],
                        ['icon' => 'heart', 'title' => 'Trato humano', 'text' => 'Te acompañamos en cada paso.'],
                        ['icon' => 'check', 'title' => 'Resultados', 'text' => 'Diagnósticos precisos y a tiempo.'],
                    ]]],
                    ['type' => 'image_text', 'data' => ['image' => null, 'image_side' => 'right', 'heading' => 'Sobre la clínica', 'text' => '<p>Describe tu clínica: equipo, especialidades y valores.</p>']],
                    ['type' => 'cta', 'data' => ['heading' => 'Agenda tu cita', 'text' => 'Estamos para cuidarte.', 'button_text' => 'Agendar', 'button_url' => '#contacto']],
                    ['type' => 'contact', 'data' => ['heading' => 'Solicita tu cita', 'subheading' => 'Te contactamos para confirmar.']],
                ]),

            self::templateEntry('lib-tpl-inmobiliaria', 'Inmobiliaria',
                'Portada, ventajas, llamada a la acción y contacto para bienes raíces.',
                'heroicon-o-building-office-2', 'Inmobiliaria', [
                    ['type' => 'hero', 'data' => ['heading' => 'Encuentra tu próximo hogar', 'subheading' => 'Propiedades seleccionadas y asesoría de principio a fin.', 'image' => null, 'button_text' => 'Ver propiedades', 'button_url' => '#contacto']],
                    ['type' => 'features', 'data' => ['heading' => '¿Por qué con nosotros?', 'items' => [
                        ['icon' => 'check', 'title' => 'Propiedades verificadas', 'text' => 'Solo inmuebles con papeles en regla.'],
                        ['icon' => 'star', 'title' => 'Asesoría experta', 'text' => 'Te guiamos en toda la compra.'],
                        ['icon' => 'shield', 'title' => 'Trámite seguro', 'text' => 'Acompañamiento legal completo.'],
                    ]]],
                    ['type' => 'cta', 'data' => ['heading' => 'Agenda una visita', 'text' => 'Encontremos juntos tu propiedad ideal.', 'button_text' => 'Contáctanos', 'button_url' => '#contacto']],
                    ['type' => 'contact', 'data' => ['heading' => 'Hablemos', 'subheading' => 'Cuéntanos qué buscas.']],
                ]),
        ];
    }

    // ---------- Packs sueltos en resources/packs/*.json ----------

    protected static function fromDisk(): array
    {
        $dir = resource_path('packs');
        if (! is_dir($dir)) {
            return [];
        }

        $out = [];
        foreach (glob($dir . '/*.json') as $file) {
            $pack = json_decode((string) file_get_contents($file), true);
            if (! is_array($pack) || ! in_array($pack['type'] ?? '', ['module', 'page-template'], true)) {
                continue;
            }

            $out[] = [
                'key' => 'file-' . basename($file, '.json'),
                'label' => $pack['label'] ?? ($pack['data']['name'] ?? $pack['data']['title'] ?? basename($file, '.json')),
                'description' => $pack['description'] ?? 'Pack importado desde archivo.',
                'category' => $pack['type'] === 'module' ? 'Módulos' : 'Plantillas',
                'icon' => $pack['icon'] ?? 'heroicon-o-cube',
                'type' => $pack['type'],
                'pack' => $pack,
            ];
        }

        return $out;
    }
}
