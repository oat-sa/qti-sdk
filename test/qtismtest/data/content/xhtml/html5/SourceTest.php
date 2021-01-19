<?php

namespace qtismtest\data\content\xhtml\html5;

use InvalidArgumentException;
use qtism\data\content\xhtml\html5\Source;
use qtismtest\QtiSmTestCase;

class SourceTest extends QtiSmTestCase
{
    public function testCreateWithValues(): void
    {
        $src = 'http://example.com/';
        $type = 'video/webm';

        $subject = new Source($src, $type);

        self::assertSame($src, $subject->getSrc());
        self::assertSame($type, $subject->getType());
    }

    public function testCreateWithDefaultValues(): void
    {
        $src = 'http://example.com/';
        $type = '';

        $subject = new Source($src);

        self::assertSame($src, $subject->getSrc());
        self::assertSame($type, $subject->getType());
    }

    public function testHasNonDefaultValues(): void
    {
        $src = 'http://example.com/';
        $type = 'video/webm';

        $subject = new Source($src, $type);

        self::assertTrue($subject->hasType());
        self::assertSame($type, $subject->getType());
    }

    public function testCreateWithNonUriSrc(): void
    {
        $wrongSrc = '';

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The "src" argument must be a valid URI, "' . $wrongSrc . '" given.');

        new Source($wrongSrc);
    }

    /**
     * @dataProvider sourceTypeValidProvider
     * @param string $type
     */
    public function testCreateWithValidType(string $type): void
    {
        $subject = new Source('http://example.com/', $type);
        self::assertSame($type, $subject->getType());
    }

    public function sourceTypeValidProvider(): array
    {
        return [
            ['video/webm'],
            ['text/plain'],
            ['application/octet-stream'],
            ['audio/ogg'],
            ['x-conference/x-cooltalk'],
            ['application/vnd.adobe.air-application-installer-package+zip'],
            ['image/jpeg'],
            ['application/rdf+xml'],
        ];
    }

    /**
     * @dataProvider sourceTypeInvalidProvider
     * @param mixed $type
     */
    public function testCreateWithInvalidType($type): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The "type" argument must be a valid Mime type, "' . $type . '" given.');

        new Source('http://example.com/', $type);
    }

    public function sourceTypeInvalidProvider(): array
    {
        return [
            [''],
            ['invalid-mime-type'],
            ['missing+slash'],
            [12],
            [true],
        ];
    }

    public function testGetQtiClassName(): void
    {
        $subject = new Source('http://example.com/');

        self::assertEquals('source', $subject->getQtiClassName());
    }
}
