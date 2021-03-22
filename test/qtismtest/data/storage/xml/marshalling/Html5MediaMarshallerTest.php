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
        $autoplay = true;
        $controls = true;
        $crossOrigin = 'use-credentials';
        $loop = true;
        $mediaGroup = 'one normalized string';
        $muted = true;
        $preload = 'auto';
        $src = 'http://example.com/video';
        $altSrc = 'http://example.com/video2';
        $track1Src = 'http://example.com/track1';
        $track2Src = 'http://example.com/track2';

        $expected = sprintf(
            '<media autoplay="%s" controls="%s" crossorigin="%s" loop="%s" mediagroup="%s" muted="%s" preload="%s" src="%s">'
            . '<source src="%s"/>'
            . '<track src="%s" srclang="en"/>'
            . '<track src="%s" srclang="en"/>'
            . '</media>',
            $autoplay ? 'true' : 'false',
            $controls ? 'true' : 'false',
            $crossOrigin,
            $loop ? 'true' : 'false',
            $mediaGroup,
            $muted ? 'true' : 'false',
            $preload,
            $src,
            $altSrc,
            $track1Src,
            $track2Src
        );

        $media = new FakeHtml5Media($autoplay, $controls, $crossOrigin, $loop, $mediaGroup, $muted, $preload, $src);
        $media->addSource(new Source($altSrc));
        $media->addTrack(new Track($track1Src));
        $media->addTrack(new Track($track2Src));

        $marshaller = new FakeHtml5MediaMarshaller('2.2.0');

        $this->assertMarshalling($expected, $media, $marshaller);
    }

    /**
     * @throws MarshallerNotFoundException
     * @throws MarshallingException
     */
    public function testMarshall22WithDefaultValues(): void
    {
        $expected = '<media/>';

        $media = new FakeHtml5Media();

        $marshaller = new FakeHtml5MediaMarshaller('2.2.0');

        $this->assertMarshalling($expected, $media, $marshaller);
    }

    /**
     * @throws MarshallerNotFoundException
     */
    public function testUnmarshall22(): void
    {
        $autoplay = true;
        $controls = true;
        $crossOrigin = 'use-credentials';
        $loop = true;
        $mediaGroup = 'one normalized string';
        $muted = true;
        $preload = 'auto';
        $src = 'http://example.com/video';
        $altSrc = 'http://example.com/video2';
        $track1Src = 'http://example.com/track1';
        $track2Src = 'http://example.com/track2';

        $xml = sprintf(
            '<media autoplay="%s" controls="%s" crossorigin="%s" loop="%s" mediagroup="%s" muted="%s" preload="%s" src="%s">
                <source src="%s"/>
                <track src="%s"/>
                <track src="%s"/>
            </media>',
            $autoplay ? 'true' : 'false',
            $controls ? 'true' : 'false',
            $crossOrigin,
            $loop ? 'true' : 'false',
            $mediaGroup,
            $muted ? 'true' : 'false',
            $preload,
            $src,
            $altSrc,
            $track1Src,
            $track2Src
        );

        $marshaller = new FakeHtml5MediaMarshaller('2.2.0');

        $expected = new FakeHtml5Media(
            $autoplay,
            $controls,
            $crossOrigin,
            $loop,
            $mediaGroup,
            $muted,
            $preload,
            $src
        );
        $expected->addSource(new Source($altSrc));
        $expected->addTrack(new Track($track1Src));
        $expected->addTrack(new Track($track2Src));

        $this->assertUnmarshalling($expected, $xml, $marshaller);
    }

    public function testUnmarshall22WithDefaultValues(): void
    {
        $xml = '<media/>';

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
