<?php
namespace qtismtest\data\expressions\operators;

use qtismtest\QtiSmEnumTestCase;
use qtism\data\expressions\operators\Statistics;

class StatisticsTest extends QtiSmEnumTestCase
{
    protected function getEnumerationFqcn()
    {
        return Statistics::class;
    }
    
    protected function getNames()
    {
        return array(
            'mean',
            'sampleVariance',
            'sampleSD',
            'popVariance',
            'popSD'
        );
    }
    
    protected function getKeys()
    {
        return array(
            'MEAN',
            'SAMPLE_VARIANCE',
            'SAMPLE_SD',
            'POP_VARIANCE',
            'POP_SD'
        );
    }
    
    protected function getConstants()
    {
        return array(
            Statistics::MEAN,
            Statistics::SAMPLE_VARIANCE,
            Statistics::SAMPLE_SD,
            Statistics::POP_VARIANCE,
            Statistics::POP_SD
        );
    }
}
