#!/usr/bin/env bash

CURR_DIR=$(pwd)

if [ -f ./.env ]; then
	set -a
	. ./.env
	set +a
	cd $DOCKER_COMPOSE_PATH
	read -p "Shell as root user? [Y/y]? " -n 1 -r
	echo # (optional) move to a new line
	if [[ $REPLY =~ ^[Yy]$ ]]; then 
		docker-compose -f $DOCKER_COMPOSE_FILE exec mysql mysql -uroot -p$MYSQL_ROOT_PASSWORD $DB_DATABASE  
	else
		docker-compose -f $DOCKER_COMPOSE_FILE exec mysql mysql -u$DB_USERNAME -p$DB_PASSWORD $DB_DATABASE  
	fi
else
	echo "Please create an .env file"
fi
cd $CURR_DIR
