Millennium — marca Incapor (assets estáticos)
============================================
Uso en Blade: asset('images/millennium/...') — no pasan por Vite; válido en local y producción.
El reemplazo del nombre del app por marca en UI está documentado en comentarios Blade
junto a `resources/views/components/brand-logo.blade.php` (PNG exportado, no SVG inline).

Archivos vectoriales (PNG sin fondo), prioridad en UI:
  millenium-vectores.png         Trazo oscuro → fondos claros (barra, login, títulos).
  millenium-vectores-blanco.png  Trazo blanco → solo fondos oscuros (variant on-dark del componente).

Otros PNG en esta carpeta (Incapor, etc.) son copias anteriores; el componente x-brand-logo usa solo los vectores.

Renovar: reemplazar los PNG manteniendo el mismo nombre de archivo para no tocar código.
