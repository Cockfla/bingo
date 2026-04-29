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
//  Subject (Bingo notifica)
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
    public function setGenerador(GeneradorNumeros $generador): self;
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