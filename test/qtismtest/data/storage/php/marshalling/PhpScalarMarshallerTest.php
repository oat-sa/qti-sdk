<?php

namespace qtismtest\data\storage\php\marshalling;

use qtism\data\storage\php\marshalling\PhpScalarMarshaller;
use qtismtest\QtiSmPhpMarshallerTestCase;
use stdClass;

class PhpScalarMarshallerTest extends QtiSmPhpMarshallerTestCase
{
    /**
     *
     * @dataProvider marshallDataProvider
     * @param string $expectedInStream
     * @param mixed $scalar
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
        $this->setExpectedException('\\InvalidArgumentException');
        $ctx = $this->createMarshallingContext();
        $marshaller = new PhpScalarMarshaller($ctx, new stdClass());
    }

    public function marshallDataProvider()
    {
        return [
            ["\$nullvalue_0 = null;\n", null],
            ["\$integer_0 = 10;\n", 10],
            ["\$double_0 = 10.44;\n", 10.44],
            ["\$string_0 = \"\";\n", ''],
            ["\$string_0 = \"Hello!\";\n", "Hello!"],
            ["\$boolean_0 = true;\n", true],
            ["\$boolean_0 = false;\n", false],
            ["\$string_0 = \"Hello \\n there!\";\n", "Hello \n there!"],
            ["\$string_0 = \"Hello \\\\n there!\";\n", "Hello \\n there!"],
            ["\$string_0 = \"Hello \\\\ there!\";\n", "Hello \\ there!"],
        ];
    }
}
