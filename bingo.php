<?php

require_once __DIR__ . '/interface.php';

class Bingo implements Sujeto
{
	private $jugadores = [];
	private $estrategia;
	private $generador;
	private $estado;
	private $numerosExtraidos = [];
	private $ultimaJugada;
	private $ganadores = [];

	public function __construct(
		?EstrategiaGanador $estrategia = null,
		?GeneradorNumeros $generador = null,
		array $jugadores = []
	) {
		$this->estrategia = $estrategia;
		$this->generador = $generador;
		$this->estado = new EstadoEsperando();

		foreach ($jugadores as $jugador) {
			if (!$jugador instanceof Observador) {
				throw new InvalidArgumentException('Todos los jugadores deben implementar Observador.');
			}

			$this->suscribir($jugador);
		}
	}

	public function setJugadores(array $jugadores): self
	{
		$this->jugadores = [];

		foreach ($jugadores as $jugador) {
			if (!$jugador instanceof Observador) {
				throw new InvalidArgumentException('Todos los jugadores deben implementar Observador.');
			}

			$this->suscribir($jugador);
		}

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

	public function iniciar(): void
	{
		$this->estado->iniciar($this);
	}

	public function jugar(): ?array
	{
		$this->estado->jugar($this);

		return $this->ultimaJugada;
	}

	public function finalizar(): void
	{
		$this->estado->finalizar($this);
	}

	public function suscribir(Observador $observador): void
	{
		foreach ($this->jugadores as $jugador) {
			if ($jugador === $observador) {
				return;
			}
		}

		$this->jugadores[] = $observador;
	}

	public function desuscribir(Observador $observador): void
	{
		$this->jugadores = array_values(array_filter(
			$this->jugadores,
			static function ($jugador) use ($observador) {
				return $jugador !== $observador;
			}
		));
	}

	public function notificar(int $numero): void
	{
		foreach ($this->jugadores as $jugador) {
			$jugador->actualizar($numero);
		}
	}

	public function puedeIniciar(): bool
	{
		return !empty($this->jugadores)
			&& $this->estrategia instanceof EstrategiaGanador
			&& $this->generador instanceof GeneradorNumeros;
	}

	public function extraerNumero(): int
	{
		if (!$this->generador instanceof GeneradorNumeros) {
			throw new LogicException('No hay un generador de numeros configurado.');
		}

		$numero = $this->generador->generarNumero();

		if (in_array($numero, $this->numerosExtraidos, true)) {
			throw new LogicException('El generador devolvio un numero repetido.');
		}

		$this->numerosExtraidos[] = $numero;

		return $numero;
	}

	public function evaluarGanadores(): array
	{
		if (!$this->estrategia instanceof EstrategiaGanador) {
			return [];
		}

		$ganadores = [];

		foreach ($this->jugadores as $jugador) {
			if (
				method_exists($jugador, 'tieneCartonGanador')
				&& $jugador->tieneCartonGanador($this->estrategia)
			) {
				$ganadores[] = $jugador;
			}
		}

		return $ganadores;
	}

	public function registrarJugada(int $numero, array $ganadores = []): void
	{
		$this->ultimaJugada = [
			'numero' => $numero,
			'ganadores' => $ganadores,
		];

		if (!empty($ganadores)) {
			$this->ganadores = $ganadores;
		}
	}

	public function setEstado(EstadoJuego $estado): void
	{
		$this->estado = $estado;
	}

	public function obtenerEstado(): EstadoJuego
	{
		return $this->estado;
	}

	public function obtenerNombreEstado(): string
	{
		if ($this->estado instanceof EstadoEsperando) {
			return 'esperando';
		}

		if ($this->estado instanceof EstadoEnCurso) {
			return 'en_curso';
		}

		return 'finalizado';
	}

	public function obtenerJugadores(): array
	{
		return $this->jugadores;
	}

	public function obtenerNumerosExtraidos(): array
	{
		return $this->numerosExtraidos;
	}

	public function obtenerGanadores(): array
	{
		return $this->ganadores;
	}

	public function obtenerUltimaJugada(): ?array
	{
		return $this->ultimaJugada;
	}

	public function hayGanadores(): bool
	{
		return !empty($this->ganadores);
	}

	private function obtenerCartonesDeJugador($jugador): array
	{
		if (is_object($jugador)) {
			if (method_exists($jugador, 'obtenerCartones')) {
				return (array) $jugador->obtenerCartones();
			}

			if (method_exists($jugador, 'obtenerCarton')) {
				return [$jugador->obtenerCarton()];
			}
		}

		if (is_array($jugador)) {
			if (array_key_exists('cartones', $jugador)) {
				return (array) $jugador['cartones'];
			}

			if (array_key_exists('carton', $jugador)) {
				return [$jugador['carton']];
			}
		}

		return [];
	}

	private function normalizarCarton($carton): array
	{
		if (is_object($carton)) {
			if (method_exists($carton, 'obtenerNumeros') && method_exists($carton, 'obtenerNumerosMarcados')) {
				return [$carton->obtenerNumeros(), $carton->obtenerNumerosMarcados()];
			}

			if (method_exists($carton, 'toArray') && method_exists($carton, 'obtenerNumerosMarcados')) {
				return [$carton->toArray(), $carton->obtenerNumerosMarcados()];
			}
		}

		if (is_array($carton)) {
			if (array_key_exists('numeros', $carton) || array_key_exists('marcados', $carton)) {
				return [
					$carton['numeros'] ?? [],
					$carton['marcados'] ?? [],
				];
			}

			return [$carton, []];
		}

		return [[], []];
	}
}

final class EstadoEsperando implements EstadoJuego
{
	public function iniciar($bingo): void
	{
		$this->asegurarBingoValido($bingo);

		if (!$bingo->puedeIniciar()) {
			throw new LogicException('El bingo necesita jugadores, estrategia y generador antes de iniciar.');
		}

		$bingo->setEstado(new EstadoEnCurso());
	}

	public function jugar($bingo): void
	{
		$this->asegurarBingoValido($bingo);

		throw new LogicException('No se puede jugar mientras el bingo esta esperando inicio.');
	}

	public function finalizar($bingo): void
	{
		$this->asegurarBingoValido($bingo);
		$bingo->setEstado(new EstadoFinalizado());
	}

	private function asegurarBingoValido($bingo): void
	{
		if (!$bingo instanceof Bingo) {
			throw new InvalidArgumentException('El estado solo puede operar sobre instancias de Bingo.');
		}
	}
}

final class EstadoEnCurso implements EstadoJuego
{
	public function iniciar($bingo): void
	{
		$this->asegurarBingoValido($bingo);

		throw new LogicException('El bingo ya esta en curso.');
	}

	public function jugar($bingo): void
	{
		$this->asegurarBingoValido($bingo);

		$numero = $bingo->extraerNumero();
		$bingo->notificar($numero);

		$ganadores = $bingo->evaluarGanadores();
		$bingo->registrarJugada($numero, $ganadores);

		if (!empty($ganadores)) {
			$bingo->setEstado(new EstadoFinalizado());
		}
	}

	public function finalizar($bingo): void
	{
		$this->asegurarBingoValido($bingo);
		$bingo->setEstado(new EstadoFinalizado());
	}

	private function asegurarBingoValido($bingo): void
	{
		if (!$bingo instanceof Bingo) {
			throw new InvalidArgumentException('El estado solo puede operar sobre instancias de Bingo.');
		}
	}
}

final class EstadoFinalizado implements EstadoJuego
{
	public function iniciar($bingo): void
	{
		$this->asegurarBingoValido($bingo);

		throw new LogicException('El bingo ya finalizo.');
	}

	public function jugar($bingo): void
	{
		$this->asegurarBingoValido($bingo);

		throw new LogicException('No se puede jugar una partida finalizada.');
	}

	public function finalizar($bingo): void
	{
		$this->asegurarBingoValido($bingo);
	}

	private function asegurarBingoValido($bingo): void
	{
		if (!$bingo instanceof Bingo) {
			throw new InvalidArgumentException('El estado solo puede operar sobre instancias de Bingo.');
		}
	}
}
