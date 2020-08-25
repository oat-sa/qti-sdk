<?php

/**
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 *
 * Copyright (c) 2013-2020 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 * @license GPLv2
 */

namespace qtism\data\storage\xml\marshalling;

use DOMElement;
use qtism\data\ExtendedTestPart;
use qtism\data\QtiComponent;
use qtism\data\TestFeedbackRefCollection;

/**
 * Marshalling/Unmarshalling implementation for ExtendedTestPart.
 */
class ExtendedTestPartMarshaller extends TestPartMarshaller
{
    /**
     * @param QtiComponent $component
     * @return DOMElement
     * @throws MarshallerNotFoundException
     * @throws MarshallingException
     */
    protected function marshall(QtiComponent $component)
    {
        $element = parent::marshall($component);

        // TestFeedbackRefs.
        foreach ($component->getTestFeedbackRefs() as $testFeedbackRef) {
            $marshaller = $this->getMarshallerFactory()->createMarshaller($testFeedbackRef);
            $element->appendChild($marshaller->marshall($testFeedbackRef));
        }

        return $element;
    }

    /**
     * @param DOMElement $element
     * @return ExtendedTestPart|\qtism\data\TestPart
     * @throws MarshallerNotFoundException
     * @throws UnmarshallingException
     */
    protected function unmarshall(DOMElement $element)
    {
        $baseComponent = parent::unmarshall($element);
        $component = ExtendedTestPart::createFromTestPart($baseComponent);

        // TestFeedbackRefs.
        $testFeedbackRefElts = $this->getChildElementsByTagName($element, 'testFeedbackRef');
        $testFeedbackRefs = new TestFeedbackRefCollection();

        foreach ($testFeedbackRefElts as $testFeedbackRefElt) {
            $marshaller = $this->getMarshallerFactory()->createMarshaller($testFeedbackRefElt);
            $testFeedbackRefs[] = $marshaller->unmarshall($testFeedbackRefElt);
        }

        $component->setTestFeedbackRefs($testFeedbackRefs);

        return $component;
    }
}
