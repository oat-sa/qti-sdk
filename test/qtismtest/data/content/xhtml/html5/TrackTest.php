<?php

namespace qtismtest\data\content\xhtml\html5;

use InvalidArgumentException;
use qtism\data\content\xhtml\html5\Track;
use qtism\data\content\enums\TrackKind;
use qtismtest\QtiSmTestCase;

class TrackTest extends QtiSmTestCase
{
    public function testCreateWithValues(): void
    {
        $src = 'http://example.com/';
        $default = true;
        $kind = TrackKind::getConstantByName('chapters');
        $srcLang = 'en';

        $subject = new Track($src, $default, $kind, $srcLang);

        self::assertEquals($src, $subject->getSrc());
        self::assertEquals($default, $subject->getDefault());
        self::assertEquals($kind, $subject->getKind());
        self::assertEquals($srcLang, $subject->getSrcLang());
    }

    public function testCreateWithDefaultValues(): void
    {
        $src = 'http://example.com/';

        $subject = new Track($src);

        self::assertEquals($src, $subject->getSrc());
        self::assertFalse($subject->getDefault());
        self::assertEquals(TrackKind::getDefault(), $subject->getKind());
        self::assertEquals('en', $subject->getSrcLang());
    }

    public function testHasNonDefaultValues(): void
    {
        $src = 'http://example.com/';
        $default = true;
        $kind = TrackKind::getConstantByName('chapters');
        $srcLang = 'en';

        $subject = new Track($src, $default, $kind, $srcLang);

        self::assertTrue($subject->hasDefault());
        self::assertEquals($default, $subject->getDefault());
        self::assertTrue($subject->hasKind());
        self::assertEquals($kind, $subject->getKind());
        self::assertTrue($subject->hasSrcLang());
        self::assertEquals($srcLang, $subject->getSrcLang());
    }

    /**
     * @dataProvider kindsOfTracks
     * @param int $kind
     */
    public function testCreateWithValidKind(int $kind): void
    {
        $subject = new Track('http://example.com/', false, $kind);
        self::assertEquals($kind, $subject->getKind());
    }

    public function kindsOfTracks(): array
    {
        return [
            [TrackKind::getConstantByName('subtitles')],
            [TrackKind::getConstantByName('captions')],
            [TrackKind::getConstantByName('descriptions')],
            [TrackKind::getConstantByName('chapters')],
            [TrackKind::getConstantByName('metadata')],
        ];
    }

    public function testCreateWithInvalidSrc(): void
    {
        $wrongSrc = 12;

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The "src" argument must be a valid URI, "' . gettype($wrongSrc) . '" given.');

        new Track($wrongSrc);
    }

    public function testCreateWithNonUriSrc(): void
    {
        $wrongSrc = '';

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The "src" argument must be a valid URI, "' . $wrongSrc . '" given.');

        new Track($wrongSrc);
    }

    public function testCreateWithInvalidDefault(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The "default" argument must be a boolean, "string" given.');

        new Track('http://example.com/', 'blah');
    }

    public function testCreateWithNonIntegerKind(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The "kind" argument must be a value from the TrackKind enumeration, "blah" given.');

        new Track('http://example.com/', false, 'blah');
    }

    public function testCreateWithInvalidKind(): void
    {
        $wrongKind = 1012;
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The "kind" argument must be a value from the TrackKind enumeration, "' . $wrongKind . '" given.');

        new Track('http://example.com/', false, $wrongKind);
    }

    public function testCreateWithInvalidSrcLang(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The "srclang" argument must be a valid BCP 47 language code, "12" given.');

        new Track('http://example.com/', false, null, 12);
    }

    public function testGetQtiClassName(): void
    {
        $subject = new Track('http://example.com/');

        self::assertEquals('track', $subject->getQtiClassName());
    }
}
