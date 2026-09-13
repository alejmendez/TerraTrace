sudo apt update
sudo apt upgrade -y

sudo sh -c 'echo "deb http://apt.postgresql.org/pub/repos/apt $(lsb_release -cs)-pgdg main" > /etc/apt/sources.list.d/pgdg.list'
wget --quiet -O - https://www.postgresql.org/media/keys/ACCC4CF8.asc | sudo apt-key add -
sudo apt update
sudo apt install postgresql-18 -y

sudo add-apt-repository ppa:ondrej/php -y
sudo apt update
sudo apt install php8.5 php8.5-cli php8.5-fpm php8.5-pgsql php8.5-gd php8.5-curl php8.5-mbstring php8.5-xml php8.5-zip php8.5-bcmath -y

curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

curl -fsSL https://deb.nodesource.com/setup_24.x | sudo -E bash -
sudo apt install nodejs -y

sudo apt install nginx -y

sudo apt install certbot php8.5-cli -y

git clone https://github.com/alejmendez/laratrufas.git laratrufas
cd laratrufas

composer install --no-dev --optimize-autoloader
cp .env.example .env
php artisan key:generate

npm install
npm run build

php artisan optimize
php artisan storage:link

sudo nano /etc/nginx/sites-available/agricolafrayleon.app

# Redirige todo el tráfico HTTP a HTTPS
server {
    listen 80;
    server_name agricolafrayleon.app *.agricolafrayleon.app;
    return 301 https://$host$request_uri;
}

# Bloque HTTPS
server {
    listen 443 ssl;
    server_name agricolafrayleon.app *.agricolafrayleon.app;
    root /home/ubuntu/laratrufas/public;

    # Configuración de compresión gzip
    gzip on;
    gzip_vary on;
    gzip_proxied any;
    gzip_min_length 1000;
    gzip_comp_level 6;
    gzip_types text/plain text/css text/xml application/json application/javascript application/xml+rss application/atom+xml image/svg+xml;

    # Certificados SSL
    ssl_certificate /etc/letsencrypt/live/agricolafrayleon.app/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/agricolafrayleon.app/privkey.pem;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_prefer_server_ciphers on;
    ssl_ciphers HIGH:!aNULL:!MD5;

    # Limitar el tamaño máximo de carga de archivos a 5MB
    client_max_body_size 20M;

    index index.php index.html index.htm;

    location / {
        proxy_pass http://127.0.0.1:8000;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.5-fpm.sock;
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        fastcgi_param PATH_INFO $fastcgi_path_info;
    }

    location ~ /\.ht {
        deny all;
    }
}

sudo ln -s /etc/nginx/sites-available/agricolafrayleon.app /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx

sudo nano /etc/systemd/system/octane.service

[Unit]
Description=Laravel Octane Server
After=network.target

[Service]
User=ubuntu
Group=ubuntu
WorkingDirectory=/home/ubuntu/laratrufas
ExecStart=/usr/bin/php artisan octane:start --server=frankenphp --host=127.0.0.1 --port=8000
Restart=always

[Install]
WantedBy=multi-user.target

sudo systemctl enable octane
sudo systemctl restart octane

sudo apt install -y certbot python3-certbot-nginx
sudo certbot --nginx -d agricolafrayleon.app -d *.agricolafrayleon.app --agree-tos --email alejmendez.87@gmail.com --non-interactive
sudo certbot --nginx --preferred-challenges=dns --server https://acme-v02.api.letsencrypt.org/directory --agree-tos -d agricolafrayleon.app -d *.agricolafrayleon.app --email alejmendez.87@gmail.com --non-interactive


sudo -u postgres psql
ALTER USER postgres WITH PASSWORD 'postgres';
CREATE DATABASE laratrufas;


echo -e '\nif [ -f ~/.bash_aliases ]; then\n    . ~/.bash_aliases\nfi\n' >> ~/.bashrc

touch ~/.bash_aliases
echo 'alias stop_services="sudo systemctl stop nginx ; sudo systemctl stop php8.5-fpm ; sudo systemctl stop octane"' >> ~/.bash_aliases
echo 'alias start_services="sudo systemctl start octane ; sudo systemctl start php8.5-fpm ; sudo systemctl start nginx"' >> ~/.bash_aliases
echo 'alias update_app_with_migrations="stop_services ; git pull ; npm run build -- -l silent ; composer install --optimize-autoloader --no-dev ; php artisan migrate --force ; php artisan optimize ; start_services"' >> ~/.bash_aliases
echo 'alias update_only_php="stop_services ; git pull ; composer install --optimize-autoloader --no-dev ; composer dump-autoload ; php artisan' >> ~/.bash_aliases
echo 'optimize ; start_services"' >> ~/.bash_aliases
echo 'alias update_only_js="stop_services ; git pull ; npm run build -- -l silent ; start_services"' >> ~/.bash_aliases
echo 'alias update_app="stop_services ; git pull ; npm run build -- -l silent ; composer install --optimize-autoloader --no-dev ; composer dump-autoload ; php artisan optimize ; start_services"' >> ~/.bash_aliases

source ~/.bashrc
