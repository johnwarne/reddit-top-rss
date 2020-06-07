<?php

function cache_set($key, $val) {
	$val = var_export($val, true);
	// HHVM fails at __set_state, so just use object cast for now
	// $val = str_replace('stdClass::__set_state', '(object)', $val);
	// Write to temp file first to ensure atomicity
	// $tmp = "cache/tmp/$key." . uniqid('', true) . '.tmp';
	$tmp = "cache/tmp/". urlencode($key) . uniqid('', true) . '.tmp';
	// file_put_contents('cache/tmp/test.txt', date('YmdGis'));
	// file_put_contents($tmp, '<?php $val = ' . $val . ';', LOCK_EX);
	file_put_contents($tmp, '<?php $val = ' . $val . ';', LOCK_EX);
	rename($tmp, "cache/tmp/" . urlencode($key));
	// @include "cache/tmp/". urlencode($key);
}



function cache_get($key) {
	@include "cache/tmp/" . urlencode($key);
	return isset($val) ? $val : false;
}


// Get file
function getFile($url, $requestType, $cachedFileLocation, $cacheExpiration, $cacheObjectKey) {
	if($requestType == "mercuryJSON") {
		// Get Mercury content
		if(CACHE_MERCURY_CONTENT == true) {
			// Use cached object if present
			if (cache_get($cacheObjectKey)) {
				return cache_get($cacheObjectKey);
			// Use cached file if present
			// } elseif (file_exists($cachedFileLocation) && time()-filemtime($cachedFileLocation) < $cacheExpiration) {
			// 	return file_get_contents($cachedFileLocation, true);
			// 	cache_set($cacheObjectKey, $mercuryJSON);
			} else {
				// Otherwise, CURL the file and cache it
				$ch = curl_init();
				$curlOptHttpHeaderArray = ['Content-Type: application/json'];
				if (defined('MERCURY_API_KEY') && !empty(MERCURY_API_KEY)) {
					$curlOptHttpHeaderArray[] = 'x-api-key: ' . MERCURY_API_KEY;
				}
				curl_setopt($ch, CURLOPT_URL, MERCURY_URL . '/parser?url=' . $url);
				curl_setopt($ch, CURLOPT_HTTPHEADER, $curlOptHttpHeaderArray);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				$mercuryJSON = curl_exec($ch);
				if(!$mercuryJSON) {
					die("Connection Failure");
				}
				// file_put_contents($cachedFileLocation, $mercuryJSON);
				cache_set($cacheObjectKey, $mercuryJSON);
				return $mercuryJSON;
				curl_close($ch);
			}
		} else {
			// Regularly CURL the Mercury content
			$curl = curl_init();
			$curlOptHttpHeaderArray = ['Content-Type: application/json'];
			if (defined('MERCURY_API_KEY') && !empty(MERCURY_API_KEY)) {
				$curlOptHttpHeaderArray[] = 'x-api-key: ' . MERCURY_API_KEY;
			}
			curl_setopt($curl, CURLOPT_URL, MERCURY_URL . '/parser?url=' . $itemDataUrl);
			curl_setopt($curl, CURLOPT_HTTPHEADER, $curlOptHttpHeaderArray);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
			$mercuryJSON = curl_exec($curl);
			if(!$mercuryJSON) {
				die("Connection Failure");
			}
			return $mercuryJSON;
			curl_close($ch);
		}
	} elseif($requestType == "redditJSON" && CACHE_REDDIT_JSON == true) {
		// Get Reddit JSON file
		// Use cached file if present
		if (cache_get($url)) {
			return cache_get($url);
		// // } elseif (file_exists($cachedFileLocation) && time() - filemtime($cachedFileLocation) < $cacheExpiration) {
		// // 	return file_get_contents($cachedFileLocation, true);
		} else {
			// Otherwise, CURL the file and cache it
      $ch = curl_init();
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_TIMEOUT, 30);
			curl_setopt($ch, CURLOPT_URL, $url);
			$curledFile = curl_exec($ch);
			// file_put_contents($cachedFileLocation, $curledFile);
			// cache_set($cacheObjectKey, 'this is a thingsojfpda');
			// return cache_get($cacheObjectKey);
			cache_set($url, json_decode($curledFile, true));
			curl_close($ch);
			return cache_get($url);
		}
  } else {
		// Regularly CURL the file
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, 30);
		curl_setopt($ch, CURLOPT_URL, $url);
		$curledFile = curl_exec($ch);
		cache_set($cacheObjectKey, $curledFile);
		return $curledFile;
		curl_close($ch);
	}
}


// Get directory size
// https://www.a2zwebhelp.com/folder-size-php
function directorySize($dir) {
	$countSize = 0;
	$count = 0;
	$dirArray = scandir($dir);
	foreach($dirArray as $key => $filename) {
		if($filename != ".." && $filename != ".") {
			if(is_dir($dir . "/" . $filename)) {
				$newFolderSize = directorySize($dir . "/" . $filename);
				$countSize = $countSize + $newFolderSize;
				} elseif(is_file($dir . "/" . $filename)) {
					$countSize = $countSize + filesize($dir . "/" . $filename);
					$count++;
			}
		}
	}
	return $countSize;
}


// Format size in bytes
// https://www.a2zwebhelp.com/folder-size-php
function sizeFormat($bytes) {
	$kb = 1024;
	$mb = $kb * 1024;
	$gb = $mb * 1024;
	$tb = $gb * 1024;
	if (($bytes >= 0) && ($bytes < $kb)) {
		return $bytes . " B";
	} elseif (($bytes >= $kb) && ($bytes < $mb)) {
		return ceil($bytes / $kb) . " KB";
	} elseif (($bytes >= $mb) && ($bytes < $gb)) {
		return ceil($bytes / $mb) . " MB";
	} elseif (($bytes >= $gb) && ($bytes < $tb)) {
		return ceil($bytes / $gb) . " GB";
	} elseif ($bytes >= $tb) {
		return ceil($bytes / $tb) . " TB";
	} else {
		return $bytes . " B";
	}
}