<?php

if(empty(REDDIT_USER) || empty(REDDIT_CLIENT_ID) || empty(REDDIT_CLIENT_SECRET)) {
	exit("Please set your Reddit username, client ID, and client secret in your Docker environment variables or config.php");
};

if(!file_exists('cache/token')) {
  mkdir('cache/token', 0755, true);
}
if(!file_exists('cache/token/token.txt')) {
  $tokenFile = fopen('cache/token/token.txt', 'w');
  fclose($tokenFile);
} elseif(time() - filectime('cache/token/token.txt') > 60 * 60 ) {
  unlink('cache/token/token.txt');
  $tokenFile = fopen('cache/token/token.txt', 'w');
  fclose($tokenFile);
}

$accessToken = file_get_contents('cache/token/token.txt');

if(empty($accessToken)) {
  $authString = base64_encode(REDDIT_CLIENT_ID . ':' . REDDIT_CLIENT_SECRET);
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, 'https://www.reddit.com/api/v1/access_token');
  curl_setopt($ch, CURLOPT_POST, 1);
  curl_setopt($ch, CURLOPT_POSTFIELDS, 'grant_type=client_credentials');
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_USERAGENT, 'web:toprss:1.0 (by /u/' . REDDIT_USER . ')');
  curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Basic ' . $authString));

  $response = curl_exec($ch);
  curl_close($ch);

  if(strpos($response, 'error') !== false) {
    die("There was an error authenticating with Reddit. Please check your username, client ID, and client secret.");
  }

  $accessToken = json_decode($response)->access_token;
  $tokenFile = fopen('cache/token/token.txt', 'w');
  fwrite($tokenFile, $accessToken);
  fclose($tokenFile);
}
