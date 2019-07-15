<?php
//Includes require globally
require '../vendor/autoload.php';
require '../config/config.php';
require '../vendor/vadersentiment/vadersentiment.php';

use Abraham\TwitterOAuth\TwitterOAuth;

$rsp = array(
    'success' => false,
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

//get twitter connection ready
$connection = new TwitterOAuth(
    $config['twitter']['key'], 
    $config['twitter']['secret'], 
    $config['twitter']['accessToken'], 
    $config['twitter']['accessTokenSecret']
);

//get the id of the last tweet to get tweets after that id.
//Get tweets with the newst date and take the highest tweet_id (Twitter still support incremental tweets' id)
$rspTweets = $conn->query(
    'SELECT tweet_id, tweet_create_at FROM tweets WHERE tweet_create_at = (SELECT MAX(tweet_create_at) FROM tweets) ORDER BY tweet_id DESC LIMIT 1'
);
$latestTweet = $rspTweets->fetch();

//prepare twitter query
$q = array(
    'q' => 'brexit',
    'geocode'  => '54.69556,-4.04584,535km',
    'result_type' => 'recent', 
    'count' => 100 
);

//Make sure get the latest tweets which are not stored
if ($rspTweets->rowCount()) {
    $q['since_id'] = $latestTweet['tweet_id'];
}

//twitter search endpoint
$statuses = $connection->get('search/tweets', $q);

//prepare statement to save tweets
$query = $conn->prepare('INSERT INTO tweets 
(tweet_id, tweet_user_name, tweet_text, tweet_create_at, tweet_user_screenname, tweet_user_image_url, twitter_account_location, tweet_location, sentiment, sentiment_icon) 
SELECT :tweet_id, :tweet_user_name, :tweet_text, :tweet_create_at, :tweet_user_screenname, :tweet_user_image_url, :twitter_account_location, :tweet_location, :sentiment, :sentiment_icon
    WHERE NOT EXISTS (SELECT * FROM tweets WHERE tweet_id = :tweet_id)
');

try {

    
    $sentimenter = new SentimentIntensityAnalyzer();

    //save tweets into db
    foreach ($statuses->statuses as $tweet) {

        //calculate sentiment
        $result = $sentimenter->getSentiment($tweet->text);
        $sentiment = 'neutral';
        $icons = array(
            'neutral' => 'meh',
            'positive' => 'smile',
            'negative' => 'frown',
        );
        if ($result['compound'] >= 0.05) {
            $sentiment = 'positive';
        } else if ($result['compound'] <= -0.05) {
            $sentiment = 'negative';
        }

        $data = array(
            ':tweet_id' => $tweet->id,
            ':tweet_user_name' => $tweet->user->name,
            ':tweet_text' => $tweet->text,
            ':tweet_create_at' => $tweet->created_at,
            ':tweet_user_screenname' => $tweet->user->screen_name,
            ':tweet_user_image_url' => $tweet->user->profile_image_url,
            ':twitter_account_location' => $tweet->user->location,
            ':tweet_location' => null,
            ':sentiment' => $sentiment,
            ':sentiment_icon' => $icons[$sentiment]
        );

        //tweets' location is not available most of the time
        if (!is_null($tweet->place)) {
            $data[':tweet_location'] = $tweet->place->country_code;
        }

        $query->execute($data);

        $rsp['records']++;
    }

    $rsp['success'] = true;

} catch (\Throwable $e) {
    $rsp['error'] = $e->getMessage();
}

$conn = null;

//header('Content-Type: application/json');
echo json_encode($rsp);
?>