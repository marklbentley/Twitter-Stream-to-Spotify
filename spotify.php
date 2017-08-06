<?php

class Spotify{

public function __construct(){
    $this->client_id = 'CLIENT_ID';
    $this->client_secret = 'CLIENT_SECRET';
	$this->refresh = 'REFRESH_TOKEN';//this is fun to get but all is in the Spotify API Documentation
  }
 
 

function authenticate(){
	
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL,            'https://accounts.spotify.com/api/token' );
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1 );
	curl_setopt($ch, CURLOPT_POST,           1 );
	curl_setopt($ch, CURLOPT_POSTFIELDS,     'grant_type=refresh_token&refresh_token='.$this->refresh ); 
	curl_setopt($ch, CURLOPT_HTTPHEADER,     array('Authorization: Basic '.base64_encode($this->client_id.':'.$this->client_secret))); 

	$result=curl_exec($ch);
	curl_close($ch);

	$result = json_decode($result);
	$token = $result->access_token;
	
	return $token;
	
}


function getTrack($song, $artist){
	
	$token = $this->authenticate();
	
	
	$artist = urlencode(str_replace('&', 'and', $artist));
	$song = urlencode($song);
	$query = $song.'%20artist:'.$artist.'&type=track&limit=1';
		
	$ch2 = curl_init();
	curl_setopt($ch2, CURLOPT_URL, 			'https://api.spotify.com/v1/search?q='.$query);
	curl_setopt($ch2, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch2, CURLOPT_CUSTOMREQUEST,  "GET");
	curl_setopt($ch2, CURLOPT_HTTPHEADER,     array('Authorization: Bearer '.$token));

	$result = curl_exec($ch2);	
	$result = json_decode($result);
    $track_id = $result->tracks->items[0]->id;
    curl_close ($ch2);

	return $track_id;
	
}

function updatePlaylist($tracks, $playlist) {
	
	$tracks = json_encode($tracks);
	$uris = "{ \"uris\" : ".$tracks."}";
	
	$token = $this->authenticate();
    $ch3 = curl_init();
	curl_setopt($ch3, CURLOPT_URL, 			'https://api.spotify.com/v1/users/USERNAME/playlists/'.$playlist.'/tracks');
	curl_setopt($ch3, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch3, CURLOPT_POSTFIELDS, 	$uris);
	curl_setopt($ch3, CURLOPT_CUSTOMREQUEST,  "PUT");
	curl_setopt($ch3, CURLOPT_HTTPHEADER,     array('Authorization: Bearer '.$token));
	
	$result = curl_exec($ch3);
	
	return $result;
	
}

function addToPlaylist($tracks, $playlist) {
	
	$tracks = json_encode($tracks);
	$uris = "{ \"uris\" : ".$tracks."}";
	
	$token = $this->authenticate();
    $ch4 = curl_init();
	curl_setopt($ch4, CURLOPT_URL, 			'https://api.spotify.com/v1/users/USERNAME/playlists/'.$playlist.'/tracks');
	curl_setopt($ch4, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch4, CURLOPT_POSTFIELDS, 	$uris);
	curl_setopt($ch4, CURLOPT_CUSTOMREQUEST,  "POST");
	curl_setopt($ch4, CURLOPT_HTTPHEADER,     array('Authorization: Bearer '.$token));
	
	$result = curl_exec($ch4);
	
	return $result;
	
	
}


}
?>