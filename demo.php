<?php

/**
 * demo.php — Ejecutar este archivo para jugar al bingo desde consola.
 *
 * Modos:
 *   php demo.php          -> menú interactivo
 *   php demo.php auto     -> partida automática sin preguntas (útil para pruebas)
 */

require_once __DIR__ . '/Infraestructura.php';
require_once __DIR__ . '/ConstruccionDelSistema.php';
require_once __DIR__ . '/Carton.php';
require_once __DIR__ . '/Jugador.php';
require_once __DIR__ . '/logica.php';
require_once __DIR__ . '/Premios.php';

// Si se pasa el argumento "auto" se ejecuta una demo sin interacción
if (isset($argv[1]) && $argv[1] === 'auto') {
    demoAutomatica();
} else {
    (new Menu())->ejecutar();
}


// -------------------------------------------------------
// Partida automática (sin input del usuario)
// -------------------------------------------------------
function demoAutomatica(): void
{
    echo str_repeat('=', 50) . PHP_EOL;
    echo '       DEMO AUTOMATICA - BINGO' . PHP_EOL;
    echo str_repeat('=', 50) . PHP_EOL . PHP_EOL;

    // 1. Crear cartones y jugadores
    $factory = new Carton5x5Factory();

    $jugadores = [
        new Jugador('Ana',   [new Carton($factory)]),
        new Jugador('Pedro', [new Carton($factory)]),
        new Jugador('Luis',  [new Carton($factory)]),
    ];

    // 2. Mostrar cartones iniciales
    echo '--- Cartones iniciales ---' . PHP_EOL;
    foreach ($jugadores as $jugador) {
        $jugador->mostrarCartones();
    }

    // 3. Construir el juego (estrategia: línea)
    $bingo = (new ConstructorBingo())
        ->setJugadores($jugadores)
        ->setEstrategia(new EstrategiaLinea())
        ->setGenerador(new GeneradorAleatorioSinRepetir(1, 75))
        ->build();

    $bingo->iniciar();
    echo PHP_EOL . '>>> Estado: ' . $bingo->obtenerNombreEstado() . PHP_EOL . PHP_EOL;

    // 4. Extraer números hasta que haya ganador
    $ronda = 1;
    while (!$bingo->hayGanadores() && $ronda <= 75) {
        $jugada = $bingo->jugar();
        echo sprintf('  Ronda %3d: [ %2d ]', $ronda, $jugada['numero']);

        if (!empty($jugada['ganadores'])) {
            echo '   <-- BINGO!';
        }

        echo PHP_EOL;
        $ronda++;
    }

    // 5. Resultado final
    echo PHP_EOL . '--- Resultado ---' . PHP_EOL;
    echo 'Estado final    : ' . $bingo->obtenerNombreEstado() . PHP_EOL;
    echo 'Numeros sacados : ' . count($bingo->obtenerNumerosExtraidos()) . PHP_EOL . PHP_EOL;

    $ganadores = $bingo->obtenerGanadores();
    if (empty($ganadores)) {
        echo 'No hubo ganadores.' . PHP_EOL;
    } else {
        echo 'Ganador(es):' . PHP_EOL;
        foreach ($ganadores as $j) {
            echo '  -> ' . $j->getNombre() . PHP_EOL;
        }
    }

    // 6. Premios con Decorator
    $pozo      = new Pozo(10000);
    $sistema   = new PremioCartonLleno(
        new PremioSegundoGanador(
            new PremioPrimerGanador(new PremioBase($pozo))
        )
    );
    $nombres      = array_map(fn($j) => $j->getNombre(), $ganadores);
    $distribucion = $sistema->otorgar(['primer' => $nombres]);

    echo PHP_EOL . '--- Premios (pozo $' . $pozo->obtenerTotal() . ') ---' . PHP_EOL;
    if (empty($distribucion)) {
        echo 'No se distribuyeron premios.' . PHP_EOL;
    } else {
        foreach ($distribucion as $nombre => $monto) {
            echo "  {$nombre}: \${$monto}" . PHP_EOL;
        }
    }

    echo PHP_EOL . str_repeat('=', 50) . PHP_EOL;
    echo '             FIN DE LA PARTIDA' . PHP_EOL;
    echo str_repeat('=', 50) . PHP_EOL;
}
