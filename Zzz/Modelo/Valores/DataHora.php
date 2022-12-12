<?php

declare(strict_types=1);

namespace Comum\Dominio\Modelo\Valores;

use DateTime;
use DateTimeImmutable;
use DateTimeZone;
use Exception;
use InvalidArgumentException;

/** @SuppressWarnings(PHPMD.TooManyPublicMethods) */
class DataHora implements IData, IHora, IDataHora
{
    use DataHoraComparacoes;

    protected static string $fusoHorario = "UTC";

    protected DateTime $objeto;

    // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
    // Exclusivos DataHora
    // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

    /**
     * Define o fuso horario padrão a ser usado pelas datas e horas na aplicação.
     * @link https://www.php.net/manual/pt_BR/timezones.php
     */
    public static function definirFusoHorario(string $fusoHorario): void
    {
        if ($fusoHorario === "Padrao") {
            $fusoHorario = "UTC";
        }

        // A definição de fuso horário precisa afetar igualmente
        // a data e a hora da aplicação para não haver inconsistências estranhas

        static::$fusoHorario = $fusoHorario;
    }

    public static function fusoHorario(): string
    {
        return static::$fusoHorario;
    }

    public function __construct(string $umaExpressao, string $zona = "Padrao")
    {
        $zona = $this->resolverFusoHorario($zona);

        try {
            $this->objeto = new DateTime($umaExpressao, new DateTimeZone($zona));
        } catch (Exception $e) {
            throw new InvalidArgumentException("Data inválida");
        }
    }

    /**
     * Cria uma nova data com base na zona especificada
     * @link https://www.php.net/manual/pt_BR/timezones.php
     * @return \Comum\Dominio\Modelo\Valores\DataHora
     */
    public static function agora(string $zona = "Padrao"): DataHora
    {
        return new DataHora('now', $zona);
    }

    /**
     * Constrói um novo objeto, mudando o fuso horário para a zona especificada.
     * Diferente do objeto Data, esta fábrica ajusta o valor para se
     * adequar ao novo fuso horário.
     * @see Comum\Dominio\Modelo\Valores\Data::comNovaZona
     */
    public function comFusoHorario(string $zona): DataHora
    {
        $nova = new DataHora($this->valor(), $this->zona());
        $nova->mudarFusoHorario($zona);
        return $nova;
    }

    /**
     * Mudança de zona atualiza o fuso horario do valor,
     * modificando o valor do objeto
     */
    protected function mudarFusoHorario(string $zona): DataHora
    {
        $this->objeto->setTimezone(new DateTimeZone($zona));
        return $this;
    }

    /**
     * O Unix Timestamp (carimbo de data/hora Unix) é uma forma de controlar o tempo
     * como um total de segundos contados desde o dia 1º de janeiro de 1970 no UTC.
     * A data e o nome são uma homenagem ao lançamento do sistema operacional Unix
     * que aconteceu década de 70.
     */
    public function timestampUtc(): int
    {
        return $this->tipoPrimitivo()
            ->setTimezone(new DateTimeZone("UTC"))
            ->getTimestamp();
    }

    public function valorUtc(): string
    {
        return $this->tipoPrimitivo()
            ->setTimezone(new DateTimeZone("UTC"))
            ->format('Y-m-d H:i:s');
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
        // Para não permitir alteração de estado
        return DateTimeImmutable::createFromMutable($this->objeto);
    }

    /**
     * O Unix Timestamp (carimbo de data/hora Unix) é uma forma de controlar o tempo
     * como um total de segundos contados desde o dia 1º de janeiro de 1970 no UTC.
     * A data e o nome são uma homenagem ao lançamento do sistema operacional Unix
     * que aconteceu década de 70.
     */
    public function timestamp(): int
    {
        return $this->objeto->getTimestamp();
    }

    public function valor(): string
    {
        return $this->objeto->format('Y-m-d H:i:s');
    }

    public function __toString(): string
    {
        return $this->valor();
    }

    // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
    // Contrato IHora
    // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

    public function diferencaEmHoras(IHora $outroObjeto): int
    {
        $diff = $this->tipoPrimitivo()->diff($outroObjeto->tipoPrimitivo());
        return $diff->h + ($diff->days * 24);
    }

    // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
    // Contrato IData
    // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

    public function diferencaEmDias(IData $outraData): int
    {
        return (int)$this->tipoPrimitivo()->diff($outraData->tipoPrimitivo())->format('%a');
    }
}
