<?php

namespace qtismtest\data\content\xhtml\html5;

use InvalidArgumentException;
use qtism\data\content\enums\CrossOrigin;
use qtism\data\content\enums\Preload;
use qtism\data\content\xhtml\html5\Html5Media;
use qtism\data\content\xhtml\html5\Source;
use qtism\data\content\xhtml\html5\Track;
use qtism\data\QtiComponentCollection;
use qtismtest\QtiSmTestCase;

class Html5MediaTest extends QtiSmTestCase
{
    public function testConstructor(): void
    {
        $subject = new FakeHtml5Media();

        self::assertEquals(new QtiComponentCollection(), $subject->getComponents());
    }

    public function testCreateWithNoValues(): void
    {
        $subject = new FakeHtml5Media();

        self::assertFalse($subject->hasSrc());
        self::assertSame('', $subject->getSrc());
        self::assertFalse($subject->hasAutoPlay());
        self::assertFalse($subject->getAutoPlay());
        self::assertFalse($subject->hasControls());
        self::assertFalse($subject->getControls());
        self::assertFalse($subject->hasCrossOrigin());
        self::assertSame(CrossOrigin::getDefault(), $subject->getCrossOrigin());
        self::assertFalse($subject->hasLoop());
        self::assertFalse($subject->getLoop());
        self::assertFalse($subject->hasMediaGroup());
        self::assertSame('', $subject->getMediaGroup());
        self::assertFalse($subject->hasMuted());
        self::assertFalse($subject->getMuted());
        self::assertFalse($subject->hasPreload());
        self::assertSame(Preload::getDefault(), $subject->getPreload());
    }

    public function testAddSource(): void
    {
        $src = 'http://example.com/';
        $source = new Source($src);

        $subject = new FakeHtml5Media();
        $subject->addSource($source);

        $components = $subject->getComponents();
        self::assertCount(1, $components);
        self::assertSame($source, $components[0]);
    }

    public function testAddTrack(): void
    {
        $src = 'http://example.com/';
        $track = new Track($src);

        $subject = new FakeHtml5Media();
        $subject->addTrack($track);

        $components = $subject->getComponents();
        self::assertCount(1, $components);
        self::assertSame($track, $components[0]);
    }

    public function testAddSourceAndTrack(): void
    {
        $src = 'http://example.com/';
        $source = new Source($src);
        $track = new Track($src);

        $subject = new FakeHtml5Media();
        $subject->addTrack($track);
        $subject->addSource($source);

        $components = $subject->getComponents();
        self::assertCount(2, $components);
        self::assertSame($source, $components[0]);
        self::assertSame($track, $components[1]);
    }

    public function testSetters(): void
    {
        $src = 'http://example.com/';
        $autoplay = true;
        $controls = true;
        $crossOrigin = CrossOrigin::getConstantByName('use-credentials');
        $loop = true;
        $mediaGroup = 'any normalized string';
        $muted = true;

        $subject = new FakeHtml5Media();
        $subject->setSrc($src);
        $subject->setAutoPlay($autoplay);
        $subject->setControls($controls);
        $subject->setCrossOrigin($crossOrigin);
        $subject->setLoop($loop);
        $subject->setMediaGroup($mediaGroup);
        $subject->setMuted($muted);

        self::assertTrue($subject->hasSrc());
        self::assertEquals($src, $subject->getSrc());
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
    }

    public function testCreateWithNonUriSrc(): void
    {
        $wrongSrc = '';

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The "src" argument must be null or a valid URI, "' . $wrongSrc . '" given.');

        (new FakeHtml5Media())->setSrc($wrongSrc);
    }

    public function testSetWithNonBooleanAutoPlay(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The "autoplay" argument must be a boolean, "foo" given.');

        (new FakeHtml5Media())->setAutoPlay('foo');
    }

    public function testSetWithNonBooleanControls(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The "controls" argument must be a boolean, "foo" given.');

        (new FakeHtml5Media())->setControls('foo');
    }

    /**
     * @dataProvider crossOriginsToTest
     * @param int $role
     */
    public function testSetWithValidCrossOrigin(int $role): void
    {
        $subject = new FakeHtml5Media();

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

        (new FakeHtml5Media())->setCrossOrigin('foo');
    }

    public function testSetWithInvalidCrossOrigin(): void
    {
        $wrongCrossOrigin = 1012;

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The "crossorigin" argument must be a value from the CrossOrigin enumeration, "' . $wrongCrossOrigin . '" given.');

        (new FakeHtml5Media())->setCrossOrigin(1012);
    }

    public function testSetWithNonBooleanLoop(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The "loop" argument must be a boolean, "foo" given.');

        (new FakeHtml5Media())->setLoop('foo');
    }

    public function testSetWithNonBooleanMuted(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The "muted" argument must be a boolean, "foo" given.');

        (new FakeHtml5Media())->setMuted('foo');
    }

    public function testGetPreload(): void
    {
        $subject = new FakeHtml5Media();

        self::assertEquals(Preload::getDefault(), $subject->getPreload());
    }

    public function testGetDefaultPreload(): void
    {
        $subject = new FakeHtml5Media();

        self::assertFalse($subject->hasPreload());
        self::assertEquals(Preload::getConstantByName('metadata'), $subject->getPreload());
    }

    public function testSetPreload(): void
    {
        $preload = Preload::getConstantByName('auto');
        $subject = new FakeHtml5Media();
        $subject->setPreload($preload);

        self::assertTrue($subject->hasPreload());
        self::assertEquals($preload, $subject->getPreload());
    }

    public function testSetPreloadWithNonIntegerValue(): void
    {
        $wrongPreload = 'foo';
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The "preload" argument must be a value from the Preload enumeration, "' . $wrongPreload . '" given.');

        (new FakeHtml5Media())->setPreload($wrongPreload);
    }

    public function testSetPreloadWithInvalidPreload(): void
    {
        $wrongPreload = 1012;
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The "preload" argument must be a value from the Preload enumeration, "' . $wrongPreload . '" given.');

        (new FakeHtml5Media())->setPreload($wrongPreload);
    }
}

class FakeHtml5Media extends Html5Media
{
    public function getQtiClassName(): string
    {
        return '';
    }
}
