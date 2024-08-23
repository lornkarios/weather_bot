#!/bin/bash
echo "Stop container"
docker stop php
docker rm php
docker image rm lornkarios/weather-api-bot
echo "Pull image"
docker pull lornkarios/weather-api-bot
echo "Start php container"
docker run -p 80:8080 -p 9000:9000 --name php -d lornkarios/weather-api-bot
echo "Finish deploying!"