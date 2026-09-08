# Changelog

Todos los cambios notables de este proyecto se documentan en este archivo.

El formato está basado en [Keep a Changelog](https://keepachangelog.com/es/1.1.0/)
y el proyecto sigue [Versionado Semántico](https://semver.org/lang/es/).

## [Sin publicar]

## [1.0.0] - 2026-09-08

Primera versión documentada: CMS no-code con funciones activables, listo para
clonar por cliente y desplegar en cPanel.

### Añadido

**Motor de contenido**
- Módulos dinámicos (tipos de contenido no-code) con CRUD autogenerado y campos:
  texto, texto largo, editor, correo, número, sí/no, fecha, lista, imagen, galería
  y relación a otro módulo.
- Constructor de páginas por bloques (hero, servicios, galería, imagen+texto, CTA,
  testimonios, precios, FAQ, mapa, video, estadísticas, formulario, listado de módulo,
  newsletter y reservas) y plantillas de página listas.
- Blog, formularios con captura de envíos, biblioteca de medios.

**Funciones del sitio (extensiones activables)**
- Sistema de activación por switch desde «Sistema → Funciones del sitio».
- **Tienda**: carrito y checkout con Stripe y PayPal (hosteado) o pedido por WhatsApp;
  **cupones** (porcentaje/fijo, vencimiento, compra mínima, límite de usos, por tienda o
  globales); **inventario/stock** por producto con descuento al pagar; **variantes**
  con precio y stock propios; **envíos** (tarifa plana + envío gratis desde umbral);
  **estados de pedido** (pendiente → pagada → enviada → entregada) con aviso al cliente.
- **Multilenguaje**: URLs por idioma (por defecto en la raíz, `/en`, `/fr`…), traducción
  de páginas, blog y ajustes, selector de idioma, `hreflang` y `canonical`.
- **Newsletter**: suscriptores (con exportación CSV) y campañas por SMTP, con baja por
  enlace firmado.
- **Reservas / Citas**: horarios configurables (días, apertura, cierre, duración, cupos),
  selector de turnos disponibles y validación en el servidor para evitar dobles reservas.
- **Generador con IA**: crea módulos y plantillas desde un prompt (OpenRouter/OpenAI),
  con la llave encriptada y respuesta saneada.

**Panel y gestión**
- Dashboard con métricas según las funciones activas (ventas, órdenes, reservas,
  suscriptores) y tablas de órdenes recientes y próximas reservas.
- Asistente de primeros pasos (checklist) para sitios recién clonados.
- Panel con la marca del cliente (nombre, logo, favicon y color), edición de perfil,
  cuentas SMTP múltiples, roles (admin/editor) y biblioteca de packs.

**Seguridad**
- Verificación en dos pasos (2FA/TOTP) opcional y **obligatoria para administradores**,
  con códigos de recuperación y desafío en el inicio de sesión.
- Headers de seguridad, anti-spam (honeypot + rate-limit), secretos encriptados.

**Frontend, SEO y analítica**
- Diseño propio responsive y re-tematizado al color de marca; favicon.
- `sitemap.xml`, `robots.txt`, Open Graph/Twitter, `canonical`, JSON-LD.
- Scripts de analítica (GA4/Meta/TikTok) con banner de cookies que difiere el seguimiento
  hasta el consentimiento.

**Plataforma**
- API REST de solo lectura con API keys.
- Correos con plantilla de marca.
- Script de clonado por cliente (`nuevo-cliente.ps1`).
- Suite de pruebas (PHPUnit) e integración continua (GitHub Actions) en cada PR.

[Sin publicar]: https://github.com/celvintr/CMS-Starter/compare/v1.0.0...HEAD
[1.0.0]: https://github.com/celvintr/CMS-Starter/releases/tag/v1.0.0
