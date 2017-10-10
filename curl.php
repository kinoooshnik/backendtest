<?php
	function request($URL, $headers, $post) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $URL);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HEADER, 1);
		curl_setopt($ch, CURLOPT_POST, $post);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		$result = curl_exec($ch);  
		curl_close($ch);
		return $result;
	}
	
	function upload($URL, $path) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $URL);
		curl_setopt($ch, CURLOPT_PUT, 1);
		$fp = fopen($path, 'r');
		curl_setopt($ch, CURLOPT_INFILE, $fp);
		curl_setopt($ch, CURLOPT_INFILESIZE, filesize($path));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$result = curl_exec ($ch);
		fclose($fp);
		curl_close($ch);
		return $result;
	}
?>