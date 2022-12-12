<?php

declare(strict_types=1);

namespace Comum\Dominio\Modelo\Valores;

use DateTime;
use DateTimeImmutable;
use DateTimeZone;
use Exception;
use InvalidArgumentException;

/** @SuppressWarnings(PHPMD.TooManyPublicMethods) */
class Hora implements IHora, IDataHora
{
    use DataHoraComparacoes;

    private DateTime $objeto;

    // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
    // Exclusivos Hora
    // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

    public function fusoHorario(): string
    {
        return DataHora::fusoHorario();
    }

    public function __construct(string $umaExpressao, string $zona = "Padrao")
    {
        $zona = $this->resolverFusoHorario($zona);

        // se não for numérico, pode ser uma expressão (ex: +1 hour)
        if (is_numeric($umaExpressao[0]) === true) {
            $umaExpressao = "1980-01-10 {$umaExpressao}";
        }

        try {
            $this->objeto = new DateTime($umaExpressao, new DateTimeZone($zona));
        } catch (Exception $e) {
            throw new InvalidArgumentException("Hora inválida");
        }
    }

    /**
     * Cria uma nova data com base na zona especificada
     * @link https://www.php.net/manual/pt_BR/timezones.php
     */
    public static function agora(string $zona = "Padrao"): self
    {
        return new self('now', $zona);
    }

    /**
     * Constrói um novo objeto, mudando o fuso horário para a zona especificada.
     * Diferente do objeto Data, esta fábrica ajusta o valor para se
     * adequar ao novo fuso horário.
     * @see Comum\Dominio\Modelo\Valores\Data::comNovaZona
     */
    public function comFusoHorario(string $zona): Hora
    {
        $nova = new Hora($this->valor(), $this->zona());
        $nova->mudarFusoHorario($zona);
        return $nova;
    }

    /**
     * Mudança de zona atualiza o fuso horario do valor,
     * modificando o valor do objeto
     */
    protected function mudarFusoHorario(string $zona): Hora
    {
        $this->objeto->setTimezone(new DateTimeZone($zona));
        return $this;
    }

    public function valorUtc(): string
    {
        return $this->tipoPrimitivo()
            ->setTimezone(new DateTimeZone("UTC"))
            ->format('H:i:s');
    }

    public function zona(): string
    {
        return $this->objeto->getTimezone()->getName();
    }

    // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
    // Contrato IDataHora
    // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

    /** @SuppressWarnings(PHPMD.StaticAccess) */
    public function tipoPrimitivo(): DateTimeImmutable
    {
        return DateTimeImmutable::createFromMutable($this->objeto);
    }

    /**
     * Diferente do Unix Timestamp de datas, que são contados a partir de
     * 1º de janeiro de 1970, este timestamp usa a contagem no mesmo dia,
     * ou seja, 23:00:00 devolverá a contagem dos segundos existentes em 23 horas.
     */
    public function timestamp(): int
    {
        $tempo   = explode(':', $this->objeto->format('H:i:s'));
        $hora    = (int)$tempo[0];
        $minuto  = (int)$tempo[1];
        $segundo = (int)$tempo[2];

        $segundos = $hora * 3600 // horas em segundos (1h = 3600s)
            + $minuto * 60 // minutos ems egundos (1m = 60s)
            + $segundo; // segundos

        return $segundos;
    }

    public function valor(): string
    {
        return $this->objeto->format('H:i:s');
    }

    public function __toString(): string
    {
        return $this->valor();
    }

    // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
    // Contrato IHora
    // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

    public function diferencaEmHoras(IHora $outraHora): int
    {
        $diff = $this->tipoPrimitivo()->diff($outraHora->tipoPrimitivo());
        return $diff->h;
    }
}
