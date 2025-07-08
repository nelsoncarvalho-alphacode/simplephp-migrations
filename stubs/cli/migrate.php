<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/db.php';

use Alphacode\Migrations\Migrator;

$migrator = new Migrator();
$migrator->runUp();