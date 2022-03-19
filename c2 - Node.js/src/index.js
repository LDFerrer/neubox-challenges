/*  Comandos para ejecutar:
    ##  Estando en la raíz del proyecto (arriba de la carpeta {src}), ejecutar:
 *  CMD1 (Vía Node Package Manager) con opciones predeterminadas:
        npm run start
 *  CMD2 (Vía Node.exe):
 *      clear; node --require "babel-register" ./src/index.js -f="./src" -i="entrada.txt" -o="salida.txt" -p="1"
 */

import fs from 'fs';
import readline from 'readline';

var $ARGS = [];             //  Aquí guardaremos todos los argumentos recibidos.
var _c = null;              //  Aquí separaremos los componentes de cada argumento.
var $tmpPrintOptn = null;   //  Aquí parsearemos la impresión de salida.
var argsPermitidos = [      //  ESTE ES UN ARREGLO DE LOS COMPONENTES SOPORTADO POR EL SCRIPT.
    '-f', '--folder',       //  Directorio de trabajo
    '-i', '--input-file',   //  Archivo de entrada.
    '-o', '--output-file',  //  Archivo de salida.
    '-p', '--print',        //  ¿Imprimir salida?.
];

//  Iteramos los argumentos recibidos.
process.argv.forEach(( val, index ) => {
    //  En cada iteración, separamos el argumento en sus componentes y buscamos un guión.
    if( ( _c = val.split('=') ) && _c[0][0] == '-' ){
        //  Si el componente cero es un argumento soportado, agregamos su valor.
        if( argsPermitidos.indexOf( _c[0] )+1 ){
            $ARGS[ _c[0] ] = _c[1];
        }
    }
});

//	El parametro @PRINT es un booleano, así que debemos parsear previamente el string de entrada.
if( $tmpPrintOptn = $ARGS['-p'] || $ARGS['--print'] || false ){

    //	Si recibimos una cadena la convertimos a minúsculas y verificamos solo la condición explícita TRUE.
    if( typeof( $tmpPrintOptn ) == 'string' && ( $tmpPrintOptn = $tmpPrintOptn.toLowerCase() ) ){

        //	Si es un String numérico, lo convertimos.
        if( !isNaN( $tmpPrintOptn ) ){
            $tmpPrintOptn = !!parseInt( $tmpPrintOptn );
        }

        //	Si solo es una palabra, buscaremos la palabra reservada TRUE.
        else {

            //	Queremos saber si EXPLÍCITAMENTE se pidió imprimir el resultado (además de guardarlo).
            $tmpPrintOptn = $tmpPrintOptn == 'true';
        }
    }
}
  
var IODefaults = {
    folder: $ARGS['-f'] || $ARGS['--folder'] || __dirname,
    input: $ARGS['-i'] || $ARGS['--input-file'] || 'entrada.txt',
    output: $ARGS['-o'] || $ARGS['--output-file'] || 'salida.txt',
    print: !!$tmpPrintOptn ? 1 : 0,
};

//  Si no recibimos el nombre de archivo en el argumento de la linea de comandos...
//  lo asumiremos como {entrada.txt}.
const file = IODefaults.folder + '/' + IODefaults.input;

//  Contador de número de lineas.
let lines = 0;

//  Interfaz para leer archivos
const rl = readline.createInterface({
    input: fs.createReadStream( file ),
    crlfDelay: Infinity
});

//  No me acordaba que tendría que pasarlo a Node.JS así que emularé su funcionamiento.
function opNaveEspacial( v1, v2 ){
    v1 = parseInt( v1 );
    v2 = parseInt( v2 );

    if( v1 === v2 ){return 0;}

    return ( v1 > v2 ) ? 1 : -1;
}

//  Función para sumar items de un array.
function sumArray( lista ) {
    let sum = 0;
    for (let i = 0; i < lista.length; i += 1) {sum += lista[i];}
    return sum;
}

//  Emularemos la función max de php para obtener el valor mas grande de una lista.
function getMaxOfArray(numArray) {
    return Math.max.apply(null, numArray);
}
 
let $numStr = [];
let $rondasJugadas = 0;
let $datos = [];
let $jugadores = {
	'j1': [],
	'j2': [],
};

//  Evento CLOSE.
rl.on( 'close', () => {
    if( $datos.length != ( $rondasJugadas + 1 ) ){
        console.log( 'Las rondas pretendidas y las registradas no coinciden. Por favor verifique.' );
    } else {

        //	Sumamos los puntos en rondas ganadas de cada jugador:
        let $j1 = sumArray( $jugadores['j1'] );
        let $j2 = sumArray( $jugadores['j2'] );

        let $PUNTAJES = opNaveEspacial( $j1, $j2 );

        //	Asumimos que SIEMPRE existe un ganador, por lo que si NO GANA 1, GANARÁ 2.
        let $GANADOR_JUEGO = ( $PUNTAJES === 1 ) ? 1 : 2;

        //	Definimos el puntaje del ganador.
        let $GANADOR_PUNTAJE = $GANADOR_JUEGO == 1 ? $j1 : $j2;

        //	Definimos la cadena de resultado.
        let $STR_SALIDA = `${$GANADOR_JUEGO} ${$GANADOR_PUNTAJE}`;

        //	Guardamos la salida.
        fs.writeFileSync( IODefaults.folder + '/' + IODefaults.output, $STR_SALIDA );

        //	Si se solicitó imprimir la salida, la imprimimos.
        if( !!IODefaults.print ){
            console.dir( $STR_SALIDA );
        }
    }
});

//  Evento: Nueva línea leída.
rl.on( 'line', line => {
    //  La línea ZERO solo tiene el número pretendido de rondas.
    if( !lines ){
        $rondasJugadas = parseInt( line );
    }

    //  Comenzamos a parsear a partir de la línea 2 (indice 1).
    if( lines ){

        //  Obtenemos los componentes de la ronda actual.
        if( $numStr = line.split( ' ' ) ){
            $datos[ lines ] = {
                'j1': parseInt( $numStr[0] ),
                'j2': parseInt( $numStr[1] ),
            };
        }

        let $ronda = $datos[ lines ];

        /*	Saber QUÉ JUGADOR ganó cada ronda, sirve para contabilizar SOLO sus puntos de rondas ganadas.
            Usamos el operador de nave espacial que nos dirá qué jugador ganó y guardaremos las rondas que ganó.
            * 1: J1,
            * -1: J2.
        */
        let $ganador = opNaveEspacial( $ronda['j1'], $ronda['j2'] );

        /*	Las rondas perdidas de cada jugador lo dejan en desventaja, y como la regla dice que gana el que consiguió
            MAYOR VENTAJA, sería ilógico agregar sus rondas en desventaja al contador.
        */
            //	Ganó J1
            if( $ganador === 1 ){
                $jugadores['j1'][ $jugadores['j1'].length ] = $ronda['j1'] - $ronda['j2'];
            }

            //	Ganó J2
            if( $ganador === -1 ){
                $jugadores['j2'][ $jugadores['j2'].length ] = $ronda['j2'] - $ronda['j1'];
            }
    }

    ++lines;
});