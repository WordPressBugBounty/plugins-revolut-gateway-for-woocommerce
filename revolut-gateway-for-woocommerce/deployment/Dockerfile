FROM eu.gcr.io/revolut-labs/docker-images/ecom-wordpress

ENV WP_CLI_ALLOW_ROOT=true

RUN apk update && \
    apk add --no-cache bash nodejs npm subversion less jq curl php-fpm nginx

RUN apk update && \
    apk add --no-cache mariadb mariadb-client bash

RUN curl -O https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar && \
    chmod +x wp-cli.phar && \
    mv wp-cli.phar /usr/local/bin/wp

COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

COPY ./nginx.conf /etc/nginx/nginx.conf

WORKDIR /var/www/html

COPY ./entrypoint.sh /entrypoint.sh

EXPOSE 80

RUN chmod +x /entrypoint.sh
RUN echo "memory_limit=4096M" > /usr/local/etc/php/conf.d/memory-limit.ini
RUN echo "upload_max_filesize=20M" > /usr/local/etc/php/conf.d/dev-php.ini

CMD ["/bin/bash", "/entrypoint.sh"]
