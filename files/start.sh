#!/bin/bash

echo "1 -- Iniciando serviços";
/etc/init.d/redis-server restart;
/etc/init.d/mysql restart;
/etc/init.d/php5-fpm restart;
/etc/init.d/nginx restart;

echo "2 -- Composer";
cd /var/www/magento2 && composer install;

chmod -R 777 /var/www/magento2/app/etc;
chmod -R 777 /var/www/magento2/var;
chmod -R 777 /var/www/magento2/pub/static;
chmod -R 777 /var/www/magento2/pub/media;
