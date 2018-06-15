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
 * Copyright (c) 2013-2015 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 * @license GPLv2
 */

namespace qtism\data\storage\xml\marshalling;

use qtism\data\AssessmentTest;
use qtism\data\TestFeedbackRefCollection;
use qtism\data\ExtendedAssessmentTest;
use qtism\data\QtiComponent;
use \DOMElement;

/**
 * Marshalling/Unmarshalling implementation for ExtendedAssessmentTest.
 *
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class ExtendedAssessmentTestMarshaller extends AssessmentTestMarshaller
{
    /**
     * @see \qtism\data\storage\xml\marshalling\Marshaller::marshall()
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
	 * @see \qtism\data\storage\xml\marshalling\Marshaller::unmarshall()
	 */
    protected function unmarshall(DOMElement $element, AssessmentTest $assessmentTest = null)
    {
        $baseComponent = parent::unmarshall($element);
        $component = ExtendedAssessmentTest::createFromAssessmentTest($baseComponent);
        
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
