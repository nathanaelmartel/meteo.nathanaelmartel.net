
rm -Rf var/cache/dev/*
rm -Rf var/cache/test/*
rm -Rf var/cache/prod/*
rm -Rf .sass-cache

#chown www-data:www-data var -R
#chmod 777 var -R
#chmod 777 nas -R

# https://symfony.com/doc/current/setup/file_permissions.html
#setfacl -dR -m u:www-data:rwX -m u:nathanael:rwX var
#setfacl -R -m u:www-data:rwX -m u:nathanael:rwX var
