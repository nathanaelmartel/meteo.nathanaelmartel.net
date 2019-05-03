
git fetch --all
git reset --hard origin/master
#git pull

./clearcache.sh

php bin/console cache:clear --env=prod --no-debug

php bin/console cache:warmup --env=prod --no-debug

#php bin/composer update
#php bin/console cache:clear --no-warmup
#php bin/console cache:warmup

php bin/console  doctrine:schema:update --dump-sql

echo ""
echo "php bin/console  doctrine:schema:update --force"
echo ""
