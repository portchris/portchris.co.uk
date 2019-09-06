#!/usr/bin/env bash

CURR_DIR=$(pwd)

if [ -f ./.env ]; then
	set -a
	. ./.env
	set +a
	cd $DOCKER_COMPOSE_PATH
	read -p "Shell as laradock user? [Y/y]? " -n 1 -r
	echo # (optional) move to a new line
	if [[ $REPLY =~ ^[Yy]$ ]]; then 
		docker-compose -f $DOCKER_COMPOSE_FILE exec --user laradock workspace bash -c 'cd '"$WEBROOT"'; exec "${SHELL:-sh}"'  
	else
		docker-compose -f $DOCKER_COMPOSE_FILE exec workspace bash -c 'cd '"$WEBROOT"'; exec "${SHELL:-sh}"' 
	fi
else
	echo "Please create an .env file"
fi
cd $CURR_DIR
