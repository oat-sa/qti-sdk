<?php

namespace qtismtest\data\storage\php\marshalling;

use InvalidArgumentException;
use qtism\data\storage\php\marshalling\PhpMarshallingException;
use qtism\data\storage\php\marshalling\PhpScalarMarshaller;
use qtismtest\QtiSmPhpMarshallerTestCase;
use stdClass;

/**
 * Class PhpScalarMarshallerTest
 *
 * @package qtismtest\data\storage\php\marshalling
 */
class PhpScalarMarshallerTest extends QtiSmPhpMarshallerTestCase
{
    /**
     *
     * @dataProvider marshallDataProvider
     * @param string $expectedInStream
     * @param mixed $scalar
     * @throws PhpMarshallingException
     */
    public function testMarshall($expectedInStream, $scalar)
    {
        $ctx = $this->createMarshallingContext();
        $marshaller = new PhpScalarMarshaller($ctx, $scalar);
        $marshaller->marshall();

        $this->assertEquals($expectedInStream, $this->getStream()->getBinary());
    }

    public function testMarshallWrongDataType()
    {
        $this->expectException(InvalidArgumentException::class);
        $ctx = $this->createMarshallingContext();
        $marshaller = new PhpScalarMarshaller($ctx, new stdClass());
    }

    /**
     * @return array
     */
    public function marshallDataProvider()
    {
        return [
            ["\$scalarnullvalue_0 = null;\n", null],
            ["\$integer_0 = 10;\n", 10],
            ["\$double_0 = 10.44;\n", 10.44],
            ["\$string_0 = \"\";\n", ''],
            ["\$string_0 = \"Hello!\";\n", 'Hello!'],
            ["\$boolean_0 = true;\n", true],
            ["\$boolean_0 = false;\n", false],
            ["\$string_0 = \"Hello \\n there!\";\n", "Hello \n there!"],
            ["\$string_0 = \"Hello \\\\n there!\";\n", "Hello \\n there!"],
            ["\$string_0 = \"Hello \\\\ there!\";\n", "Hello \\ there!"],
        ];
    }
}
