#可以按照需求切换版本
FROM php:7.0.20-fpm
RUN apt-get update && apt-get install -y \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libmcrypt-dev \
        libpng12-dev \
        unzip \
        wget \
    && wget https://github.com/liexusong/php-beast/archive/master.zip \
    && unzip master.zip \
    && cd php-beast-master \
    && phpize  \
    && ./configure  \
    && make \
    && make install \
    && docker-php-ext-install -j$(nproc) iconv mcrypt pdo_mysql mbstring opcache \
    && docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/ \
    && docker-php-ext-install -j$(nproc) gd \
    && pecl install redis-3.1.2 \
    && docker-php-ext-enable redis

 #&& pecl install xdebug-2.5.0 \
 #&& docker-php-ext-enable xdebug
    
#Flag:最后记得清理apt产生的垃圾，减少空间占用  rm -rf /....
