<?php
	
    //Includes require globally
    require 'vendor/autoload.php';
    require 'config/config.php';	
	
	//Connect to the database
	try {
        $conn = new PDO(
            'mysql:host='. $config['database']['host'] .';dbname='. $config['database']['dbname'], 
            $config['database']['username'], 
            $config['database']['password']
        );
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

	} catch (Exception $e) {
		echo 'Caught Error: '.$e->getMessage()."\n";
    }
    
    //Get all tweets
    $rspTweets = $conn->query('SELECT * FROM tweets ORDER BY tweet_create_at desc LIMIT 20');
    $tweets = array();

	foreach($rspTweets as $row) {
        $date = new DateTime($row['tweet_create_at']);
        $row['tweet_create_at'] = $date->format('H:i:s d M Y');
        array_push($tweets, $row);
    }
    //Load templates
    $m = new Mustache_Engine(array(
        'loader' => new Mustache_Loader_FilesystemLoader(dirname(__FILE__) . '/views'),
    ));
   
    //assign to the template content
	$data = array(
        'content' =>  'Brexit Tweets',
        'description' => 'Stay up to date with today\'s tweets',
        'tweets' => $tweets
    );

	/****************************************
	*  			    PAGE BUILDER       		*
	*										*
	****************************************/		

    echo $m->render(
        'default',
        $data
    );

    $conn = null;
?>