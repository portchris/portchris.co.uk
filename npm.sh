#!/usr/bin/env bash

CURR_DIR=$(pwd)

if [ -f ./.env ]; then
	set -a
	. ./.env
	set +a
	cd $DOCKER_COMPOSE_PATH
	docker-compose -f $DOCKER_COMPOSE_FILE exec --user laradock workspace npm $@ --prefix $WEBROOT
else
	echo "Please create an .env file"
fi
cd $CURR_DIR
