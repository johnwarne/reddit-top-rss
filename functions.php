<?php

// Get file
function getFile($url, $requestType, $cachedFileLocation, $cacheExpiration) {
	if($requestType == "mercuryJSON") {
		// Get Mercury content
		if(CACHE_MERCURY_CONTENT) {
			// Use cached file if present
			if (file_exists($cachedFileLocation) && time()-filemtime($cachedFileLocation) < $cacheExpiration) {
				return file_get_contents($cachedFileLocation, true);
			} else {
				// Otherwise, CURL the file and cache it
				$ch = curl_init();
				$curlOptHttpHeaderArray = ['Content-Type: application/json'];
				if (defined('MERCURY_API_KEY')) {
					$curlOptHttpHeaderArray[] = 'x-api-key: ' . MERCURY_API_KEY;
				}
				curl_setopt($ch, CURLOPT_URL, MERCURY_URL . '/parser?url=' . $url);
				curl_setopt($ch, CURLOPT_HTTPHEADER, $curlOptHttpHeaderArray);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				$mercuryJSON = curl_exec($ch);
				if(!$mercuryJSON) {
					die("Connection Failure");
				}
				file_put_contents($cachedFileLocation, $mercuryJSON);
				return $mercuryJSON;
				curl_close($ch);
			}
		} else {
			// Regularly CURL the Mercury content
			$curl = curl_init();
			$curlOptHttpHeaderArray = ['Content-Type: application/json'];
			if (defined('MERCURY_API_KEY')) {
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
	} elseif($requestType == "redditJSON" && CACHE_REDDIT_JSON) {
		// Get Reddit JSON file
		// Use cached file if present
		if (file_exists($cachedFileLocation) && time() - filemtime($cachedFileLocation) < $cacheExpiration) {
			return file_get_contents($cachedFileLocation, true);
		} else {
			// Otherwise, CURL the file and cache it
      $ch = curl_init();
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_TIMEOUT, 30);
			curl_setopt($ch, CURLOPT_URL, $url);
			$curledFile = curl_exec($ch);
			file_put_contents($cachedFileLocation, $curledFile);
			return $curledFile;
			curl_close($ch);
		}
  } else {
		// Regularly CURL the file
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, 30);
		curl_setopt($ch, CURLOPT_URL, $url);
		$curledFile = curl_exec($ch);
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