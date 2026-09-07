# Guía de contribución

¡Gracias por tu interés en mejorar **CMS Starter**! Toda ayuda es bienvenida: correcciones,
funciones nuevas, packs para la biblioteca, documentación o traducciones.

## Antes de empezar

- Para **cambios grandes**, abre primero un *issue* para conversarlo y no duplicar esfuerzo.
- Para **bugs**, incluye pasos para reproducir, lo esperado y lo que ocurre.

## Entorno de desarrollo

```bash
git clone https://github.com/celvintr/CMS-Starter.git
cd CMS-Starter
composer install
npm install && npm run build
cp .env.example .env
php artisan key:generate
php artisan migrate --seed --seeder=Database\\Seeders\\DemoContentSeeder
php artisan storage:link
php artisan serve
```

## Flujo de trabajo

1. Haz un **fork** del repositorio.
2. Crea una rama descriptiva: `git checkout -b feat/mi-mejora` o `fix/algo`.
3. Haz cambios pequeños y enfocados (un PR = un tema).
4. **Prueba localmente** que todo sigue funcionando (panel, frontend, migraciones).
5. Haz commit con un mensaje claro y abre el **Pull Request** contra `main`.

## Estándares de código

- **PHP:** sigue [PSR-12](https://www.php-fig.org/psr/psr-12/) y el estilo del código existente
  (nombres, comentarios en español donde ya los hay).
- **Laravel/Filament:** respeta los patrones del proyecto (Resources, Support helpers, etc.).
- No incluyas en el PR: el archivo `.env`, la carpeta `vendor/`, `node_modules/`,
  la base de datos (`*.sqlite`) ni assets compilados (`public/build`).
- No agregues dependencias sin justificarlas en el PR.

## Revisión y seguridad

- La rama `main` está **protegida**: no se puede hacer push directo.
- **Todo Pull Request es revisado por un mantenedor antes de fusionarse.** Ningún cambio entra
  sin revisión.
- Se rechazan de inmediato PRs con: secretos/credenciales, binarios sospechosos, código
  ofuscado, o cambios que abran huecos de seguridad.
- ¿Reportar una vulnerabilidad? No abras un issue público: sigue [SECURITY.md](SECURITY.md).

## Aportar packs a la biblioteca

Exporta un módulo o plantilla desde el panel (**Exportar**), guárdalo como `.json` en
`resources/packs/` y ábrelo en un PR. Aparecerá automáticamente en la Biblioteca de packs.

---

Al contribuir, aceptas que tu aporte se publique bajo la licencia [MIT](LICENSE) del proyecto.
