<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Alphacode\Migrations\Validator;

Validator::validate(getcwd());