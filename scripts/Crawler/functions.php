<?php

# Se incluyen las bibliotecas:
include('libraries\\LIB_http.php');
include('libraries\\LIB_parse.php');
include('libraries\\LIB_resolve_addresses.php');
include('libraries\\LIB_http_codes.php');
include('libraries\\LIB_exclusion_list.php');
include('libraries\\LIB_simple_spider.php');
include('libraries\\LIB_download_images.php');
include_once('scripts\\Solr\\querySolrFunctions.php');

/* ----------------------------------------------------------------------------------------- */
/*                                         FUNCIONES                                         */
/* ----------------------------------------------------------------------------------------- */

	/**
	 * get_info.
	 *
	 * Obtiene la información de
	 * un archivo.
	 *
	 * @param	string 	$fileDir
	 * @return 	string
	 */
	function get_info(string $fileDir): string {
		if(empty($fileDir)) { return ''; }
		$content = file_get_contents($fileDir);
		$content = str_replace("'", "''", $content);
		if($content !== false) return $content;
		else return '';
	}


	/* ----------------------------------------------------------------------------------------- */
	/* ----------------------------------------------------------------------------------------- */

	/**
	 * remove_stopwords.
	 * Elimina las palabras vacías existentes
	 * dentro de la cadena ingresada.
	 *
	 * @param string &$str (por referencia)
	 */
	function remove_stopwords(string &$str, string $language) {
		if(empty($str)) {
				return '';
		}

		//Se crean arreglos temporales para almacenar las palabras vacías y el arreglo sin palabras vacías.
		$arr_stpwrds = array();

		//Abre un documento que contiene la lista de las palabras vacías a usar.
		if ($language = 'es') {
			$file = fopen('scripts\\Crawler\\languages\\stopwords\\stop_words_spanish.txt', 'r') or die('No se pudo abrir el archivo.');
		} else {
			$file = fopen('scripts\\Crawler\\languages\\stopwords\\stop_words_english.txt', 'r') or die('No se pudo abrir el archivo.');
		}

		// Se recorre cada línea del archivo:
		while(!feof($file)) {
				$line = trim((mb_strtoupper(fgets($file), 'UTF-8')));
				$arr_stpwrds[$line] = $line;
		}
		fclose($file); // Se cierra el archivo.

		//Se separa la cadena de entrada en un arreglo.
		$delimiter = ' ';
		$words = array_filter(explode($delimiter, $str));

		//Se filtran las palabras vacías de la cadena.
		$str = '';
		foreach($words as $valor){
			if(!in_array(mb_strtoupper($valor, 'UTF-8'), $arr_stpwrds)){
					$str .= ' ' . $valor;
			}
		}
	}

	/* ----------------------------------------------------------------------------------------- */
	/* ----------------------------------------------------------------------------------------- */

	/**
	 * clean_string.
	 * Limpia una cadena dejando sólo
	 * los caracteres alfanuméricos,
	 * acepta acentos.
	 *
	 * @param 	string	&$str (por referencia)
	 */
	function clean_string(string &$str) {
		$str = preg_replace('/[^[:alnum:]]/u', ' ', $str);
		// remove_stopwords($str);
		return $str;
	}


/* ----------------------------------------------------------------------------------------- */
/* ----------------------------------------------------------------------------------------- */
/*                               Funciones de la base de datos                               */
/* ----------------------------------------------------------------------------------------- */
/* ----------------------------------------------------------------------------------------- */

	/**
	 * insert_document.
	 * Inserta un documento en la BD de Solr.
	 *
	 * @param	array	$document
	 * @return	boolean
	 */
	function insert_document(array $document): bool {
		if(empty($document)){
			echo "Error al insertar el documento, arreglo vacío.<br><br>";
			return false;
		}
		// Se inserta el registro en la tabla:
		if(!add_document($document)){
			echo "Error al insertar el documento " . $connection->error . '<br><br>';
			return false;
		}
		return true;
	}


	/* ----------------------------------------------------------------------------------------- */
	/* ----------------------------------------------------------------------------------------- */


	/**
	 * search_in_solr.
	 * Toma una consulta; retorna un array de los resultados
	 * de dicha consulta desde Solr.
	 *
	 * @param	string	$query
	 * @return	mixed
	 */
	function search_in_solr(string $query): array {
		// Arreglo que almacena los resultados de la consulta:
		$myArray = [];

		// Resultado de la consulta:
		$results = get_query_documents($query);

		// Si no se puede realizar la consulta:
		if (empty($results)) {
			echo "<p class='text-white'>No se encontró nada con: '{$query}'</p><br><br>";
		}

		// Se almacenan los resultados en un arreglo:
		foreach($results as $doc) {
			$myArray[] = $doc;
		}

		return $myArray;
	}

	/* ----------------------------------------------------------------------------------------- */
	/* ----------------------------------------------------------------------------------------- */

	/**
	 * search_URL_in_BD.
	 * Busca una URL dentro de la base de datos.
	 *
	 * @param	string	$url
	 * @return	boolean
	 */
	function search_URL_in_solr(string $url): bool {
		if(!exist_document($url)){
			return false;
		}
		return true;
	}

	/* ----------------------------------------------------------------------------------------- */
	/* ----------------------------------------------------------------------------------------- */

	/**
	 * update_solr_document.
	 * Actualiza el contenido de un documento en la base
	 * de datos.
	 *
	 * @param	array	$document
	 * @return	boolean
	 */
	function update_solr_document(array $document): bool {
		if(!update_document($document)) {
			return false;
		}
		return true;
	}

/* ----------------------------------------------------------------------------------------- */
/* ----------------------------------------------------------------------------------------- */
/*                                   Funciones del crawler                                   */
/* ----------------------------------------------------------------------------------------- */
/* ----------------------------------------------------------------------------------------- */

	/**
	 * download_webpage.
	 * Descarga un sitio web. Retorna un arreglo
	 * si la operación se realizó correctamente,
	 * null en caso contrario.
	 *
	 * @param	string 	$target 	Default: ''
	 * @param	string 	$ref    	Default: ''
	 * @param	boolean	$headers	Default: false
	 * @return	mixed
	 */
	function download_webpage(string $target = null, string $ref = null, bool $headers = false): array {
		if(empty($target)) {
			return null;
		}

		if($headers === true) {
			$return_array = http_get_withheader($target, $ref);
		} else {
			// Se desarga la página con header:
			$return_array = http_get($target, $ref);
		}

		return $return_array;
	}

	/* ----------------------------------------------------------------------------------------- */
	/* ----------------------------------------------------------------------------------------- */

	/**
	 * validate_links.
	 * Valida los enlaces de un sitio web previamente descargado.
	 *
	 * Retorna un arreglo con los enlaces válidos (status 200).
	 * Retorna un array vacío en caso de no haber ningún enlace.
	 * Retorna null si no se pasaron parámetros válidos.
	 *
	 * @param	array 	$downloaded_page	Default: null
	 * @param	string	$target         	Default: null
	 * @param	string	$page_base      	Default: null
	 * @return	mixed
	 */
	function validate_links(Array $downloaded_page = [], string $target = null, string $page_base = null){
		if(empty($downloaded_page) || empty($target) || empty($page_base)) {
			return null;
		}

		$saved_links = []; // Guarda los enlaces.

		// Se parsean los enlaces:
		$link_array = parse_array($downloaded_page['FILE'], $beg_tag="<a", $close_tag=">");

		for($i = 0; $i < count($link_array); $i++) {
			// Se parsea el atributo http desde el enlace:
			$link = get_attribute($tag = $link_array[$i], $attribute = "href");

			// Se crea la dirección completamente resuelta:
			$resolved_link_address = resolve_address($link, $page_base);

			// Se comprueba el enlace, si es válido se guarda:
			$downloaded_link = http_get($resolved_link_address, $target);
			if($downloaded_link['STATUS']['http_code'] == 200){
				$saved_links[] = $downloaded_link['STATUS']['url'];
			}
		}
		return $saved_links;
	}

	/* ----------------------------------------------------------------------------------------- */
	/* ----------------------------------------------------------------------------------------- */

	/**
	 * string_between_strings.
	 * Retorna una subcadena entre dos cadenas.
	 *
	 * @param	string	$str
	 * @param	string	$start
	 * @param	string	$end
	 * @return	mixed
	 */
	function string_between_strings(string $str, string $start, string $end) {
		$substring_start = strpos($str, $start);
		$substring_start += strlen($start);
		$size = strpos($str, $end, $substring_start) - $substring_start;
		return substr($str, $substring_start, $size);
	}

	/* ----------------------------------------------------------------------------------------- */
	/* ----------------------------------------------------------------------------------------- */

	/**
	 * validate_url.
	 * Valida las URL. Retorna true si es válida,
	 * false si no.
	 *
	 * @param	string	$url
	 * @return	bool
	 */
	function validate_url(string $url): bool {
		$path = parse_url($url, PHP_URL_PATH);
		$encoded_path = array_map('urlencode', explode('/', $path));
		$url = trim(str_replace($path, implode('/', $encoded_path), $url));

		return filter_var($url, FILTER_VALIDATE_URL) ? true : false;
	}

	/* ----------------------------------------------------------------------------------------- */
	/* ----------------------------------------------------------------------------------------- */

	/**
	 * remove_slash_URL.
	 * Elimina el último slash '/' de la cadena de la URL.
	 *
	 * @param	string	$url
	 * @return	mixed
	 */
	function remove_slash_URL(string $url) {
		if(!empty($url) && $url[-1] && $url[-1] === '/'){
			$url = substr($url, 0, -1);
		}
		return $url;
	}

	/* ----------------------------------------------------------------------------------------- */
	/* ----------------------------------------------------------------------------------------- */

	/**
	 * remove_hash_URL.
	 * Remueve el símbolo de hash '#' de una cadena URL.
	 *
	 * @param	string	$url
	 * @return	mixed
	 */
	function remove_hash_URL(string $url) {
		if(empty($url)) {
			return $url;
		}
		$pattern = array('[/#.*]', '[#.*]');
		$replacement = '';
		return preg_replace($pattern, $replacement, $url);
	}

	/* ----------------------------------------------------------------------------------------- */
	/* ----------------------------------------------------------------------------------------- */
	/*                                  Funciones de las cookies                                 */
	/* ----------------------------------------------------------------------------------------- */
	/* ----------------------------------------------------------------------------------------- */

	/**
	 * get_cookie.
	 * Obtiene la cookie. Retorna null si no existe la cookie.
	 *
	 * @param	string	$key	Default: null. Nombre de la cookie.
	 * @return	void
	 */
function get_cookie(string $key = null){
	if(!empty($_COOKIE[$key])) {
		return $_COOKIE[$key];
	} else {
		return null;
	}
}

/* ----------------------------------------------------------------------------------------- */
/* ----------------------------------------------------------------------------------------- */

/**
 * uuID.
 * Genera un ID único pseudoaleatorio.
 *
 * @param	integer	$lenght	Default: 20. Largo de la cadena.
 * @return	mixed
 */
function uuID(int $lenght = 20) {
	if (function_exists("random_bytes")) {
			$bytes = random_bytes(ceil($lenght / 2));
	} elseif (function_exists("openssl_random_pseudo_bytes")) {
			$bytes = openssl_random_pseudo_bytes(ceil($lenght / 2));
	}
	return substr(bin2hex($bytes), 0, $lenght);
}

/* ----------------------------------------------------------------------------------------- */
/* ----------------------------------------------------------------------------------------- */

/**
 * setInLocalStorage.
 * Almacena las URLs en una cookie.
 *
 * Retorna las URLs si se realizó la operación correctamente, null en caso contrario.
 *
 * @param	string 	$key  	Default: null. Clave (nombre) de la cookie.
 * @param	string 	$value	Default: null. Valor a almacenar en la cookie.
 * @param	integer	$days 	Default: 1. Número de días que estará disponible la cookie.
 * @return	mixed
 */
function set_cookies(string $key = null, string $value = null, int $days = 1) {
	if(empty($key) || empty($key) || $days > 365 || $days < 1) {
		return null;
	}

	// Se crea un arreglo con las cadenas de la entrada:
	$actualItems = '';
	if(!empty($_COOKIE[$key])){
		$actualItems = $_COOKIE[$key];
		$actualItems = explode(PHP_EOL, $actualItems);
		$newItems = explode(PHP_EOL, $value);
		$actualItems = array_unique(array_merge($actualItems, $newItems));
	} else {
		$actualItems = explode(PHP_EOL, $value);
		$actualItems = array_unique($actualItems);
	}

	// Se eliminan las cadenas que no sean URLs válidas:
	foreach($actualItems as $k=>&$url){
		if(!filter_var($url, FILTER_VALIDATE_URL)){
			unset($actualItems[$k]);
		}
	}
	$actualItems = implode(PHP_EOL, $actualItems);

	// Se establece el tiempo de la cookie:
	$seconds = 60;
	$minutes = 60;
	$hours = 24;
	$expire = time() + ($days * $hours * $minutes * $seconds);

	// Se crean las cookies:
	$id = '';
	setcookie($key, $actualItems, $expire, "/");
	if(!isset($_COOKIE['user'])){
		// Cookie de usuario.
		$id = uuID();
		setcookie('user', $id, $expire, "/");
	} else {
		$id = get_cookie('user');
	}

	return $actualItems;
}

/* ----------------------------------------------------------------------------------------- */
/* ----------------------------------------------------------------------------------------- */

/**
 * clear_cookies.
 * Elimina las cookies.
 *
 * @return	void
 */
function clear_cookies() {
	foreach($_COOKIE as $k=>&$c){
		unset($_COOKIE[$k]);
		setcookie($k, null, 0, "/");
	}
}

?>