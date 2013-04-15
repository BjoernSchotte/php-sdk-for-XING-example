<?php

function setTokensFromSession() {
    global $app;

    $conf = $app['XingClient.conf'];
    $conf['token'] = $app['session']->get('Xing.token');
    $conf['token_secret'] = $app['session']->get('Xing.token_secret');

    $app['XingClient.conf'] = $conf;
}

// welcome
$app->match('/', function() use ($app) {
    return $app['twig']->render('index.html.twig');
})->bind('homepage');

$app->get('/about', function() use ($app) {
    return $app['twig']->render('about.html.twig');
});

// login into Xing: request token and start authorization request
$app->get('/login', function() use ($app) {
    $client = \BjoernSchotte\XingClient::factory($app['XingClient.conf']);
    $client->XingRequestToken();

    $app['session']->set('Xing.token', $client->getConfig()->get('token'));
    $app['session']->set('Xing.token_secret', $client->getConfig()->get('token_secret'));

    $client->XingAuthorize();
});

// user is authorized, request access token
$app->get('/authorized', function() use ($app) {
    $token = $app['request']->get('oauth_token');
    $verifier = $app['request']->get('oauth_verifier');

    $app['session']->set('Xing.oauth_verifier', $verifier);

    $conf = $app['XingClient.conf'];
    $conf['token'] = $app['session']->get('Xing.token');
    $conf['token_secret'] = $app['session']->get('Xing.token_secret');

    $app['XingClient.conf'] = $conf;

    $client = \BjoernSchotte\XingClient::factory($app['XingClient.conf']);

    // request access token
    $client->XingAccessToken($verifier);

//    print "Access token " . $client->XingAccessToken . " - secret " . $client->XingAccessTokenSecret . "<br/>";

    $conf = $app['XingClient.conf'];
    $conf['token'] = $client->XingAccessToken;
    $conf['token_secret'] = $client->XingAccessTokenSecret;
    $app['XingClient.conf'] = $conf;

    $app['session']->set('Xing.token', $app['XingClient.conf']['token']);
    $app['session']->set('Xing.token_secret', $app['XingClient.conf']['token_secret']);
    $app['session']->set('Xing.userID', $client->XingUserId);

    $client = \BjoernSchotte\XingClient::factory($app['XingClient.conf']);

    // get user's ID card and save it to session
    $command = $client->getCommand('users.me.id_card');
    $result = $client->execute($command);
    //print "<pre>" . $command->getRequest() . "</pre>";

    $app['session']->set('Xing.id_card', $result["id_card"]);

    return $app->redirect('/about');
    //return $app['twig']->render('index.html.twig');
    // return 'Your User ID is ' . $client->XingUserId . '<br/>';
});

$app->get('/users/me', function() use ($app) {
    setTokensFromSession();
    $client = \BjoernSchotte\XingClient::factory($app['XingClient.conf']);

    // get user's ID card and save it to session
    $command = $client->getCommand('users.me');
    $result = $client->execute($command);

    return $app['twig']->render('users.me.html.twig', array('usersMe' => $result['users'][0]));
});

$app->get('/status_message', function() use ($app) {
    return $app['twig']->render('status_message.html.twig');
});

$app->post('/status_message', function() use ($app) {
    $msg = $app['request']->get('new_message');

    setTokensFromSession();
    $client = \BjoernSchotte\XingClient::factory($app['XingClient.conf']);

    $params = array(
        "id" => $app['session']->get('Xing.userID'),
        "message" => $msg
    );

    $command = $client->getCommand('status_message', $params);
    $result = $client->execute($command);

    return $app['twig']->render('status_message.html.twig', array(
        'post_response' => $result['response'],
        'statusCode' => $result['statusCode']
    ));
});

// find users by email addresses
$app->get('/find_by_emails', function() use ($app) {
    return $app['twig']->render('find_by_emails.html.twig');
});

$app->post('/find_by_emails', function() use ($app) {
    $emails = $app['request']->get('emails');

    setTokensFromSession();
    $client = \BjoernSchotte\XingClient::factory($app['XingClient.conf']);

    $params = array(
        "emails" => $emails
    );
    $command = $client->getCommand('users.find_by_emails', $params);
    $result = $client->execute($command);
//    var_dump($result['results']);

    return $app['twig']->render('find_by_emails.html.twig', array(
        'total' => $result['results']['total'],
        'items' => $result['results']['items'],
        'statusCode' => $result['statusCode']
    ));
});

// search for users
$app->get('/users', function() use ($app) {
    return $app['twig']->render('users.html.twig');
});

$app->post('/users', function() use ($app) {
    $id = $app['request']->get('id');

    setTokensFromSession();
    $client = \BjoernSchotte\XingClient::factory($app['XingClient.conf']);

    $params = array(
        "id" => $id
    );
    $command = $client->getCommand('users', $params);
    $result = $client->execute($command);
/*
    var_dump($result['users']);
    print "# Sent the following request: <br/>";
    print "<pre>" . $command->getRequest() . "</pre>";

    print "with response: <br/>";
    print "<pre>" . $command->getResponse() . "</pre><br/>";
*/

    return $app['twig']->render('users.html.twig', array(
        'total' => count($result['users']),
        'items' => $result['users'],
        'statusCode' => $result['statusCode']
    ));

});

// logout, destroy all session information, return to /
$app->get('/logout', function() use($app) {
    $app['session']->clear();
    return $app->redirect('/');
});

/*
$app->get('/', function () use ($app) {
    return $app->redirect('/hello');
});
*/