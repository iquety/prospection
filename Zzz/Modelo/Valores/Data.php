<?php

declare(strict_types=1);

namespace Comum\Dominio\Modelo\Valores;

use DateTime;
use DateTimeImmutable;
use DateTimeZone;
use Exception;
use InvalidArgumentException;

class Data implements IData, IHora, IDataHora
{
    use DataHoraComparacoes;

    protected DateTime $objeto;

    // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
    // Exclusivos Data
    // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

    public function __construct(string $umaExpressao)
    {
        try {
            $this->objeto = new DateTime($umaExpressao, new DateTimeZone("UTC"));
        } catch (Exception $e) {
            throw new InvalidArgumentException("Data inválida");
        }

        $this->objeto->setTime(0, 0, 0, 0);
    }

    /**
     * Cria uma nova data com base na zona especificada
     * @link https://www.php.net/manual/pt_BR/timezones.php
     */
    public static function agora(): Data
    {
        return new Data('now');
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
        return $this->objeto->format('Y-m-d');
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
