<?php

namespace qtismtest\data\content\xhtml\html5;

use InvalidArgumentException;
use qtism\data\content\xhtml\html5\CrossOrigin;
use qtism\data\content\xhtml\html5\Media;
use qtismtest\QtiSmTestCase;

class MediaTest extends QtiSmTestCase
{
    public function testCreateWithNoValues()
    {
        $subject = new FakeMedia();

        $this->assertFalse($subject->getAutoPlay());
        $this->assertFalse($subject->getControls());
        $this->assertEmpty($subject->getCrossOrigin());
        $this->assertFalse($subject->getLoop());
        $this->assertFalse($subject->getMuted());
        $this->assertEmpty($subject->getSrc());
    }

    public function testSetters()
    {
        $autoplay = true;
        $controls = true;
        $crossOrigin = CrossOrigin::USE_CREDENTIALS;
        $loop = true;
        $muted = true;
        $src = 'http://example.com/';
        
        $subject = new FakeMedia();
        $subject->setAutoPlay($autoplay);
        $subject->setControls($controls);
        $subject->setCrossOrigin($crossOrigin);
        $subject->setLoop($loop);
        $subject->setMuted($muted);
        $subject->setSrc($src);

        $this->assertEquals($autoplay, $subject->getAutoPlay());
        $this->assertEquals($controls, $subject->getControls());
        $this->assertEquals($crossOrigin, $subject->getCrossOrigin());
        $this->assertEquals($loop, $subject->getLoop());
        $this->assertEquals($muted, $subject->getMuted());
        $this->assertEquals($src, $subject->getSrc());
    }

    public function testSetWithNonBooleanAutoPlay()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The "autoplay" argument must be a boolean, "string" given.');

        (new FakeMedia())->setAutoPlay('foo');
    }

    public function testSetWithNonBooleanControls()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The "controls" argument must be a boolean, "string" given.');

        (new FakeMedia())->setControls('foo');
    }

    /**
     * @dataProvider crossOriginsToTest
     * @param int $role
     */
    public function testSetWithValidCrossOrigin(int $role)
    {
        $subject = new FakeMedia();

        $subject->setCrossOrigin($role);
        $this->assertEquals($role, $subject->getCrossOrigin());
    }

    public function crossOriginsToTest(): array
    {
        return [
            [CrossOrigin::ANONYMOUS],
            [CrossOrigin::USE_CREDENTIALS],
        ];
    }

    public function testSetWithNonIntegerCrossOrigin()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The "crossorigin" argument must be a value from the CrossOrigin enumeration, "string" given.');

        (new FakeMedia())->setCrossOrigin('foo');
    }

    public function testSetWithInvalidCrossOrigin()
    {
        $wrongCrossOrigin = 1012;

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The "crossorigin" argument must be a value from the CrossOrigin enumeration, "' . $wrongCrossOrigin . '" given.');

        (new FakeMedia())->setCrossOrigin(1012);
    }

    public function testSetWithNonBooleanLoop()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The "loop" argument must be a boolean, "string" given.');

        (new FakeMedia())->setLoop('foo');
    }

    public function testSetWithNonBooleanMuted()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The "muted" argument must be a boolean, "string" given.');

        (new FakeMedia())->setMuted('foo');
    }

    public function testCreateWithNonStringSrc()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The "src" argument must be a valid URI, "integer" given.');

        (new FakeMedia())->setSrc(12);
    }

    public function testCreateWithNonUriSrc()
    {
        $wrongSrc = '';

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The "src" argument must be a valid URI, "' . $wrongSrc . '" given.');

        (new FakeMedia())->setSrc($wrongSrc);
    }
}

class FakeMedia extends Media
{
    public function getQtiClassName()
    {
    }

    public function getComponents()
    {
    }
}
