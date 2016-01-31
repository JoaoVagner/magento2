#!/bin/bash

echo "1 -- Iniciando serviços";
/etc/init.d redis-server start
/etc/init.d mysql start
/etc/init.d php5-fpm start
/etc/init.d nginx start

echo "2 -- Composer";
cd /var/www/magento2 && composer install;