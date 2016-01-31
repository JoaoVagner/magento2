# Docker Magento 2

Esse projeto contém um built usando Ubuntu com: MySQL, Redis, Nginx e PHP

Para fazer o build:
'sudo docker build -t joaovagner/php_nginx_mysql_redis_magento2 .'


Para rodar em modo interativo execute no seu terminal:

'sudo docker run --name nginx_magento_container -p 80:80 -v $(pwd)/magento2:/var/www/magento2 -v $(pwd)/database:/var/lib/mysql -it joaovagner/php_nginx_mysql_redis_magento2 /usr/local/bin/start.sh'