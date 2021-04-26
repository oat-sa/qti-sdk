<?php

namespace qtismtest\data\content\xhtml;

use InvalidArgumentException;
use qtism\data\content\xhtml\ObjectElement;
use qtismtest\QtiSmTestCase;

/**
 * Class ObjectTest
 */
class ObjectTest extends QtiSmTestCase
{
    public function testCreateWrongData()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("The 'data' argument must be a URI or an empty string, 'integer' given.");

        new ObjectElement(999, 'image/png');
    }

    public function testCreateWrongType()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("The 'type' argument must be a non-empty string, 'integer' given.");

        new ObjectElement('./my-image.png', 999);
    }

    /**
     * @dataProvider widthsToTest
     * @param mixed $width
     * @param string|null $expected
     */
    public function testSetWidth($width, ?string $expected): void
    {
        $object = new ObjectElement('./my-image.png', 'image/png');
        $object->setWidth($width);
        self::assertEquals($expected, $object->getWidth());
    }

    /**
     * @dataProvider widthsToTest
     * @param mixed $height
     * @param string|null $expected
     */
    public function testSetHeight($height, ?string $expected): void
    {
        $object = new ObjectElement('./my-image.png', 'image/png');
        $object->setHeight($height);
        self::assertEquals($expected, $object->getHeight());
    }

    public function widthsToTest(): array
    {
        return [
            ['10%','10%'],
            [10, '10'],
            ['10', '10'],
            [null, null],
            [-1, null],
        ];
    }

    /**
     * @dataProvider wrongLengthsToTest
     * @param mixed $width
     * @param string|null $message
     */
    public function testSetWidthWrongType($width, string $message = null): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The "width" argument must be a positive integer with optional percent sign, "' . $message ?? $width . '" given.');

        $object = new ObjectElement('./my-image.png', 'image/png');
        $object->setWidth($width);
    }

    /**
     * @dataProvider wrongLengthsToTest
     * @param mixed $height
     * @param string|null $message
     */
    public function testSetHeightWrongType($height, string $message = null): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The "height" argument must be a positive integer with optional percent sign, "' . $message ?? $height . '" given.');

        $object = new ObjectElement('./my-image.png', 'image/png');
        $object->setHeight($height);
    }

    public function wrongLengthsToTest(): array
    {
        return [
            [999.999],
            [-10],
            ['10px'],
            [[], 'array'],
            [new class() {
                public function __toString(): string
                {
                    return '10%';
                }
            }, 'object'],
        ];
    }
}
