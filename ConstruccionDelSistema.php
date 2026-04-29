<?php

// 🏗️ BUILDER DEL BINGO

class ConstructorBingo implements BingoBuilder
{
    private array $jugadores = [];
    private EstrategiaGanador $estrategia;

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

    public function build()
    {
        return new Bingo(
            $this->jugadores,
            $this->estrategia
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