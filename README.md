# Portchris Portfolio Site

## Environment

### Setup
[Clone the laradock repo](https://laradock.io/getting-started) and structure like so:
```
+ laradock
+ portchris.co.uk
``` 

Custom docker-compose.portchris.yml will need copying from portchris.co.uk to laradock directory 

### Variables

On top of the usual [laradock .env sample](https://github.com/laradock/laradock/blob/master/env-example) AND the [Laravel .env.sample](https://github.com/laravel/laravel/blob/master/.env.example), I have made the following additions / alterations:
```
###########################################################
#################### porthris.co.uk #######################
###########################################################

WEBROOT=/var/www/portchris.co.uk
DOCKER_COMPOSE_SERVICES="workspace php-fpm php-worker nginx mysql redis redis-cluster redis-webui docker-in-docker docker-registry docker-web-ui"
DOCKER_COMPOSE_PATH="../laradock/"
DOCKER_COMPOSE_FILE="docker-compose.portchris.co.uk.yml"
NGINX_VIRTUAL_ROOT=/var/www/portchris.co.uk/public
NGINX_VIRTUAL_HOST=my.domain.com,api.my.domain.com
NGINX_VIRTUAL_PORT=8082
NGINX_VIRTUAL_PROTO=http
NGINX_LETSENCRYPT_EMAIL=my@email.com
NGINX_LETSENCRYPT_HOST=my.domain.com,api.my.domain.com
NGINX_LETSENCRYPT_TEST=true
NGINX_ENABLE_IPV6=true

###########################################################
################## Laravel General Setup ##################
###########################################################
APP_NAME="Chris Rogers Full-stack Web Developer"
APP_ENV=local
APP_KEY="MY_BASE64_KEY"
APP_DEBUG=true
APP_URL=http://api.my.domain.com/

LOG_CHANNEL=stack

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306 # Internal port inside container
DB_DATABASE=portchris
DB_USERNAME=portchris
DB_PASSWORD=portchris

BROADCAST_DRIVER=log
CACHE_DRIVER=file
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=8379

MAIL_DRIVER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME="BLAH"
MAIL_PASSWORD="BLAH"
MAIL_ENCRYPTION=null
```

### Scripts
- `start.sh` - Copies docker-compose.portchris.yml to laradock and start containers
- `build.sh` - Full rebuild of all used containers
- `stop.sh` - stop running containers
- `composer.sh` - run composer commands
- `laravel.sh` - Runs Laravel artisan CLI tool inside workspace container
- `shell.sh` - Bash shell inside workspace container
- `npm.sh` - Runs Node Package Manager inside workspace container
- `mysql.sh` - Shell into the MySQL container and login to the database

## Laravel 

### Setup
Create app key, store this in .env:
```
./laravel.sh key:generate
```

Create the JSON Web Token for the API
```
./laravel.sh jwt:generate
```

Seed the DB after migration with the basic info
```
./laravel.sh migrate:install
```

## Front-end Development

### Angular Js

This project was generated with [angular-cli](https://github.com/angular/angular-cli) version 1.0.0-rc.1.

#### Development server
First you have to install [angular-cli](https://github.com/angular/angular-cli).

#### angular-laravel
- First clone via `git bash` or download.
- Go to your root folder and run this command
```
npm install
```
- After `npm install` again run this command to install `bootstrap, tether and jquery`
```
npm install bootstrap@next
```
- Download [laravel-api](https://github.com/eliyas5044/laravel-api), which i used as a RESTful api.
- Run your `angular` app by this command
```
ng serve -o
```
and run your `laravel` api by this command
```
php artisan serve
```
You will see this app will load data from your api.

Enjoy!
