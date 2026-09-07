# CMS Starter — Laravel 12 + Filament

Un **CMS a medida, sin límites de plantilla**, pensado para agencias y freelancers que
construyen muchos sitios administrables. En lugar de instalar plugins, **creas tus propios
módulos de contenido desde el panel** (como los Custom Post Types de WordPress, pero de
verdad no-code), los colocas en cualquier página y los publicas. Cada cliente es una
instalación independiente que se genera en segundos.

<p align="center">
  <img src="screenshots/home.png" alt="Página de inicio" width="820">
</p>

---

## Características

### Motor de contenido no-code
- **Módulos dinámicos**: creas tipos de contenido (Productos, Propiedades, Doctores, Cursos…)
  desde el panel, defines sus campos y el CRUD se genera solo. Aparecen automáticamente en el
  menú del administrador.
- **Tipos de campo**: texto, texto largo, editor con formato, correo, número/precio, sí/no,
  fecha, lista de opciones, imagen, galería y **relación a otro módulo** (CMS relacional).
- **Constructor de páginas por bloques**: Hero, servicios, galería, imagen+texto, CTA,
  formulario, listado de módulo y más — se arrastran y ordenan.
- **Plantillas de página** listas para usar (landing, negocio local, tienda, "nosotros").

### Módulos con lógica ("plugins")
- **Formularios** que capturan y almacenan envíos, visibles en el panel.
- **Tienda** con carrito, **pagos con Stripe y PayPal** (checkout seguro y hosteado) y
  registro de **órdenes** en el panel, o **pedido por WhatsApp** como alternativa.

### Gestión
- **Dashboard con estadísticas**: resumen de páginas, módulos, registros y mensajes, gráfica
  de actividad y tabla de últimos mensajes.
- **Roles y permisos**: Administrador (control total) y Editor (solo contenido).
- **Ajustes del sitio**: nombre, logo, colores de marca, WhatsApp, redes y SEO — el sitio
  entero se re-tematiza con el color de marca.

### Frontend
- Diseño propio con tipografía **Bricolage Grotesque + Inter**, íconos SVG, totalmente
  **responsive** y **theme-aware** al color de marca.

### SEO
- **`sitemap.xml`** y **`robots.txt`** automáticos, **Open Graph + Twitter Cards** (para que se
  vea bien al compartir en redes y WhatsApp), **canonical** y **datos estructurados JSON-LD**
  (Organización + Artículo en el blog).

### API / Headless
- API REST de solo lectura para consumir el contenido desde **apps móviles, otros frontends
  (Next.js, etc.) o integraciones**. Autenticación por **API keys** gestionadas desde el panel.

### Generación con IA
- Conectas **tu propia API** (OpenRouter — que da acceso a OpenAI, Gemini, Claude… — u OpenAI)
  y describes lo que quieres: la IA **genera el módulo o la plantilla** y lo instala, listo para
  editar. La llave se guarda **encriptada** y la respuesta se **valida y sanea** antes de crear
  nada.

### Packs (ecosistema)
- **Biblioteca de packs**: módulos y plantillas curados (Tienda, Servicios, Equipo,
  Testimonios, FAQ, Propiedades, Citas, Restaurante, Clínica, Inmobiliaria) que se **instalan
  de un clic** desde el panel.
- **Exporta e importa** cualquier módulo o plantilla como archivo `.json` portable. Deja packs
  en `resources/packs/` y aparecen en la biblioteca — así cualquiera puede ampliarla y
  compartir sin tocar código.

### Multi-cliente
- Script `nuevo-cliente.ps1` que **clona y configura** un sitio nuevo (base de datos, admin,
  contenido base) en segundos.

---

## Capturas

| Escritorio | Móvil | Desde plantilla |
|---|---|---|
| ![Home](screenshots/home.png) | ![Móvil](screenshots/home-mobile.png) | ![Plantilla](screenshots/template.png) |

---

## Stack

- **Laravel 12** (PHP 8.2+)
- **Filament 3** (panel de administración)
- **Blade + Tailwind CSS** (frontend)
- **MySQL** (producción) / **SQLite** (desarrollo)

---

## Instalación

```bash
git clone <este-repo> cms-starter
cd cms-starter
composer install
npm install
npm run build          # compila el CSS (Tailwind v4 + Vite)
cp .env.example .env
php artisan key:generate
php artisan migrate --seed --seeder=Database\\Seeders\\DemoContentSeeder
php artisan storage:link
php artisan serve
```

> Para desarrollar el frontend con recarga en vivo: `npm run dev` (en otra terminal).

- Sitio: `http://localhost:8000`
- Panel: `http://localhost:8000/admin`

Crea tu usuario administrador:

```bash
php artisan make:filament-user
```

> El seeder incluye un usuario editor de prueba: `editor@demo.com` / `editor123`.

---

## Un sitio nuevo por cliente (Windows / Laragon)

```powershell
.\nuevo-cliente.ps1 -Slug "farmacia-lopez" -Nombre "Farmacia Lopez" -Email "admin@farmacia.com"
```

Copia el proyecto, genera la base de datos con contenido base, enlaza storage y crea el
administrador. Al terminar muestra la URL y las credenciales.

---

## Despliegue en hosting compartido (cPanel)

1. **Compila los assets localmente** antes de subir: `npm run build` (genera `public/build`).
2. PHP 8.2+ en el selector de versión de PHP.
3. Base de datos MySQL y credenciales en `.env`.
4. Sube el proyecto **con la carpeta `public/build`** y apunta el dominio a `/public`.
5. `php artisan migrate --force && php artisan storage:link && php artisan config:cache`.
6. **No se necesita Node en el servidor**: Tailwind ya está compilado y Filament trae sus assets.

---

## API / Headless

Genera una llave en **Ajustes → API keys** y consume el contenido con el header
`Authorization: Bearer <token>` (o `X-API-Key: <token>`):

```bash
curl -H "Authorization: Bearer TU_TOKEN" https://tudominio.com/api/pages
```

Endpoints (solo lectura): `/api/settings`, `/api/pages`, `/api/pages/{slug}`,
`/api/posts`, `/api/posts/{slug}`, `/api/modules`, `/api/modules/{slug}`,
`/api/modules/{slug}/entries`, `/api/modules/{slug}/entries/{entry}`.

Solo expone contenido publicado y ajustes públicos (nunca secretos). Rate-limit de 60 req/min.

## Pagos con Stripe

1. Crea una cuenta en [stripe.com](https://stripe.com) y copia tus llaves (modo prueba: `pk_test_…` / `sk_test_…`).
2. En el panel → **Ajustes del sitio → Pagos (Stripe)**: activa, pega las llaves y la moneda.
3. En Stripe → Developers → Webhooks, agrega el endpoint `https://tudominio.com/stripe/webhook`
   (evento `checkout.session.completed`) y pega el **secreto del webhook** (`whsec_…`) en Ajustes.
4. El botón **"Pagar con tarjeta"** aparece en el carrito; las órdenes llegan a **Contenido → Órdenes**.

Los datos de tarjeta se procesan en la página segura de Stripe (checkout hosteado): **nunca tocan
tu servidor**. Los montos se calculan en el servidor y el pago se confirma por webhook firmado.

## Pagos con PayPal

1. En [developer.paypal.com](https://developer.paypal.com) crea una app y copia el **Client ID** y **Secret** (sandbox para pruebas).
2. En el panel → **Ajustes del sitio → Pagos (PayPal)**: activa, elige modo (sandbox/live) y pega las credenciales.
3. En el carrito aparece el botón **PayPal**; el cliente aprueba en PayPal y el pago se **captura**
   del lado del servidor, marcando la orden como pagada. Las credenciales se guardan **encriptadas**.

## Seguridad

Protecciones incluidas:

- **Headers de seguridad** en todas las respuestas (X-Frame-Options, X-Content-Type-Options,
  Referrer-Policy, Permissions-Policy y HSTS bajo HTTPS).
- **Anti-spam**: honeypot en los formularios públicos + **rate-limit** (8 envíos/min por IP).
- **Subidas** limitadas a imágenes y tamaño máximo.
- **Llaves de IA encriptadas** en la base de datos.
- **Roles**: el cliente (Editor) no accede a módulos, ajustes ni usuarios.

Checklist antes de publicar en producción:

- [ ] `APP_ENV=production` y `APP_DEBUG=false`
- [ ] Servir por **HTTPS** (activa HSTS y cookies seguras)
- [ ] `SESSION_SECURE_COOKIE=true`
- [ ] Crear el administrador y **cambiar** cualquier contraseña de ejemplo
- [ ] `php artisan config:cache && php artisan route:cache`
- [ ] Mantener dependencias al día (`composer update`, `npm update`)

## Licencia

[MIT](LICENSE) — libre para usar, modificar y distribuir.
