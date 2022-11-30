<?php

declare(strict_types=1);

namespace qti\customOperators\math\fraction;

class Denominator extends NumeratorDenominator
{
    protected function extract(array $values)
    {
        return $values[1];
    }
}
