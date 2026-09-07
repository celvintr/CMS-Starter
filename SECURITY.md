# Política de seguridad

## Versiones soportadas

Se da soporte de seguridad a la **última versión** de la rama `main`.

## Reportar una vulnerabilidad

**Por favor, NO abras un issue público** para reportar vulnerabilidades: eso las expone antes
de que puedan corregirse.

En su lugar, usa el reporte privado de GitHub:

1. Ve a la pestaña **Security** del repositorio.
2. Haz clic en **"Report a vulnerability"** (Private vulnerability reporting).

> El mantenedor debe activar esta opción en **Settings → Security → Private vulnerability
> reporting**. Si no está disponible, contáctalo por un canal privado.

Incluye en tu reporte:

- Descripción del problema y su impacto.
- Pasos para reproducirlo (o prueba de concepto).
- Versión / commit afectado.

## Qué esperar

- Confirmación de recibido lo antes posible.
- Trabajaremos en una corrección y te mantendremos al tanto.
- Se dará crédito a quien reporte de forma responsable (si así lo desea).

## Buenas prácticas al desplegar

- `APP_DEBUG=false` y `APP_ENV=production`.
- Servir siempre por **HTTPS**.
- Mantener PHP, Composer y las dependencias **actualizadas**.
- Nunca subir el archivo `.env` ni credenciales al repositorio.
