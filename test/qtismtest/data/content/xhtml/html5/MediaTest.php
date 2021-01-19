<?php

namespace qtismtest\data\content\xhtml\html5;

use InvalidArgumentException;
use qtism\data\content\enums\CrossOrigin;
use qtism\data\content\xhtml\html5\Media;
use qtism\data\content\enums\Preload;
use qtism\data\content\xhtml\html5\Source;
use qtism\data\content\xhtml\html5\Track;
use qtism\data\QtiComponentCollection;
use qtismtest\QtiSmTestCase;

class MediaTest extends QtiSmTestCase
{
    public function testConstructor(): void
    {
        $subject = new FakeMedia();

        self::assertEquals(new QtiComponentCollection(), $subject->getComponents());
    }

    public function testCreateWithNoValues(): void
    {
        $subject = new FakeMedia();

        self::assertFalse($subject->hasAutoPlay());
        self::assertFalse($subject->getAutoPlay());
        self::assertFalse($subject->hasControls());
        self::assertFalse($subject->getControls());
        self::assertFalse($subject->hasCrossOrigin());
        self::assertEmpty($subject->getCrossOrigin());
        self::assertFalse($subject->hasLoop());
        self::assertFalse($subject->getLoop());
        self::assertFalse($subject->hasMuted());
        self::assertFalse($subject->getMuted());
        self::assertFalse($subject->hasSrc());
        self::assertEmpty($subject->getSrc());
    }

    public function testAddSource(): void
    {
        $src = 'http://example.com/';
        $source = new Source($src);

        $subject = new FakeMedia();

        $components = $subject->getComponents();
        self::assertCount(0, $components);

        $subject->addSource($source);

        $components = $subject->getComponents();
        self::assertCount(1, $components);
        $component = $components[0];
        self::assertInstanceOf(Source::class, $component);
        self::assertEquals($src, $component->getSrc());
    }

    public function testAddTrack(): void
    {
        $src = 'http://example.com/';
        $track = new Track($src);

        $subject = new FakeMedia();

        $components = $subject->getComponents();
        self::assertCount(0, $components);

        $subject->addTrack($track);

        $components = $subject->getComponents();
        self::assertCount(1, $components);
        $component = $components[0];
        self::assertInstanceOf(Track::class, $component);
        self::assertEquals($src, $component->getSrc());
    }

    public function testSetters(): void
    {
        $autoplay = true;
        $controls = true;
        $crossOrigin = CrossOrigin::getConstantByName('use-credentials');
        $loop = true;
        $mediaGroup = 'any normalized string';
        $muted = true;
        $src = 'http://example.com/';
        
        $subject = new FakeMedia();
        $subject->setAutoPlay($autoplay);
        $subject->setControls($controls);
        $subject->setCrossOrigin($crossOrigin);
        $subject->setLoop($loop);
        $subject->setMediaGroup($mediaGroup);
        $subject->setMuted($muted);
        $subject->setSrc($src);

        self::assertTrue($subject->hasAutoPlay());
        self::assertEquals($autoplay, $subject->getAutoPlay());
        self::assertTrue($subject->hasControls());
        self::assertEquals($controls, $subject->getControls());
        self::assertTrue($subject->hasCrossOrigin());
        self::assertEquals($crossOrigin, $subject->getCrossOrigin());
        self::assertTrue($subject->hasLoop());
        self::assertEquals($loop, $subject->getLoop());
        self::assertTrue($subject->hasMediaGroup());
        self::assertEquals($mediaGroup, $subject->getMediaGroup());
        self::assertTrue($subject->hasMuted());
        self::assertEquals($muted, $subject->getMuted());
        self::assertTrue($subject->hasSrc());
        self::assertEquals($src, $subject->getSrc());
    }

    public function testSetWithNonBooleanAutoPlay(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The "autoplay" argument must be a boolean, "foo" given.');

        (new FakeMedia())->setAutoPlay('foo');
    }

    public function testSetWithNonBooleanControls(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The "controls" argument must be a boolean, "foo" given.');

        (new FakeMedia())->setControls('foo');
    }

    /**
     * @dataProvider crossOriginsToTest
     * @param int $role
     */
    public function testSetWithValidCrossOrigin(int $role): void
    {
        $subject = new FakeMedia();

        $subject->setCrossOrigin($role);
        self::assertEquals($role, $subject->getCrossOrigin());
    }

    public function crossOriginsToTest(): array
    {
        return [
            [CrossOrigin::getConstantByName('anonymous')],
            [CrossOrigin::getConstantByName('use-credentials')],
        ];
    }

    public function testSetWithNonIntegerCrossOrigin(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The "crossorigin" argument must be a value from the CrossOrigin enumeration, "foo" given.');

        (new FakeMedia())->setCrossOrigin('foo');
    }

    public function testSetWithInvalidCrossOrigin(): void
    {
        $wrongCrossOrigin = 1012;

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The "crossorigin" argument must be a value from the CrossOrigin enumeration, "' . $wrongCrossOrigin . '" given.');

        (new FakeMedia())->setCrossOrigin(1012);
    }

    public function testSetWithNonBooleanLoop(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The "loop" argument must be a boolean, "foo" given.');

        (new FakeMedia())->setLoop('foo');
    }

    public function testSetWithNonBooleanMuted(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The "muted" argument must be a boolean, "foo" given.');

        (new FakeMedia())->setMuted('foo');
    }

    public function testCreateWithNonUriSrc(): void
    {
        $wrongSrc = '';

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The "src" argument must be null or a valid URI, "' . $wrongSrc . '" given.');

        (new FakeMedia())->setSrc($wrongSrc);
    }

    public function testGetPreload(): void
    {
        $subject = new FakeMedia();

        self::assertEquals(Preload::getDefault(), $subject->getPreload());
    }

    public function testGetDefaultPreload(): void
    {
        $subject = new FakeMedia();

        self::assertFalse($subject->hasPreload());
        self::assertEquals(Preload::getConstantByName('metadata'), $subject->getPreload());
    }

    public function testSetPreload(): void
    {
        $preload = Preload::getConstantByName('auto');
        $subject = new FakeMedia();
        $subject->setPreload($preload);

        self::assertTrue($subject->hasPreload());
        self::assertEquals($preload, $subject->getPreload());
    }

    public function testSetPreloadWithNonIntegerValue(): void
    {
        $wrongPreload = 'foo';
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The "preload" argument must be a value from the Preload enumeration, "' . $wrongPreload . '" given.');

        (new FakeMedia())->setPreload($wrongPreload);
    }

    public function testSetPreloadWithInvalidPreload(): void
    {
        $wrongPreload = 1012;
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The "preload" argument must be a value from the Preload enumeration, "' . $wrongPreload . '" given.');

        (new FakeMedia())->setPreload($wrongPreload);
    }
}

class FakeMedia extends Media
{
    public function getQtiClassName(): string
    {
        return '';
    }
}
