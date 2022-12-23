<?php

namespace qtismtest\data\expressions\operators;

use InvalidArgumentException;
use qtism\common\enums\BaseType;
use qtism\data\expressions\BaseValue;
use qtism\data\expressions\ExpressionCollection;
use qtism\data\expressions\operators\Equal;
use qtism\data\expressions\operators\ToleranceMode;
use qtismtest\QtiSmTestCase;
use UnexpectedValueException;

/**
 * Class EqualTest
 */
class EqualTest extends QtiSmTestCase
{
    public function testInstantiationNoToleranceButRequired(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('The tolerance argument must be specified when ToleranceMode = ABSOLUTE or EXACT.');

        $equal = new Equal(
            new ExpressionCollection([new BaseValue(BaseType::INTEGER, 10), new BaseValue(BaseType::INTEGER, 10)]),
            ToleranceMode::ABSOLUTE
        );
    }

    public function testSetToleranceModeWrongValue(): void
    {
        $equal = new Equal(
            new ExpressionCollection([new BaseValue(BaseType::INTEGER, 10), new BaseValue(BaseType::INTEGER, 10)])
        );

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("The toleranceMode argument must be a value from the ToleranceMode enumeration, '1' given.");

        $equal->setToleranceMode(true);
    }

    public function testSetToleranceMissingT0(): void
    {
        $equal = new Equal(
            new ExpressionCollection([new BaseValue(BaseType::INTEGER, 10), new BaseValue(BaseType::INTEGER, 10)])
        );

        $equal->setToleranceMode(ToleranceMode::ABSOLUTE);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The tolerance array must contain at least t0.');

        $equal->setTolerance([]);
    }

    public function testSetToleranceTooMuchTs(): void
    {
        $equal = new Equal(
            new ExpressionCollection([new BaseValue(BaseType::INTEGER, 10), new BaseValue(BaseType::INTEGER, 10)])
        );

        $equal->setToleranceMode(ToleranceMode::ABSOLUTE);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The tolerance array must contain at most t0 and t1');

        $equal->setTolerance([1, 2, 3]);
    }

    public function testSetIncludeLowerBoundWrongType(): void
    {
        $equal = new Equal(
            new ExpressionCollection([new BaseValue(BaseType::INTEGER, 10), new BaseValue(BaseType::INTEGER, 10)])
        );

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("The includeLowerBound argument must be a boolean, 'string' given.");

        $equal->setIncludeLowerBound('str');
    }

    public function testSetIncludeUpperBoundWrongType(): void
    {
        $equal = new Equal(
            new ExpressionCollection([new BaseValue(BaseType::INTEGER, 10), new BaseValue(BaseType::INTEGER, 10)])
        );

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("The includeUpperBound argument must be a boolean, 'string' given.");

        $equal->setIncludeUpperBound('str');
    }
}
