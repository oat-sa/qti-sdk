<?php

namespace qtismtest\data\content;

use InvalidArgumentException;
use qtism\data\content\enums\AriaLive;
use qtism\data\content\enums\AriaOrientation;
use qtism\data\content\xhtml\text\Span;
use qtismtest\QtiSmTestCase;
use stdClass;

class BodyElementTest extends QtiSmTestCase
{
    public function testRawInstantiation()
    {
        $span = new Span();
        $this->assertSame('', $span->getAriaControls());
        $this->assertSame('', $span->getAriaDescribedBy());
        $this->assertSame('', $span->getAriaFlowTo());
        $this->assertSame('', $span->getAriaLabel());
        $this->assertSame('', $span->getAriaLabelledBy());
        $this->assertSame('', $span->getAriaLevel());
        $this->assertSame(false, $span->getAriaLive());
        $this->assertSame(false, $span->getAriaOrientation());
        $this->assertSame('', $span->getAriaOwns());
        $this->assertSame('', $span->getId());
        $this->assertSame('', $span->getClass());
        $this->assertSame('', $span->getLang());
        $this->assertSame('', $span->getLabel());
        $this->assertFalse($span->hasId());
        $this->assertFalse($span->hasClass());
        $this->assertFalse($span->hasLang());
        $this->assertFalse($span->hasLabel());
        $this->assertFalse($span->hasAriaControls());
        $this->assertFalse($span->hasAriaDescribedBy());
        $this->assertFalse($span->hasAriaFlowTo());
        $this->assertFalse($span->hasAriaLabel());
        $this->assertFalse($span->hasAriaLabelledBy());
        $this->assertFalse($span->hasAriaLive());
        $this->assertFalse($span->hasAriaOrientation());
        $this->assertFalse($span->hasAriaOwns());
    }

    public function testSetId()
    {
        $span = new Span();

        $this->setExpectedException(
            '\\InvalidArgumentException',
            "The 'id' argument of a body element must be a valid identifier or an empty string"
        );

        $span->setId(999);
    }

    public function testClassWrongType()
    {
        $span = new Span();

        $this->setExpectedException(
            '\\InvalidArgumentException',
            "The 'class' argument must be a valid class name, '999' given"
        );

        $span->setClass(999);
    }

    public function testSetLabelWrongType()
    {
        $span = new Span();

        $this->setExpectedException(
            '\\InvalidArgumentException',
            "The 'label' argument must be a string that does not exceed 256 characters."
        );

        $span->setLabel(999);
    }

    public function testSetDirectionWrongLabel()
    {
        $span = new Span();

        $this->setExpectedException(
            '\\InvalidArgumentException',
            "The 'dir' argument must be a value from the Direction enumeration."
        );

        $span->setDir(true);
    }

    /**
     * @param $value
     * @dataProvider validAriaControlsAttributesProvider
     */
    public function testValidAriaControlsAttributes($value)
    {
        $span = new Span();
        $span->setAriaControls($value);

        $this->assertEquals($value, $span->getAriaControls());
    }

    public function validAriaControlsAttributesProvider()
    {
        return [
            [''],
            ['_IDREF'],
            ['IDREF1 IDREF2']
        ];
    }

    /**
     * @param $value
     * @dataProvider validAriaDescribedByAttributesProvider
     */
    public function testValidAriaDescribedByAttributes($value)
    {
        $span = new Span();
        $span->setAriaDescribedBy($value);

        $this->assertEquals($value, $span->getAriaDescribedBy());
    }

    public function validAriaDescribedByAttributesProvider()
    {
        return [
            [''],
            ['_IDREF'],
            ['IDREF1 IDREF2']
        ];
    }

    /**
     * @param $value
     * @dataProvider validAriaFlowToAttributesProvider
     */
    public function testValidAriaFlowToAttributes($value)
    {
        $span = new Span();
        $span->setAriaFlowTo($value);

        $this->assertEquals($value, $span->getAriaFlowTo());
    }

    public function validAriaFlowToAttributesProvider()
    {
        return [
            [''],
            ['_IDREF'],
            ['IDREF1 IDREF2']
        ];
    }

    /**
     * @param $value
     * @dataProvider validAriaLabelledByAttributesProvider
     */
    public function testValidAriaLabelledByAttributes($value)
    {
        $span = new Span();
        $span->setAriaLabelledBy($value);

        $this->assertEquals($value, $span->getAriaLabelledBy());
    }

    public function validAriaLabelledByAttributesProvider()
    {
        return [
            [''],
            ['_IDREF'],
            ['IDREF1 IDREF2']
        ];
    }

    /**
     * @param $value
     * @dataProvider validAriaLabelledByAttributesProvider
     */
    public function testValidAriaOwnsAttributes($value)
    {
        $span = new Span();
        $span->setAriaOwns($value);

        $this->assertEquals($value, $span->getAriaOwns());
    }

    public function validAriaOwnsAttributesProvider()
    {
        return [
            [''],
            ['_IDREF'],
            ['IDREF1 IDREF2']
        ];
    }

    /**
     * @param $value
     * @dataProvider validAriaLevelAttributesProvider
     */
    public function testValidAriaLevelAttributes($value)
    {
        $span = new Span();
        $span->setAriaLevel($value);

        $this->assertEquals(strval($value), $span->getAriaLevel());
    }

    public function validAriaLevelAttributesProvider()
    {
        return [
            [''],
            ['1'],
            [1],
            ['20'],
            [20]
        ];
    }

    /**
     * @param $value
     * @dataProvider validAriaLiveAttributesProvider
     */
    public function testValidAriaLiveAttributes($value)
    {
        $span = new Span();
        $span->setAriaLive($value);

        $this->assertEquals($value, $span->getAriaLive());
    }

    public function validAriaLiveAttributesProvider()
    {
        return [
            [AriaLive::OFF],
            [AriaLive::POLITE],
            [AriaLive::ASSERTIVE]
        ];
    }

    /**
     * @param $value
     * @dataProvider validAriaOrientationAttributesProvider
     */
    public function testValidAriaOrientationAttributes($value)
    {
        $span = new Span();
        $span->setAriaOrientation($value);

        $this->assertEquals($value, $span->getAriaOrientation());
    }

    public function validAriaOrientationAttributesProvider()
    {
        return [
            [AriaOrientation::HORIZONTAL],
            [AriaOrientation::VERTICAL]
        ];
    }

    /**
     * @param $value
     * @dataProvider validAriaLabelAttributesProvider
     */
    public function testValidAriaLabelAttributes($value)
    {
        $span = new Span();
        $span->setAriaLabel($value);

        $this->assertEquals($value, $span->getAriaLabel());
    }

    public function validAriaLabelAttributesProvider()
    {
        return [
            [''],
            ['A normalized string!']
        ];
    }

    /**
     * @param mixed $value
     * @param string $msg
     * @dataProvider invalidAriaControlsAttributesProvider
     */
    public function testInvalidAriaControlsAttributes($value, $msg = null)
    {
        $msg = $msg ?? "'${value}' is not a valid value for attribute 'aria-controls'.";

        $this->setExpectedException(
            InvalidArgumentException::class,
            $msg
        );

        $span = new Span();
        $span->setAriaControls($value);
    }

    public function invalidAriaControlsAttributesProvider()
    {
        return [
            ['999999'],
            ['ABCD 999999'],
            [false],
            [10],
            [25.55],
            [null],
            [new stdClass(), "'instance of stdClass' is not a valid value for attribute 'aria-controls'."]
        ];
    }

    /**
     * @param mixed $value
     * @param string $msg
     * @dataProvider invalidAriaDescribedByAttributesProvider
     */
    public function testInvalidAriaDescribedByAttributes($value, $msg = null)
    {
        $msg = $msg ?? "'${value}' is not a valid value for attribute 'aria-describedby'.";

        $this->setExpectedException(
            InvalidArgumentException::class,
            $msg
        );

        $span = new Span();
        $span->setAriaDescribedBy($value);
    }

    public function invalidAriaDescribedByAttributesProvider()
    {
        return [
            ['999999'],
            ['ABCD 999999'],
            [false],
            [10],
            [25.55],
            [null],
            [new stdClass(), "'instance of stdClass' is not a valid value for attribute 'aria-describedby'."]
        ];
    }

    /**
     * @param mixed $value
     * @param string $msg
     * @dataProvider invalidAriaFlowToAttributesProvider
     */
    public function testInvalidAriaFlowToAttributes($value, $msg = null)
    {
        $msg = $msg ?? "'${value}' is not a valid value for attribute 'aria-flowto'.";

        $this->setExpectedException(
            InvalidArgumentException::class,
            $msg
        );

        $span = new Span();
        $span->setAriaFlowTo($value);
    }

    public function invalidAriaFlowToAttributesProvider()
    {
        return [
            ['999999'],
            ['ABCD 999999'],
            [false],
            [10],
            [25.55],
            [null],
            [new stdClass(), "'instance of stdClass' is not a valid value for attribute 'aria-flowto'."]
        ];
    }

    /**
     * @param mixed $value
     * @param string $msg
     * @dataProvider invalidAriaLabelledByAttributesProvider
     */
    public function testInvalidAriaLabelledByAttributes($value, $msg = null)
    {
        $msg = $msg ?? "'${value}' is not a valid value for attribute 'aria-labelledby'.";

        $this->setExpectedException(
            InvalidArgumentException::class,
            $msg
        );

        $span = new Span();
        $span->setAriaLabelledBy($value);
    }

    public function invalidAriaLabelledByAttributesProvider()
    {
        return [
            ['999999'],
            ['ABCD 999999'],
            [false],
            [10],
            [25.55],
            [null],
            [new stdClass(), "'instance of stdClass' is not a valid value for attribute 'aria-labelledby'."]
        ];
    }

    /**
     * @param mixed $value
     * @param string $msg
     * @dataProvider invalidAriaOwnsAttributesProvider
     */
    public function testInvalidAriaOwnsAttributes($value, $msg = null)
    {
        $msg = $msg ?? "'${value}' is not a valid value for attribute 'aria-owns'.";

        $this->setExpectedException(
            InvalidArgumentException::class,
            $msg
        );

        $span = new Span();
        $span->setAriaOwns($value);
    }

    public function invalidAriaOwnsAttributesProvider()
    {
        return [
            ['999999'],
            ['ABCD 999999'],
            [false],
            [10],
            [25.55],
            [null],
            [new stdClass(), "'instance of stdClass' is not a valid value for attribute 'aria-owns'."]
        ];
    }

    /**
     * @param mixed $value
     * @param string $msg
     * @dataProvider invalidAriaLevelAttributesProvider
     */
    public function testInvalidAriaLevelAttributes($value, $msg = null)
    {
        $msg = $msg ?? "'${value}' is not a valid value for attribute 'aria-level'.";

        $this->setExpectedException(
            InvalidArgumentException::class,
            $msg
        );

        $span = new Span();
        $span->setAriaLevel($value);
    }

    public function invalidAriaLevelAttributesProvider()
    {
        return [
            ['ABCD 999999'],
            [false],
            [null],
            [new stdClass(), "'instance of stdClass' is not a valid value for attribute 'aria-level'."]
        ];
    }

    /**
     * @param mixed $value
     * @param string $msg
     * @dataProvider invalidAriaLiveAttributesProvider
     */
    public function testInvalidAriaLiveAttributes($value, $msg = null)
    {
        $msg = $msg ?? "'${value}' is not a valid value for attribute 'aria-live'.";

        $this->setExpectedException(
            InvalidArgumentException::class,
            $msg
        );

        $span = new Span();
        $span->setAriaLive($value);
    }

    public function invalidAriaLiveAttributesProvider()
    {
        return [
            ['ABCD 999999'],
            [''],
            [null],
            [new stdClass(), "'instance of stdClass' is not a valid value for attribute 'aria-live'."]
        ];
    }

    /**
     * @param mixed $value
     * @param string $msg
     * @dataProvider invalidAriaOrientationAttributesProvider
     */
    public function testInvalidAriaOrientationAttributes($value, $msg = null)
    {
        $msg = $msg ?? "'${value}' is not a valid value for attribute 'aria-orientation'.";

        $this->setExpectedException(
            InvalidArgumentException::class,
            $msg
        );

        $span = new Span();
        $span->setAriaOrientation($value);
    }

    public function invalidAriaOrientationAttributesProvider()
    {
        return [
            ['ABCD 999999'],
            [''],
            [null],
            [new stdClass(), "'instance of stdClass' is not a valid value for attribute 'aria-orientation'."]
        ];
    }

    /**
     * @param mixed $value
     * @param string $msg
     * @dataProvider invalidAriaLabelAttributesProvider
     */
    public function testInvalidAriaLabelAttributes($value, $msg = null)
    {
        $msg = $msg ?? "'${value}' is not a valid value for attribute 'aria-label'.";

        $this->setExpectedException(
            InvalidArgumentException::class,
            $msg
        );

        $span = new Span();
        $span->setAriaLabel($value);
    }

    public function invalidAriaLabelAttributesProvider()
    {
        return [
            [false],
            [10],
            [-5],
            [55.55],
            [null],
            [new stdClass(), "'instance of stdClass' is not a valid value for attribute 'aria-label'."]
        ];
    }
}
