<?php

require_once __DIR__ . '/interface.php';
require_once __DIR__ . '/bingo.php';
require_once __DIR__ . '/Infraestructura.php';

// 🏗️ BUILDER DEL BINGO

class ConstructorBingo implements BingoBuilder
{
    private array $jugadores = [];
    private ?EstrategiaGanador $estrategia = null;
    private ?GeneradorNumeros $generador = null;

    public function setJugadores(array $jugadores): self
    {
        $this->jugadores = $jugadores;
        return $this;
    }

    public function setEstrategia(EstrategiaGanador $estrategia): self
    {
        $this->estrategia = $estrategia;
        return $this;
    }

    public function setGenerador(GeneradorNumeros $generador): self
    {
        $this->generador = $generador;
        return $this;
    }

    public function build()
    {
        if (!$this->estrategia instanceof EstrategiaGanador) {
            throw new LogicException('Debes configurar una estrategia antes de construir el bingo.');
        }

        if (!$this->generador instanceof GeneradorNumeros) {
            $this->generador = new GeneradorAleatorioSinRepetir();
        }

        return new Bingo(
            $this->estrategia,
            $this->generador,
            $this->jugadores
        );
    }
}

// FACTORY DE CARTONES (5x5)

class Carton5x5Factory implements CartonFactory
{
    public function crearCarton(): array
    {
        $numeros = range(1, 75);
        shuffle($numeros);

        $carton = [];
        $indice = 0;

        for ($fila = 0; $fila < 5; $fila++) {
            for ($columna = 0; $columna < 5; $columna++) {
                $carton[$fila][$columna] = $numeros[$indice];
                $indice++;
            }
        }

        return $carton;
    }
}

// GeneradorAleatorioSinRepetir se define en Infraestructura.php