<?php

declare(strict_types=1);

namespace qtismtest\data\expressions\operators;

use qtism\data\expressions\operators\Statistics;
use qtismtest\QtiSmEnumTestCase;

/**
 * Class StatisticsTest
 */
class StatisticsTest extends QtiSmEnumTestCase
{
    /**
     * @return string
     */
    protected function getEnumerationFqcn(): string
    {
        return Statistics::class;
    }

    /**
     * @return array
     */
    protected function getNames(): array
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
    protected function getKeys(): array
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
    protected function getConstants(): array
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
