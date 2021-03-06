#!/usr/bin/env bash

CURR_DIR=$(pwd)

if [ -f ./.env ]; then
	set -a
	. ./.env
	set +a
	if [ -f "$DOCKER_COMPOSE_PATH$DOCKER_COMPOSE_FILE" ]; then
		rm -f $DOCKER_COMPOSE_PATH$DOCKER_COMPOSE_FILE
	fi
	if [ -f "$DOCKER_COMPOSE_PATH/.env" ]; then
		rm -f $DOCKER_COMPOSE_PATH/.env
	fi
	cp ./.env $DOCKER_COMPOSE_PATH
	cp ./$DOCKER_COMPOSE_FILE $DOCKER_COMPOSE_PATH
	cd $DOCKER_COMPOSE_PATH
	docker-compose -f $DOCKER_COMPOSE_FILE up --remove-orphans -d $DOCKER_COMPOSE_SERVICES
else
	echo "Please create an .env file"
fi
cd $CURR_DIR
