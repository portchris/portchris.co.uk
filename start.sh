#!/usr/bin/env bash

CURR_DIR=$(pwd)

if [ -f ./.env ]; then
	export $(grep -v '^#' ./.env | xargs)
	if [ -f "$DOCKER_COMPOSE_PATH$DOCKER_COMPOSE_FILE" ]; then
		rm -f $DOCKER_COMPOSE_PATH$DOCKER_COMPOSE_FILE
	fi
	cp ./$DOCKER_COMPOSE_FILE $DOCKER_COMPOSE_PATH
	cd $DOCKER_COMPOSE_PATH
	docker-compose -f $DOCKER_COMPOSE_FILE up --remove-orphans -d "$DOCKER_COMPOSE_SERVICES"
else
	echo "Please create an .env file"
fi
cd $CURR_DIR
