<?php
	const YANDEX_DISK_URL = 'https://cloud-api.yandex.net/v1/disk/resources/';
	const CLOUD_DIRECTORY = '/CodeXBackEndTest/';
	
	/*
	ACCESS_TOKEN
	DB_LOCATION
	DB_USER
	DB_PASSWORD
	DB_NAME
	*/
	include_once './config.php';
	include_once './yaDisk.php';
	
	$mysqli = mysqli_connect(DB_LOCATION, DB_USER, DB_PASSWORD, DB_NAME);
	
	if(isset($_FILES['photo'])) { //Загрузка фото
		if($_FILES['photo']['error'] == 0) {
			if(strstr($_FILES['photo']['name'], '.jpg') != false || strstr($_FILES['photo']['name'], '.jpeg') != false) $fileFormat = '.jpg';
			else if(strstr($_FILES['photo']['name'], '.png') != false) $fileFormat = '.png';
			else if(strstr($_FILES['photo']['name'], '.gif') != false) $fileFormat = '.gif';
			else if(strstr($_FILES['photo']['name'], '.bmp') != false) $fileFormat = '.bmp';
			else { 
				echo 'Ошибка при загрузке файла. Код ошибки: -1(Foramt error)';
				goto formaterror;
			}
				
			$res = $mysqli->query("SELECT max(id) FROM `photo`");
			$row = $res->fetch_assoc();
			
			$fileName = $_FILES['photo']['tmp_name'];
			$newName = ($row['max(id)'] + 1) . $fileFormat;
			
			cloudUploadFile($newName, $fileName);
			
			$mysqli->query("INSERT INTO `photo`(`format`) VALUES ('" . str_replace('.', '', $fileFormat) . "')");
			
			echo 'Файл загружен. ID файла: ' . ($row['max(id)'] + 1);
			formaterror:
		}
		else echo 'Ошибка при загрузке файла. Код ошибки: ' . $_FILES['photo']['error'];
	}
	else if(isset($_POST["URL"])) {
		if(strstr($_POST["URL"], '.jpg') != false || strstr($_POST["URL"], '.jpeg') != false) $fileFormat = '.jpg';
		else if(strstr($_POST["URL"], '.png') != false) $fileFormat = '.png';
		else if(strstr($_POST["URL"], '.gif') != false) $fileFormat = '.gif';
		else if(strstr($_POST["URL"], '.bmp') != false) $fileFormat = '.bmp';
		else { 
			echo 'Ошибка при загрузке файла. Код ошибки: -1(Foramt error)';
			goto formaterror;
		}
				
		$res = $mysqli->query("SELECT max(id) FROM `photo`");
		$row = $res->fetch_assoc();
		
		$newName = ($row['max(id)'] + 1) . $fileFormat;
		
		cloudUploadURL($newName, $_POST["URL"]);
		
		$mysqli->query("INSERT INTO `photo`(`format`) VALUES ('" . str_replace('.', '', $fileFormat) . "')");
		
		echo 'Файл загружен. ID файла: ' . ($row['max(id)'] + 1);
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
				$res = $mysqli->query("SELECT * FROM `photo` WHERE `id` = " . $_GET['id']);
				$row = $res->fetch_assoc();
				
				$URL = cloudURLGet('/CodeXBackEndTest/' . $_GET['id'] . '.' . $row['format']);
				
				if($row['format'] == 'jpg') $im = imagecreatefromjpeg($URL);
				else if($row['format'] == 'png') $im = imagecreatefrompng($URL);
				else if($row['format'] == 'gif') $im = imagecreatefromgif($URL);
				else if($row['format'] == 'bmp') $im = imagecreatefrombmp($URL);
				
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
					
					header('Content-Type: image/' . str_replace('.', '', $row['format']));
					imagejpeg($im);
					imagedestroy($im);
				}
				else echo 'Ошибка при получении файла';
			}
		}
	}
?>
