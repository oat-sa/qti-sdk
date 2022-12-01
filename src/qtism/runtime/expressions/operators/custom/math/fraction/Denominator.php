<?php

namespace qti\customOperators\math\fraction;

class Denominator extends NumeratorDenominator
{
    protected function extract(array $values)
    {
        return $values[1];
    }
}
