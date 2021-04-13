<?php

namespace qti\customOperators\math\fraction;

class Numerator extends NumeratorDenominator
{
    protected function extract(array $values)
    {
        return $values[0];
    }
}
