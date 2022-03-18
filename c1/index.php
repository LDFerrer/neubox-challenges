<?php

/*	***** COMENTARIO *****
	Al hablar de CIFRADO y mensajes, se asume que el codigo debe enfocarse en la seguridad y fiabilidad del mensaje.
	por lo que usaremos validaciones mediante expresiones regulares para impedir inyecciones de cualquier tipo.
*/

/*    [BLOQUE]: Impresión y control de errores.
**    No incluir estas funciones desde fuera, porque si se dañan: PERDEMOS EL CONTROL DE ERRORES.
*/
	error_reporting(E_ALL);
	
	ini_set("display_errors", "off");

	/*	errorMsg:	Matar el hilo de ejecución actual e imprimir el mensaje de error.
		[ Default ]
			@param String $excptnOrErrorStr:	Cadena de error a imprimir para indicar el error.

		[+1 Sobrecarga]
			@param Exception $excptnOrErrorStr:	Instancia {Exception} con la información del error.

		[+2 Sobrecarga]
			@param Throwable $excptnOrErrorStr:	Instancia {Throwable} con la información del error.
	*/
	function errorMsg( $excptnOrErrorStr ){

		//	Si $excptnOrErrorStr no es un String ni una instancia Throwable... terminamos aquí.
		if( !is_string( $excptnOrErrorStr ) and empty( $excptnOrErrorStr instanceof Throwable ) ){
			die ( '[Error No Controlado].');
		}
	
		//    Si solo recibimos la cadena del error, matamos el proceso imprimiendo el error.
		if( is_string( $excptnOrErrorStr ) ){
			die( $excptnOrErrorStr );
		}

		//    Preparamos una lista de elementos de mensaje de error.
		$error = [];
	
		//    Obtenemos características del error (Si están disponibles).
		$error[] =  'Código: ' .    $excptnOrErrorStr->getCode() ?? NULL;
		$error[] =  'Línea: ' .     $excptnOrErrorStr->getLine() ?? NULL;
		$error[] =  'Archivo: ' .   $excptnOrErrorStr->getFile() ?? NULL;
		$error[] =  'Mensaje: ' .   $excptnOrErrorStr->getMessage() ?? NULL;
		$error[] =  'Trace: ' .     $excptnOrErrorStr->getTraceAsString() ?? NULL;
	
		//    Eliminamos basura.
		foreach ( $error as $i => &$err ){
			if( is_null( $err ) ){
				unset( $error[ $i ] );
			}
		}
	
		//    Matamos el proceso imprimiendo el error.
		die ( '[Error]. ' . implode( '. <br/>' . PHP_EOL, $error ) );
	}
	
	//	Controlador de errores
	function cError($errno, $errstr, $errfile, $errline) {
	
		//    Preparamos una lista de elementos de mensaje de error.
		$error = [];
	
		//    Obtenemos características del error (Si están disponibles).
		$error[] =  'Código: ' .    $errno ?? NULL;
		$error[] =  'Línea: ' .     $errline ?? NULL;
		$error[] =  'Archivo: ' .   $errfile ?? NULL;
		$error[] =  'Mensaje: ' .   $errstr ?? NULL;
	
		//    Eliminamos basura.
		foreach ( $error as $i => &$err ){
			if( is_null( $err ) ){
				unset( $error[ $i ] );
			}
		}
	
		//    Matamos el proceso imprimiendo el error.
		errorMsg ( '[Error]. ' . implode( '. <br/>' . PHP_EOL, $error ) );
	}
	
	//	Establecemos el Controlador de errores
	set_error_handler('cError');
	
	//	Establecemos el Controlador de excepciones
	set_exception_handler('errorMsg');

// [TERMINA BLOQUE]: Impresión y control de errores.

$RUTAS_TRABAJO = [];

#### Parametros modificadores: ###

	/*	Desde línea de comandos y parametros de URL (GET):
	 *	 -f, --folder:		Directorio de trabajo.
	 *	 -b, --book:		Libro de instrucciones.
	 *	 -i, --input-file:	Archivo de entrada.
	 *	 -o, --output-file:	Archivo de salida.
	 *	 -p, --print:		¿Imprimir salida?.
	*/
		
		/* Comandos cortos */
		$shortopts  = '';
		$shortopts .= 'f:';  		// String 			@FOLDER =>			Valor opcional
		$shortopts .= 'b:';			// String			@BOOK => 			Valor opcional
		$shortopts .= 'i:';			// String			@INPUT-FILENAME => 	Valor opcional
		$shortopts .= 'o:';			// String			@OUTPUT-FILENAME => Valor opcional
		$shortopts .= 'p:';			// Boolean String	@PRINT =>			Valor opcional
		
		/* Comandos largos */
		$longopts  = [
		    'folder:',				// String 			@FOLDER =>			Valor opcional
		    'book:',				// String 			@BOOK =>			Valor opcional
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
		$RUTAS_TRABAJO['folder'] =		realpath( $CfgParams['f'] ?? $CfgParams['folder'] ?? FALSE );
		$RUTAS_TRABAJO['book'] =		basename( $CfgParams['b'] ?? $CfgParams['book'] ?? FALSE );
		$RUTAS_TRABAJO['input-file'] =	basename( $CfgParams['i'] ?? $CfgParams['input-file'] ?? FALSE );
		$RUTAS_TRABAJO['output-file'] =	basename( $CfgParams['o'] ?? $CfgParams['output-file'] ?? FALSE );
		$RUTAS_TRABAJO['print'] =		intval( $tmpPrintOptn );
		
		//	Si no recibimos un directorio de trabajo, establecemos el directorio actual como predeterminado.
		if( !$RUTAS_TRABAJO['folder'] ){
			$RUTAS_TRABAJO['folder'] = __DIR__;
		}
		
		//	Si no recibimos un libro de instrucciones, establecemos un valor predeterminado.
		if( !$RUTAS_TRABAJO['book'] ){
			$RUTAS_TRABAJO['book'] = '1.libro-de-instrucciones.txt';
		}

		//	Si recibimos un nombre de archivo  de entrada, veificamos su extensión.
		if( $i =& $RUTAS_TRABAJO['input-file'] ){
			
			if( strtolower( substr( $i, strrpos( $i, '.' )+1 ) ) !== 'txt' ){
				throw new Exception( 'Por seguridad, los archivos de entrada solo pueden tener extensión *.txt' );
			}
		} else {
			$RUTAS_TRABAJO['input-file'] = '2.entrada.txt';
		}
		
		//	Si no recibimos un archivo de salida, establecemos un valor predeterminado.
		if( !$RUTAS_TRABAJO['output-file'] ){
			$RUTAS_TRABAJO['output-file'] = '3.salida.txt';
		}

#	-----------------------------------------------------------------------------------

	//	Definimos las rutas de los archivos que vamos a usar y verificamos permisos.
	$RUTAS = [
		//	El archivo de clase siempre debe estar junto a los demás del proyecto
		'clase_cifrado'		=>		__DIR__ . '/clase_cifrado.php',
		
		//	Todos los archivos deben estar en el mismo directorio de trabajo {$RUTAS_TRABAJO['folder']}.
		'instrucciones'		=>		$RUTAS_TRABAJO['folder'] . '/' . $RUTAS_TRABAJO['book'],
		'archv_entrada'		=>		$RUTAS_TRABAJO['folder'] . '/' . $RUTAS_TRABAJO['input-file'],
		'archv_salida'		=>		$RUTAS_TRABAJO['folder'] . '/' . $RUTAS_TRABAJO['output-file'],
	];
	
	if( empty( $f = realpath( $RUTAS['clase_cifrado'] ) ) || !is_readable( $f ) ){
		throw new Exception( 'No se puede leer el archivo de cifrado.' );
	}

	if( empty( $f = realpath( $RUTAS['instrucciones'] ) ) || !is_readable( $f ) ){
		throw new Exception( 'No se puede leer el libro de instrucciones.' );
	}

	if( empty( $f = realpath( $RUTAS['archv_entrada'] ) ) || !is_readable( $f ) ){
		throw new Exception( 'No se puede leer el archivo de entrada.' );
	}

	if( empty( $f = dirname( realpath( $RUTAS['archv_salida'] ) ) ) || !is_writable( $f ) ){
		throw new Exception( 'La carpeta de salida no permite escribir el archivo de salida [' . $f . '].' );
	}
	
	unset( $f );
	
	define( 'RUTAS', $RUTAS );

#	-----------------------------------------------------------------------------------
	
//    [BLOQUE]: Cargar clase de cifrado.

	//    Cargamos el archivo de clase o lanzamos una excepción si no pudimos.
	if( empty( require_once( RUTAS[ 'clase_cifrado' ] ) ?: FALSE ) ){
		
		//    No se pudo cargar
		throw new Exception( 'No se pudo cargar la clase de cifrado de mensajes.' );
	}

// [TERMINA BLOQUE]: Cargar clase de cifrado.

#	-----------------------------------------------------------------------------------

//    [BLOQUE]: Indexamos el Archivo de instrucciones.

	//	Cargamos las instrucciones.
	if( empty( $fgc = file_get_contents( RUTAS[ 'instrucciones' ] ?: FALSE ) ) ){
		throw new Exception( 'No se pudo cargar el archivo de instrucciones.' );
	}

	/*	Limpiamos Saltos de línea según el S.O. que creó el archivo: Linux = \n, Mac = \r, Windows = \r\n
	 *	Y las guardamos cada línea en una lista de instrucciones [$instrucciones].
	*/
	if( empty( $instrucciones = explode( '|', preg_replace( '/[\r\n]+/', '|', $fgc ) ) ) ){
		throw new Exception( 'No se pudo obtener la lista de instrucciones.' );
	}

	//	
	foreach ( $instrucciones as &$instruccion ){
		$instruccion = cifrador::processInstruction( $instruccion );
	}

	//	Indexamos las instrucciones.
	define( 'INSTRUCCIONES', $instrucciones );

	//	Solo necesitamos la constante.
	unset( $instrucciones );

// [TERMINA BLOQUE]: Indexamos el Archivo de instrucciones.

#	-----------------------------------------------------------------------------------

//    [BLOQUE]: Cargando Archivo de entrada.

	//	Cargamos las instrucciones o lanzamos una excepción si no pudimos.
	if( empty( $fgc = file_get_contents( RUTAS[ 'archv_entrada' ] ?: FALSE ) ) ){
		throw new Exception( 'No se pudo cargar el archivo de entrada.' );
	}
	
	/*	Limpiamos Saltos de línea según el S.O. que creó el archivo: Linux = \n, Mac = \r, Windows = \r\n
	 *	Y asignamos cada línea a una variable según corresponda.
	*/
	list( $sizes, $fstInst, $sndInst, $msg ) = explode( PHP_EOL, $fgc );
	// list( $sizes, $fstInst, $sndInst, $msg ) = explode( '|', preg_replace( '/[\r\n]+/', '|', $fgc ) );

	//	Todas las variables deben tener un valor asignado, de lo contrario, ocurrió un error.
	if( FALSE
		|| !( $sizes =		trim( $sizes ) )
		|| !( $msg =		trim( $msg ) )
		|| ( $fstInst =	trim( $fstInst ) ?: NULL ) === FALSE
		|| ( $sndInst =	trim( $sndInst ) ?: NULL ) === FALSE
	){
		throw new Exception( 'Formato incorrecto en su archivo de entrada.' );
	}

	//	Nos aseguramos que todos los tamaños sean números enteros.
	if( !preg_match( '/(?<M1>\d+) (?<M2>\d+) (?<N>\d+)/i', $sizes, $msgsLen ) ){
		throw new Exception( 'Corrupción de tamaños de palabra en su archivo de entrada.' );
	}
	
	$N = intval( $msgsLen['N'] );
	$lenN = strlen( $msg );

	/*	Verificamos el tamaño de $N
		N siempre estará entre 3 y 5000 inclusive.
			N = ( 3, 5000 );	=>		Intervalo abierto a los lados.
	*/
	if( !$N || $N != $lenN || !( $N > 3 || $N < 5000 ) ){

		//	No tenemos forma de indicar que hay una falla en tamaño de mensaje, así que mostramos la excepción.
		throw new Exception( 'Logitud de mensaje inválida.' );
	}

// [TERMINA BLOQUE]: Cargando Archivo de entrada.

#	-----------------------------------------------------------------------------------

//    [BLOQUE]: Archivo de salida.
	/*		A partir de aquí ya validamos todas las condiciones necesarias para ejecutar el codigo,
	 *	así que ya no lanzaremos excepciones y solo nos limitaremos a responder si existen instrucciones válidas.
	*/

	//	Por defecto consideraremos que SI existe una instrucciones escondidas.
	$Hay_Instruc_Escondida = [
		1 => 'SI',
		2 => 'SI',
	];

	$M1 =		intval( $msgsLen['M1'] );
	$M2 =		intval( $msgsLen['M2'] );
	$lenM1 =	strlen( $fstInst );
	$lenM2 =	strlen( $sndInst );
	
	$fstInst =	cifrador::validateInstruction( $fstInst );
	$sndInst =	cifrador::validateInstruction( $sndInst );
	$msg =		cifrador::processMessage( $msg );

	try {

		$Inst1 = &$Hay_Instruc_Escondida[ 1 ];
		
		//	Verificamos que las instrucciones recibidas estén registradas en el libro de instrucciones.
		if( in_array( $fstInst, INSTRUCCIONES ) === FALSE ){
			throw new Exception( 'Mensaje corrupto 1.' );
		}

		if( strpos( $msg, $fstInst ) === FALSE ){
			throw new Exception( 'La primera instrucción NO existe en el mensaje .' );
		}

		/*	Verificamos el tamaño de $M1: M1 y M2 siempre estarán entre 2 y 50 inclusive.
				{ M1, M2 } = ( 2, 50 );	=>		Intervalo abierto a los lados.
		*/
		if( $M1 != $lenM1 || !( $M1 > 2 || $M1 < 50 ) ){
			throw new Exception( 'Logitud de primera instrucción inválida.' );
		}

	} catch ( Exception $e ) {
		$Inst1 = 'NO';
	}

	try {

		$Inst2 = &$Hay_Instruc_Escondida[ 2 ];
		
		//	Verificamos que las instrucciones recibidas estén registradas en el libro de instrucciones.
		if( !in_array( $sndInst, INSTRUCCIONES ) ){
			throw new Exception( 'Mensaje corrupto 2.' );
		}

		if( strpos( $msg, $sndInst ) === FALSE ){
			throw new Exception( 'La segunda instrucción NO existe en el mensaje .' );
		}

		/*	Verificamos el tamaño de $M2: M1 y M2 siempre estarán entre 2 y 50 inclusive.
				{ M1, M2 } = ( 2, 50 );	=>		Intervalo abierto a los lados.
		*/
		if( $M2 != $lenM1 || !( $M2 > 2 || $M2 < 50 ) ){
			throw new Exception( 'Logitud de segunda instrucción inválida.' );
		}
		
	} catch ( Exception $e ) {
		$Inst2 = 'NO';
	}
	
	//	Guardando archivo de salida.
	file_put_contents( RUTAS[ 'archv_salida' ], implode( PHP_EOL, $Hay_Instruc_Escondida ) );

// [TERMINA BLOQUE]: Generando archivo de salida.

#	-----------------------------------------------------------------------------------

//	Si se solicitó que se imprima el resultado en pantalla: IMPRIMIMOS.
if( $RUTAS_TRABAJO['print'] ){
	
	//	Imprimimos el archivo como texto plano
	header( 'Content-Type: text/plain' );
	readfile( RUTAS[ 'archv_salida' ] );
}