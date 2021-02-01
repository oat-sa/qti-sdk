<?php

namespace qtismtest\data\storage\php\marshalling;

use qtism\common\datatypes\QtiPair;
use qtism\common\datatypes\QtiPoint;
use qtism\data\storage\php\marshalling\Utils as PhpMarshallingUtils;
use qtismtest\QtiSmTestCase;

/**
 * Class PhpMarshallingUtilsTest
 */
class PhpMarshallingUtilsTest extends QtiSmTestCase
{
    /**
     * @dataProvider variableNameDataProvider
     * @param mixed $value
     * @param int $occurence
     * @param string $expected
     */
    public function testVariableName($value, $occurence, $expected)
    {
        $this::assertEquals($expected, PhpMarshallingUtils::variableName($value, $occurence));
    }

    /**
     * @return array
     */
    public function variableNameDataProvider()
    {
        return [
            [null, 0, 'scalarnullvalue_0'],
            [null, 1, 'scalarnullvalue_1'],
            ['string!', 0, 'string_0'],
            ['string!', 2, 'string_2'],
            [-23, 0, 'integer_0'],
            [200, 3, 'integer_3'],
            [34.3, 0, 'double_0'],
            [24.3, 4, 'double_4'],
            [true, 0, 'boolean_0'],
            [false, 5, 'boolean_5'],
            [new QtiPoint(0, 0), 0, 'qtipoint_0'],
            [new QtiPair('A', 'B'), 6, 'qtipair_6'],
            [['a', true, false], 0, 'array_0'],
            [[], 7, 'array_7'],
        ];
    }
}
