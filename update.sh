
git fetch --all
git reset --hard origin/master
#git pull

./clearcache.sh

php bin/console  doctrine:schema:update --dump-sql

echo ""
echo "php bin/console  doctrine:schema:update --force"
echo ""
