<?php

namespace qtism\data\storage\xml\marshalling;

use DOMElement;
use qtism\data\QtiComponent;
use qtism\data\results\CandidateResponse;
use qtism\data\state\Value;
use qtism\data\state\ValueCollection;

class CandidateResponseMarshaller extends Marshaller
{
    protected function marshall(QtiComponent $component)
    {
        $element = self::getDOMCradle()->createElement($this->getExpectedQtiClassName());

        if ($component->hasValues()) {
            /** @var Value $value */
            foreach ($component->getValues() as $value) {
                $valueElement= $this->getMarshallerFactory()
                    ->createMarshaller($value)
                    ->marshall($value);
                $element->appendChild($valueElement);
            }
        }

        return $element;
    }

    protected function unmarshall(DOMElement $element)
    {
       $valuesElements = self::getChildElementsByTagName($element, 'value');
        if (!empty($valuesElements)) {
            $values = [];
            foreach ($valuesElements as $valuesElement) {
                $values[] = $this->getMarshallerFactory()
                    ->createMarshaller($valuesElement)
                    ->unmarshall($valuesElement);
            }
            $valueCollection = new ValueCollection($values);
        } else {
            $valueCollection = null;
        }

        return new CandidateResponse($valueCollection);
    }

    public function getExpectedQtiClassName()
    {
         return 'candidateResponse';
    }


}