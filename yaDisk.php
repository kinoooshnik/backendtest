<?php
	/*
	ACCESS_TOKEN
	*/
	include_once './config.php';
	include_once './curl.php';
	
	function cloudUploadFile($newName, $fileName) {	
		$result = request(YANDEX_DISK_URL . 'upload?path=' . CLOUD_DIRECTORY . $newName . '&overwrite=true', array(
		'Authorization: OAuth ' . ACCESS_TOKEN
		), 0);
		
		upload(json_decode(stristr($result, '{'))->href, $fileName);
	}
	
	function cloudUploadURL($newName, $URL) {	
		request(YANDEX_DISK_URL . 'upload?url=' . $_POST["URL"] . '&path=' . CLOUD_DIRECTORY . $newName, array(
		'Authorization: OAuth ' . ACCESS_TOKEN
		), 1);
	}
	
	function cloudURLGet($URL){
	
		$result = request(YANDEX_DISK_URL . 'download?path=' . $URL, array(
		'Authorization: OAuth ' . ACCESS_TOKEN, 
		'Accept: application/json', 
		'Content-Type: application/json'
		), 0);
		
		return json_decode(stristr($result, '{'))->href;
	}
?>