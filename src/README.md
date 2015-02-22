Laravel Base Project
=====================

[![Build Status](https://travis-ci.org/shin1x1/laravel-base-project.svg?branch=master)](https://travis-ci.org/shin1x1/laravel-base-project)

My Base Project with Laravel 4

## Installation

use `composer create-project` command.

```
$ composer create-project shin1x1/laravel-base-project . -s dev
```

## Enable Laravel 4 Debuger

[https://github.com/barryvdh/laravel-debugbar](https://github.com/barryvdh/laravel-debugbar)

```
$ php artisan debugbar:publish
```

## Deploy to Heroku

remove composer.lock in .gitignore.

```
$ vim .gitignore
composer.lock <--- remove it
```

git init.

```
$ git init
```

execute `heroku_create` command.

```
$ ./heroku_create
Creating mighty-sea-4703... done, stack is cedar
http://xxxxxxxxxxxxx.herokuapp.com/ | git@heroku.com:xxxxxxxxxxxx.git
Git remote heroku added
Setting config vars and restarting xxxxxxxxxxx... done, v3
LARAVEL_ENV: heroku
Adding heroku-postgresql on xxxxxxxxx... done, v5 (free)
...
```

deploy application to heroku.

```
$ git add .
$ git commit -m 'init'
$ git push heroku master
```


