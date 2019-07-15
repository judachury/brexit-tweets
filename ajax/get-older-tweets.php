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

//twitter provides 64 bit numer, in case it is binary
if (isset($_GET['tweetId']) && preg_match('/\d{19,64}/', $_GET['tweetId'], $matches)) {
    $tweetId = $matches[0];
}

$tweets = $conn->prepare(
    'SELECT * FROM tweets WHERE tweet_create_at < (SELECT tweet_create_at FROM tweets WHERE tweet_id = :tweetId) AND tweet_id != :tweetId ORDER BY tweet_create_at DESC, tweet_id DESC LIMIT 20;'
);

$tweets->execute(array(
    'tweetId' => $tweetId
));
$rsp['records'] = $tweets->rowCount();

$m = new Mustache_Engine(array(
    'loader' => new Mustache_Loader_FilesystemLoader( dirname(__FILE__, 2) . '/views'),
));

if ($tweets->rowCount()) {
    $rsp['data']['tweets'] = $tweets->fetchAll();    

    $rsp['content'] = $m->render(
        'partials/tweet.mustache',
        $rsp['data']
    );
}

$conn = null;

$rsp['success'] = true;
header('Content-Type: application/json');
echo json_encode($rsp);