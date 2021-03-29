<?php

namespace qtismtest\data\content\xhtml\html5;

use InvalidArgumentException;
use qtism\data\content\enums\TrackKind;
use qtism\data\content\xhtml\html5\Track;
use qtismtest\QtiSmTestCase;

class TrackTest extends QtiSmTestCase
{
    public function testCreateWithValues(): void
    {
        $src = 'http://example.com/';
        $srcLang = 'ja';
        $default = true;
        $kind = TrackKind::getConstantByName('chapters');

        $subject = new Track($src, $srcLang, $default, $kind);

        self::assertEquals($src, $subject->getSrc());
        self::assertEquals($srcLang, $subject->getSrcLang());
        self::assertEquals($default, $subject->getDefault());
        self::assertEquals($kind, $subject->getKind());
    }

    public function testCreateWithStringValues(): void
    {
        $src = 'http://example.com/';
        $srcLang = 'ja';
        $default = 'true';
        $kindAsString = 'chapters';

        $subject = new Track($src, $srcLang, $default, $kindAsString);

        self::assertSame($src, $subject->getSrc());
        self::assertEquals($srcLang, $subject->getSrcLang());
        self::assertTrue($subject->getDefault());
        self::assertEquals(
            TrackKind::getConstantByName($kindAsString),
            $subject->getKind()
        );
    }

    public function testCreateWithDefaultValues(): void
    {
        $src = 'http://example.com/';

        $subject = new Track($src);

        self::assertEquals($src, $subject->getSrc());
        self::assertEquals('en', $subject->getSrcLang());
        self::assertFalse($subject->getDefault());
        self::assertEquals(TrackKind::getDefault(), $subject->getKind());
    }

    public function testHasNonDefaultValues(): void
    {
        $src = 'http://example.com/';
        $srcLang = 'en';
        $default = true;
        $kind = TrackKind::getConstantByName('chapters');

        $subject = new Track($src, $srcLang, $default, $kind);

        self::assertTrue($subject->hasSrcLang());
        self::assertEquals($srcLang, $subject->getSrcLang());
        self::assertTrue($subject->hasDefault());
        self::assertEquals($default, $subject->getDefault());
        self::assertTrue($subject->hasKind());
        self::assertEquals($kind, $subject->getKind());
    }

    /**
     * @dataProvider kindsOfTracks
     * @param int $kind
     */
    public function testCreateWithValidKind(int $kind): void
    {
        $subject = new Track('http://example.com/', null, false, $kind);
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

    public function testCreateWithNonUriSrc(): void
    {
        $wrongSrc = '';

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The "src" argument must be a valid URI, "' . $wrongSrc . '" given.');

        new Track($wrongSrc);
    }

    public function testCreateWithInvalidSrcLang(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The "srclang" argument must be a valid BCP 47 language code, "12" given.');

        new Track('http://example.com/', 12, false);
    }

    public function testCreateWithInvalidDefault(): void
    {
        $wrongDefault = 'blah';

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The "default" argument must be a boolean, "' . $wrongDefault . '" given.');

        new Track('http://example.com/', null, $wrongDefault);
    }

    public function testCreateWithNonIntegerKind(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The "kind" argument must be a value from the TrackKind enumeration, "blah" given.');

        new Track('http://example.com/', null, false, 'blah');
    }

    public function testCreateWithInvalidKind(): void
    {
        $wrongKind = 1012;
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The "kind" argument must be a value from the TrackKind enumeration, "' . $wrongKind . '" given.');

        new Track('http://example.com/', null, false, $wrongKind);
    }

    public function testGetQtiClassName(): void
    {
        $subject = new Track('http://example.com/');

        self::assertEquals('track', $subject->getQtiClassName());
    }
}
