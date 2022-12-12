<?php
	include_once 'scripts\\Crawler\\functions.php';

	// Se recuperan las urls:
	$value = $_POST['textArea'];
	$key = 'myUrls';

	// Se limpian las URLs del textArea:
	$value = explode(PHP_EOL, $value);
	$arr_value = array();
	foreach($value as $valor){
		$url = remove_hash_URL($valor);
		$url = remove_slash_URL($url);
		$arr_value[] = $url;
	}
	$value = implode(PHP_EOL, array_unique($arr_value));

	// Se obtienen las URLs de las cookies si existen,
	// si no, se crean las cookies:
	if(!isset($_COOKIE['myUrls'])) {
		// Las cookies duran 30 días:
		$value = set_cookies($key, $value, 30);
	} else {
		$value = $_COOKIE['myUrls'].PHP_EOL.$value;
		$value = set_cookies($key, $value, 30);
	}

?>