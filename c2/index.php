<?php

/*	***** COMENTARIO *****
	Todos los valores esperados son strings, por lo que solo necesitamos hacer (cast) a tipo entero sin mayor validación.
*/

if( empty( $FGC = is_readable( $f = realpath( __DIR__ . '/entrada.txt' ) ) ? file_get_contents( $f ) : FALSE ) ){
	
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
	
	/*	Usamos el operador de nave espacial que nos dirá qué jugador ganó y guardaremos las rondas que ganó.
		* 1: J1,
		* -1: J2.
	*/
	$ganador = $ronda[ 'j1' ] <=> $ronda[ 'j2' ];

	//	Ganó J1
	if( $ganador === 1 ){
		
		//	Calculamos la ventaja con que ganó el jugador.
		$j1[] = ( $ronda[ 'j1' ] - $ronda[ 'j2' ] );
	}

	//	Ganó J2
	if( $ganador === -1 ){
		
		//	Calculamos la ventaja con que ganó el jugador.
		$j2[] = ( $ronda[ 'j2' ] - $ronda[ 'j1' ] );
	}
}

//	Comparamos los puntajes mas altos de ventajas de cada jugador:
$PUNTAJES = ( $j1 = max( $j1 ) ) <=> ( $j2 = max( $j2 ) );

//	Asumimos que SIEMPRE existe un ganador, por lo que si NO GANA 1, GANARÁ 2.
$GANADOR_JUEGO = ( $PUNTAJES === 1 ) ? 1 : 2;

$GANADOR_PUNTAJE = $GANADOR_JUEGO == 1 ? $j1 : $j2;

$STR_SALIDA = "$GANADOR_JUEGO $GANADOR_PUNTAJE";

if( !is_writable( __DIR__ ) ){
	die( 'No se pudo guardar el archivo. Compruebe permisos de escritura.' );
}

file_put_contents( __DIR__ . '/salida.txt', $STR_SALIDA );

die( $STR_SALIDA );