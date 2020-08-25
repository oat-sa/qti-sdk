<?php

namespace qtismtest\data\expressions\operators;

use qtism\data\expressions\operators\Statistics;
use qtismtest\QtiSmEnumTestCase;

/**
 * Class StatisticsTest
 *
 * @package qtismtest\data\expressions\operators
 */
class StatisticsTest extends QtiSmEnumTestCase
{
    /**
     * @return string
     */
    protected function getEnumerationFqcn()
    {
        return Statistics::class;
    }

    /**
     * @return array
     */
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

    /**
     * @return array
     */
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

    /**
     * @return array
     */
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
