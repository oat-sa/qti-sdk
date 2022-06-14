<?php

namespace qtismtest\runtime\expressions\operators;

use qtism\common\datatypes\QtiFloat;
use qtism\common\datatypes\QtiInteger;
use qtism\common\datatypes\QtiString;
use qtism\common\enums\BaseType;
use qtism\data\QtiComponent;
use qtism\data\storage\xml\marshalling\MarshallerNotFoundException;
use qtism\runtime\common\OrderedContainer;
use qtism\runtime\common\RecordContainer;
use qtism\runtime\expressions\operators\OperandsCollection;
use qtism\runtime\expressions\operators\OperatorProcessingException;
use qtism\runtime\expressions\operators\PatternMatchProcessor;
use qtismtest\QtiSmTestCase;

/**
 * Class PatternMatchProcessorTest
 */
class PatternMatchProcessorTest extends QtiSmTestCase
{
    /**
     * @dataProvider patternMatchProvider
     *
     * @param string $string
     * @param string $pattern
     * @param bool $expected
     * @throws MarshallerNotFoundException
     */
    public function testPatternMatch($string, $pattern, $expected)
    {
        $expression = $this->createFakeExpression($pattern);
        $operands = new OperandsCollection([$string]);
        $processor = new PatternMatchProcessor($expression, $operands);
        $this::assertSame($expected, $processor->process()->getValue());
    }

    /**
     * @dataProvider nullProvider
     *
     * @param string $string
     * @param string $pattern
     * @throws MarshallerNotFoundException
     */
    public function testNull($string, $pattern)
    {
        $expression = $this->createFakeExpression($pattern);
        $operands = new OperandsCollection([$string]);
        $processor = new PatternMatchProcessor($expression, $operands);
        $this::assertNull($processor->process());
    }

    public function testNotEnougOperands()
    {
        $expression = $this->createFakeExpression('abc');
        $operands = new OperandsCollection();
        $this->expectException(OperatorProcessingException::class);
        $processor = new PatternMatchProcessor($expression, $operands);
    }

    public function testTooMuchOperands()
    {
        $expression = $this->createFakeExpression('abc');
        $operands = new OperandsCollection([new QtiString('string'), new QtiString('string')]);
        $this->expectException(OperatorProcessingException::class);
        $processor = new PatternMatchProcessor($expression, $operands);
    }

    public function testWrongCardinality()
    {
        $expression = $this->createFakeExpression('abc');
        $operands = new OperandsCollection([new RecordContainer(['A' => new QtiInteger(1)])]);
        $processor = new PatternMatchProcessor($expression, $operands);
        $this->expectException(OperatorProcessingException::class);
        $result = $processor->process();
    }

    public function testWrongBaseType()
    {
        $expression = $this->createFakeExpression('abc');
        $operands = new OperandsCollection([new QtiFloat(255.34)]);
        $processor = new PatternMatchProcessor($expression, $operands);
        $this->expectException(OperatorProcessingException::class);
        $result = $processor->process();
    }

    public function testInternalError()
    {
        $expression = $this->createFakeExpression('[');
        $operands = new OperandsCollection([new QtiString('string!')]);
        $processor = new PatternMatchProcessor($expression, $operands);
        try {
            $result = $processor->process();
            $this::assertFalse(true);
        } catch (OperatorProcessingException $e) {
            $this::assertTrue(true);
            $this::assertEquals(OperatorProcessingException::RUNTIME_ERROR, $e->getCode());
        }
    }

    /**
     * @return array
     */
    public function patternMatchProvider()
    {
        return [
            [new QtiString('string'), 'string', true],
            [new QtiString('string'), 'stRing', false],
            [new QtiString('string'), 'shell', false],
            [new QtiString('stringString'), '.*', true], // in xml schema 2, dot matches white-spaces
            [new QtiString('^String$'), 'String', false], // No carret nor dollar in xml schema 2
            // no aplicable because commit [ db4d9a49 ]: / fix: update removing caret and dollar, update tests /
//            [new QtiString('^String$'), '^String$', true],
            [new QtiString('^String'), '[^String]*', false],
            [new QtiString('aaa'), '[^String]*', true],
            [new QtiString('Str/ing'), 'Str/ing', true],
            [new QtiString('Str^ing'), 'Str^ing', true],
            [new QtiString('99'), '\d{1,2}', true],
            [new QtiString('abc'), '\d{1,2}', false],
        ];
    }

    /**
     * @return array
     */
    public function nullProvider()
    {
        return [
            [null, '\d{1,2}'],
            [new QtiString(''), '\d{1,2}'],
            [new OrderedContainer(BaseType::STRING), '\d{1,2}'],
        ];
    }

    /**
     * @param $pattern
     * @return QtiComponent
     * @throws MarshallerNotFoundException
     */
    public function createFakeExpression($pattern)
    {
        return $this->createComponentFromXml('
			<patternMatch pattern="' . $pattern . '">
				<baseValue baseType="string">String!</baseValue>
			</patternMatch>
		');
    }
}
