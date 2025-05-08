#!/bin/bash
set -e

WORDPRESS_DB_NAME="wordpress"
WORDPRESS_DB_USERNAME="wordpress"
WORDPRESS_DB_PASSWORD=$(openssl rand -base64 10)

WORDPRESS_ADMIN_USERNAME="admin"
WORDPRESS_ADMIN_PASSWORD=$(openssl rand -base64 10)

echo "Initializing MariaDB..."

mysql_install_db --user=mysql --ldata=/var/lib/mysql

mysqld_safe --datadir=/var/lib/mysql &

echo "Waiting for MariaDB to start..."
until mysqladmin --user=root --password="$MYSQL_ROOT_PASSWORD" ping --silent; do
    echo "Waiting for MariaDB to be ready..."
    sleep 2
done

mysql --user=root --password="$MYSQL_ROOT_PASSWORD" <<EOF
CREATE DATABASE IF NOT EXISTS $WORDPRESS_DB_NAME;
CREATE USER IF NOT EXISTS '$WORDPRESS_DB_USERNAME'@'localhost' IDENTIFIED BY '$WORDPRESS_DB_PASSWORD';
GRANT ALL PRIVILEGES ON $WORDPRESS_DB_NAME.* TO '$WORDPRESS_DB_USERNAME'@'localhost';
FLUSH PRIVILEGES;
EOF

echo "Database setup complete."

echo "Starting PHP-FPM..."
php-fpm &

echo "Starting Nginx..."
nginx &

echo "Setting up WordPress..."

wp core download --path=/var/www/html
wp config create --path=/var/www/html/ \
  --dbname=${WORDPRESS_DB_NAME} \
  --dbuser=${WORDPRESS_DB_USERNAME} \
  --dbpass=${WORDPRESS_DB_PASSWORD} \
  --dbhost=localhost:/var/run/mysqld/mysqld.sock

wp core install --path=/var/www/html/ \
  --url=localhost \
  --title="WordPress Test" \
  --admin_user=${WORDPRESS_ADMIN_USERNAME} \
  --admin_password=${WORDPRESS_ADMIN_PASSWORD} \
  --admin_email=admin@example.com \
  --skip-email

wp rewrite structure '/%postname%/'

echo "Installing and activating WooCommerce..."
wp plugin install woocommerce --activate

echo "Instaling Revolut Payment Gateway plugin"
wp plugin install revolut-gateway-for-woocommerce --activate

echo "Installing and activating Storefront theme..."
wp theme install storefront --activate

echo "Adding basic WooCommerce settings..."
wp option set woocommerce_store_address "test street"
wp option set woocommerce_store_address_2 "PO16 7GZ"
wp option set woocommerce_store_city "London"
wp option set woocommerce_default_country "GB"
wp option set woocommerce_store_postcode "12345"
wp option set woocommerce_currency "GBP"
wp option set woocommerce_product_type "both"
wp option set woocommerce_allow_tracking "no"

echo "Importing WooCommerce shop pages..."
wp wc --user=admin tool run install_pages

echo "Installing and activating the WordPress Importer plugin..."
wp plugin install wordpress-importer --activate

echo "Importing some sample data..."
wp import wp-content/plugins/woocommerce/sample-data/sample_products.xml --authors=skip

echo "Change store to public mode" 
wp option update blog_public 1

echo "All services are up and running!"
tail -f /dev/null