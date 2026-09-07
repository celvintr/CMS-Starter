<?php

namespace App\Support;

/**
 * Plantillas de página prearmadas: composiciones de bloques listas para usar.
 * Se aplican desde "Páginas → Crear desde plantilla".
 */
class PageTemplates
{
    public static function all(): array
    {
        return [
            'landing_servicios' => [
                'name' => 'Landing de servicios',
                'description' => 'Portada + servicios + llamada a la acción + contacto.',
                'blocks' => [
                    ['type' => 'hero', 'data' => [
                        'heading' => 'Impulsamos tu negocio al siguiente nivel',
                        'subheading' => 'Servicios profesionales con resultados medibles y atención cercana.',
                        'image' => null,
                        'button_text' => 'Solicitar información',
                        'button_url' => '#contacto',
                    ]],
                    ['type' => 'features', 'data' => [
                        'heading' => 'Nuestros servicios',
                        'items' => [
                            ['icon' => 'bolt', 'title' => 'Rápido', 'text' => 'Respuesta ágil y entregas a tiempo.'],
                            ['icon' => 'shield', 'title' => 'Confiable', 'text' => 'Calidad garantizada en cada proyecto.'],
                            ['icon' => 'chat', 'title' => 'Soporte cercano', 'text' => 'Te acompañamos en todo el proceso.'],
                        ],
                    ]],
                    ['type' => 'cta', 'data' => [
                        'heading' => '¿Hablamos de tu proyecto?',
                        'text' => 'Cuéntanos qué necesitas y te damos una propuesta sin compromiso.',
                        'button_text' => 'Contáctanos',
                        'button_url' => '#contacto',
                    ]],
                    ['type' => 'contact', 'data' => [
                        'heading' => 'Contáctanos',
                        'subheading' => 'Déjanos tus datos y te respondemos lo antes posible.',
                    ]],
                ],
            ],

            'negocio_local' => [
                'name' => 'Negocio local',
                'description' => 'Portada + sección "sobre nosotros" + beneficios + contacto.',
                'blocks' => [
                    ['type' => 'hero', 'data' => [
                        'heading' => 'Bienvenido a nuestro negocio',
                        'subheading' => 'Calidad, confianza y el mejor trato para nuestra comunidad.',
                        'image' => null,
                        'button_text' => 'Ver más',
                        'button_url' => '#contacto',
                    ]],
                    ['type' => 'image_text', 'data' => [
                        'image' => null,
                        'image_side' => 'left',
                        'heading' => 'Sobre nosotros',
                        'text' => '<p>Somos un negocio con años de experiencia atendiendo a nuestros clientes con dedicación. Edita este texto desde el panel para contar tu historia.</p>',
                    ]],
                    ['type' => 'features', 'data' => [
                        'heading' => '¿Por qué elegirnos?',
                        'items' => [
                            ['icon' => 'star', 'title' => 'Calidad', 'text' => 'Productos y servicios en los que puedes confiar.'],
                            ['icon' => 'heart', 'title' => 'Trato cercano', 'text' => 'Te atendemos como mereces.'],
                            ['icon' => 'clock', 'title' => 'Puntualidad', 'text' => 'Cumplimos con lo que prometemos.'],
                        ],
                    ]],
                    ['type' => 'contact', 'data' => [
                        'heading' => 'Visítanos o escríbenos',
                        'subheading' => 'Estamos para atenderte.',
                    ]],
                ],
            ],

            'tienda_simple' => [
                'name' => 'Tienda simple',
                'description' => 'Portada + productos + llamada a la acción. (Requiere un módulo tipo Tienda.)',
                'blocks' => [
                    ['type' => 'hero', 'data' => [
                        'heading' => 'Descubre nuestros productos',
                        'subheading' => 'Calidad al mejor precio, con pedido fácil por WhatsApp.',
                        'image' => null,
                        'button_text' => 'Ver catálogo',
                        'button_url' => '/m/productos',
                    ]],
                    ['type' => 'module_list', 'data' => [
                        'module' => 'productos',
                        'heading' => 'Lo más vendido',
                        'limit' => 6,
                        'columns' => 3,
                        'show_link' => true,
                    ]],
                    ['type' => 'cta', 'data' => [
                        'heading' => '¿No encuentras lo que buscas?',
                        'text' => 'Escríbenos y te ayudamos a conseguirlo.',
                        'button_text' => 'Contáctanos',
                        'button_url' => '#contacto',
                    ]],
                    ['type' => 'contact', 'data' => [
                        'heading' => 'Haz tu pedido',
                        'subheading' => 'Cuéntanos qué necesitas.',
                    ]],
                ],
            ],

            'nosotros' => [
                'name' => 'Página "Nosotros"',
                'description' => 'Texto de presentación + imagen con texto + llamada a la acción.',
                'blocks' => [
                    ['type' => 'richtext', 'data' => [
                        'content' => '<h1>Sobre nosotros</h1><p>Cuenta aquí la historia de tu empresa: quiénes son, qué hacen y qué los hace diferentes. Edita este contenido desde el panel.</p>',
                    ]],
                    ['type' => 'image_text', 'data' => [
                        'image' => null,
                        'image_side' => 'right',
                        'heading' => 'Nuestra misión',
                        'text' => '<p>Describe la misión y los valores que guían tu trabajo día a día.</p>',
                    ]],
                    ['type' => 'cta', 'data' => [
                        'heading' => 'Trabajemos juntos',
                        'text' => 'Descubre cómo podemos ayudarte.',
                        'button_text' => 'Escríbenos',
                        'button_url' => '/contacto',
                    ]],
                ],
            ],
        ];
    }

    public static function options(): array
    {
        return collect(static::all())->map(fn ($t) => $t['name'])->all();
    }

    public static function get(string $key): ?array
    {
        return static::all()[$key] ?? null;
    }
}
