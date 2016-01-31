FROM ubuntu:14.04
MAINTAINER Joao Vagner <joao@joaovagner.com.br>

RUN DEBIAN_FRONTEND=noninteractive

#Atualiza OS
RUN apt-get -qq update

# Pacotes básicos
RUN apt-get -y --force-yes install wget nano curl git unzip supervisor g++ make nginx mysql-server-5.6 redis-server php5-cli php5-fpm php5-dev php5-mysql php5-curl php5-intl php5-mcrypt php5-memcache php5-imap php5-sqlite php5-gd php5-xsl

#Composer do php
RUN bash -c "wget http://getcomposer.org/composer.phar && mv composer.phar /usr/local/bin/composer && chmod +x /usr/local/bin/composer"

# Instalando Redis
RUN mkdir -p /tmp/php-redis
WORKDIR /tmp/php-redis
RUN wget https://github.com/phpredis/phpredis/archive/2.2.5.zip; unzip 2.2.5.zip
WORKDIR /tmp/php-redis/phpredis-2.2.5
RUN /usr/bin/phpize; ./configure; make; make install
RUN echo "extension=redis.so" > /etc/php5/mods-available/redis.ini
RUN php5enmod redis

RUN php5enmod mcrypt

# Libera acesso externo do mysql
RUN sed -i -e"s/^bind-address\s*=\s*127.0.0.1/bind-address = 0.0.0.0/" /etc/mysql/my.cnf

# Configurando timezone e habilitando o daemon do php-fpm
RUN sed -i "s/;date.timezone =.*/date.timezone = UTC/" /etc/php5/fpm/php.ini
RUN sed -i "s/;date.timezone =.*/date.timezone = UTC/" /etc/php5/cli/php.ini
RUN sed -i "s/;cgi.fix_pathinfo=1/cgi.fix_pathinfo=0/" /etc/php5/fpm/php.ini
RUN sed -i "s/;daemonize = yes/daemonize = no/" /etc/php5/fpm/php-fpm.conf

# configurando vhost
RUN echo "\ndaemon off;" >> /etc/nginx/nginx.conf

#adicionando configurações
ADD files/default /etc/nginx/sites-available/default
ADD files/supervisord.conf /etc/supervisor/supervisord.conf
ADD files/php-fpm.conf /etc/php5/fpm/php-fpm.conf
ADD files/start.sh /usr/local/bin/start.sh

#permissões da pasta e mysql database
WORKDIR /var/www
RUN chown www-data:www-data /var/www
RUN service mysql start && service redis-server start && mysql -e "create database magento2"

# libera portas
EXPOSE 80
EXPOSE 443

VOLUME ["/var/www/magento2"]

# Comandos default do container e start supervisor
CMD ["supervisord", "--nodaemon"]
CMD ["service", "mysql", "start"]
CMD ["service", "redis-server", "start"]
CMD ["service", "php5-fpm", "start"]
CMD ["service", "nginx", "start"]
CMD ["/usr/local/bin/start.sh"]
