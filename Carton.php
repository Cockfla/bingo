<?php
/*
interface CartonFactory
{
    public function crearCarton(): array;
}
*/

class Carton
{
    private array $numeros;
    private array $numerosMarcados = [];

    public function __construct(CartonFactory $factory)
    {
        $this->numeros = $factory->crearCarton();
        $this->validarCarton($this->numeros);
    }

    public function marcarNumero(int $numero): void
    {
        if ($this->contieneNumero($numero) && !$this->estaMarcado($numero)) {
            $this->numerosMarcados[] = $numero;
        }
    }

    public function contieneNumero(int $numero): bool
    {
        foreach ($this->numeros as $fila) {
            if (in_array($numero, $fila, true)) {
                return true;
            }
        }

        return false;
    }

    public function estaMarcado(int $numero): bool
    {
        return in_array($numero, $this->numerosMarcados, true);
    }

    public function getNumeros(): array
    {
        return $this->numeros;
    }

    public function getNumerosMarcados(): array
    {
        return $this->numerosMarcados;
    }

    public function mostrarCarton(): void
    {
        foreach ($this->numeros as $fila) {
            foreach ($fila as $numero) {
                echo $this->estaMarcado($numero)
                    ? "[X]\t"
                    : "[$numero]\t";
            }

            echo PHP_EOL;
        }
    }

    private function validarCarton(array $numeros): void
    {
        if (empty($numeros)) {
            throw new InvalidArgumentException("El cartón no puede estar vacío.");
        }

        $todos = [];

        foreach ($numeros as $fila) {
            if (!is_array($fila) || empty($fila)) {
                throw new InvalidArgumentException("Cada fila debe ser un array no vacío.");
            }

            foreach ($fila as $numero) {
                if (!is_int($numero)) {
                    throw new InvalidArgumentException("El cartón solo puede contener números enteros.");
                }

                if (in_array($numero, $todos, true)) {
                    throw new InvalidArgumentException("El cartón no puede tener números repetidos.");
                }

                $todos[] = $numero;
            }
        }
    }
}