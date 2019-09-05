#!/usr/bin/env bash

CURR_DIR=$(pwd)

if [ -f ./.env ]; then
	set -a
	. ./.env
	set +a
	cd $DOCKER_COMPOSE_PATH
	docker-compose stop -t0
	docker-compose rm -f
else
	echo "Please create an .env file"
fi
cd $CURR_DIR
