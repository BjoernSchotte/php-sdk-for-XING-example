<?php
// Cache
$app['cache.path'] = __DIR__ . '/../cache';

// Http cache
$app['http_cache.cache_dir'] = $app['cache.path'] . '/http';

// Twig cache
// $app['twig.options.cache'] = $app['cache.path'] . '/twig';

$app['debug'] = true;

$app['XingClient.conf'] = array(
    'consumer_key' => '<yourkey>',
    'consumer_secret' => '<yoursecret>',
    'token' => false,
    'token_secret' => false,
    'callback' => '<callback URL>',
);

require_once __DIR__ . '/prod_local.php';