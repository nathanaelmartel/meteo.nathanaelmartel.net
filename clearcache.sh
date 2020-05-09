
rm -Rf var/cache/dev/*
rm -Rf var/cache/test/*
rm -Rf var/cache/prod/*

chown www-data:www-data var -R
chmod 777 var -R
