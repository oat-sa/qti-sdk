<?php

namespace qtismtest\data\expressions\operators;

use qtism\data\expressions\operators\MathFunctions;
use qtismtest\QtiSmEnumTestCase;

/**
 * Class MathFunctionsTest
 *
 * @package qtismtest\data\expressions\operators
 */
class MathFunctionsTest extends QtiSmEnumTestCase
{
    /**
     * @return string
     */
    protected function getEnumerationFqcn()
    {
        return MathFunctions::class;
    }

    /**
     * @return array
     */
    protected function getNames()
    {
        return [
            'sin',
            'cos',
            'tan',
            'sec',
            'csc',
            'cot',
            'asin',
            'acos',
            'atan',
            'atan2',
            'asec',
            'acsc',
            'acot',
            'sinh',
            'cosh',
            'tanh',
            'sech',
            'csch',
            'coth',
            'log',
            'ln',
            'exp',
            'abs',
            'signum',
            'floor',
            'ceil',
            'toDegrees',
            'toRadians',
        ];
    }

    /**
     * @return array
     */
    protected function getKeys()
    {
        return [
            'SIN',
            'COS',
            'TAN',
            'SEC',
            'CSC',
            'COT',
            'ASIN',
            'ACOS',
            'ATAN',
            'ATAN2',
            'ASEC',
            'ACSC',
            'ACOT',
            'SINH',
            'COSH',
            'TANH',
            'SECH',
            'CSCH',
            'COTH',
            'LOG',
            'LN',
            'EXP',
            'ABS',
            'SIGNUM',
            'FLOOR',
            'CEIL',
            'TO_DEGREES',
            'TO_RADIANS',
        ];
    }

    /**
     * @return array
     */
    protected function getConstants()
    {
        return [
            MathFunctions::SIN,
            MathFunctions::COS,
            MathFunctions::TAN,
            MathFunctions::SEC,
            MathFunctions::CSC,
            MathFunctions::COT,
            MathFunctions::ASIN,
            MathFunctions::ACOS,
            MathFunctions::ATAN,
            MathFunctions::ATAN2,
            MathFunctions::ASEC,
            MathFunctions::ACSC,
            MathFunctions::ACOT,
            MathFunctions::SINH,
            MathFunctions::COSH,
            MathFunctions::TANH,
            MathFunctions::SECH,
            MathFunctions::CSCH,
            MathFunctions::COTH,
            MathFunctions::LOG,
            MathFunctions::LN,
            MathFunctions::EXP,
            MathFunctions::ABS,
            MathFunctions::SIGNUM,
            MathFunctions::FLOOR,
            MathFunctions::CEIL,
            MathFunctions::TO_DEGREES,
            MathFunctions::TO_RADIANS,
        ];
    }
}
