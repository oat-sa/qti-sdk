<?php

namespace qtismtest\data\content\xhtml\html5;

use InvalidArgumentException;
use qtism\data\content\xhtml\html5\Track;
use qtism\data\content\xhtml\html5\TrackKind;
use qtismtest\QtiSmTestCase;

class TrackTest extends QtiSmTestCase
{
    public function testCreateWithValues()
    {
        $src = 'http://example.com/';
        $default = true;
        $kind = TrackKind::CHAPTERS;
        $srcLang = 'en';

        $subject = new Track($src, $default, $kind, $srcLang);

        $this->assertEquals($src, $subject->getSrc());
        $this->assertEquals($default, $subject->getDefault());
        $this->assertEquals($kind, $subject->getKind());
        $this->assertEquals($srcLang, $subject->getSrcLang());
    }

    public function testCreateWithDefaultValues()
    {
        $src = 'http://example.com/';

        $subject = new Track($src);

        $this->assertEquals($src, $subject->getSrc());
        $this->assertFalse($subject->getDefault());
        $this->assertEquals(TrackKind::SUBTITLES, $subject->getKind());
        $this->assertEquals('', $subject->getSrcLang());
    }

    public function testHasNonDefaultValues()
    {
        $src = 'http://example.com/';
        $default = true;
        $kind = TrackKind::CHAPTERS;
        $srcLang = 'en';

        $subject = new Track($src, $default, $kind, $srcLang);

        $this->assertTrue($subject->hasDefault());
        $this->assertEquals($default, $subject->getDefault());
        $this->assertTrue($subject->hasKind());
        $this->assertEquals($kind, $subject->getKind());
        $this->assertTrue($subject->hasSrcLang());
        $this->assertEquals($srcLang, $subject->getSrcLang());
    }

    /**
     * @dataProvider kindsOfTracks
     * @param int $kind
     */
    public function testCreateWithValidKind(int $kind)
    {
        $subject = new Track('http://example.com/', false, $kind);
        $this->assertEquals($kind, $subject->getKind());
    }

    public function kindsOfTracks()
    {
        return [
            [TrackKind::SUBTITLES],
            [TrackKind::CAPTIONS],
            [TrackKind::DESCRIPTIONS],
            [TrackKind::CHAPTERS],
            [TrackKind::METADATA],
        ];
    }

    public function testCreateWithInvalidSrc()
    {
        $wrongSrc = 12;

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The "src" argument must be a valid URI, "' . gettype($wrongSrc) . '" given.');

        new Track($wrongSrc);
    }

    public function testCreateWithNonUriSrc()
    {
        $wrongSrc = '';

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The "src" argument must be a valid URI, "' . $wrongSrc . '" given.');

        new Track($wrongSrc);
    }

    public function testCreateWithInvalidDefault()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The "default" argument must be a boolean, "string" given.');

        new Track('http://example.com/', 'blah');
    }

    public function testCreateWithNonIntegerKind()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The "kind" argument must be a value from the TrackKind enumeration, "string" given.');

        new Track('http://example.com/', false, 'blah');
    }

    public function testCreateWithInvalidKind()
    {
        $wrongKind = 1012;
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The "kind" argument must be a value from the TrackKind enumeration, "' . $wrongKind . '" given.');

        new Track('http://example.com/', false, $wrongKind);
    }

    public function testCreateWithInvalidSrcLang()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The "srclang" argument must be a string, "integer" given.');

        new Track('http://example.com/', false, TrackKind::SUBTITLES, 12);
    }

    public function testGetQtiClassName()
    {
        $subject = new Track('http://example.com/');
        
        $this->assertEquals('track', $subject->getQtiClassName());
    }
}
