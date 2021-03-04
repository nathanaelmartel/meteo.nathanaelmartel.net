#!/bin/bash

echo ""
echo " -> Commit / Push"
echo ""

./new-version.sh

echo ""
echo " -> Connection au server"
echo ""

ssh root@51.15.20.32 -t 'cd /data/websites/meteo.nathanaelmartel.net; ./update.sh; exit'

echo ""
echo " -> code déployé en prod sur https://meteo.nathanaelmartel.net/ !"
echo ""
