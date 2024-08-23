docker network create -d bridge weather_bot_network
docker run -p 3306:3306 \
 --name=mysql -d \
 --env-file=.env \
 -v ./data:/var/lib/mysql \
 --network=weather_bot_network \
mysql:8.0.25