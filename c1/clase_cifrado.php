<?php
realpath($_SERVER['SCRIPT_FILENAME'])==__FILE__ && die(header("$_SERVER[SERVER_PROTOCOL] 404 Not Found", 1, 404) );

/*  Clase para controlar mensajes cifrados.
	* Los caracteres posibles corresponden a la expresión regular: [a-zA-Z0-9]
		- No deben contener espacios.
		- Acepta números del 0 - 9
		- Por ser una expresión regular, el rango [a-zA-Z] no acepta [Ññ].
*/

class cifrador{
	
	const regExpAllowedChars = '[a-zA-Z0-9]';
	const regExpDeniedChars = '[^a-zA-Z0-9]';

	//	Estandarizamos la salida a una cadena sin acentos
	public static function stdString( $string ){
		$string = str_replace(
				['á', 'à', 'ä', 'â', 'ª', 'Á', 'À', 'Â', 'Ä'],
				['a', 'a', 'a', 'a', 'a', 'A', 'A', 'A', 'A'],
				$string
		);
		$string = str_replace(
				['é', 'è', 'ë', 'ê', 'É', 'È', 'Ê', 'Ë'],
				['e', 'e', 'e', 'e', 'E', 'E', 'E', 'E'],
				$string
		);
		$string = str_replace(
				['í', 'ì', 'ï', 'î', 'Í', 'Ì', 'Ï', 'Î'],
				['i', 'i', 'i', 'i', 'I', 'I', 'I', 'I'],
				$string
		);
		$string = str_replace(
				['ó', 'ò', 'ö', 'ô', 'Ó', 'Ò', 'Ö', 'Ô'],
				['o', 'o', 'o', 'o', 'O', 'O', 'O', 'O'],
				$string
		);
		$string = str_replace(
				['ú', 'ù', 'ü', 'û', 'Ú', 'Ù', 'Û', 'Ü'],
				['u', 'u', 'u', 'u', 'U', 'U', 'U', 'U'],
				$string
		);
		$string = str_replace(
				['ñ', 'Ñ', 'ç', 'Ç'],
				['n', 'N', 'c', 'C',],
				$string
		);

		return $string;
	}

	//    Nos aseguramos que la {$string} tenga solo 1 letra repetida por palabra.
	public static function prepareWords( $string ){

		$string = explode( ' ', $string );

		//	Creamos una lista para las letras del alfabeto.
		$letrasAlfa = [];

		//	Agregamos cada letra del alfabeto
		for( $x = 65; $x <=90; $x++ ){
			$letrasAlfa[ $x ] = chr( $x );
		}

		for( $x = 97; $x <=122; $x++ ){
			$letrasAlfa[ $x ] = chr( $x );
		}

		//	Iteramos cada palabra de la cadena actual.
		foreach ( $string as &$word ){

			//	Eliminamos caracteres no permitidos en la palabra actual.
			$word = self::removeBadChars( $word );

			//	En cada palabra buscamos las letras del alfabeto.
			foreach ( $letrasAlfa as &$char ){
				
				//	Reemplazamos las letras repetidas por solo 1 de ellas.
				$word = preg_replace( '@['.$char.']+@', $char, $word );

			}
		}

		return implode( ' ', $string );
	}

	//    Conversión de palabras a su versión en capitales.
	public static function cptlWords( $string ){
		return ucwords( strtolower( $string ) );
	}

	//    Eliminamos caracteres no aceptados.
	public static function removeBadChars( $string ){
		return preg_replace( '/'.self::regExpDeniedChars.'/', '', $string );
	}

	//    Normalizar cadena de instrucción
	public static function processInstruction( $string ){

		//	Iniciamos la limpieza de la instrucción recibida.
		$string =	self::stdString( $string );			//	Estandarizamos la linea
		$string =	self::prepareWords( $string );		//	Eliminamos letras repetidas
		$string =	self::cptlWords( $string );			//	Capitalizamos las palabras.
		$string =	str_replace( ' ', '', $string );	//	Eliminamos espacios
		
		return $string;
	}

	//    Normalizar cadena de instrucción
	public static function validateInstruction( $string ){

		//	Iniciamos la limpieza de la instrucción recibida.
		$string =	self::stdString( $string );			//	Estandarizamos la linea
		$string =	self::prepareWords( $string );		//	Eliminamos letras repetidas
		$string =	str_replace( ' ', '', $string );	//	Eliminamos espacios
		
		return $string;
	}

	//    Normalizar mensaje
	public static function processMessage( $string ){

		//	Iniciamos la limpieza de la instrucción recibida.
		$string =	self::stdString( $string );			//	Estandarizamos la linea
		$string =	self::prepareWords( $string );		//	Eliminamos letras repetidas
		$string =	str_replace( ' ', '', $string );	//	Eliminamos espacios
		
		return $string;
	}
}