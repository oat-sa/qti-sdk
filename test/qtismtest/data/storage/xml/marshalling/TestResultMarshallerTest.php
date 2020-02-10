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
use oat\dtms\DateTime;
use qtism\common\datatypes\QtiIdentifier;
use qtism\data\results\CandidateResponse;
use qtism\data\results\ItemVariableCollection;
use qtism\data\results\ResultResponseVariable;
use qtism\data\results\ResultTemplateVariable;
use qtism\data\results\TestResult;
use qtismtest\QtiSmTestCase;

class TestResultMarshallerTest extends QtiSmTestCase
{
    public function testUnmarshall()
    {
        /** @var TestResult $testResult */
        $testResult = $this->createComponentFromXml('
            <testResult identifier="fixture-identifier" datestamp="2018-06-27T09:41:45.529">
                <responseVariable identifier="response-identifier" cardinality="single">
                    <candidateResponse>test</candidateResponse>
                </responseVariable>
                <templateVariable identifier="response-identifier" cardinality="single">
                    <value>test1</value>
                    <value>test2</value>
                </templateVariable>
            </testResult>
        ');

        $this->assertInstanceOf(TestResult::class, $testResult);

        $this->assertEquals('fixture-identifier', $testResult->getIdentifier()->getValue());
        $this->assertEquals('fixture-identifier', $testResult->getIdentifier());

        $this->assertInstanceOf(DateTime::class, $testResult->getDatestamp());

        $this->assertTrue($testResult->hasItemVariables());
        $this->assertInstanceOf(ItemVariableCollection::class, $testResult->getItemVariables());
        $this->assertEquals(2, $testResult->getItemVariables()->count());
    }

    public function testUnmarshallMinimal()
    {
        /** @var TestResult $testResult */
        $testResult = $this->createComponentFromXml('
            <testResult identifier="fixture-identifier" datestamp="2018-06-27T09:41:45.529" />
        ');

        $this->assertInstanceOf(TestResult::class, $testResult);

        $this->assertEquals('fixture-identifier', $testResult->getIdentifier()->getValue());
        $this->assertEquals('fixture-identifier', $testResult->getIdentifier());

        $this->assertInstanceOf(DateTime::class, $testResult->getDatestamp());

        $this->assertFalse($testResult->hasItemVariables());
        $this->assertNull($testResult->getItemVariables());
    }

    public function testMarshall()
    {
        $component = new TestResult(
            new QtiIdentifier('fixture-identifier'),
            new DateTime('2018-06-27T09:41:45.529'),
            new ItemVariableCollection([
                new ResultResponseVariable(
                    new QtiIdentifier('response-identifier'),
                    0,
                    new CandidateResponse()
                ),
                new ResultTemplateVariable(
                    new QtiIdentifier('response-identifier'),
                    0
                ),
            ])
        );

        /** @var DOMElement $element */
        $element = $this->getMarshallerFactory()->createMarshaller($component)->marshall($component);

        $this->assertInstanceOf(DOMElement::class, $element);

        $this->assertEquals($component->getQtiClassName(), $element->nodeName);

        $this->assertEquals('fixture-identifier', $element->getAttribute('identifier'));
        $this->assertTrue($element->hasAttribute('datestamp'));

        $this->assertEquals(1, $element->getElementsByTagName('responseVariable')->length);
        $this->assertEquals(1, $element->getElementsByTagName('templateVariable')->length);
    }

    public function testMarshallMinimal()
    {
        $component = new TestResult(
            new QtiIdentifier('fixture-identifier'),
            new DateTime('2018-06-27T09:41:45.529')
        );

        /** @var DOMElement $element */
        $element = $this->getMarshallerFactory()->createMarshaller($component)->marshall($component);

        $this->assertInstanceOf(DOMElement::class, $element);

        $this->assertEquals($component->getQtiClassName(), $element->nodeName);

        $attributes = [];
        for ($i = 0; $i < 2; $i++) {
            $attributes[] = $element->attributes->item($i)->name;
        }
        $this->assertEmpty(array_diff($attributes, ['identifier', 'datestamp']));

        $this->assertFalse($element->hasChildNodes());
    }

    public function testGetExpectedQtiClassName()
    {
        $component = new TestResult(
            new QtiIdentifier('fixture-identifier'),
            new DateTime('2018-06-27T09:41:45.529')
        );
        $marshaller = $this->getMarshallerFactory()->createMarshaller($component);
        $this->assertEquals($component->getQtiClassName(), $marshaller->getExpectedQtiClassName());
    }
}
