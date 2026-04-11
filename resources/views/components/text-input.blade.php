@props(['disabled' => false])

{{-- Millennium: inputs — foco arena + borde oscuro; @tailwindcss/forms compatible --}}
<input @disabled($disabled) {{ $attributes->merge(['class' => 'border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-millennium-dark dark:focus:border-millennium-sand focus:ring-millennium-sand dark:focus:ring-millennium-sand rounded-md shadow-sm']) }}>
