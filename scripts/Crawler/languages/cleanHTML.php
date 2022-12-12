<?php
	function clean_HTML(string $inputStr) {
		include_once 'scripts\\Crawler\\libraries\\LIB_parse.php';
		include_once 'scripts\\Crawler\\libraries\\Html2Text.php';
		include_once 'scripts\\Crawler\\functions.php';
		require_once 'scripts\\Crawler\\languages\\vendor\\autoload.php';
		require_once 'scripts\\Crawler\\languages\\stemmer-es-1.0\\stemm_es.php';

		if(empty($inputStr)) {
				return array('', '', '', Array());
		}

		$html = new \Html2Text\Html2Text($inputStr);
		$html = $html->getText();
		$detector = new LanguageDetector\LanguageDetector();
		$language = $detector->evaluate($html)->getLanguage();
		$title = '';
		$html_text = '';
		$snippet = '';
		$keywords = [];
		$meta = '';
		$keyword_len = 3;

		// Se obtiene el título de la página:
		if($title = return_between($inputStr, '<title>', '</title>', 'EXCL')){
			$title = mb_strtolower($title, 'UTF-8');
			// Codifica la inputStr a UTF-8, para homogeneizar:
			$title = mb_convert_encoding($title, 'UTF-8', mb_detect_encoding($title));
			$title = preg_replace('/\s+/', ' ', $title);
			$title = ucfirst(trim($title));
		}


		// Se obtienen las palabras clave:
		if($meta = return_between($inputStr, '<meta name=keywords content="', '">', 'EXCL')){
			$meta = mb_convert_encoding($meta, 'UTF-8', mb_detect_encoding($meta));
			$keywords = array_unique(explode(',', $meta));
			foreach($keywords as $key => $word){
				if(strlen($word) > $keyword_len){
					$keywords[$key] = ucfirst(trim($word));
				}
			}
		} elseif($meta = return_between($inputStr, "<meta name=keywords content='", "'>", 'EXCL')){
			$meta = mb_convert_encoding($meta, 'UTF-8', mb_detect_encoding($meta));
			$keywords = array_unique(explode(',', $meta));
			foreach($keywords as $key => $word){
				if(strlen($word) > $keyword_len){
					$keywords[$key] = ucfirst(trim($word));
				}
			}
		} elseif($meta = return_between($inputStr, "<meta name='keywords' content='", "'>", 'EXCL')){
			$meta = mb_convert_encoding($meta, 'UTF-8', mb_detect_encoding($meta));
			$keywords = array_unique(explode(',', $meta));
			foreach($keywords as $key => $word){
				if(strlen($word) > $keyword_len){
					$keywords[$key] = ucfirst(trim($word));
				}
			}
		} elseif($meta = return_between($inputStr, '<meta name="keywords" content="', '">', 'EXCL')){
			$meta = mb_convert_encoding($meta, 'UTF-8', mb_detect_encoding($meta));
			$keywords = array_unique(explode(',', $meta));
			foreach($keywords as $key => $word){
				if(strlen($word) > $keyword_len){
					$keywords[$key] = ucfirst(trim($word));
				}
			}
		}

		// Se obtiene el contenido y el snippet del HTML:
		if($html) {
				$pattern = array('[\[/.*\]]','[\[https:.*\]]','[\[http:.*\]]',
												'[https://www\..*\.com]', '[http://www\..*\.com]',
												'[www\..*\.com]', '{\".*\":.*}');
				$replacement = '';
				$html_text = preg_replace($pattern, $replacement, $html);
				// Codifica la inputStr a UTF-8, para homogeneizar:
				$html_text = mb_convert_encoding($html_text, 'UTF-8', mb_detect_encoding($html_text));
				$html_text = mb_strtolower($html_text, 'UTF-8');
				$html_text = clean_string($html_text);
				$html_text = preg_replace('/\s+/', ' ', $html_text);
				$snippet = ucfirst(trim(substr($html_text, 0, 150)));
				remove_stopwords($html_text, $language);
				// Se lematiza el texto:
				$str_temp = explode(' ', $html_text);
				$stemmer = new stemm_es();
				foreach($str_temp as $key=>$str){
					// Se obtiene la raíz:
					$str_temp[$key] = $stemmer->stemm($str);
				}
				$html_text = trim(implode(' ', $str_temp));
		}

		return array($title, $snippet, $html_text, $keywords);
	}
?>