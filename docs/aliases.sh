alias stop_services="sudo systemctl stop nginx ; sudo systemctl stop php5-fpm ; sudo systemctl stop octane"
alias start_services="sudo systemctl start octane ; sudo systemctl start php8.5-fpm ; sudo systemctl start nginx"
alias update_app_with_migrations="stop_services ; git pull ; npm run build -- -l silent ; composer install --optimize-autoloader --no-dev ; php artisan migrate --force ; php artisan optimize ; start_services"
alias update_only_php="stop_services ; git pull ; composer install --optimize-autoloader --no-dev ; composer dump-autoload ; php artisan
optimize ; start_services"
alias update_only_js="stop_services ; git pull ; npm run build -- -l silent ; start_services"
alias update_app="stop_services ; git pull ; npm run build -- -l silent ; composer install --optimize-autoloader --no-dev ; composer dump-autoload ; php artisan optimize ; start_services"
