<?php

require_once __DIR__ . '/interface.php';
require_once __DIR__ . '/Premios.php';

// ===============================
// ⚙️ Generador de números sin repetir
// ===============================
class GeneradorAleatorioSinRepetir implements GeneradorNumeros
{
    private array $numerosDisponibles;

    public function __construct(int $minimo = 1, int $maximo = 75)
    {
        if ($minimo > $maximo) {
            throw new InvalidArgumentException('El rango del generador no es valido.');
        }

        $this->numerosDisponibles = range($minimo, $maximo);
        shuffle($this->numerosDisponibles);
    }

    public function generarNumero(): int
    {
        if (empty($this->numerosDisponibles)) {
            throw new LogicException('No quedan numeros disponibles para extraer.');
        }

        return array_shift($this->numerosDisponibles);
    }

    public function numerosRestantes(): int
    {
        return count($this->numerosDisponibles);
    }
}

// ===============================
// 📋 Menú contextual del juego
// ===============================
class Menu
{
    private float $montoPozo = 0;

    // ------------------------------------------------
    // Punto de entrada
    // ------------------------------------------------
    public function ejecutar(): void
    {
        $this->titulo('BINGO');

        $jugadores  = $this->configurarJugadores();
        $estrategia = $this->elegirEstrategia();
        $modo       = $this->elegirModo();

        $montoStr         = $this->leer('Monto del pozo (ej: 10000): ');
        $this->montoPozo  = max(0, (float) $montoStr);

        $bingo = (new ConstructorBingo())
            ->setJugadores($jugadores)
            ->setEstrategia($estrategia)
            ->setGenerador(new GeneradorAleatorioSinRepetir())
            ->build();

        $bingo->iniciar();

        $this->separador();
        echo '>>> Estado: ' . $bingo->obtenerNombreEstado() . PHP_EOL . PHP_EOL;

        $this->bucleDeJuego($bingo, $modo);
        $this->mostrarResultados($bingo);
    }

    // ------------------------------------------------
    // Configuración de jugadores
    // ------------------------------------------------
    private function configurarJugadores(): array
    {
        echo PHP_EOL;
        $cantidadStr = $this->leer('Cuantos jugadores van a jugar? (minimo 2): ');
        $cantidad    = max(2, (int) $cantidadStr);

        $factory   = new Carton5x5Factory();
        $jugadores = [];

        for ($i = 1; $i <= $cantidad; $i++) {
            $nombre = $this->leer("  Nombre del jugador {$i}: ");
            if (trim($nombre) === '') {
                $nombre = "Jugador{$i}";
            }

            $jugadores[] = new Jugador($nombre, [new Carton($factory)]);
        }

        return $jugadores;
    }

    // ------------------------------------------------
    // Elección de estrategia de victoria
    // ------------------------------------------------
    private function elegirEstrategia(): EstrategiaGanador
    {
        echo PHP_EOL . 'Elige la estrategia de victoria:' . PHP_EOL;
        echo '  [1] Linea (horizontal o vertical)' . PHP_EOL;
        echo '  [2] Cuatro esquinas' . PHP_EOL;
        echo '  [3] Carton lleno (bingo!)' . PHP_EOL;

        $opcion = (int) $this->leer('Opcion: ');

        return match ($opcion) {
            2       => new EstrategiaCuatroEsquinas(),
            3       => new EstrategiaLleno(),
            default => new EstrategiaLinea(),
        };
    }

    // ------------------------------------------------
    // Elección del modo de juego
    // ------------------------------------------------
    private function elegirModo(): int
    {
        echo PHP_EOL . 'Modo de juego:' . PHP_EOL;
        echo '  [1] Automatico (extrae todos los numeros de una vez)' . PHP_EOL;
        echo '  [2] Manual     (presiona ENTER por cada numero)' . PHP_EOL;

        return (int) $this->leer('Opcion: ') === 2 ? 2 : 1;
    }

    // ------------------------------------------------
    // Bucle principal de extracción de números
    // ------------------------------------------------
    private function bucleDeJuego(Bingo $bingo, int $modo): void
    {
        echo PHP_EOL;
        $ronda = 1;

        while (!$bingo->hayGanadores()) {
            if ($modo === 2) {
                $this->leer('  [ENTER] Extraer siguiente numero...');
            }

            $jugada = $bingo->jugar();
            $numero  = $jugada['numero'];

            echo sprintf('  Ronda %3d: [ %2d ]', $ronda, $numero);

            if (!empty($jugada['ganadores'])) {
                echo '   <-- BINGO!';
            }

            echo PHP_EOL;

            if ($ronda >= 75) {
                break;
            }

            $ronda++;
        }
    }

    // ------------------------------------------------
    // Resultados finales
    // ------------------------------------------------
    private function mostrarResultados(Bingo $bingo): void
    {
        $this->titulo('RESULTADO FINAL');

        $ganadores = $bingo->obtenerGanadores();
        $extraidos = $bingo->obtenerNumerosExtraidos();

        echo 'Estado final    : ' . $bingo->obtenerNombreEstado() . PHP_EOL;
        echo 'Numeros sacados : ' . count($extraidos) . ' de 75' . PHP_EOL . PHP_EOL;

        if (empty($ganadores)) {
            echo 'No hubo ganadores en esta partida.' . PHP_EOL;
        } else {
            echo 'Ganador(es):' . PHP_EOL;
            foreach ($ganadores as $jugador) {
                echo '  -> ' . $jugador->getNombre() . PHP_EOL;
            }
        }

        echo PHP_EOL . '--- Cartones al terminar ---' . PHP_EOL;
        foreach ($bingo->obtenerJugadores() as $jugador) {
            $jugador->mostrarCartones();
        }

        // --- Premios ---
        if ($this->montoPozo > 0 && !empty($ganadores)) {
            $pozo    = new Pozo($this->montoPozo);
            $sistema = new PremioCartonLleno(
                new PremioSegundoGanador(
                    new PremioPrimerGanador(new PremioBase($pozo))
                )
            );

            $nombres      = array_map(fn($j) => $j->getNombre(), $ganadores);
            $distribucion = $sistema->otorgar(['primer' => $nombres]);

            echo PHP_EOL . '--- Premios (pozo $' . $this->montoPozo . ') ---' . PHP_EOL;
            if (empty($distribucion)) {
                echo 'No se distribuyeron premios.' . PHP_EOL;
            } else {
                foreach ($distribucion as $nombre => $monto) {
                    echo "  {$nombre}: \${$monto}" . PHP_EOL;
                }
            }
        }

        $this->separador();
        echo '            FIN DE LA PARTIDA' . PHP_EOL;
        $this->separador();
    }

    // ------------------------------------------------
    // Helpers de UI
    // ------------------------------------------------
    private function leer(string $prompt): string
    {
        echo $prompt;
        $linea = fgets(STDIN);
        return $linea !== false ? trim($linea) : '';
    }

    private function titulo(string $texto): void
    {
        echo PHP_EOL . str_repeat('=', 50) . PHP_EOL;
        echo '  ' . strtoupper($texto) . PHP_EOL;
        echo str_repeat('=', 50) . PHP_EOL;
    }

    private function separador(): void
    {
        echo str_repeat('-', 50) . PHP_EOL;
    }
}
