<?php
namespace qtismtest\data\content\xhtml;

use qtismtest\QtiSmTestCase;
use qtism\data\content\xhtml\Img;

class ImgTest extends QtiSmTestCase
{
    public function testCreateInvalidSrc()
    {
        $this->setExpectedException(
            '\\InvalidArgumentException',
            "The 'src' argument must be a valid URI, '999' given."
        );
        
        new Img(999, '999');
    }
    
    public function testCreateInvalidAlt()
    {
        $this->setExpectedException(
            '\\InvalidArgumentException',
            "The 'alt' argument must be a string, 'integer' given."
        );
        
        new Img('999.png', 999);
    }
    
    public function testSetLongdescWrongType()
    {
        $this->setExpectedException(
            '\\InvalidArgumentException',
            "The 'longdesc' argument must be a valid URI, '999' given."
        );
        
        $img = new Img('999.png', '999');
        $img->setLongdesc(999);
    }
    
    public function testSetHeightWrongFormat()
    {
        $this->setExpectedException(
            '\\InvalidArgumentException',
            "The 'height' argument must be a valid XHTML length value, '999xp' given."
        );
        
        $img = new Img('999.png', '999');
        $img->setHeight('999xp');
    }
    
    public function testSetWidthWrongFormat()
    {
        $this->setExpectedException(
            '\\InvalidArgumentException',
            "The 'width' argument must be a valid XHTML length value, '999xp' given."
        );
        
        $img = new Img('999.png', '999');
        $img->setWidth('999xp');
    }
}
