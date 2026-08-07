#!/bin/bash
set -e

# Railway يعطيك متغير PORT، لازم أباتشي يسمع عليه
PORT=${PORT:-80}

sed -i "s/Listen 80/Listen ${PORT}/g" /etc/apache2/ports.conf
sed -i "s/:80/:${PORT}/g" /etc/apache2/sites-enabled/000-default.conf

exec apache2-foreground
