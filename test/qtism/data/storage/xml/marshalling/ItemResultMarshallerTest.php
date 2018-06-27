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
 * Copyright (c) 2018 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 * @author Moyon Camille, <camille@taotesting.com>
 * @license GPLv2
 */

use oat\dtms\DateTime;
use qtism\common\datatypes\QtiIdentifier;
use qtism\data\results\ResultTemplateVariable;
use qtism\data\results\ResultResponseVariable;
use qtism\data\results\ItemResult;
use qtism\data\results\ItemVariableCollection;
use qtism\data\results\SessionStatus;
use qtism\common\datatypes\QtiString;
use qtism\common\datatypes\QtiInteger;
use qtism\data\results\CandidateResponse;

require_once (dirname(__FILE__) . '/../../../../../QtiSmTestCase.php');

class ItemResultMarshallerTest extends QtiSmTestCase
{
    public function testUnmarshall()
    {
        /** @var ItemResult $itemResult */
        $itemResult = $this->createComponentFromXml('
            <itemResult identifier="fixture-identifier" datestamp="2018-06-27T09:41:45.529" sessionStatus="final" sequenceIndex="2">
                <responseVariable identifier="response-identifier" cardinality="single">
                    <candidateResponse>test</candidateResponse>
                </responseVariable>
                <templateVariable identifier="response-identifier" cardinality="single">
                    <value>test1</value>
                    <value>test2</value>
                </templateVariable>
                <candidateComment>comment-fixture</candidateComment>
            </itemResult>
        ');

        $this->assertInstanceOf(ItemResult::class, $itemResult);

        $this->assertEquals('fixture-identifier', $itemResult->getIdentifier()->getValue());
        $this->assertEquals('fixture-identifier', $itemResult->getIdentifier());

        $this->assertInstanceOf(DateTime::class, $itemResult->getDatestamp());

        $this->assertEquals('final', SessionStatus::getNameByConstant($itemResult->getSessionStatus()));

        $this->assertTrue($itemResult->hasSequenceIndex());
        $this->assertEquals(2, $itemResult->getSequenceIndex()->getValue());

        $this->assertTrue($itemResult->hasCandidateComment());
        $this->assertEquals('comment-fixture', $itemResult->getCandidateComment());

        $this->assertTrue($itemResult->hasItemVariables());
        $this->assertInstanceOf(ItemVariableCollection::class, $itemResult->getItemVariables());
        $this->assertEquals(2, $itemResult->getItemVariables()->count());
    }

    public function testUnmarshallMinimal()
    {
        /** @var ItemResult $itemResult */
        $itemResult = $this->createComponentFromXml('
            <itemResult identifier="fixture-identifier" datestamp="2018-06-27T09:41:45.529" sessionStatus="initial" />
        ');

        $this->assertInstanceOf(ItemResult::class, $itemResult);

        $this->assertEquals('fixture-identifier', $itemResult->getIdentifier()->getValue());
        $this->assertEquals('fixture-identifier', $itemResult->getIdentifier());

        $this->assertInstanceOf(DateTime::class, $itemResult->getDatestamp());

        $this->assertEquals('initial', SessionStatus::getNameByConstant($itemResult->getSessionStatus()));

        $this->assertFalse($itemResult->hasSequenceIndex());
        $this->assertNull($itemResult->getSequenceIndex());
        $this->assertFalse($itemResult->hasCandidateComment());
        $this->assertNull($itemResult->getCandidateComment());
        $this->assertFalse($itemResult->hasItemVariables());
        $this->assertNull($itemResult->getItemVariables());
    }

    public function testMarshall()
    {
        $component = new ItemResult(
            new QtiIdentifier('fixture-identifier'),
            new DateTime('2018-06-27T09:41:45.529'),
            1,
            new ItemVariableCollection(array(
                new ResultResponseVariable(
                    new QtiIdentifier('response-identifier'), 0, new CandidateResponse()
                ),
                new ResultTemplateVariable(
                    new QtiIdentifier('response-identifier'), 0
                )
            )),
            new QtiString('candidate-comment'),
            new QtiInteger(1)
        );

        /** @var DOMElement $element */
        $element = $this->getMarshallerFactory()->createMarshaller($component)->marshall($component);

        $this->assertInstanceOf(DOMElement::class, $element);

        $this->assertEquals($component->getQtiClassName(), $element->nodeName);

        $this->assertEquals('fixture-identifier', $element->getAttribute('identifier'));
        $this->assertTrue($element->hasAttribute('datestamp'));
        $this->assertEquals('initial', $element->getAttribute('sessionStatus'));
        $this->assertEquals(1, $element->getAttribute('sequenceIndex'));

        $this->assertEquals(1,$element->getElementsByTagName('responseVariable')->length);
        $this->assertEquals(1,$element->getElementsByTagName('templateVariable')->length);
    }

    public function testMarshallMinimal()
    {
        $component = new ItemResult(
            new QtiIdentifier('fixture-identifier'),
            new DateTime('2018-06-27T09:41:45.529'),
            1
        );

        /** @var DOMElement $element */
        $element = $this->getMarshallerFactory()->createMarshaller($component)->marshall($component);

        $this->assertInstanceOf(DOMElement::class, $element);

        $this->assertEquals($component->getQtiClassName(), $element->nodeName);

        $attributes = [];
        for ($i=0; $i<2; $i++) {
            $attributes[] = $element->attributes->item($i)->name;
        }
        $this->assertEmpty(array_diff($attributes, array('identifier', 'datestamp', 'sessionStatus')));

        $this->assertFalse($element->hasChildNodes());
    }

    public function testGetExpectedQtiClassName()
    {
        $component = new ItemResult(
            new QtiIdentifier('fixture-identifier'),
            new DateTime('2018-06-27T09:41:45.529'),
            1
        );
        $marshaller = $this->getMarshallerFactory()->createMarshaller($component);
        $this->assertEquals($component->getQtiClassName(), $marshaller->getExpectedQtiClassName());
    }
}