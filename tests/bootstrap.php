<?php

require_once '../vendor/autoload.php';

if (!$config = @include 'configuration.php') {
    $config = require 'configuration.php.dist';
}
