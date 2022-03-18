import fs from 'fs'
import readline from 'readline'

//  Si no recibimos el nombre de archivo en el argumento de la linea de comandos...
//  lo asumiremos como {entrada.txt}.
const file = __dirname + '/' + ( process.argv[2] || 'entrada.txt' );

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

        //	Comparamos los puntajes mas altos de ventajas de cada jugador:
        let $j1 = getMaxOfArray( $jugadores['j1'] );
        let $j2 = getMaxOfArray( $jugadores['j2'] );

        let $PUNTAJES = opNaveEspacial( $j1, $j2 );

        //	Asumimos que SIEMPRE existe un ganador, por lo que si NO GANA 1, GANARÁ 2.
        let $GANADOR_JUEGO = ( $PUNTAJES === 1 ) ? 1 : 2;

        let $GANADOR_PUNTAJE = $GANADOR_JUEGO == 1 ? $j1 : $j2;

        let $STR_SALIDA = `${$GANADOR_JUEGO} ${$GANADOR_PUNTAJE}`;

        fs.writeFileSync( __dirname + '/salida.txt', $STR_SALIDA );

        console.dir( $STR_SALIDA );
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

        /*	Usamos el operador de nave espacial que nos dirá qué jugador ganó y guardaremos las rondas que ganó.
            * 1: J1,
            * -1: J2.
        */
        let $ganador = opNaveEspacial( $ronda['j1'], $ronda['j2'] );

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