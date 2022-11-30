<?php

declare(strict_types=1);

namespace qtismtest\data\content\xhtml;

use InvalidArgumentException;
use qtism\data\content\xhtml\Img;
use qtismtest\QtiSmTestCase;

/**
 * Class ImgTest
 */
class ImgTest extends QtiSmTestCase
{
    public function testCreateInvalidAlt(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("The 'alt' argument must be a string, 'integer' given.");

        new Img('999.png', 999);
    }

    /**
     * @dataProvider lengthsToTest
     * @param mixed $width
     * @param string|null $expected
     */
    public function testSetWidth($width, ?string $expected): void
    {
        $object = new Img('999.png', '999');
        $object->setWidth($width);
        self::assertEquals($expected, $object->getWidth());
    }

    /**
     * @dataProvider lengthsToTest
     * @param mixed $height
     * @param string|null $expected
     */
    public function testSetHeight($height, ?string $expected): void
    {
        $object = new Img('999.png', '999');
        $object->setHeight($height);
        self::assertEquals($expected, $object->getHeight());
    }

    public function lengthsToTest(): array
    {
        return [
            ['10%', '10%'],
            [10, '10'],
            ['10', '10'],
            [null, null],
            [-1, null],
        ];
    }

    /**
     * @dataProvider wrongLengthsToTest
     * @param mixed $height
     * @param string|null $message
     */
    public function testSetHeightWrongFormat($height, string $message = null): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The "height" argument must be a positive integer with optional percent sign, "' . $message ?? $height . '" given.');

        $img = new Img('999.png', '999');
        $img->setHeight($height);
    }

    /**
     * @dataProvider wrongLengthsToTest
     * @param mixed $width
     * @param string|null $message
     */
    public function testSetWidthWrongFormat($width, string $message = null): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The "width" argument must be a positive integer with optional percent sign, "' . $message ?? $width . '" given.');

        $img = new Img('999.png', '999');
        $img->setWidth($width);
    }

    public function wrongLengthsToTest(): array
    {
        return [
            [999.999],
            [-10],
            ['10px'],
            [[], 'array'],
            [
                new class() {
                    public function __toString(): string
                    {
                        return '10%';
                    }
                },
                'object',
            ],
        ];
    }
}
