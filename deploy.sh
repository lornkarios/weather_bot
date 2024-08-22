#!/bin/bash
echo "Stop container"
docker stop backend
docker rm backend
docker image rm lornkarios/weather-api-bot
echo "Pull image"
docker pull lornkarios/weather-api-bot
echo "Start frontend container"
docker run -p 80:80 -p 9000:9000 --name backend -d lornkarios/weather-api-bot
echo "Finish deploying!"