#!/usr/bin/env bash

CURR_DIR=$(pwd)

if [ -z ${USER_ID+x} ]; then
	USER_ID=$(id -u)
fi

if [ -z ${GROUP_ID+x} ]; then
	GROUP_ID=$(id -g)
fi

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
	read -p "Use Docker cache? [Y/y]? " -n 1 -r
	echo # (optional) move to a new line
	if [[ $REPLY =~ ^[Yy]$ ]]; then 
		docker-compose -f $DOCKER_COMPOSE_FILE build --build-arg UID=$USER_ID --build-arg GID=$GROUP_ID workspace $DOCKER_COMPOSE_SERVICES $@
	else
		docker-compose -f $DOCKER_COMPOSE_FILE build --pull --no-cache --build-arg UID=$USER_ID --build-arg GID=$GROUP_ID workspace $DOCKER_COMPOSE_SERVICES $@
	fi
else
	echo "Please create an .env file"
fi
cd $CURR_DIR