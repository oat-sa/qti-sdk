<?php

namespace qtismtest\data\storage\xml\marshalling;

use DOMElement;
use qtism\data\content\xhtml\html5\Html5Media;
use qtism\data\content\xhtml\html5\Source;
use qtism\data\content\xhtml\html5\Track;
use qtism\data\QtiComponent;
use qtism\data\QtiComponentCollection;
use qtism\data\storage\xml\marshalling\Html5MediaMarshaller;
use qtism\data\storage\xml\marshalling\MarshallerNotFoundException;
use qtism\data\storage\xml\marshalling\MarshallingException;
use qtism\data\storage\xml\marshalling\UnmarshallingException;

class Html5MediaMarshallerTest extends Html5ElementMarshallerTest
{
    /**
     * @throws MarshallerNotFoundException
     * @throws MarshallingException
     */
    public function testMarshall22(): void
    {
        $src = 'http://example.com/video';
        $autoplay = true;
        $controls = true;
        $crossOrigin = 'use-credentials';
        $loop = true;
        $mediaGroup = 'one normalized string';
        $muted = true;
        $preload = 'auto';
        $altSrc = 'http://example.com/video2';
        $track1Src = 'http://example.com/track1';
        $track1Lang = 'it';
        $track2Src = 'http://example.com/track2';
        $track2Lang = 'es';

        $expected = sprintf(
            '<%s src="%s" autoplay="%s" controls="%s" crossorigin="%s" loop="%s" mediagroup="%s" muted="%s" preload="%s">'
            . '<%s src="%s"/>'
            . '<%s src="%s" srclang="%s"/>'
            . '<%s src="%s" srclang="%s"/>'
            . '</%s>',
            $this->namespaceTag('media'),
            $src,
            $autoplay ? 'true' : 'false',
            $controls ? 'true' : 'false',
            $crossOrigin,
            $loop ? 'true' : 'false',
            $mediaGroup,
            $muted ? 'true' : 'false',
            $preload,
            $this->prefixTag('source'),
            $altSrc,
            $this->prefixTag('track'),
            $track1Src,
            $track1Lang,
            $this->prefixTag('track'),
            $track2Src,
            $track2Lang,
            $this->prefixTag('media')
        );

        $media = new FakeHtml5Media($src, $autoplay, $controls, $crossOrigin, $loop, $mediaGroup, $muted, $preload);
        $media->addSource(new Source($altSrc));
        $media->addTrack(new Track($track1Src, $track1Lang));
        $media->addTrack(new Track($track2Src, $track2Lang));

        $marshaller = new FakeHtml5MediaMarshaller('2.2.0');

        $this->assertMarshalling($expected, $media, $marshaller);
    }

    /**
     * @throws MarshallerNotFoundException
     * @throws MarshallingException
     */
    public function testMarshall22WithDefaultValues(): void
    {
        $expected = '<' . $this->namespaceTag('media') . '/>';

        $media = new FakeHtml5Media();

        $marshaller = new FakeHtml5MediaMarshaller('2.2.0');

        $this->assertMarshalling($expected, $media, $marshaller);
    }

    /**
     * @throws MarshallerNotFoundException
     */
    public function testUnmarshall22(): void
    {
        $src = 'http://example.com/video';
        $autoplay = true;
        $controls = true;
        $crossOrigin = 'use-credentials';
        $loop = true;
        $mediaGroup = 'one normalized string';
        $muted = true;
        $preload = 'auto';
        $altSrc = 'http://example.com/video2';
        $track1Src = 'http://example.com/track1';
        $track1Lang = 'it';
        $track2Src = 'http://example.com/track2';
        $track2Lang = 'es';

        $xml = sprintf(
            '<%s src="%s" autoplay="%s" controls="%s" crossorigin="%s" loop="%s" mediagroup="%s" muted="%s" preload="%s">'
            . '<%s src="%s"/>'
            . '<%s src="%s" srclang="%s"/>'
            . '<%s src="%s" srclang="%s"/>'
            . '</%s>',
            $this->namespaceTag('media'),
            $src,
            $autoplay ? 'true' : 'false',
            $controls ? 'true' : 'false',
            $crossOrigin,
            $loop ? 'true' : 'false',
            $mediaGroup,
            $muted ? 'true' : 'false',
            $preload,
            $this->prefixTag('source'),
            $altSrc,
            $this->prefixTag('track'),
            $track1Src,
            $track1Lang,
            $this->prefixTag('track'),
            $track2Src,
            $track2Lang,
            $this->prefixTag('media')
       );

        $marshaller = new FakeHtml5MediaMarshaller('2.2.0');

        $expected = new FakeHtml5Media(
            $src,
            $autoplay,
            $controls,
            $crossOrigin,
            $loop,
            $mediaGroup,
            $muted,
            $preload
        );
        $expected->addSource(new Source($altSrc));
        $expected->addTrack(new Track($track1Src, $track1Lang));
        $expected->addTrack(new Track($track2Src, $track2Lang));

        $this->assertUnmarshalling($expected, $xml, $marshaller);
    }

    public function testUnmarshall22WithDefaultValues(): void
    {
        $xml = '<' . $this->namespaceTag('media') . '/>';

        $marshaller = new FakeHtml5MediaMarshaller('2.2.0');

        $expected = new FakeHtml5Media();
        $this->assertUnmarshalling($expected, $xml, $marshaller);
    }
}

class FakeHtml5Media extends Html5Media
{
    public function getQtiClassName(): string
    {
        return 'media';
    }

    public function getComponents(): QtiComponentCollection
    {
        return new QtiComponentCollection();
    }
}

class FakeHtml5MediaMarshaller extends Html5MediaMarshaller
{
    /**
     * @throws MarshallingException
     * @throws UnmarshallingException
     */
    public function __call($method, $args)
    {
        if ($method === 'marshall') {
            return $this->marshall($args[0]);
        }
        if ($method === 'unmarshall') {
            return $this->unmarshall($args[0]);
        }
    }

    /**
     * @param DOMElement $element
     * @return QtiComponent
     * @throws UnmarshallingException
     * @throws MarshallerNotFoundException
     */
    protected function unmarshall(DOMElement $element)
    {
        $component = new FakeHtml5Media();
        $this->fillBodyElement($component, $element);
        return $component;
    }

    public function getExpectedQtiClassName()
    {
    }
}
