<?php

/*    [BLOQUE]: Impresión y control de errores.
**    No incluir estas funciones desde fuera, porque si se dañan: PERDEMOS EL CONTROL DE ERRORES.
*/
error_reporting(E_ALL);

ini_set("display_errors", "off");

function errorMsg( $e ){

	//    Si solo recibimos la cadena del error, matamos el proceso imprimiendo el error.
	if( !is_object( $e ) ){
		die( $e );
	}

	//    Preparamos una lista de elementos de mensaje de error.
	$error = [];

	//    Obtenemos características del error (Si están disponibles).
	$error[] =  'Código: ' .    $e->getCode() ?? NULL;
	$error[] =  'Línea: ' .     $e->getLine() ?? NULL;
	$error[] =  'Archivo: ' .   $e->getFile() ?? NULL;
	$error[] =  'Mensaje: ' .   $e->getMessage() ?? NULL;
	$error[] =  'Trace: ' .     $e->getTraceAsString() ?? NULL;

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


throw new Error( 'Ocurrió un error fatal.' );