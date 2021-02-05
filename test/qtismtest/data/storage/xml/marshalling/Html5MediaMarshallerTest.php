<?php

namespace qtismtest\data\storage\xml\marshalling;

use DOMElement;
use qtism\data\content\xhtml\html5\Media;
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

        $expected = sprintf(
            '<media autoplay="%s" controls="%s" crossorigin="%s" loop="%s" mediagroup="%s" muted="%s" preload="%s" src="%s"/>',
            $autoplay ? 'true' : 'false',
            $controls ? 'true' : 'false',
            $crossOrigin,
            $loop ? 'true' : 'false',
            $mediaGroup,
            $muted ? 'true' : 'false',
            $preload,
            $src
        );

        $media = new FakeHtml5Media($autoplay, $controls, $crossOrigin, $loop, $mediaGroup, $muted, $preload, $src);

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

        $xml = sprintf(
            '<video autoplay="%s" controls="%s" crossorigin="%s" loop="%s" mediagroup="%s" muted="%s" preload="%s" src="%s"/>',
            $autoplay ? 'true' : 'false',
            $controls ? 'true' : 'false',
            $crossOrigin,
            $loop ? 'true' : 'false',
            $mediaGroup,
            $muted ? 'true' : 'false',
            $preload,
            $src
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

class FakeHtml5Media extends Media
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

    protected function marshall(QtiComponent $component)
    {
        $element = self::getDOMCradle()->createElement('media');
        $this->fillElement($element, $component);
        return $element;
    }

    /**
     * @param DOMElement $element
     * @return QtiComponent
     * @throws UnmarshallingException
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
