<?php

include_once 'composer/vendor/autoload.php';

// port y core se usan para los enlaces de la API:
$port = 8983;
$core = "proyecto_busqueda";

//*******************************************************************************
//**************************PROBANDO FUNCIONES***********************************
//*******************************************************************************

/*
echo "<br>";
echo "Pruebas SOLR";
print_r(get_document_using_URL("url5"));
echo "<br>";
echo "<br>";
*/

//check_ping();


//create multiple documents
//$arrDocs = createArrayDocuments();//añadiendo multiples documentos
//addDcouments($arrDocs);

//create one document
//add_document(createDocument());//añadiendo un solo documento


//delete documents
/*
delete_document("url1");
delete_document("url2");
delete_document("url3");
delete_document("url4");
delete_document("url5");
*/
//delete_document("url6");


//search terms with json php
// $arrDocuments = get_query_documents("elden");
// print_r($arrDocuments);


/*
echo "<br>";
echo "<br>";
echo "existe documento";
echo "<br>";
echo "<br>";
$responseDocument = exist_document("url 5");
echo $responseDocument ? 'true' : 'false';
*/

//PRUEBAS FACETADO
/*
echo "<br>";
echo "FACETED";
echo "<br>";
$terminoFacetado = get_all_faceted_category();
print_r($terminoFacetado);
*/


//PRUEBAS SUGGEST
/*
$terminoASugerir = "";
echo $terminoASugerir;
echo "<br>";
$pruebaSugerencia = get_suggest_terms($terminoASugerir);
print_r($pruebaSugerencia);
echo "<br>";
echo "<br>";
*/

/*
//PRUEBAS SPELL
$terminoACorregir = "infinitee";
$correcionTermino = get_spell_terms($terminoACorregir);
echo $correcionTermino;
*/


/* ----------------------------------------------------------------------------------------- */
/*                                         Funciones                                         */
/* ----------------------------------------------------------------------------------------- */

/**
 * add_document.
 * Recibe un documento y lo añade a Solr.
 *
 * @param	array	$document
 * @return	boolean
 */
function add_document(array $document): bool {
	$client = get_client();
	$update = $client->createUpdate();
	if (!exist_document($document['url'])) {
		$newDoc = $update->createDocument();
		$newDoc->title = $document['title'];
		$newDoc->category = $document['category'];
		$newDoc->content = $document['content'];
		$newDoc->last_date = $document['last_date'];
		$newDoc->snippet = $document['snippet'];
		$newDoc->url = $document['url'];
		$update->addDocument($newDoc);
		$update->addCommit();
		// Esto ejecuta la consulta y retorna el resultado:
		$result = $client->update($update);
		return true;
	}
	return false;
}

	/* ----------------------------------------------------------------------------------------- */
	/* ----------------------------------------------------------------------------------------- */

/**
 * add_documents.
 * Recibe un array de documentos y los añade a Solr.
 *
 * @param	array	$arrDocs
 * @return	boolean
 */
function add_documents(array $arrDocs): bool {
	if (count($arrDocs) > 0) {
		$client = get_client();
		$update = $client->createUpdate();
		$arrUpdateDocs = array();
		for ($i = 0; $i < count($arrDocs); $i++) {
			if (!exist_document($arrDocs[$i]['url'])) {
				$newDoc = $update->createDocument();
				$newDoc->title = $arrDocs[$i]['title'];
				$newDoc->category = $arrDocs[$i]['category'];
				$newDoc->content = $arrDocs[$i]['content'];
				$newDoc->last_date = $arrDocs[$i]['last_date'];
				$newDoc->snippet = $arrDocs[$i]['snippet'];
				$newDoc->url = $arrDocs[$i]['url'];
				array_push($arrUpdateDocs, $newDoc);
			}
		}
		$update->addDocuments($arrUpdateDocs);
		$update->addCommit();
		// Esto ejecuta la consulta y retorna el resultado:
		$result = $client->update($update);
		return true;
	}
	return false;
}

	/* ----------------------------------------------------------------------------------------- */
	/* ----------------------------------------------------------------------------------------- */

/**
 * delete_document.
 * Elimina un documento a partir de la url
 *
 * @param	string	$url
 * @return	boolean
 */
function delete_document(string $url): bool {
	if (!exist_document($url)) {
		return false;
	}
	$client = get_client();
	// Obtiene una actualización de la instancia de la consulta:
	$update = $client->createUpdate();
	// Añade la consulta para eliminar y un comando de commit a la actualización de la consulta:
	$update->addDeleteQuery("url:{$url}");
	$update->addCommit();
	// Esto ejecuta la consulta y retorna el resultado:
	$result = $client->update($update);
	return true;
}

	/* ----------------------------------------------------------------------------------------- */
	/* ----------------------------------------------------------------------------------------- */

/**
 * exist_document.
 * Verifica si existe un documento en la BD de Solr.
 *
 * @param	string	$urlString
 * @return	boolean
 */
function exist_document(string $urlString): bool {
	$port = $GLOBALS['port'];
	$core = $GLOBALS['core'];
	//Importante cambiar el puerto y el nombre del score al que apunta solr
	//localhost:8983; valores por default
	//core = /exampleDocuments/ ; valores por default
	$endPoint = "http://localhost:{$port}/solr/{$core}/select";
	//http://localhost:8983/solr/exampleDocuments/select?q=description:halo&facet=on&facet.field=category

	//suggest=true&suggest.build=true&suggest.dictionary=mySuggester&suggest.q=ha
	$params = [
		"q" => 'url:"' . $urlString . '"',
	];
	$url = $endPoint . '?' . http_build_query($params);
	//echo  $url;
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$output = curl_exec($ch);
	curl_close($ch);
	$result = json_decode($output, true);
	if (isset($result['response']) && count($result['response']['docs']) > 0) {
		return true;
	}
	return false;
}

	/* ----------------------------------------------------------------------------------------- */
	/* ----------------------------------------------------------------------------------------- */

/**
 * get_document_using_URL.
 * Hace una consulta para consultar un documento a partir de su URL, si no esta devuelve campos vacios;
 *
 * @param	string	$urlString
 * @return	mixed
 */
function get_document_using_URL(string $urlString) {
	$port = $GLOBALS['port'];
	$core = $GLOBALS['core'];
	//Importante cambiar el puerto y el nombre del score al que apunta solr
	//localhost:8983; valores por default
	//core = /exampleDocuments/ ; valores por default
	$endPoint = "http://localhost:{$port}/solr/{$core}/select";

	$params = [
		"q" => 'url:"' . $urlString . '"',
	];
	$url = $endPoint . '?' . http_build_query($params);
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$output = curl_exec($ch);
	curl_close($ch);
	$result = json_decode($output, true);
	$arrayDocuments = array();
	if ($result['response']['docs'] > 0) {
		foreach ($result['response']['docs'] as $fields) {
			$arrDocument = array(
				"title" => $fields['title'][0],
				"category" => $fields['category'],
				"content" => $fields['content'][0],
				"last_date" => $fields['last_date'][0],
				"snippet" => $fields['snippet'][0],
				"url" => $fields['url'][0],
			);
			array_push($arrayDocuments, $arrDocument);
		}
	}
	return $arrayDocuments;
}

	/* ----------------------------------------------------------------------------------------- */
	/* ----------------------------------------------------------------------------------------- */

/**
 * update_document.
 * Actualiza un documento en la BD de Solr.
 *
 * @param	string	$url
 * @param	string	$date
 * @return	bool
 */
function update_document(array $document): bool {
	$old_document = get_document_using_URL($document['url']);
	if($document['date'] > $old_document['date']) {
		if(delete_document($old_document['url'])){
			add_document($document);
			return true;
		}
	}
	return false;
}

	/* ----------------------------------------------------------------------------------------- */
	/* ----------------------------------------------------------------------------------------- */

/**
 * get_client.
 * Obtiene el cliente de Solarium.
 *
 * @global
 * @return	mixed
 */
function get_client() {
	$adapter = new Solarium\Core\Client\Adapter\Curl();
	$eventDispatcher = new Symfony\Component\EventDispatcher\EventDispatcher();
	$config = array(
		'endpoint' => array(
			'localhost' => array(
				'host' => 'localhost',
				'port' => 8983,
				'path' => '/',
				'core' => 'proyecto_busqueda',
			)
		)
	);
	return new Solarium\Client($adapter, $eventDispatcher, $config);
}

	/* ----------------------------------------------------------------------------------------- */
	/* ----------------------------------------------------------------------------------------- */

/**
 * check_ping.
 * Verifica la conexion del cliente.
 *
 * @return	void
 */
function check_ping() {
	// create a client instance
	$client = get_client();

	// create a ping query
	$ping = $client->createPing();

	// execute the ping query
	try {
		$result = $client->ping($ping);
		echo 'Ping query successful';
		echo '<br/><pre>';
		var_dump($result->getData());
		echo '</pre>';
	} catch (Exception $e) {
		echo 'Ping query failed';
	}
}

	/* ----------------------------------------------------------------------------------------- */
	/* ----------------------------------------------------------------------------------------- */

/**
 * get_suggest_terms.
 * A partir de una consulta devuelve una lista de sugerencias asociadas
 *
 * @param	mixed	$searchTerm
 * @return	mixed
 */
function get_suggest_terms($searchTerm) {
	$arr_terms = array();
	//$searchTerm = "ha"; //ejemplo de una sugerencia
	//Importante cambiar el puerto y el nombre del score al que apunta solr
	//localhost:8983; valores por default
	//core = /exampleDocuments/ ; valores por default
	$endPoint = "http://localhost:8983/solr/proyecto_busqueda/suggest";

	//suggest=true&suggest.build=true&suggest.dictionary=mySuggester&suggest.q=ha
	$params = [
			"suggest" => 'true',
			"suggest.build" => 'true',
			"suggest.dictionary" => 'mySuggester',
			"suggest.q" => $searchTerm,
			"format" => 'json'
	];
	$url = $endPoint . '?' . http_build_query($params);
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$output = curl_exec($ch);
	curl_close($ch);
	$result = json_decode($output, true);

	foreach ($result['suggest']['mySuggester'] as $term) {
		foreach ($term['suggestions'] as $paramsSuggestions) {
			array_push($arr_terms, $paramsSuggestions["term"]);
		}
	}

	if (empty($arr_terms)) {
		return  null;
	}
	return $arr_terms;
}

	/* ----------------------------------------------------------------------------------------- */
	/* ----------------------------------------------------------------------------------------- */

/**
 * get_spell_terms.
 * A partir de una consulta devuelve la pronunciacion correcta de una palabra
 *
 * @param	mixed	$searchTerm
 * @return	mixed
 */
function get_spell_terms($searchTerm) {
		$port = $GLOBALS['port'];
		$core = $GLOBALS['core'];
		$arr_terms = array();
		//$searchTerm = "haloo"; //ejemplo de una sugerencia
		//Importante cambiar el puerto y el nombre del score al que apunta solr
		//localhost:8983; valores por default
		//core = /exampleDocuments/ ; valores por default
		$endPoint = "http://localhost:{$port}/solr/{$core}/spell";

		//suggest=true&suggest.build=true&suggest.dictionary=mySuggester&suggest.q=ha
		$params = [
			"df" => "text",
			"spellcheck.q" => $searchTerm . "+ultra+sharp",
			"spellcheck" => "true",
			"format" => "json"
    ];
    $url = $endPoint . "?" . http_build_query($params);
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $output = curl_exec($ch);
    curl_close($ch);
    $result = json_decode($output, true);
    foreach ($result['spellcheck']['suggestions'] as $paramsSuggest) {
			if (is_array($paramsSuggest)) {
				$paramsTerms = $paramsSuggest['suggestion'];
				foreach ($paramsTerms as $paramTerm) {
					return $paramTerm['word'];
				}
			}
		}
	return null;
}

	/* ----------------------------------------------------------------------------------------- */
	/* ----------------------------------------------------------------------------------------- */

/**
 * get_term_faceted_category.
 * Devuelve a partir de una consulta las categorias en orden acumulativo, es decir los de mayor cantidad primero
 *
 * @param	mixed	$searchTerm
 * @return	mixed
 */
function get_term_faceted_category($searchTerm) {
	$port = $GLOBALS['port'];
	$core = $GLOBALS['core'];
	//Importante cambiar el puerto y el nombre del score al que apunta solr
	//localhost:8983; valores por default
	//core = /exampleDocuments/ ; valores por default
	$endPoint = "http://localhost:8983/solr/proyecto_busqueda/select";
	//http://localhost:8983/solr/exampleDocuments/select?q=description:halo&facet=on&facet.field=category

	//suggest=true&suggest.build=true&suggest.dictionary=mySuggester&suggest.q=ha
	$params = [
			"q" => 'description:' . $searchTerm,
			"facet" => 'on',
			"facet.field" => 'category'
	];
	$url = $endPoint . '?' . http_build_query($params);
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$output = curl_exec($ch);
	curl_close($ch);
	$result = json_decode($output, true);
	$arrFacetedTerms = array();
	foreach ($result['facet_counts']['facet_fields'] as $term) {
			$arrFacetedTerms = $term; //retorna array con las facetas
	}
	return $arrFacetedTerms; //retorna array vacio
}

	/* ----------------------------------------------------------------------------------------- */
	/* ----------------------------------------------------------------------------------------- */

/**
 * get_all_faceted_category.
 * Devuelve todas las categorias en orden acumulativo, es decir los de mayor cantidad primero
 *
 * @return	mixed
 */
function get_all_faceted_category() {
	$port = $GLOBALS['port'];
	$core = $GLOBALS['core'];
	//Importante cambiar el puerto y el nombre del score al que apunta solr
	//localhost:8983; valores por default
	//core = /exampleDocuments/ ; valores por default
	$endPoint = "http://localhost:{$port}/solr/{$core}/select";
	//http://localhost:8983/solr/exampleDocuments/select?q=description:halo&facet=on&facet.field=category

	//suggest=true&suggest.build=true&suggest.dictionary=mySuggester&suggest.q=ha
	$params = [
		"q" => '*',
		"facet" => 'on',
		"facet.field" => 'category'
	];
	$url = $endPoint . '?' . http_build_query($params);
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$output = curl_exec($ch);
	curl_close($ch);
	$result = json_decode($output, true);
	$arrFacet = array();
	if ($result['facet_counts']['facet_fields'] > 0) {
		$arrFacetedTerms = array();
		foreach ($result['facet_counts']['facet_fields'] as $term) {
			$arrFacetedTerms = $term;
		}
		for ($i = 0; $i < count($arrFacetedTerms); $i = $i + 2) {
			$arrayFactedTerm = array("facet" => $arrFacetedTerms[$i], "number" => $arrFacetedTerms[$i + 1]);
			array_push($arrFacet, $arrayFactedTerm);
		}
	}
	return $arrFacet; //retorna array vacío.
}

	/* ----------------------------------------------------------------------------------------- */
	/* ----------------------------------------------------------------------------------------- */

/**
 * get_query_documents.
 * A partir de la consulta devuelve un array con todos los documentos relacionados.
 *
 * @param	mixed	$searchWord
 * @return	mixed
 */
function get_query_documents($searchWord) {
	$port = $GLOBALS['port'];
	$core = $GLOBALS['core'];
	//Importante cambiar el puerto y el nombre del score al que apunta solr
	//localhost:8983; valores por default
	//core = /exampleDocuments/ ; valores por default
	$endPoint = "http://localhost:{$port}/solr/{$core}/select";
	//http://localhost:8983/solr/exampleDocuments/select?q=description:halo&facet=on&facet.field=category

	//suggest=true&suggest.build=true&suggest.dictionary=mySuggester&suggest.q=ha
	$params = [
		"q" => "(content:({$searchWord}) OR category:({$searchWord}) OR title:({$searchWord}) OR snippet:({$searchWord}))",
		"fl" => '*,score'
	];
	$url = $endPoint . '?' . http_build_query($params);
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$output = curl_exec($ch);
	curl_close($ch);
	$result = json_decode($output, true);
	$arrayDocuments = array();
	if (isset($result['response']) && $result['response']['docs'] > 0) {
		foreach ($result['response']['docs'] as $fields) {
			$arrDocument = array(
				"title" => $fields['title'][0],
				"category" => $fields['category'],
				"content" => $fields['content'][0],
				"last_date" => $fields['last_date'],
				"snippet" => $fields['snippet'][0],
				"url" => $fields['url'][0],
				"score" => $fields['score']
			);
			array_push($arrayDocuments, $arrDocument);
		}
	}
	return $arrayDocuments;
}

/**
 * get_all_title_documents.
 * A partir de la consulta devuelve un array con todos los titulos de los documentos.
 *
 * @return	mixed
 */
function get_all_title_documents() {
    $port = $GLOBALS["port"];
    $core = $GLOBALS["core"];
    //Importante cambiar el puerto y el nombre del score al que apunta solr
    //localhost:8983; valores por default
    //core = /exampleDocuments/ ; valores por default
    $endPoint = "http://localhost:$port/solr/$core/select";
    //http://localhost:8983/solr/exampleDocuments/select?q=description:halo&facet=on&facet.field=category

    //suggest=true&suggest.build=true&suggest.dictionary=mySuggester&suggest.q=ha
    $params = [
        "q" => "*",
        "rows" => "1000"
    ];
    $url = $endPoint . "?" . http_build_query($params);
    //echo  $url;
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $output = curl_exec($ch);
    curl_close($ch);
    $result = json_decode($output, true);
    //print_r($result);
    //echo count($result["response"]["docs"]);


    $arrayDocuments = array();
    if($result["response"]["docs"]!=null){
        if ($result["response"]["docs"] > 0) {
            foreach ($result["response"]["docs"] as $fields) {
                $arrDocument = array(
                    "title" => $fields["title"][0],
                );
                array_push($arrayDocuments, $arrDocument);
            }
        }
    }
    //print_r($arrayDocuments);
    return $arrayDocuments;
}

/**
 * file_title_documents.
 * Crea un archivo de texto con todos los titulos de los documentos.
 *
 */
function file_title_documents() {
	// Directorio donde se guardarán los archivos enviados:
	$directorio = "scripts\\Solr\\titles\\";
	// Se crea el directorio si no existe:
	if(!is_dir($directorio)){
		mkdir($directorio);
	}
	// Se abre el archivo de texto donde se guardarán las urls, se crea el archivo si no existe.
	$myfile = fopen($directorio."autocomplete.txt", 'w');
	$list = get_all_title_documents();
	foreach($list as $value){
		fwrite($myfile, $value["title"]."\n");
	}
	fclose($myfile);

}


//Mis configuraciones: solr 8.11.2, Java 18
//***********************COMANDOS SOLR************************************************************************

//Ejecutar solr
//ir al directorio: /solr-8.11.2/bin
//solr.cmd start

//Para configurar el puerto es:
// bin/solr start -p 8983

//Crear nucleo o BD
//solr create_core -c exampleDocuments

//Eliminar core o BD
//solr delete -c exampleDocuments

//PASO 1
//Crear los fields o campos de solr

//PASO 2
//Configurando FIELDS
//El unico field manual que se añade es last_date, que corresponde a la ultima fecha de actualizacion del sitio web
//tan solo es ir al cliente de solr, añadir un field nuevo de tipo pdate, con las propiedades activadas de: Indexed, Stored, DocValues	


//PASO 3
//**************************************Configurando el SUGGESTER**********************************************
//se encuentra el archivo config.xml en esta carpeta: solr-8.11.2\server\solr\proyecto_busqueda\conf
//NOTA: dete haberse creado el core o proyecto para poder acceder al solrconfig.xml
//Solo se modifica el field: category o el que este por defecto; se cambia por el que se quiera decidir, abajo en especifico cual parte
//<str name="field">category</str>
//Pegar lo siguiente tal como esta al solrconfig.xml considerando haber modificado el field al que se quiere aplicar el suggester

/*
 <!-- SUGGESTER
     <str name="weightField">price</str>
    -->
  <searchComponent name="suggest" class="solr.SuggestComponent">
  <lst name="suggester">
    <str name="name">mySuggester</str>
    <str name="lookupImpl">FuzzyLookupFactory</str>
    <str name="dictionaryImpl">DocumentDictionaryFactory</str>
    <str name="field">category</str>
    <str name="suggestAnalyzerFieldType">string</str>
    <str name="buildOnStartup">false</str>
  </lst>
</searchComponent>

<requestHandler name="/suggest" class="solr.SearchHandler" startup="lazy">
  <lst name="defaults">
    <str name="suggest">true</str>
    <str name="suggest.count">10</str>
  </lst>
  <arr name="components">
    <str>suggest</str>
  </arr>
</requestHandler>
*/


//PASO 4
//**************************************Configurando SPELL******************************************************
//Por defecto solr incluye SPELL en todos los proyectos, solo se requiere modificar el campo o field
//Importante fijarse en field, ya que se cambiara al campo sobre el cual se quiera hacer el spellcheck

/*
 <!-- a spellchecker built from a field of the main index -->
    <lst name="spellchecker">
      <str name="name">default</str>
      <str name="field">description</str>
      <str name="classname">solr.DirectSolrSpellChecker</str>
      <!-- the spellcheck distance measure used, the default is the internal levenshtein -->
      <str name="distanceMeasure">internal</str>
      <!-- minimum accuracy needed to be considered a valid spellcheck suggestion -->
      <float name="accuracy">0.5</float>
      <!-- the maximum #edits we consider when enumerating terms: can be 1 or 2 -->
      <int name="maxEdits">2</int>
      <!-- the minimum shared prefix when enumerating terms -->
      <int name="minPrefix">1</int>
      <!-- maximum number of inspections per result. -->
      <int name="maxInspections">5</int>
      <!-- minimum length of a query term to be considered for correction -->
      <int name="minQueryLength">4</int>
      <!-- maximum threshold of documents a query term can appear to be considered for correction -->
      <float name="maxQueryFrequency">0.01</float>
      <!-- uncomment this to require suggestions to occur in 1% of the documents
        <float name="thresholdTokenFrequency">.01</float>
      -->
    </lst>
*/
