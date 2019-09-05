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

On top of the usual [laradock .env sample](https://github.com/laradock/laradock/blob/master/env-example) I have made the following additions / alterations

```
### Paths #########################################################
# Point to the path of your applications code on your host
APP_CODE_PATH_HOST=../

### Workspace #####################################################
WORKSPACE_INSTALL_NPM_ANGULAR_CLI=true
WORKSPACE_INSTALL_XDEBUG=true

### Nginx #########################################################
# HTTP port that matches your custom docker-compose.yml public facing port
NGINX_HOST_HTTP_PORT=8080
# HTTPS port that matches your custom docker-compose.yml public facing port
NGINX_HOST_HTTPS_PORT=8443

### Portchris Specific ############################################
DOCKER_COMPOSE_SERVICES="php-fpm nginx mariadb"
DOCKER_COMPOSE_PATH="../laradock/"
DOCKER_COMPOSE_FILE="docker-compose.portchris.co.uk.yml"
```

### Scripts
- `start.sh` - Copies docker-compose.portchris.yml to laradock and start containers
- `build.sh` - Full rebuild of all used containers
- `stop.sh` - stop running containers
- `composer.sh` - run composer commands
- `artisan.sh` - Runs Laravel CLI tool inside workspace container
- `shell.sh` - Bash shell inside workspace container
- `npm.sh` - Runs Node Package Manager inside workspace container

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
