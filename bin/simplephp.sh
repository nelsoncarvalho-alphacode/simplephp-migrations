#!/usr/bin/env php
<?php

require __DIR__ . '/../vendor/autoload.php';

use Alphacode\Migrations\SimplePHPCommand;

$cmd = new SimplePHPCommand();
$cmd->handle($argv);
