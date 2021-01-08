<?php

namespace qtismtest\data\content\xhtml\html5;

use InvalidArgumentException;
use qtism\data\content\xhtml\html5\Video;
use qtism\data\content\enums\Preload;
use qtismtest\QtiSmTestCase;

class VideoTest extends QtiSmTestCase
{
    public function testSetPoster()
    {
        $poster = 'http://example.com/poster.jpg';
        $subject = new Video();

        $this->assertFalse($subject->hasPoster());
        $this->assertEquals('', $subject->getPoster());
        
        $subject->setPoster($poster);
        $this->assertTrue($subject->hasPoster());
        $this->assertEquals($poster, $subject->getPoster());
    }
    
    public function testSetPosterWithNonString()
    {
        $wrongPoster = 12;

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The "poster" argument must be a valid URI, "' . gettype($wrongPoster) . '" given.');

        (new Video())->setPoster($wrongPoster);
    }

    public function testSetPosterWithNonUri()
    {
        $wrongPoster = '';

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The "poster" argument must be a valid URI, "' . $wrongPoster . '" given.');

        (new Video())->setPoster($wrongPoster);
    }

    public function testSetHeight()
    {
        $height = 1012;
        $subject = new Video();

        $this->assertFalse($subject->hasHeight());
        $this->assertEquals(0, $subject->getHeight());
        
        $subject->setHeight($height);
        $this->assertTrue($subject->hasHeight());
        $this->assertEquals($height, $subject->getHeight());
    }
    
    public function testSetHeightWithNonInteger()
    {
        $wrongHeight = 'foo';

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The "height" argument must be 0 or a positive integer, "' . gettype($wrongHeight) . '" given.');

        (new Video())->setHeight($wrongHeight);
    }

    public function testSetHeightWithNegativeInteger()
    {
        $wrongHeight = -12;

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The "height" argument must be 0 or a positive integer, "' . $wrongHeight . '" given.');

        (new Video())->setHeight($wrongHeight);
    }
 
    public function testSetWidth()
    {
        $width = 1012;
        $subject = new Video();

        $this->assertFalse($subject->hasWidth());
        $this->assertEquals(0, $subject->getWidth());
        
        $subject->setWidth($width);
        $this->assertTrue($subject->hasWidth());
        $this->assertEquals($width, $subject->getWidth());
    }
    
    public function testSetWidthWithNonInteger()
    {
        $wrongWidth = 'foo';

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The "width" argument must be 0 or a positive integer, "' . gettype($wrongWidth) . '" given.');

        (new Video())->setWidth($wrongWidth);
    }

    public function testSetWidthWithNegativeInteger()
    {
        $wrongWidth = -12;

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The "width" argument must be 0 or a positive integer, "' . $wrongWidth . '" given.');

        (new Video())->setWidth($wrongWidth);
    }
    
    public function testGetQtiClassName()
    {
        $subject = new Video();

        $this->assertEquals('video', $subject->getQtiClassName());
    }
}
