#!/usr/bin/env sh
# Build sin `php artisan *:cache` para evitar el error Railpack:
# "secret DB_PASSWORD: not found" cuando DB_PASSWORD es referencia cruzada.
# La optimización (config/route/view) la hace el arranque de FrankenPHP/Railpack con BD ya disponible.
set -e
npm run build
mkdir -p storage/framework/sessions storage/framework/views storage/framework/cache storage/framework/testing storage/logs bootstrap/cache
chmod -R a+rw storage
