<?php

namespace qtismtest\data\content\xhtml\html5;

use InvalidArgumentException;
use qtism\data\content\xhtml\html5\Audio;
use qtism\data\content\xhtml\html5\CrossOrigin;
use qtism\data\content\xhtml\html5\Media;
use qtism\data\content\xhtml\html5\Source;
use qtism\data\content\xhtml\html5\Track;
use qtism\data\QtiComponentCollection;
use qtismtest\QtiSmTestCase;

class MediaTest extends QtiSmTestCase
{
    public function testConstructor()
    {
        $subject = new FakeMedia();

        $this->assertEquals(new QtiComponentCollection(), $subject->getComponents());
    }

    public function testCreateWithNoValues()
    {
        $subject = new FakeMedia();

        $this->assertFalse($subject->hasAutoPlay());
        $this->assertFalse($subject->getAutoPlay());
        $this->assertFalse($subject->hasControls());
        $this->assertFalse($subject->getControls());
        $this->assertFalse($subject->hasCrossOrigin());
        $this->assertEmpty($subject->getCrossOrigin());
        $this->assertFalse($subject->hasLoop());
        $this->assertFalse($subject->getLoop());
        $this->assertFalse($subject->hasMuted());
        $this->assertFalse($subject->getMuted());
        $this->assertFalse($subject->hasSrc());
        $this->assertEmpty($subject->getSrc());
    }

    public function testAddSource()
    {
        $src = 'http://example.com/';
        $source = new Source($src);

        $subject = new Audio();

        $components = $subject->getComponents();
        $this->assertCount(0, $components);

        $subject->addSource($source);

        $components = $subject->getComponents();
        $this->assertCount(1, $components);
        $component = $components[0];
        $this->assertInstanceOf(Source::class, $component);
        $this->assertEquals($src, $component->getSrc());
    }

    public function testAddTrack()
    {
        $src = 'http://example.com/';
        $track = new Track($src);

        $subject = new Audio();

        $components = $subject->getComponents();
        $this->assertCount(0, $components);

        $subject->addTrack($track);

        $components = $subject->getComponents();
        $this->assertCount(1, $components);
        $component = $components[0];
        $this->assertInstanceOf(Track::class, $component);
        $this->assertEquals($src, $component->getSrc());
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

        $this->assertTrue($subject->hasAutoPlay());
        $this->assertEquals($autoplay, $subject->getAutoPlay());
        $this->assertTrue($subject->hasControls());
        $this->assertEquals($controls, $subject->getControls());
        $this->assertTrue($subject->hasCrossOrigin());
        $this->assertEquals($crossOrigin, $subject->getCrossOrigin());
        $this->assertTrue($subject->hasLoop());
        $this->assertEquals($loop, $subject->getLoop());
        $this->assertTrue($subject->hasMuted());
        $this->assertEquals($muted, $subject->getMuted());
        $this->assertTrue($subject->hasSrc());
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
}
