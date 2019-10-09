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
WORKSPACE_NPM_PORT=4200
JWT_SECRET=<MY_SECRET>

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

###########################################################
################## Laradock General Setup #################
###########################################################

## MySQL
# Prefer latest 5 rather than 8 since laravel 5/6 is not compatible yet
MYSQL_VERSION=5.7
MYSQL_DATABASE=MY_USER
MYSQL_USER=MY_DATABASE
MYSQL_PASSWORD=MY_PASSWORD
# Specific external port for portchris database access
MYSQL_PORT=8306
MYSQL_ROOT_PASSWORD=MY_ROOT_PASSWORD
MYSQL_ENTRYPOINT_INITDB=./mysql/docker-entrypoint-initdb.d

## Workspace
WORKSPACE_INSTALL_NPM_GULP=true
WORKSPACE_INSTALL_NPM_ANGULAR_CLI=true
WORKSPACE_INSTALL_XDEBUG=true
# User/Group IDs = 1000 or 1001
WORKSPACE_PUID=1001
WORKSPACE_PGID=1001
```

## Nginx
Portchris adds to and uses the default laradock nginx configurations, I have manipulated the docker and env config to work with [Docker Nginx Proxy](https://github.com/jwilder/nginx-proxy) & [Docker Letsencrypt Nginx Proxy Companion](https://github.com/JrCs/docker-letsencrypt-nginx-proxy-companion)

In the laradock container `../laradock/nginx/sites` (typically) we to add config for both the frontend and backend:

Angular JS App (frontend) - portchris.co.uk.app.conf:
```
server {

    listen <MY_NGINX_VIRTUAL_PORT_ENV_VAR>;
    listen [::]:<MY_NGINX_VIRTUAL_PORT_ENV_VAR>;

    server_name portchris.portchris.co.uk;
    root /var/www/portchris.co.uk/dist;
    index index.html index.htm;

    location / {
         try_files $uri $uri/ /index.html$is_args$args;
    }

    location ~ /\.ht {
        deny all;
    }

    error_log /var/log/nginx/app_error.log;
    access_log /var/log/nginx/app_access.log;
}

```

Laravel (backend) - portchris.co.uk.laravel.conf
```
server {

    listen <MY_NGINX_VIRTUAL_PORT_ENV_VAR>;
    listen [::]:<MY_NGINX_VIRTUAL_PORT_ENV_VAR>;

    # For https
    # listen 443 ssl;
    # listen [::]:443 ssl ipv6only=on;
    # ssl_certificate /etc/nginx/ssl/default.crt;
    # ssl_certificate_key /etc/nginx/ssl/default.key;

    server_name api.portchris.portchris.co.uk;
    root /var/www/portchris.co.uk/public;
    index index.php index.html index.htm;

    location / {
         try_files $uri $uri/ /index.php$is_args$args;
    }

    location ~ \.php$ {
        try_files $uri /index.php =404;
        fastcgi_pass php-upstream;
        fastcgi_index index.php;
        fastcgi_buffers 16 16k;
        fastcgi_buffer_size 32k;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        #fixes timeouts
        fastcgi_read_timeout 600;
        include fastcgi_params;
    }

    location ~ /\.ht {
        deny all;
    }

    error_log /var/log/nginx/laravel_error.log;
    access_log /var/log/nginx/laravel_access.log;
}
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
./laravel.sh vendor:publish --provider="Tymon\JWTAuth\Providers\LaravelServiceProvider" 
./laravel.sh jwt:generate
```

Seed the DB with the basic table
```
./laravel.sh db:seed
```

Import your story. Portchris conforms to the [ChoiceScript JS format](https://choicescriptdev.fandom.com/wiki/Script), So please refer to this structure for your stories. I have the following stories by default (in order):

```
./storage/story/startup.txt
./storage/story/manager.txt
./storage/story/ending.txt
```
`startup` is the ChoiceScript definition for the beginning of the story, all stories should begin with this chapter

They are imported into Laravel using a custom command like so:
```
./laravel.sh portchris:story:import startup
./laravel.sh portchris:story:import manager
./laravel.sh portchris:story:import ending
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
./npm.sh install
```
- After `npm install` again run this command to install `bootstrap, tether and jquery`
```
./npm.sh install bootstrap@next
```
- Download [laravel-api](https://github.com/eliyas5044/laravel-api), which I've used as a RESTful api.
- Run your `angular` app by this command
```
./npm.sh start
```

## Deployment

```
./shell.sh
ng build --prod
```

Enjoy!
