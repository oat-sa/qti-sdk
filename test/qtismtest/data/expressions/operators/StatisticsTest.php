<?php

namespace qtismtest\data\expressions\operators;

use qtism\data\expressions\operators\Statistics;
use qtismtest\QtiSmEnumTestCase;

class StatisticsTest extends QtiSmEnumTestCase
{
    protected function getEnumerationFqcn()
    {
        return Statistics::class;
    }

    protected function getNames()
    {
        return [
            'mean',
            'sampleVariance',
            'sampleSD',
            'popVariance',
            'popSD',
        ];
    }

    protected function getKeys()
    {
        return [
            'MEAN',
            'SAMPLE_VARIANCE',
            'SAMPLE_SD',
            'POP_VARIANCE',
            'POP_SD',
        ];
    }

    protected function getConstants()
    {
        return [
            Statistics::MEAN,
            Statistics::SAMPLE_VARIANCE,
            Statistics::SAMPLE_SD,
            Statistics::POP_VARIANCE,
            Statistics::POP_SD,
        ];
    }
}
