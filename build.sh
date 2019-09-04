#!/usr/bin/env bash

CURR_DIR=$(pwd)

if [ -z ${USER_ID+x} ]; then
	USER_ID=$(id -u)
fi

if [ -z ${GROUP_ID+x} ]; then
	GROUP_ID=$(id -g)
fi

if [ -f ./.env ]; then
	export $(grep -v '^#' ./.env | xargs)
	if [ -f "$DOCKER_COMPOSE_PATH$DOCKER_COMPOSE_FILE" ]; then
		rm -f $DOCKER_COMPOSE_PATH$DOCKER_COMPOSE_FILE
	fi
	cp ./$DOCKER_COMPOSE_FILE $DOCKER_COMPOSE_PATH
	cd $DOCKER_COMPOSE_PATH
	docker-compose -f $DOCKER_COMPOSE_FILE build --no-cache --build-arg UID=$USER_ID --build-arg GID=$GROUP_ID workspace $DOCKER_COMPOSE_SERVICES
else
	echo "Please create an .env file"
fi
cd $CURR_DIR