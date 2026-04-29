<?php

require_once __DIR__ . '/interface.php';

//Logica Ganador Linea (Strategy)

class EstrategiaLinea implements EstrategiaGanador, ComponenteEstrategia {

    public function esGanador(array $carton, array $numerosMarcados): bool {
        if (empty($carton) || empty($numerosMarcados)) {
            return false;
        }

        $filas = count($carton);
        $columnas = count($carton[0]);
        $marcadosMap = array_flip($numerosMarcados); // Optimización: búsqueda O(1)

        // 1. Verificar Filas (Eje X)
        foreach ($carton as $fila) {
            if ($this->verificarLinea($fila, $marcadosMap)) {
                return true;
            }
        }

        // 2. Verificar Columnas (Eje Y)
        for ($col = 0; $col < $columnas; $col++) {
            $columna = array_column($carton, $col);
            if ($this->verificarLinea($columna, $marcadosMap)) {
                return true;
            }
        }

        return false;
    }

    // Revisa si todos los números de una línea están en el mapa de marcados

    private function verificarLinea(array $linea, array $marcadosMap): bool {
        foreach ($linea as $numero) {
            if (!isset($marcadosMap[$numero])) {
                return false; // Si falta uno, esta línea no sirve
            }
        }
        return true; // Linea llena
    }
}

//Logica Ganador Cuatro Esquinas (Strategy)

class EstrategiaCuatroEsquinas implements EstrategiaGanador {

    public function esGanador(array $carton, array $numerosMarcados): bool {

        $filas = count($carton);
        $columnas = count($carton[0]);

        $esquinas = [
            $carton[0][0],
            $carton[0][$columnas - 1],
            $carton[$filas - 1][0],
            $carton[$filas - 1][$columnas - 1],
        ];

        foreach ($esquinas as $numero) {
            if (!in_array($numero, $numerosMarcados)) {
                return false;
            }
        }

        return true;
    }
}

//Logica Ganador Lleno (Strategy)

class EstrategiaLleno implements EstrategiaGanador, ComponenteEstrategia {
    public function esGanador(array $carton, array $numerosMarcados): bool {
        // Verificar si todos los números del cartón están marcados
        foreach ($carton as $fila) {
            foreach ($fila as $numero) {
                if (!in_array($numero, $numerosMarcados)) {
                    return false; // No es ganador si hay un número sin marcar
                }
            }
        }
        return true; // Es ganador si todos los números están marcados
    }
}