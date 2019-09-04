#!/usr/bin/env bash

CURR_DIR=$(pwd)

if [ -f ./.env ]; then
	export $(grep -v '^#' ./.env | xargs)
	cd $DOCKER_COMPOSE_PATH
	docker-compose -f $DOCKER_COMPOSE_FILE --user laradock exec workspace composer --working-dir=/var/www/ $@  
else
	echo "Please create an .env file"
fi
cd $CURR_DIR
