<?php

require_once __DIR__ . '/interface.php';
require_once __DIR__ . '/Carton.php';

/*interface Observador
{
    public function actualizar(int $numero): void;
}
*/

class Jugador implements Observador
{
    private string $nombre;

    /**
     * @var Carton[]
     */
    private array $cartones = [];

    public function __construct(string $nombre, array $cartones)
    {
        if (trim($nombre) === '') {
            throw new InvalidArgumentException("El nombre del jugador no puede estar vacío.");
        }

        if (empty($cartones)) {
            throw new InvalidArgumentException("El jugador debe tener al menos un cartón.");
        }

        foreach ($cartones as $carton) {
            if (!$carton instanceof Carton) {
                throw new InvalidArgumentException("Todos los cartones deben ser instancias de Carton.");
            }
        }

        $this->nombre = $nombre;
        $this->cartones = $cartones;
    }

    public function actualizar(int $numero): void
    {
        foreach ($this->cartones as $carton) {
            $carton->marcarNumero($numero);
        }
    }

    public function tieneCartonGanador(EstrategiaGanador $estrategia): bool
    {
        foreach ($this->cartones as $carton) {
            if ($estrategia->esGanador(
                $carton->getNumeros(),
                $carton->getNumerosMarcados()
            )) {
                return true;
            }
        }

        return false;
    }

    public function getNombre(): string
    {
        return $this->nombre;
    }

    public function getCartones(): array
    {
        return $this->cartones;
    }

    public function agregarCarton(Carton $carton): void
    {
        $this->cartones[] = $carton;
    }

    public function mostrarCartones(): void
    {
        echo "Cartones de {$this->nombre}:" . PHP_EOL;

        foreach ($this->cartones as $indice => $carton) {
            echo "Cartón #" . ($indice + 1) . PHP_EOL;
            $carton->mostrarCarton();
            echo PHP_EOL;
        }
    }
}