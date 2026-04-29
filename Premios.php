<?php

require_once __DIR__ . '/interface.php';

class Pozo {
    private $total = 0;

    public function __construct(float $montoInicial = 0) {
        $this->total = $montoInicial;
    }

    public function agregar(float $monto): void {
        $this->total += $monto;
    }

    public function obtenerTotal(): float {
        return $this->total;
    }

    public function reiniciar(): void {
        $this->total = 0;
    }
}

//premio 
interface Premio {
    public function otorgar(array $ganadores): array;
    public function getPozo(): float;
}

class PremioBase implements Premio {
    protected $pozo;

    public function __construct(Pozo $pozo) {
        $this->pozo = $pozo;
    }

    public function otorgar(array $ganadores): array {
        return [];
    }

    public function getPozo(): float {
        return $this->pozo->obtenerTotal();
    }
}

abstract class PremioDecorator implements Premio {
    protected $componente;

    public function __construct(Premio $componente) {
        $this->componente = $componente;
    }

    public function otorgar(array $ganadores): array {
        return $this->componente->otorgar($ganadores);
    }

    public function getPozo(): Float {
        return $this->componente->getPozo();
    }
}

// primer ganador 
class PremioPrimerGanador extends PremioDecorator {
    public function otorgar(array $ganadores): array {
        $premios = parent::otorgar($ganadores);

        if (!isset($ganadores["primer"])) return $premios;

        $monto = $this->getPozo() * 0.40;
        $porJugador = $monto / count($ganadores["primer"]);

        foreach ($ganadores["primer"] as $jugador) {
            $premios[$jugador] = ($premios[$jugador] ?? 0) + $porJugador;
        }

        return $premios;
    }
}

// segundo ganador 
class PremioSegundoGanador extends PremioDecorator {
    public function otorgar(array $ganadores): array {
        $premios = parent::otorgar($ganadores);

        if (!isset($ganadores["segundo"])) return $premios;

        $monto = $this->getPozo() * 0.20;
        $porJugador = $monto / count($ganadores["segundo"]);

        foreach ($ganadores["segundo"] as $jugador) {
            $premios[$jugador] = ($premios[$jugador] ?? 0) + $porJugador;
        }

        return $premios;
    }
}

// carton lleno 
class PremioCartonLleno extends PremioDecorator {
    public function otorgar(array $ganadores): array {
        $premios = parent::otorgar($ganadores);

        if (!isset($ganadores["carton_lleno"])) return $premios;

        $monto = $this->getPozo() * 0.40;
        $porJugador = $monto / count($ganadores["carton_lleno"]);

        foreach ($ganadores["carton_lleno"] as $jugador) {
            $premios[$jugador] = ($premios[$jugador] ?? 0) + $porJugador;
        }

        return $premios;
    }
}
?>