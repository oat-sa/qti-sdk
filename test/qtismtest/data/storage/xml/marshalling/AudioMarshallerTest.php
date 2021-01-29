<?php

namespace qtismtest\data\storage\xml\marshalling;

use qtism\data\content\xhtml\html5\Audio;
use qtism\data\storage\xml\marshalling\MarshallerNotFoundException;
use qtism\data\storage\xml\marshalling\MarshallingException;

class AudioMarshallerTest extends Html5ElementMarshallerTest
{
    /**
     * @throws MarshallerNotFoundException
     * @throws MarshallingException
     */
    public function testMarshallerDoesNotExistInQti21(): void
    {
        $this->assertHtml5MarshallingOnlyInQti22AndAbove(new Audio(), 'audio');
    }

    /**
     * @throws MarshallerNotFoundException
     * @throws MarshallingException
     */
    public function testMarshall22(): void
    {
        $expected = '<audio/>';

        $object = new Audio();

        $this->assertMarshalling($expected, $object);
    }

    /**
     * @throws MarshallerNotFoundException
     */
    public function testUnMarshallerDoesNotExistInQti21(): void
    {
        $this->assertHtml5UnmarshallingOnlyInQti22AndAbove('<audio/>', 'audio');
    }

    /**
     * @throws MarshallerNotFoundException
     */
    public function testUnmarshall22(): void
    {
        $xml = '<audio/>';

        $expected = new Audio();

        $this->assertUnmarshalling($expected, $xml);
    }
}
