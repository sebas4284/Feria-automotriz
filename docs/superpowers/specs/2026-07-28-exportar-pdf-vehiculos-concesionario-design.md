# Exportar PDF combinado de vehículos por concesionario

## Contexto

Hoy la ficha de un vehículo (`resources/views/vehiculos/ficha.blade.php`) es una página HTML que el usuario imprime/guarda como PDF manualmente desde el navegador (`window.print()`). No existe ninguna librería de generación de PDF en el proyecto (`composer.json`) ni ningún comando de exportación masiva.

El usuario necesita obtener, de una sola vez, un PDF con las fichas de todos los vehículos del concesionario "Usados de Occidente".

Restricción relevante: el proyecto se despliega en hosting compartido tipo cPanel (rama actual `fix/403-htaccess-hosting`), por lo que no se puede depender de Node.js/Chrome headless (ej. Browsershot/Puppeteer) para renderizar PDFs.

## Objetivo

Un comando artisan que genera un único archivo PDF con una página por vehículo, para todos los vehículos de un concesionario dado, sin importar su estado (Disponible/Reservado/Vendido), ordenados por placa ascendente.

## Fuera de alcance

- Botón o acción en la interfaz web para disparar la exportación.
- Filtrado por estado del vehículo.
- Reproducción pixel-perfect del diseño CSS Grid de `ficha.blade.php` (se usa una maquetación por tablas, visualmente equivalente pero no idéntica, por limitaciones de compatibilidad de dompdf con CSS Grid).

## Diseño

### 1. Librería de PDF

Se agrega `barryvdh/laravel-dompdf` como dependencia de producción (`composer require barryvdh/laravel-dompdf`). Es PHP puro, sin binarios externos, compatible con hosting compartido.

### 2. Vista PDF: `resources/views/vehiculos/ficha-pdf.blade.php`

Nueva vista Blade, dedicada a renderizado PDF (no reemplaza `ficha.blade.php`, que sigue usándose para el print manual desde el navegador).

- Recibe una colección de vehículos (`$vehiculos`), no un solo modelo.
- Por cada vehículo, genera una sección de página completa con los mismos campos que `ficha.blade.php`: placa, vitrina (nombre del concesionario), marca, línea/versión, fecha matrícula, ciudad matrícula, modelo, cilindraje, combustible, transmisión, kilometraje, SOAT, tecnomecánica, precio normal, bono, precio feria, accesorios.
- Maquetación con `<table>` en lugar de CSS Grid (dompdf no soporta `display: grid` de forma confiable).
- Salto de página entre vehículos vía `page-break-after: always;` (menos en el último).
- Reutiliza el logo actual (`public/images/expocarshow-logo-white.png`) en el encabezado de cada página.
- Tamaño de página carta (letter), orientación vertical, igual que el print actual (`@page { size: letter portrait; }`).

### 3. Comando artisan: `app/Console/Commands/ExportarVehiculosPdf.php`

```
php artisan vehiculos:exportar-pdf "Usados de Occidente"
```

Firma: `vehiculos:exportar-pdf {concesionario : Nombre exacto del concesionario}`

Flujo:
1. Busca `Concesionario::where('nombre', $concesionario)->first()`.
   - Si no existe: mensaje de error claro (`$this->error(...)`) y termina con código de salida distinto de cero.
2. Obtiene `$concesionario->vehiculos()->orderBy('placa')->get()`.
   - Si la colección está vacía: avisa (`$this->warn(...)`) y termina sin generar archivo.
3. Renderiza `vehiculos.ficha-pdf` con la colección completa usando `Pdf::loadView(...)` (facade de dompdf), tamaño `letter`.
4. Guarda el PDF en `storage/app/exports/vehiculos-{slug-concesionario}-{YYYY-MM-DD}.pdf` (crea el directorio `exports` si no existe; usa `Illuminate\Support\Str::slug()` para el nombre del concesionario).
5. Imprime en consola (`$this->info(...)`) la ruta absoluta del archivo generado y la cantidad de vehículos incluidos.

### Manejo de errores

- Concesionario inexistente → error y salida temprana, sin crear archivo.
- Concesionario sin vehículos → advertencia y salida temprana, sin crear archivo vacío.
- No se contempla manejo especial de vehículos con campos nulos: la vista ya usa `?:  '—'` como en la ficha original.

### Testing

- Test de Feature (`tests/Feature/ExportarVehiculosPdfTest.php`): 
  - Genera el PDF correctamente para un concesionario con vehículos (verifica que el archivo se crea en disco).
  - Falla con mensaje adecuado si el concesionario no existe.
  - Avisa y no genera archivo si el concesionario no tiene vehículos.
