#!/bin/sh
# Genera msc-contenido.zip y msc-theme.zip en dist/ para subirlos desde el administrador de WordPress.
set -e
cd "$(dirname "$0")/.."
mkdir -p dist
rm -f dist/msc-contenido.zip dist/msc-theme.zip
( cd wp-content/plugins && zip -qr ../../dist/msc-contenido.zip msc-contenido -x '*.DS_Store' )
( cd wp-content/themes && zip -qr ../../dist/msc-theme.zip msc-theme -x '*.DS_Store' )
ls -lh dist
