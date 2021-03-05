<?php

namespace qtismtest\data\content\xhtml\html5;

use InvalidArgumentException;
use qtism\data\content\xhtml\html5\Video;
use qtismtest\QtiSmTestCase;

class VideoTest extends QtiSmTestCase
{
    public function testCreateWithValues(): void
    {
        $poster = 'http://example.com/poster.jpg';
        $height = 10;
        $width = 12;

        $subject = new Video($poster, $height, $width);

        self::assertEquals($poster, $subject->getPoster());
        self::assertEquals($height, $subject->getHeight());
        self::assertEquals($width, $subject->getWidth());
    }

    public function testCreateWithDefaultValues(): void
    {
        $subject = new Video();

        self::assertEquals('', $subject->getPoster());
        self::assertEquals(0, $subject->getHeight());
        self::assertEquals(0, $subject->getWidth());
    }

    public function testSetPoster(): void
    {
        $poster = 'http://example.com/poster.jpg';
        $subject = new Video();

        self::assertFalse($subject->hasPoster());
        self::assertEquals('', $subject->getPoster());

        $subject->setPoster($poster);
        self::assertTrue($subject->hasPoster());
        self::assertEquals($poster, $subject->getPoster());
    }

    public function testSetPosterWithNonString(): void
    {
        $wrongPoster = [];

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The "poster" argument must be null or a valid URI, "' . gettype($wrongPoster) . '" given.');

        (new Video())->setPoster($wrongPoster);
    }

    public function testSetPosterWithNonUri(): void
    {
        $wrongPoster = '';

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The "poster" argument must be null or a valid URI, "' . $wrongPoster . '" given.');

        (new Video())->setPoster($wrongPoster);
    }

    public function testSetHeight(): void
    {
        $height = 1012;
        $subject = new Video();

        self::assertFalse($subject->hasHeight());
        self::assertEquals(0, $subject->getHeight());

        $subject->setHeight($height);
        self::assertTrue($subject->hasHeight());
        self::assertEquals($height, $subject->getHeight());
    }

    public function testSetHeightWithNonInteger(): void
    {
        $wrongHeight = 'foo';

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The "height" argument must be 0 or a positive integer, "' . gettype($wrongHeight) . '" given.');

        (new Video())->setHeight($wrongHeight);
    }

    public function testSetHeightWithNegativeInteger(): void
    {
        $wrongHeight = -12;

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The "height" argument must be 0 or a positive integer, "' . $wrongHeight . '" given.');

        (new Video())->setHeight($wrongHeight);
    }

    public function testSetWidth(): void
    {
        $width = 1012;
        $subject = new Video();

        self::assertFalse($subject->hasWidth());
        self::assertEquals(0, $subject->getWidth());

        $subject->setWidth($width);
        self::assertTrue($subject->hasWidth());
        self::assertEquals($width, $subject->getWidth());
    }

    public function testSetWidthWithNonInteger(): void
    {
        $wrongWidth = 'foo';

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The "width" argument must be 0 or a positive integer, "' . gettype($wrongWidth) . '" given.');

        (new Video())->setWidth($wrongWidth);
    }

    public function testSetWidthWithNegativeInteger(): void
    {
        $wrongWidth = -12;

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The "width" argument must be 0 or a positive integer, "' . $wrongWidth . '" given.');

        (new Video())->setWidth($wrongWidth);
    }

    public function testGetQtiClassName(): void
    {
        $subject = new Video();

        self::assertEquals('video', $subject->getQtiClassName());
    }
}
