<?php

namespace qtismtest\data\content\xhtml\html5;

use InvalidArgumentException;
use qtism\data\content\xhtml\html5\Source;
use qtismtest\QtiSmTestCase;

class SourceTest extends QtiSmTestCase
{
    public function testCreateWithValues()
    {
        $src = 'http://example.com/';
        $type = 'video/webm';

        $subject = new Source($src, $type);

        $this->assertEquals($src, $subject->getSrc());
        $this->assertEquals($type, $subject->getType());
    }

    public function testCreateWithDefaultValues()
    {
        $src = 'http://example.com/';
        $type = '';

        $subject = new Source($src);

        $this->assertEquals($src, $subject->getSrc());
        $this->assertEquals($type, $subject->getType());
    }

    public function testHasNonDefaultValues()
    {
        $src = 'http://example.com/';
        $type = 'video/webm';

        $subject = new Source($src, $type);

        $this->assertTrue($subject->hasType());
        $this->assertEquals($type, $subject->getType());
    }

    public function testCreateWithInvalidSrc()
    {
        $wrongSrc = 12;

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The "src" argument must be a valid URI, "' . gettype($wrongSrc) . '" given.');

        new Source($wrongSrc);
    }

    public function testCreateWithNonUriSrc()
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
    public function testCreateWithValidType(string $type)
    {
        $subject = new Source('http://example.com/', $type);
        $this->assertEquals($type, $subject->getType());
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
     * @param null $given
     */
    public function testCreateWithInvalidType($type, $given = null)
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The "type" argument must be a valid Mime type, "' . ($given ?? $type) . '" given.');

        new Source('http://example.com/', $type);
    }

    public function sourceTypeInvalidProvider(): array
    {
        return [
            [''],
            ['invalid-mime-type'],
            ['missing+slash'],
            [12, 'integer'],
            [false, 'boolean'],
        ];
    }
}
