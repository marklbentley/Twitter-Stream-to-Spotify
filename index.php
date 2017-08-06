<?php

/* 
Using Twitter feed to update Spotify playlist 
This may not be exact as uses a time period rather than exact list.
Live could quite possibly start/run late/early
But is best solution to having to rely on Presenters to update also allows for instant production after live show.
*/

//call in all the files that do the magic
require_once('./TwitterAPIExchange.php');
require_once('./spotify.php');

//get which show
$show = $_GET['show'];

switch($show){
	
	case 'show1':
		$startTime = '9';
		$endTime = '10';
		$playlist = 'PLAYLIST_ID';
		$tweetCount = '20';
		break;
		
	case 'show2':
		$startTime = '7';
		$endTime = '10';
		$playlist = 'PLAYLIST_ID';
		$tweetCount = '60';
		break;
		
	case 'show3':
		$startTime = '7';
		$endTime = '10';
		$playlist = 'PLAYLIST_ID';
		$tweetCount = '60';
		break;
	
}

//Get last X tweets because twitter doesn't allow to search by time range
$settings = array(
    'oauth_access_token' => "OAUTH_ACCESS_TOKEN",
    'oauth_access_token_secret' => "OAUTH_ACCESS_TOKEN",
    'consumer_key' => "CONSUMER_KEY",
    'consumer_secret' => "CONSUMER_SECRET"
);

$url = "https://api.twitter.com/1.1/statuses/user_timeline.json";
$getfield = "?screen_name=TWITTER_NAME&count=".$tweetCount;
$requestMethod = "GET";

$twitter = new TwitterAPIExchange($settings);
$twitterResponse = $twitter->setGetfield($getfield)
	->buildOauth($url, $requestMethod)
	->performRequest();

//function to convert times to unix
function getTimes($s,$e) {
	
	$tz = 'Europe/London';
	$now = new DateTime("now", new DateTimeZone($tz));
	$y = $now->format('Y');
	$m = $now->format('m');
	$d = $now->format('j');
		
	$st = new DateTime();
	$st -> setTimezone(new DateTimeZone($tz));
	$st -> setDate($y, $m, $d);
	$st -> setTime($s, 0, 0);
	$showStart = $st->format('U');
	
	$st -> setTime($e, 5, 0);
	$showEnd = $st->format('U');
		
	return array($showStart, $showEnd);
	
}	

//function to get song and artist from tweet
function getDeets($tweet) {
	
	$text = $tweet['text'];
	$text = str_replace('#nowplaying', '', $text);
	$text = explode(' by ', $text);
	
	return $text;
	
}

//extract data from twitter response
function extractFromTwitter($twitterResponse, $start, $end) {
	
	$twResponse = json_decode($twitterResponse, JSON_OBJECT_AS_ARRAY);
	$twResponse = array_reverse($twResponse);
	
	$trackList = array();
	
	for($i = 0; $i < count($twResponse); $i++) {
		
		//get the time tweeted
		$tweeted = $twResponse[$i]['created_at'];
		$datetime = new DateTime($tweeted);
		$time = $datetime->format('U');
		
		//get the times for comparison
		$times = getTimes($start, $end);
		
		//if between start and end times add to array
		if($time > $times[0] && $time < $times[1]){
			
			//get song and artist and push to array
			$deets = getDeets($twResponse[$i]);
			array_push($trackList, $deets);
			 
		}
		
	}
	
	return $trackList;
}



//array of songs and artists
$playList = extractFromTwitter($twitterResponse, $startTime, $endTime);
$spotify = new Spotify();
$spotifyArray = array();

for($i=0; $i < count($playList); $i++){
	
	$id = $spotify->getTrack($playList[$i][0], $playList[$i][1]);
	if($id){
		array_push($spotifyArray, 'spotify:track:'.$id);
	}
	
	
	
}

//do the updates
if($show == 'freespinpm') { //if adding to playlist
	
	echo $spotify -> addToPlayList($spotifyArray, $playlist);
	
}else{ //replace playlist
	
	echo $spotify -> updatePlayList($spotifyArray, $playlist);

}



?>