#!/bin/bash

echo "Last version: "
more version

echo "New version: "

read newversion


echo $newversion > version
echo $newversion > templates/version-number.html.twig
date=`git log -1 --format="%at" | xargs -I{} date -d @{} +"%d/%m/%Y %H:%M"`
echo "<span title=\"$date\">version $newversion</span>" > templates/version.html.twig

git add templates/version.html.twig
git add templates/views/version-number.html.twig
git add version
git commit -m 'new version'
git tag v$newversion -m '$newversion'
git push
git push --tag


echo "terminé"
