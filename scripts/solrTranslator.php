<?php

use Parle\{Lexer, Token, Parser, ParserException};

//$resultado = translateTerms('Alexis y no Jose o Darwin o no karlos');
//echo $resultado;

/**
 * translate_to_solr.
 * Traduce una cadena a una consulta
 * válida de Solr.
 *
 * @param	string	$words
 * @return	mixed
 */
function translate_to_solr(string $words) {

	$words = preg_replace('/[^a-zA-ZÀ-ú0-9]/', ' ', mb_strtolower($words, 'UTF-8'));

	$p = new Parser;
	$p->token('TERMINO');
	//$p->token('PIZQ');
	//$p->token('PDER');
	$p->token('Y');
	$p->token('O');
	$p->token('NO');
	$p->token('COMILLAS');
	$p->push('START', 'EXPRESION');
	$p->push('EXPRESION', 'TERMINOS');

	$exp_exp = $p->push('EXPRESION', 'EXPRESION EXPRESION');
	$exp_op = $p->push('EXPRESION', 'EXPRESION OPERADOR EXPRESION');
	$exp_op_or = $p->push('OPERADOR', 'O');
	$exp_op_and = $p->push('OPERADOR', 'Y');
	$exp_op_and_no = $p->push('OPERADOR', 'Y NO');
	$exp_op_or_no = $p->push('OPERADOR', 'O NO');

	//$procd_no_termino = $p->push('TERMINOS', 'NO TERMINOS');
	//$procd_no_termino2 = $p->push('NO_TERMINOS', 'NO TERMINOS');
	$producccion_terminos_termino = $p->push('TERMINOS', 'TERMINOS TERMINO');
	$produccion_termino = $p->push('TERMINOS', 'TERMINO');
	$produccion_termino_exacto = $p->push('TERMINOS', 'COMILLAS TERMINO COMILLAS');
	$p->build();

	$lex = new Lexer;
	//$lex->push('[(]', $p->tokenId('PIZQ'));
	//$lex->push('[)]', $p->tokenId('PDER'));
	$lex->push('[y]', $p->tokenId('Y'));
	$lex->push('[o]', $p->tokenId('O'));
	$lex->push('[n][o]', $p->tokenId('NO'));
	$lex->push('[a-zA-ZÀ-ú0-9/_/-/./,]+', $p->tokenId('TERMINO'));
	$lex->push('[\s]+', Token::SKIP);
	$lex->build();

	$stringQuery = '';
	//$words = '12.45 y rosaldo o braulio y no darwin y no juan o alexis';
	$p->consume($words, $lex);
	$count = 0;
	$containsOP = false;
	do {
		switch ($p->action) {
			case Parser::ACTION_ERROR:
				throw new ParserException('Error');
				break;
			case Parser::ACTION_SHIFT:
			case Parser::ACTION_GOTO:
				//var_dump($p->trace());
				break;
			case Parser::ACTION_REDUCE:
				$rid = $p->reduceId;
				//Produce Operadores OR y AND
				if ($rid == $exp_op_or) {
						$stringQuery  =  $stringQuery . ' OR ';
				}
				if ($rid == $exp_op_and) {
						$stringQuery  =  $stringQuery . ' AND ';
				}
				if ($rid == $exp_op_and_no) {
						$stringQuery  =  $stringQuery . ' AND NOT ';
				}
				if ($rid == $exp_op_or_no) {
						$stringQuery  =  $stringQuery . ' OR NOT ';
				}
				/*
				if ($rid == $procd_no_termino) {
						$stringQuery  =  $stringQuery . ' NOT ';
						//$stringQuery  = $stringQuery . $arrayAtributes[$i] . $p->sigil(1);
						//$stringQuery =  $stringQuery . $arrayAtributes[$i] . $p->sigil(1) . "%' AND NOT ";
				}
				*/
				if ($rid == $producccion_terminos_termino) {
						$stringQuery  =  $stringQuery . ' ' . $p->sigil(1) . '';
				}
				if ($rid == $produccion_termino) {
						//if(str_contains($p->sigil(0)));
						$stringQuery  =  $stringQuery . ' ' . $p->sigil(0) . ' ';
				}
				break;
		}
		$p->advance();
	} while (Parser::ACTION_ACCEPT != $p->action);

	$stringQuery = mb_strtoupper($stringQuery, 'UTF-8');

	return $stringQuery;
}
