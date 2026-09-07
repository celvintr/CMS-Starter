<?php

namespace Database\Seeders;

use App\Models\Entry;
use App\Models\Module;
use App\Models\Page;
use App\Models\Post;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoContentSeeder extends Seeder
{
    public function run(): void
    {
        // --- Usuario editor de ejemplo (rol limitado: solo contenido) ---
        User::updateOrCreate(['email' => 'editor@demo.com'], [
            'name' => 'Editor Demo',
            'role' => 'editor',
            'password' => 'editor123',
        ]);

        // --- Ajustes del sitio ---
        SiteSetting::updateOrCreate(['id' => 1], [
            'site_name' => 'Mi Empresa',
            'tagline' => 'Calidad en cada detalle',
            'primary_color' => '#2563eb',
            'secondary_color' => '#0f172a',
            'whatsapp' => '+504 9999-9999',
            'phone' => '+504 2200-0000',
            'email' => 'contacto@miempresa.com',
            'address' => 'Tegucigalpa, Honduras',
            'facebook' => 'https://facebook.com',
            'instagram' => 'https://instagram.com',
            'footer_text' => 'Somos una empresa dedicada a ofrecer los mejores productos y servicios para nuestros clientes.',
            'meta_title' => 'Mi Empresa — Soluciones a tu medida',
            'meta_description' => 'Ofrecemos productos y servicios de calidad. Contáctanos hoy mismo.',
        ]);

        // --- Página de inicio ---
        Page::updateOrCreate(['slug' => 'home'], [
            'title' => 'Inicio',
            'show_in_menu' => false,
            'sort_order' => 0,
            'content' => [
                [
                    'type' => 'hero',
                    'data' => [
                        'heading' => 'Todo lo que tu negocio necesita, en un solo lugar',
                        'subheading' => 'Productos y servicios de calidad, atención cercana y resultados que se notan. Descubre por qué nuestros clientes nos eligen.',
                        'image' => null,
                        'button_text' => 'Ver productos',
                        'button_url' => '/m/productos',
                    ],
                ],
                [
                    'type' => 'features',
                    'data' => [
                        'heading' => 'Nuestros servicios',
                        'items' => [
                            ['icon' => 'bolt', 'title' => 'Rápido', 'text' => 'Entregamos resultados en tiempo récord sin sacrificar la calidad.'],
                            ['icon' => 'shield', 'title' => 'Confiable', 'text' => 'Miles de clientes confían en nosotros año tras año.'],
                            ['icon' => 'sparkles', 'title' => 'Innovador', 'text' => 'Usamos la última tecnología para darte lo mejor.'],
                        ],
                    ],
                ],
                [
                    'type' => 'module_list',
                    'data' => [
                        'module' => 'productos',
                        'heading' => 'Nuestros productos',
                        'limit' => 6,
                        'columns' => 3,
                        'show_link' => true,
                    ],
                ],
                [
                    'type' => 'cta',
                    'data' => [
                        'heading' => '¿Listo para empezar?',
                        'text' => 'Contáctanos hoy y recibe una asesoría gratuita.',
                        'button_text' => 'Contáctanos',
                        'button_url' => '#contacto',
                    ],
                ],
                [
                    'type' => 'contact',
                    'data' => [
                        'heading' => 'Contáctanos',
                        'subheading' => 'Déjanos tus datos y te responderemos lo antes posible.',
                    ],
                ],
            ],
        ]);

        // --- Página Nosotros ---
        Page::updateOrCreate(['slug' => 'nosotros'], [
            'title' => 'Nosotros',
            'show_in_menu' => true,
            'sort_order' => 1,
            'content' => [
                [
                    'type' => 'richtext',
                    'data' => [
                        'content' => '<h1>Sobre nosotros</h1><p>Somos una empresa con años de experiencia ofreciendo productos y servicios de calidad. Nuestra misión es ayudar a nuestros clientes a alcanzar sus metas.</p><p>Edita este contenido desde el panel de administración en <strong>Contenido → Páginas</strong>.</p>',
                    ],
                ],
                [
                    'type' => 'cta',
                    'data' => [
                        'heading' => 'Trabajemos juntos',
                        'text' => 'Descubre cómo podemos ayudarte.',
                        'button_text' => 'Escríbenos',
                        'button_url' => '/contacto',
                    ],
                ],
            ],
        ]);

        // --- Página Contacto ---
        Page::updateOrCreate(['slug' => 'contacto'], [
            'title' => 'Contacto',
            'show_in_menu' => true,
            'sort_order' => 2,
            'content' => [
                [
                    'type' => 'contact',
                    'data' => [
                        'heading' => 'Hablemos',
                        'subheading' => 'Estamos listos para atenderte.',
                    ],
                ],
            ],
        ]);

        // --- Entradas del blog ---
        Post::updateOrCreate(['slug' => 'bienvenidos-a-nuestro-blog'], [
            'title' => 'Bienvenidos a nuestro blog',
            'excerpt' => 'Este es el primer artículo de ejemplo. Aquí podrás publicar noticias y novedades.',
            'body' => '<p>Este es un artículo de ejemplo. Desde el panel de administración puedes crear, editar y publicar entradas para tu blog o sección de noticias.</p><p>Cada entrada tiene su propia imagen de portada, resumen y contenido con formato.</p>',
            'is_published' => true,
            'published_at' => now(),
        ]);

        Post::updateOrCreate(['slug' => 'como-editar-tu-sitio'], [
            'title' => 'Cómo editar tu sitio',
            'excerpt' => 'Aprende a usar el panel para cambiar textos, imágenes y secciones.',
            'body' => '<p>Editar tu sitio es muy sencillo. Ingresa al panel, ve a <strong>Páginas</strong> y arrastra los bloques que necesites: portada, servicios, galería, llamada a la acción y más.</p>',
            'is_published' => true,
            'published_at' => now()->subDay(),
        ]);

        // --- Módulo dinámico de ejemplo: Productos ---
        $productos = Module::updateOrCreate(['slug' => 'productos'], [
            'name' => 'Productos',
            'type' => 'tienda',
            'singular_label' => 'Producto',
            'plural_label' => 'Productos',
            'icon' => 'heroicon-o-shopping-bag',
            'is_public' => true,
            'sort_order' => 1,
            'fields' => [
                ['key' => 'imagen', 'label' => 'Imagen', 'type' => 'image', 'required' => false, 'options' => ''],
                ['key' => 'precio', 'label' => 'Precio', 'type' => 'number', 'required' => false, 'options' => ''],
                ['key' => 'categoria', 'label' => 'Categoría', 'type' => 'select', 'required' => false, 'options' => 'Ropa, Calzado, Accesorios'],
                ['key' => 'descripcion', 'label' => 'Descripción', 'type' => 'textarea', 'required' => false, 'options' => ''],
            ],
        ]);

        Entry::updateOrCreate(['module_id' => $productos->id, 'slug' => 'camisa-basica'], [
            'title' => 'Camisa básica',
            'is_published' => true,
            'sort_order' => 1,
            'data' => ['precio' => 299.00, 'categoria' => 'Ropa', 'descripcion' => 'Camisa de algodón cómoda para el día a día.'],
        ]);

        Entry::updateOrCreate(['module_id' => $productos->id, 'slug' => 'zapatos-deportivos'], [
            'title' => 'Zapatos deportivos',
            'is_published' => true,
            'sort_order' => 2,
            'data' => ['precio' => 899.00, 'categoria' => 'Calzado', 'descripcion' => 'Zapatos ligeros ideales para correr o entrenar.'],
        ]);

        // --- Módulo con lógica de ejemplo: Formulario de cotización ---
        Module::updateOrCreate(['slug' => 'cotizacion'], [
            'name' => 'Cotizaciones',
            'type' => 'formulario',
            'singular_label' => 'Cotización',
            'plural_label' => 'Cotizaciones',
            'icon' => 'heroicon-o-clipboard-document-list',
            'is_public' => false,
            'sort_order' => 2,
            'fields' => [
                ['key' => 'nombre', 'label' => 'Nombre', 'type' => 'text', 'required' => true, 'options' => ''],
                ['key' => 'email', 'label' => 'Correo', 'type' => 'email', 'required' => false, 'options' => ''],
                ['key' => 'telefono', 'label' => 'Teléfono', 'type' => 'text', 'required' => true, 'options' => ''],
                ['key' => 'servicio', 'label' => 'Servicio de interés', 'type' => 'select', 'required' => false, 'options' => 'Diseño, Desarrollo, Mantenimiento'],
                ['key' => 'detalle', 'label' => 'Cuéntanos qué necesitas', 'type' => 'textarea', 'required' => false, 'options' => ''],
            ],
        ]);

        // Coloca el formulario de cotización dentro de la página de contacto.
        $contacto = Page::where('slug', 'contacto')->first();
        if ($contacto) {
            $contacto->update([
                'content' => [
                    [
                        'type' => 'form',
                        'data' => [
                            'module' => 'cotizacion',
                            'heading' => 'Solicita tu cotización',
                            'subheading' => 'Llena el formulario y te enviamos una propuesta.',
                            'button_text' => 'Enviar solicitud',
                        ],
                    ],
                    [
                        'type' => 'contact',
                        'data' => [
                            'heading' => 'O escríbenos directo',
                            'subheading' => 'También puedes dejarnos un mensaje rápido.',
                        ],
                    ],
                ],
            ]);
        }
    }
}
