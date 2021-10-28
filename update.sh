
git fetch --all
git reset --hard origin/master
#git pull

./clearcache.sh


#php bin/console  doctrine:schema:update --dump-sql
#php bin/console  doctrine:schema:update --force

bin/console --no-interaction doctrine:migrations:migrate


