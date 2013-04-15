<?php
/**
 * sample web application for demonstrating php-sdk-for-XING capabilities
 *
 * it is based on Silex, the micro framework
 *
 * @copyright (C) 2013 by Björn Schotte <bjoern.schotte@googlemail.com> <bjoern.schotte@mayflower.de>
 * @license Apache 2.0 license
 */

ini_set('display_errors', 0);
require_once __DIR__.'/../vendor/autoload.php';

$app = new Silex\Application();

require __DIR__.'/../resources/config/prod.php';
require __DIR__.'/../src/app.php';
require __DIR__.'/../src/controllers.php';

$app['http_cache']->run();
