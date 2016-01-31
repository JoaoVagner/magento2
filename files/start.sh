#!/bin/bash

echo "1 -- Iniciando serviços";
service redis-server start
service mysql start
service php5-fpm start
service nginx start

echo "2 -- Composer";
cd /var/www/magento2 && composer install;