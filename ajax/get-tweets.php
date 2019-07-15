<?php
//Includes require globally
require '../vendor/autoload.php';
require '../config/config.php';

use Abraham\TwitterOAuth\TwitterOAuth;

$rsp = array(
    'success' => false,
    'data' => array(),
    'content' => '',
    'records' => 0,
    'error' => ''
);
try {
    //connect to db
    $conn = new PDO(
        'mysql:host='. $config['database']['host'] .';dbname='. $config['database']['dbname'], 
        $config['database']['username'], 
        $config['database']['password']
    );
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch(PDOException $e) {
    $rsp['error'] = $e->getMessage();
}

$tweetId = 0;
$autoupdate = false;
$tweetType = '';
$limit = '';

//twitter provides 64 bit numer, in case it is binary
if (isset($_GET['tweetId']) && preg_match('/\d{19,64}/', $_GET['tweetId'], $matches)) {
    $tweetId = $matches[0];
}

if (isset($_GET['autoupdate']) && $_GET['autoupdate'] == 'on') {
    $autoupdate = true;
}

if (isset($_GET['type']) && $_GET['type'] == 'new') {
    $tweetType = 'new';
    $limit = ' LIMIT 20';
}

//When there are no tweets on the page
if ($tweetType == 'new') {
    $tweets = $conn->prepare(
        'SELECT * FROM tweets ORDER BY tweet_create_at DESC, tweet_id DESC'. $limit . ';'
    );
//when there are tweets on the page
} else {
    $tweets = $conn->prepare(
        'SELECT * FROM tweets WHERE tweet_create_at > (SELECT tweet_create_at FROM tweets WHERE tweet_id = :tweetId) AND tweet_id != :tweetId ORDER BY tweet_create_at DESC, tweet_id DESC'. $limit . ';'
    );
}

$tweets->execute(array(
    'tweetId' => $tweetId
));
$rsp['records'] = $tweets->rowCount();

$m = new Mustache_Engine(array(
    'loader' => new Mustache_Loader_FilesystemLoader( dirname(__FILE__, 2) . '/views'),
));

//templates for tweets or message to load tweets
if ($tweets->rowCount() && $autoupdate) {
    $rsp['data']['tweets'] = $tweets->fetchAll();    

    $rsp['content'] = $m->render(
        'partials/tweet.mustache',
        $rsp['data']
    );
} else if ($tweets->rowCount() && !$autoupdate) {
    $rsp['content'] = $m->render(
        'partials/autoupload-alert.mustache',
        $rsp
    );
}

$conn = null;

$rsp['success'] = true;
header('Content-Type: application/json');
echo json_encode($rsp);