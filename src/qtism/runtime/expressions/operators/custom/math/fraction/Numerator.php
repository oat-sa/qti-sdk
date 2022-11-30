<?php

declare(strict_types=1);

namespace qti\customOperators\math\fraction;

class Numerator extends NumeratorDenominator
{
    protected function extract(array $values)
    {
        return $values[0];
    }
}
