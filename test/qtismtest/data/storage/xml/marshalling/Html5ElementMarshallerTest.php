<?php

namespace qtismtest\data\storage\xml\marshalling;

use DOMDocument;
use qtism\data\content\xhtml\html5\Html5Element;
use qtism\data\storage\xml\marshalling\MarshallerNotFoundException;
use qtism\data\storage\xml\marshalling\MarshallingException;
use qtismtest\QtiSmTestCase;
use RuntimeException;

class Html5ElementMarshallerTest extends QtiSmTestCase
{
    /**
     * @param Html5Element $object
     * @param string $elementName
     * @throws MarshallerNotFoundException
     * @throws MarshallingException
     */
    protected function assertHtml5MarshallingOnlyInQti22AndAbove(Html5Element $object, string $elementName)
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('No marshaller implementation found while marshalling component \'' . $elementName . '\'.');
        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($object);
        $marshaller->marshall($object);
    }

    /**
     * @param string $xml
     * @param string $elementName
     * @throws MarshallerNotFoundException
     */
    public function assertHtml5UnmarshallingOnlyInQti22AndAbove(string $xml, string $elementName)
    {
        $element = $this->createDOMElement($xml);
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('No Marshaller implementation found while unmarshalling element \'' . $elementName . '\'.');
        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($element);
        $marshaller->unmarshall($element);
    }

    /**
     * @param string $expected
     * @param Html5Element $object
     * @throws MarshallerNotFoundException
     * @throws MarshallingException
     */
    protected function assertMarshalling(string $expected, Html5Element $object)
    {
        $marshaller = $this->getMarshallerFactory('2.2.0')->createMarshaller($object);
        $element = $marshaller->marshall($object);

        $dom = new DOMDocument('1.0', 'UTF-8');
        $element = $dom->importNode($element, true);
        $this->assertEquals($expected, $dom->saveXML($element));
    }
}
