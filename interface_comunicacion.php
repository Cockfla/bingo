<?php

// ===============================
// 🎯 Strategy (Reglas del juego)
// ===============================
interface EstrategiaGanador {
    public function esGanador(array $carton, array $numerosMarcados): bool;
}


// ===============================
// 📡 Observer (Jugador recibe números)
// ===============================
interface Observador {
    public function actualizar(int $numero): void;
}


// ===============================
// 📢 Subject (Bingo notifica)
// ===============================
interface Sujeto {
    public function suscribir(Observador $observador): void;
    public function desuscribir(Observador $observador): void;
    public function notificar(int $numero): void;
}


// ===============================
// 🎮 State (Estados del juego)
// ===============================
interface EstadoJuego {
    public function iniciar($bingo): void;
    public function jugar($bingo): void;
    public function finalizar($bingo): void;
}


// ===============================
// 🎁 Componente base (Strategy + Decorator)
// ===============================
interface ComponenteEstrategia {
    public function esGanador(array $carton, array $numerosMarcados): bool;
}


// ===============================
// 🏗️ Builder (Construcción del Bingo)
// ===============================
interface BingoBuilder {
    public function setJugadores(array $jugadores): self;
    public function setEstrategia(EstrategiaGanador $estrategia): self;
    public function build();
}


// ===============================
// 🏭 Factory de cartones
// ===============================
interface CartonFactory {
    public function crearCarton(): array;
}


// ===============================
// ⚙️ Generador de números
// ===============================
interface GeneradorNumeros {
    public function generarNumero(): int;
}


// ===============================
// 📢 Implementación Subject
// ===============================
class NotificadorBingo implements Sujeto {
    private array $observadores = [];

    public function suscribir(Observador $observador): void {
        if (!in_array($observador, $this->observadores, true)) {
            $this->observadores[] = $observador;
        }
    }

    public function desuscribir(Observador $observador): void {
        foreach ($this->observadores as $key => $obs) {
            if ($obs === $observador) {
                unset($this->observadores[$key]);
            }
        }
    }

    public function notificar(int $numero): void {
        foreach ($this->observadores as $observador) {
            $observador->actualizar($numero);
        }
    }

    // ESTE MÉTODO ES EL QUE USARÁ EL RESTO DEL SISTEMA
    public function emitirNumero(int $numero): void {
        echo "📢 Número anunciado: $numero\n";
        $this->notificar($numero);
    }
}


// ===============================
// 👤 Implementación Observer
// ===============================
class Jugador implements Observador {
    private string $nombre;
    private array $numerosMarcados = [];

    public function __construct(string $nombre) {
        $this->nombre = $nombre;
    }

    public function actualizar(int $numero): void {
        echo "👤 {$this->nombre} recibió el número: $numero\n";
        $this->numerosMarcados[] = $numero;
    }

    // Solo para prueba
    public function verNumeros(): array {
        return $this->numerosMarcados;
    }
}


$notificador = new NotificadorBingo();

$jugador1 = new Jugador("Juan");
$jugador2 = new Jugador("Maria");

$notificador->suscribir($jugador1);
$notificador->suscribir($jugador2);

$notificador->emitirNumero(10);
$notificador->emitirNumero(25);
