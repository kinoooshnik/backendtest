<?php
	const YANDEX_DISK_URL = 'https://cloud-api.yandex.net/v1/disk/resources/';
	const ACCESS_TOKEN = '';
	const CLOUD_DIRECTORY = '/CodeXBackEndTest/';
	const FILE_FORMAT = '.jpg';
	
	if(isset($_FILES['photo'])) { //Загрузка фото
		if($_FILES['photo']['error'] == 0) {
			if(file_exists('./count.txt') == true) {
				$fp = fopen('./count.txt', 'r');
				$count = intval(fread($fp, filesize('./count.txt')));
				fclose($fp);
			}
			else $count = 0;
			$count++;
			$fp = fopen('./count.txt', 'w+');
			fwrite($fp, $count);
			fclose($fp);
			
			$fileName = $_FILES['photo']['tmp_name'];
			$newName = $count . FILE_FORMAT;
			
			$result = request(YANDEX_DISK_URL . 'upload?path=' . CLOUD_DIRECTORY . $newName . '&overwrite=true', array(
			'Authorization: OAuth ' . ACCESS_TOKEN
			), 0);
			
			upload(json_decode(stristr($result, '{'))->href, $fileName);
			
			echo 'Файл загружен. ID файла: ' . $count;
		}
		else echo 'Ошибка при загрузке файла. Код ошибки: ' . $_FILES['photo']['error'];
	}
	if(isset($_POST["URL"])) {
		echo '1';
		if(file_exists('./count.txt') == true) {
			$fp = fopen('./count.txt', 'r');
			$count = intval(fread($fp, filesize('./count.txt')));
			fclose($fp);
		}
		else $count = 0;
		$count++;
		$fp = fopen('./count.txt', 'w+');
		fwrite($fp, $count);
		fclose($fp);
		
		$newName = $count . FILE_FORMAT;
		
		$result = request(YANDEX_DISK_URL . 'upload?url=' . $_POST["URL"] . '&path=' . CLOUD_DIRECTORY . $newName, array(
		'Authorization: OAuth ' . ACCESS_TOKEN
		), 1);
		
		echo 'Загрузка файла начата. ID файла: ' . $count;
	}
	if(isset($_GET['method'])) {
		if($_GET['method'] == 'upload') {
			echo '
			<form enctype="multipart/form-data" action="/index.php?method=upload" method="POST">
			<input type="hidden" name="MAX_FILE_SIZE" value="10485760" />
			Загрузить картинку: <input name="photo" type="file" />
			<input type="submit" value="Отправить" />
			</form>
			<form action="/index.php?method=upload" method="POST">
			<p>Ссылка на картинку:
			<input name="URL" type="text" size="100">
			<input type="submit" value="Отправить"></p></form>';
		}
		else if($_GET['method'] == 'get') {
			if(isset($_GET['id'])) {
				$result = request(YANDEX_DISK_URL . 'download?path=/CodeXBackEndTest/' . $_GET['id'] . FILE_FORMAT, array(
				'Authorization: OAuth ' . ACCESS_TOKEN, 
				'Accept: application/json', 
				'Content-Type: application/json'
				), 0);
				if(FILE_FORMAT == '.jpeg' || FILE_FORMAT == '.jpg') $im = imagecreatefromjpeg(json_decode(stristr($result, '{'))->href);
				else if(FILE_FORMAT == '.png') $im = imagecreatefrompng(json_decode(stristr($result, '{'))->href);
				
				if($im != false){
					if(isset($_GET['filter'])) {	
						$strSize = str_replace('resize', '', str_replace('crop', '', $_GET['filter']));
						$new_width = explode("x", $strSize)[0];
						$new_hight = explode("x", $strSize)[1];
						
						$width = imagesx($im);
						$hight = imagesy($im);

						$image_p = imagecreatetruecolor($new_width, $new_hight);
						
						if(preg_match('/^crop[0-9]+x[0-9]+/', $_GET['filter']))
							imagecopyresampled($image_p, $im, 0, 0, $width/2-$new_width/2, $hight/2-$new_hight/2, $new_width, $new_hight, $new_width, $new_hight);
						else
							imagecopyresampled($image_p, $im, 0, 0, 0, 0, $new_width, $new_hight, $width, $hight);
						$im = $image_p;
					}
					
					header('Content-Type: image/' . str_replace('.', '', FILE_FORMAT));
					imagejpeg($im);
					imagedestroy($im);
				}
				else echo 'Ошибка при получении файла';
			}
		}
	}
	
	
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
