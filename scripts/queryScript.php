<?php

	include 'solrTranslator.php';
	include_once 'Crawler\\functions.php';

	/* ----------------------------------------------------------------------------------------- */
	/* ----------------------------------------------------------------------------------------- */
	/*                               Se realiza la conexión a Solr                               */
	/* ----------------------------------------------------------------------------------------- */
	/* ----------------------------------------------------------------------------------------- */

try{
	$query = $_POST['searchBox'];
	$solrQuery = translate_to_solr($query);

	// Se obtienen los websites obtenidos de los resultados de la query:
	$websites = search_in_solr($solrQuery);

	// Para la descarga de archivos:
	echo '<br><br>';
	echo '<div class=\'subContainer\'>';
	foreach($websites as $d=>$s){
		$snippet = $s['snippet'] . '[...]';
		// Se crear el enlace para descargar el archivo:
		echo "<p>
						<a class='url1' href='{$s['url']}'>
							{$s['title']}
						</a>
						<br>
						<p class='text-white'>{$snippet}</p>
						<span class='score'>Score: {$s['score']}</span>
						<hr>
					</p>";
	}
	echo '</div>';

} catch (Exception $e) {
	echo "<span class='text-white'>No se encontraron documentos.</span><br><br>";
}




?>