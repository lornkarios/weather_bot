#!/bin/bash
echo "Stop container"
docker stop php
docker rm php
docker image rm lornkarios/weather-api-bot
echo "Pull image"
docker pull lornkarios/weather-api-bot
echo "Start php container"
cd /home/akov/php
docker run -p 443:443 -p 9000:9000 \
  --name php -d --env-file=.env \
  --network=weather_bot_network \
  -v ./.well-known:/var/www/html/.well-known \
  -v /etc/letsencrypt:/etc/letsencrypt \
  lornkarios/weather-api-bot
echo "Finish deploying!"