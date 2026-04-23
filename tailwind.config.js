import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/**
 * Millennium / Incapor — Tailwind
 * Fuente única de `millennium.sand` y `millennium.dark` para clases `bg-millennium-*`,
 * `text-millennium-*`, etc. Espejo en `resources/css/app.css` (:root) solo para variables
 * globales; al cambiar tonos, actualizar ambos sitios.
 */
/** @type {import('tailwindcss').Config} */
export default {
    // Solo tema claro: los `dark:` solo aplican si existe `.dark` en el árbol (no lo usamos).
    darkMode: 'class',

    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            // Millennium — paleta corporativa centralizada (cambiar hex aquí o en app.css :root).
            // sand #DDB387 = superficie/acento dominante (casi blanco con calidez); dark #321D17 = texto y CTAs sólidos.
            colors: {
                millennium: {
                    sand: '#DDB387',
                    dark: '#321D17',
                },
            },
        },
    },

    plugins: [forms],
};
