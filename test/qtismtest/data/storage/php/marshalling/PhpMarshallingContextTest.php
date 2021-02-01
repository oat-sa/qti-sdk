<?php

namespace qtismtest\data\storage\php\marshalling;

use InvalidArgumentException;
use qtism\common\datatypes\QtiCoords;
use qtism\common\datatypes\QtiPoint;
use qtism\common\datatypes\QtiShape;
use qtism\common\storage\MemoryStream;
use qtism\data\storage\php\marshalling\PhpMarshallingContext;
use qtism\data\storage\php\PhpStreamAccess;
use qtismtest\QtiSmTestCase;
use RuntimeException;

/**
 * Class PhpMarshallingContextTest
 */
class PhpMarshallingContextTest extends QtiSmTestCase
{
    /**
     * An open access to a PHP source code stream.
     *
     * @param PhpStreamAccess
     */
    private $streamAccess;

    /**
     * @param PhpStreamAccess $streamAccess
     */
    protected function setStreamAccess(PhpStreamAccess $streamAccess)
    {
        $this->streamAccess = $streamAccess;
    }

    /**
     * @return mixed
     */
    protected function getStreamAccess()
    {
        return $this->streamAccess;
    }

    public function setUp(): void
    {
        parent::setUp();

        $stream = new MemoryStream();
        $stream->open();
        $this->setStreamAccess(new PhpStreamAccess($stream));
    }

    public function tearDown(): void
    {
        parent::tearDown();

        $streamAccess = $this->getStreamAccess();
        unset($streamAccess);
    }

    public function testPhpMarshallingContext()
    {
        $ctx = new PhpMarshallingContext($this->getStreamAccess());
        $this::assertFalse($ctx->mustFormatOutput());

        $ctx->setFormatOutput(true);
        $this::assertTrue($ctx->mustFormatOutput());

        $ctx->pushOnVariableStack('foo');
        $this::assertEquals(['foo'], $ctx->popFromVariableStack());

        $ctx->pushOnVariableStack(['foo', 'bar']);
        $this::assertEquals(['foo', 'bar'], $ctx->popFromVariableStack(2));

        $this::assertInstanceOf(PhpStreamAccess::class, $ctx->getStreamAccess());
    }

    public function testPhpMarshallingTooLargeQuantity()
    {
        $ctx = new PhpMarshallingContext($this->getStreamAccess());
        $ctx->pushOnVariableStack(['foo', 'bar', '2000']);

        try {
            $values = $ctx->popFromVariableStack(4);
            $this::assertFalse(true, 'An exception must be thrown because the requested quantity is too large.');
        } catch (RuntimeException $e) {
            $this::assertTrue(true);
        }
    }

    public function testPhpMarshallingEmptyStack()
    {
        $ctx = new PhpMarshallingContext($this->getStreamAccess());

        try {
            $value = $ctx->popFromVariableStack();
            $this::assertFalse(true, 'An exception must be thrown because the variable names stack is empty.');
        } catch (RuntimeException $e) {
            $this::assertTrue(true);
        }
    }

    public function testWrongQuantity()
    {
        $ctx = new PhpMarshallingContext($this->getStreamAccess());
        $ctx->pushOnVariableStack('foo');

        try {
            $value = $ctx->popFromVariableStack(0);
            $this::assertTrue(false, "An exception must be thrown because the 'quantity' argument must be >= 1");
        } catch (InvalidArgumentException $e) {
            $this::assertTrue(true);
        }
    }

    public function testGenerateVariableName()
    {
        $ctx = new PhpMarshallingContext($this->getStreamAccess());

        $this::assertEquals('integer_0', $ctx->generateVariableName(0));
        $this::assertEquals('integer_1', $ctx->generateVariableName(-10));
        $this::assertEquals('scalarnullvalue_0', $ctx->generateVariableName(null));
        $this::assertEquals('scalarnullvalue_1', $ctx->generateVariableName(null));
        $this::assertEquals('scalarnullvalue_2', $ctx->generateVariableName(null));
        $this::assertEquals('boolean_0', $ctx->generateVariableName(true));
        $this::assertEquals('boolean_1', $ctx->generateVariableName(false));
        $this::assertEquals('double_0', $ctx->generateVariableName(20.3));
        $this::assertEquals('double_1', $ctx->generateVariableName(0.0));
        $this::assertEquals('string_0', $ctx->generateVariableName('String!'));
        $this::assertEquals('string_1', $ctx->generateVariableName('String!'));
        $this::assertEquals('integer_2', $ctx->generateVariableName(1337));

        $this::assertEquals('qticoords_0', $ctx->generateVariableName(new QtiCoords(QtiShape::CIRCLE, [10, 10, 5])));
        $this::assertEquals('qticoords_1', $ctx->generateVariableName(new QtiCoords(QtiShape::CIRCLE, [10, 10, 3])));
        $this::assertEquals('qtipoint_0', $ctx->generateVariableName(new QtiPoint(0, 0)));
        $this::assertEquals('qtipoint_1', $ctx->generateVariableName(new QtiPoint(0, 1)));
        $this::assertEquals('qticoords_2', $ctx->generateVariableName(new QtiCoords(QtiShape::CIRCLE, [5, 5, 3])));
    }
}
