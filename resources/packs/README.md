# Packs sueltos

Deja aquí archivos `.json` de packs (módulos o plantillas) y aparecerán
automáticamente en la **Biblioteca de packs** del panel, listos para importar.

Un pack se genera desde el panel con **Exportar** (en Módulos o Páginas) y tiene
esta forma:

```json
{
  "cms": "cms-starter",
  "type": "module",            // o "page-template"
  "version": 1,
  "label": "Mi módulo",        // opcional: título en la biblioteca
  "description": "Qué hace",   // opcional
  "icon": "heroicon-o-cube",   // opcional
  "data": { ... }
}
```

Así cualquiera puede compartir y ampliar la biblioteca sin tocar código.
