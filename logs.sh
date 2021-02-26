#!/usr/bin/env bash

CURR_DIR=$(pwd)

if [ -f ./.env ]; then
	set -a
	. ./.env
	set +a
	cd $DOCKER_COMPOSE_PATH	
	docker-compose -f $DOCKER_COMPOSE_FILE logs --tail 100 -f $@
else
	echo "Please create an .env file"
fi
cd $CURR_DIR

