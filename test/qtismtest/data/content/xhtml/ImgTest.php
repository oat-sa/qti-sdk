<?php

namespace qtismtest\data\content\xhtml;

use InvalidArgumentException;
use qtism\data\content\xhtml\Img;
use qtismtest\QtiSmTestCase;

/**
 * Class ImgTest
 */
class ImgTest extends QtiSmTestCase
{
    public function testCreateInvalidAlt()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("The 'alt' argument must be a string, 'integer' given.");

        new Img('999.png', 999);
    }

    public function testSetHeightWrongFormat()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("The 'height' argument must be a valid XHTML length value, '999xp' given.");

        $img = new Img('999.png', '999');
        $img->setHeight('999xp');
    }

    public function testSetWidthWrongFormat()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("The 'width' argument must be a valid XHTML length value, '999xp' given.");

        $img = new Img('999.png', '999');
        $img->setWidth('999xp');
    }
}
