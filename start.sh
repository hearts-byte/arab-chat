#!/bin/bash
set -e

# حذف أي وحدة MPM زايدة عن prefork بشكل مباشر ونهائي
rm -f /etc/apache2/mods-enabled/mpm_event.load /etc/apache2/mods-enabled/mpm_event.conf
rm -f /etc/apache2/mods-enabled/mpm_worker.load /etc/apache2/mods-enabled/mpm_worker.conf

# تفعيل prefork فقط (إذا مش مفعّل)
a2enmod mpm_prefork 2>/dev/null || true

# ضبط منفذ Railway الديناميكي
PORT=${PORT:-80}
sed -i "s/Listen 80/Listen ${PORT}/g" /etc/apache2/ports.conf
sed -i "s/:80/:${PORT}/g" /etc/apache2/sites-enabled/000-default.conf

exec apache2-foreground
