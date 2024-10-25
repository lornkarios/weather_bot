#!/bin/bash
echo "Stop container"
docker stop php
docker rm php
docker image rm lornkarios/weather-api-bot
echo "Pull image"
docker pull lornkarios/weather-api-bot
echo "Start php container"
cd /home/akov/php
docker run
  -v ./.well-known:/var/www/html/.well-known \
  lornkarios/weather-api-bot
echo "Finish deploying!"