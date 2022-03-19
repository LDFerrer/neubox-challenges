<?php

/*	***** COMENTARIO *****
	Todos los valores esperados son strings, por lo que solo necesitamos hacer (cast) a tipo entero sin mayor validación.
*/


$RUTAS = [];

#### Parametros modificadores: ###

	/*	Desde línea de comandos y parametros de URL (GET):
	 *	 -f, --folder:		Directorio de trabajo.
	 *	 -i, --input-file:	Archivo de entrada.
	 *	 -o, --output-file:	Archivo de salida.
	 *	 -p, --print:		¿Imprimir salida?.
	*/

		/* Comandos cortos */
		$shortopts  = '';
		$shortopts .= 'f:';  		// String 			@FOLDER =>			Valor opcional
		$shortopts .= 'i:';			// String			@INPUT-FILENAME => 	Valor opcional
		$shortopts .= 'o:';			// String			@OUTPUT-FILENAME => Valor opcional
		$shortopts .= 'p:';			// Boolean String	@PRINT =>			Valor opcional
		
		/* Comandos largos */
		$longopts  = [
		    'folder:',				// String 			@FOLDER =>			Valor opcional
		    'input-file:',			// String			@INPUT-FILENAME => 	Valor opcional
		    'output-file:',			// String			@OUTPUT-FILENAME => Valor opcional
		    'print:',				// Boolean String	@PRINT =>			Valor opcional
		];
		
		$CfgParams = NULL;
		
		//	Intentaremos tomar los $CfgParams de los parametros recibidos en línea de comandos.
		if( $options = getopt( $shortopts, $longopts ) ){
			$CfgParams =&$options;

		//	Si no recibimos parametros de comandos, apuntaremos la URL
		} else {
			$CfgParams =&$_GET;
		}
		
		//	El parametro @PRINT es un booleano, así que debemos parsear previamente el string de entrada.
		if( $tmpPrintOptn = $CfgParams['p'] ?? $CfgParams['print'] ?? FALSE ){
			
			//	Si recibimos una cadena la convertimos a minúsculas y verificamos solo la condición explícita TRUE.
			if( is_string( $tmpPrintOptn ) && ( $tmpPrintOptn = strtolower( $tmpPrintOptn ) )){

				//	Si es un String numérico, lo convertimos.
				if( is_numeric( $tmpPrintOptn ) ){
					$tmpPrintOptn = !!intval( $tmpPrintOptn );
				}
				
				//	Si solo es una palabra, buscaremos la palabra reservada TRUE.
				else{
					
					//	Queremos saber si EXPLÍCITAMENTE se pidió imprimir el resultado (además de guardarlo).
					$tmpPrintOptn = $tmpPrintOptn == 'true';
				}
			}
		}

		//	Definimos las opciones
		$RUTAS['folder'] =		realpath( $CfgParams['f'] ?? $CfgParams['folder'] ?? FALSE );
		$RUTAS['input-file'] =	basename( $CfgParams['i'] ?? $CfgParams['input-file'] ?? FALSE );
		$RUTAS['output-file'] =	basename( $CfgParams['o'] ?? $CfgParams['output-file'] ?? FALSE );
		$RUTAS['print'] =		intval( $tmpPrintOptn );
		
		//	Si no recibimos un directorio de trabajo, establecemos el directorio actual como predeterminado.
		if( !$RUTAS['folder'] ){
			$RUTAS['folder'] = __DIR__;
		}

		//	Si recibimos un nombre de archivo  de entrada, veificamos su extensión.
		if( $i =& $RUTAS['input-file'] ){
			
			if( strtolower( substr( $i, strrpos( $i, '.' )+1 ) ) !== 'txt' ){
				throw new Exception( 'Por seguridad, los archivos de entrada solo pueden tener extensión *.txt' );
			}
		} else {
			$RUTAS['input-file'] = 'entrada.txt';
		}
		
		//	Si no recibimos un archivo de salida, establecemos un valor predeterminado.
		if( !$RUTAS['output-file'] ){
			$RUTAS['output-file'] = 'salida.txt';
		}

if( empty( $FGC = is_readable( $f = realpath( $RUTAS['folder'].'/'.$RUTAS['input-file'] ) ) ? file_get_contents( $f ) : FALSE ) ){
	
	//	No pudimos cargar el archivo
	die( 'No se pudo cargar el archivo de puntuaciones' );
}

if( empty( $datos = explode( PHP_EOL, trim( $FGC ) ) ) ){
	die( 'No se pudo obtener la lista de instrucciones.' );
}

//	Apuntaremos al valor de las rondas.
$rondasJugadas = intval( $datos[0] );

//	Aquí guardaremos el valor de DELTA.
$jugadores = [
	'j1'	=>	[],
	'j2'	=>	[],
];

$j1 = &$jugadores['j1'];
$j2 = &$jugadores['j2'];

//	Convertimos cada ronda en un arreglo:
foreach( $datos as $i => &$valor ){
	
	//	$i > 0 == TRUE && !!$numStr
	if( $i and ( $numStr = explode( ' ', $valor ) ) ){
		$valor = [
			'j1'	=>	intval( $numStr[0] ),
			'j2'	=>	intval( $numStr[1] ),
		];
	}
}

//	Verificamos que tengamos la tabla completa
if( count( $datos ) != ( $rondasJugadas + 1) ){
	die( 'Las rondas pretendidas y las registradas no coinciden. Por favor verifique.' );
}

//	Gana quien obtuvo la ventaja mas amplia al final del juego completo.
for( $x = 1; $x <= $rondasJugadas; $x++ ){
	
	$ronda = &$datos[ $x ];
	
	/*	Saber QUÉ JUGADOR ganó cada ronda, sirve para contabilizar SOLO sus puntos de rondas ganadas.
		Usamos el operador de nave espacial que nos dirá qué jugador ganó y guardaremos las rondas que ganó.
		* 1: J1,
		* -1: J2.
	*/
	$ganador = $ronda[ 'j1' ] <=> $ronda[ 'j2' ];

	/*	Las rondas perdidas de cada jugador lo dejan en desventaja, y como la regla dice que gana el que consiguió
		MAYOR VENTAJA, sería ilógico agregar sus rondas en desventaja al contador.
	*/
		//	Ganó J1
		if( $ganador === 1 ){
	
			//	Calculamos la ventaja con que ganó el jugador y la agregamos a su lista de rondas ganadas.
			$j1[] = ( $ronda[ 'j1' ] - $ronda[ 'j2' ] );
		}
	
		//	Ganó J2
		if( $ganador === -1 ){
			
			//	Calculamos la ventaja con que ganó el jugador y la agregamos a su lista de rondas ganadas.
			$j2[] = ( $ronda[ 'j2' ] - $ronda[ 'j1' ] );
		}
}

//	Sumamos las rondas ganadas de cada jugador:
$PUNTAJES = ( $j1 = array_sum( $j1 ) ) <=> ( $j2 = array_sum( $j2 ) );

//	Asumimos que SIEMPRE existe un ganador, por lo que si NO GANA 1, GANARÁ 2.
$GANADOR_JUEGO = ( $PUNTAJES === 1 ) ? 1 : 2;

//	Definimos el puntaje del ganador.
$GANADOR_PUNTAJE = $GANADOR_JUEGO == 1 ? $j1 : $j2;

//	Definimos la cadena de resultado.
$STR_SALIDA = "$GANADOR_JUEGO $GANADOR_PUNTAJE";

//	Si el directorio destino NO tiene permisos de escritura, lanzamos un error.
if( !is_writable( $RUTAS['folder'] ) ){
	die( 'No se pudo guardar el archivo. Compruebe permisos de escritura.' );
}

//	Guardamos la salida.
file_put_contents( $RUTAS['folder'].'/'.$RUTAS['output-file'], $STR_SALIDA );

//	Si se solicitó imprimir la salida, la imprimimos.
if( $RUTAS['print'] ){
	die( $STR_SALIDA );
}