<?php
	include_once('functions.php');
	include('languages\\cleanHTML.php');

	$WEB_PAGES = []; // Páginas guardas temporalmente para ser preprocesadas.
	$PROCESSED_PAGES = 0; // Páginas procesadas.
	$INDEXED_PAGES = 0; // Páginas indexadas.
	$URLS = []; // URLs del archivo txt.

	/* ----------------------------------------------------------------------------------------- */
	/*                           Se obtienen los enlaces de las cookies                          */
	/* ----------------------------------------------------------------------------------------- */
	if(isset($_COOKIE['user']) && isset($_COOKIE['myUrls'])) {
		// Se limpian las URLs del textArea:
		$value = get_cookie('myUrls');
		$value = explode(PHP_EOL, $value);
		$arr_value = array();
		foreach($value as $valor){
			$url = remove_hash_URL($valor);
			$url = remove_slash_URL($url);
			$arr_value[] = $url;
		}
		$URLS = array_unique($arr_value);
	} else {
		exit;
	}

	/* ----------------------------------------------------------------------------------------- */
	/*                                           Spider                                          */
	/* ----------------------------------------------------------------------------------------- */
	stream_context_set_default(
		array(
				'http' => array(
						'user_agent'=>"php/testing",
						'host'=>"localhost"
				),
		)
	);
	foreach($URLS as $SEED_URL){

		/* ----------------------------------------------------------------------------------------- */
		/*                                    Profundidad nivel 0                                    */
		/* ----------------------------------------------------------------------------------------- */
		$headers = get_headers($SEED_URL, 1); // Se obtienen los encabezados.
		if(str_contains($headers[0], '200') || str_contains($headers[0], '301')){
		$WEB_PAGES[] = Array('CONTENT'=>download_webpage($SEED_URL, $ref='', false),
									'URL'=>$SEED_URL, 'HEADERS'=>$headers);
		} else {
			continue;
		}
		$PROCESSED_PAGES++;
		$MAX_PENETRATION = 1; // Establece la profundidad de la penetración de la araña.
		$FETCH_DELAY     = 10; // Espera en segundos entre la descarga de cada página.
		$ALLOW_OFFISTE   = false; // No permite que la araña se desplace desde el dominio de $SEED_URL.
		$spider_array = array();

		/* ----------------------------------------------------------------------------------------- */
		/*                                    Profundidad nivel 1                                    */
		/* ----------------------------------------------------------------------------------------- */

		// Se obtienen los enlaces de $SEED_URL:
		$temp_link_array = harvest_links($SEED_URL);
		$spider_array = archive_links($spider_array, 0, $temp_link_array);

		// Enlaces de la araña en los niveles de penetración restantes:
		for($penetration_level=1; $penetration_level<=$MAX_PENETRATION; $penetration_level++){
			$previous_level = $penetration_level - 1;
			if(isset($spider_array[0])){
				for($i=0; $i<count($spider_array[$previous_level]); $i++){
						unset($temp_link_array);
						$temp_link_array = harvest_links($spider_array[$previous_level][$i]);
						$spider_array = archive_links($spider_array, $penetration_level, $temp_link_array);
				}
			}
		}

		// Descarga las páginas del arreglo $spider_array:
		for($penetration_level=1; $penetration_level<=$MAX_PENETRATION; $penetration_level++) {
			if(isset($spider_array[0])){
				for($i=0; $i<count($spider_array[$previous_level]); $i++) {
					$headers = get_headers($spider_array[$previous_level][$i], 1); // Se obtienen los encabezados.
					if(str_contains($headers[0], '200') || str_contains($headers[0], '301')){
						$WEB_PAGES[] = Array('CONTENT'=>download_webpage($spider_array[$previous_level][$i], $ref='', false),
													'URL'=>$spider_array[$previous_level][$i],
													'HEADERS'=>$headers);
						$PROCESSED_PAGES++;
					}
				}
			}
		}
	}

	/* ----------------------------------------------------------------------------------------- */
	/*                         Se indexan los archivos a la base de datos                        */
	/* ----------------------------------------------------------------------------------------- */
	foreach($WEB_PAGES as $wp){
		$url = $wp['URL'];
		$url = remove_slash_URL($url);
		$url = remove_hash_URL($url);
		if(search_URL_in_solr($url)) {
			// El sitio web ya ha sido indexado con anterioridad; se busca la fecha de última
			// modificación del sitio web si lo tiene:
			if (isset($wp['HEADERS']['Last-Modified'])) {
				// Si la fecha de modificación es mayor a la fecha de la última indexación,
				// se intenta actualizar:
				$date = date("Y-d-m H:i:s", strtotime($wp['HEADERS']['Last-Modified']));
				$content = clean_HTML($wp['CONTENT']['FILE']);
				$snippet = $content[1];
				$content[2] = str_replace("'", "\\'", $content[2]); // Contenido preprocesado.
				$title = $content[0];
				$document = array(
					'title' => $title,
					'category' => $content[3],
					'content' => $content[2],
					'last_date' => $date,
					'snippet' => $snippet,
					'url' => $url,
				);
				if(update_solr_document($document)){
					$INDEXED_PAGES++;
				} else {
					continue;
				}
			}
		} else{
			// Inserción normal a la BD:
			if(isset($wp['HEADERS']['Date'])){
				$date = date("Y-d-m H:i:s", strtotime($wp['HEADERS']['Date'][0]));
			} elseif(isset($wp['HEADERS']['date'])){
				$date = date("Y-d-m H:i:s", strtotime($wp['HEADERS']['date'][0]));
			}
			$content = clean_HTML($wp['CONTENT']['FILE']);
			$snippet = $content[1];
			$content[2] = str_replace("'", "\\'", $content[2]); // Contenido preprocesado.
			$title = $content[0];
			$document = array(
				'title' => $title,
				'category' => $content[3],
				'content' => $content[2],
				'last_date' => $date,
				'snippet' => $snippet,
				'url' => $url,
			);
			if(insert_document($document)){
				$INDEXED_PAGES++;
			}
		}
	}

	echo "<span class='score'>Páginas indizadas: {$INDEXED_PAGES}</span><br>";
	echo "<span class='score'>Páginas procesadas: {$PROCESSED_PAGES}</span><br><br>";

?>