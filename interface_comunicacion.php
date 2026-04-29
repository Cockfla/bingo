<?php

require_once __DIR__ . '/interface.php';

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
