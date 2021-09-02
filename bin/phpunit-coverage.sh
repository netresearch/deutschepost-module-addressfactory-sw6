#!/usr/bin/env bash

composer dump-autoload
/app/vendor/bin/phpunit --coverage-html coverage
