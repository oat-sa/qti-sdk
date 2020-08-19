<?php

namespace qtismtest\data\expressions\operators;

use qtism\common\enums\BaseType;
use qtism\data\expressions\BaseValue;
use qtism\data\expressions\ExpressionCollection;
use qtism\data\expressions\operators\Equal;
use qtism\data\expressions\operators\ToleranceMode;
use qtismtest\QtiSmTestCase;

class EqualTest extends QtiSmTestCase
{
    public function testInstantiationNoToleranceButRequired()
    {
        $this->setExpectedException(
            \UnexpectedValueException::class,
            "The tolerance argument must be specified when ToleranceMode = ABSOLUTE or EXACT."
        );

        $equal = new Equal(
            new ExpressionCollection([new BaseValue(BaseType::INTEGER, 10), new BaseValue(BaseType::INTEGER, 10)]),
            ToleranceMode::ABSOLUTE
        );
    }

    public function testSetToleranceModeWrongValue()
    {
        $equal = new Equal(
            new ExpressionCollection([new BaseValue(BaseType::INTEGER, 10), new BaseValue(BaseType::INTEGER, 10)])
        );

        $this->setExpectedException(
            \InvalidArgumentException::class,
            "The toleranceMode argument must be a value from the ToleranceMode enumeration, '1' given."
        );

        $equal->setToleranceMode(true);
    }

    public function testSetToleranceMissingT0()
    {
        $equal = new Equal(
            new ExpressionCollection([new BaseValue(BaseType::INTEGER, 10), new BaseValue(BaseType::INTEGER, 10)])
        );

        $equal->setToleranceMode(ToleranceMode::ABSOLUTE);

        $this->setExpectedException(
            \InvalidArgumentException::class,
            "The tolerance array must contain at least t0."
        );

        $equal->setTolerance([]);
    }

    public function testSetToleranceTooMuchTs()
    {
        $equal = new Equal(
            new ExpressionCollection([new BaseValue(BaseType::INTEGER, 10), new BaseValue(BaseType::INTEGER, 10)])
        );

        $equal->setToleranceMode(ToleranceMode::ABSOLUTE);

        $this->setExpectedException(
            \InvalidArgumentException::class,
            "The tolerance array must contain at most t0 and t1"
        );

        $equal->setTolerance([1, 2, 3]);
    }

    public function testSetIncludeLowerBoundWrongType()
    {
        $equal = new Equal(
            new ExpressionCollection([new BaseValue(BaseType::INTEGER, 10), new BaseValue(BaseType::INTEGER, 10)])
        );

        $this->setExpectedException(
            \InvalidArgumentException::class,
            "The includeLowerBound argument must be a boolean, 'string' given."
        );

        $equal->setIncludeLowerBound('str');
    }

    public function testSetIncludeUpperBoundWrongType()
    {
        $equal = new Equal(
            new ExpressionCollection([new BaseValue(BaseType::INTEGER, 10), new BaseValue(BaseType::INTEGER, 10)])
        );

        $this->setExpectedException(
            \InvalidArgumentException::class,
            "The includeUpperBound argument must be a boolean, 'string' given."
        );

        $equal->setIncludeUpperBound('str');
    }
}
