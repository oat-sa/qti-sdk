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
 * Copyright (c) 2018-2020 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 * @author Moyon Camille <camille@taotesting.com>
 * @license GPLv2
 */

namespace qtismtest\data\storage\xml\marshalling;

use DOMElement;
use qtism\data\results\CandidateResponse;
use qtism\data\state\Value;
use qtism\data\state\ValueCollection;
use qtismtest\QtiSmTestCase;

/**
 * Class CandidateResponseMarshallerTest
 *
 * @package qtismtest\data\storage\xml\marshalling
 */
class CandidateResponseMarshallerTest extends QtiSmTestCase
{
    public function testUnmarshall()
    {
        /** @var CandidateResponse $candidateResponse */
        $candidateResponse = $this->createComponentFromXml('
            <candidateResponse>
                <value>fixture1</value>
                <value>fixture2</value>
            </candidateResponse>
        ');

        $this->assertInstanceOf(CandidateResponse::class, $candidateResponse);

        $this->assertTrue($candidateResponse->hasValues());
        $this->assertInstanceOf(ValueCollection::class, $candidateResponse->getValues());
        $this->assertEquals(2, $candidateResponse->getValues()->count());
    }

    public function testUnmarshallMinimal()
    {
        /** @var CandidateResponse $candidateResponse */
        $candidateResponse = $this->createComponentFromXml('
            <candidateResponse />
        ');

        $this->assertInstanceOf(CandidateResponse::class, $candidateResponse);

        $this->assertFalse($candidateResponse->hasValues());
        $this->assertNull($candidateResponse->getValues());
    }

    public function testMarshall()
    {
        $component = new CandidateResponse(
            new ValueCollection([
                new Value('fixture1'),
                new Value('fixture2'),
            ])
        );

        /** @var DOMElement $element */
        $element = $this->getMarshallerFactory()->createMarshaller($component)->marshall($component);

        $this->assertInstanceOf(DOMElement::class, $element);

        $this->assertEquals($component->getQtiClassName(), $element->nodeName);

        $this->assertEquals(2, $element->getElementsByTagName('value')->length);
        /** @var DOMElement $node */
        foreach ($element->childNodes as $node) {
            $this->assertEquals('value', $node->nodeName);
        }
    }

    public function testMarshallMinimal()
    {
        $component = new CandidateResponse();

        /** @var DOMElement $element */
        $element = $this->getMarshallerFactory()->createMarshaller($component)->marshall($component);

        $this->assertInstanceOf(DOMElement::class, $element);

        $this->assertEquals($component->getQtiClassName(), $element->nodeName);
        $this->assertFalse($element->hasAttributes());
        $this->assertFalse($element->hasChildNodes());
    }

    public function testGetExpectedQtiClassName()
    {
        $component = new CandidateResponse();
        $marshaller = $this->getMarshallerFactory()->createMarshaller($component);
        $this->assertEquals($component->getQtiClassName(), $marshaller->getExpectedQtiClassName());
    }
}
