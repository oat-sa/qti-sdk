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
use qtism\common\datatypes\QtiIdentifier;
use qtism\common\enums\BaseType;
use qtism\common\enums\Cardinality;
use qtism\data\results\CandidateResponse;
use qtism\data\results\ResultResponseVariable;
use qtism\data\state\CorrectResponse;
use qtism\data\state\Value;
use qtism\data\state\ValueCollection;
use qtismtest\QtiSmTestCase;

/**
 * Class ResponseVariableMarshallerTest
 */
class ResponseVariableMarshallerTest extends QtiSmTestCase
{
    public function testUnmarshall(): void
    {
        /** @var ResultResponseVariable $resultResponseVariable */
        $resultResponseVariable = $this->createComponentFromXml('
            <responseVariable cardinality="single" identifier="fixture-identifier" baseType="string" choiceSequence="value-id-1">
                <correctResponse>
                    <value>fixture-value1</value>
                    <value>fixture-value2</value>
                </correctResponse>
                <candidateResponse>
                    <value fieldIdentifier="value-id-1">fixture-value1</value>
                    <value fieldIdentifier="value-id-2">fixture-value2</value>
                    <value fieldIdentifier="value-id-3">fixture-value3</value>
                </candidateResponse>
            </responseVariable>
        ');

        $this::assertInstanceOf(ResultResponseVariable::class, $resultResponseVariable);

        $this::assertEquals('fixture-identifier', $resultResponseVariable->getIdentifier()->getValue());
        $this::assertEquals('fixture-identifier', $resultResponseVariable->getIdentifier());

        $this::assertEquals(Cardinality::getConstantByName('single'), $resultResponseVariable->getCardinality());

        $this::assertInstanceOf(CandidateResponse::class, $resultResponseVariable->getCandidateResponse());
        $this::assertEquals(3, $resultResponseVariable->getCandidateResponse()->getValues()->count());

        $this::assertTrue($resultResponseVariable->hasBaseType());
        $this::assertEquals(BaseType::getConstantByName('string'), $resultResponseVariable->getBaseType());

        $this::assertTrue($resultResponseVariable->hasCorrectResponse());
        $this::assertInstanceOf(CorrectResponse::class, $resultResponseVariable->getCorrectResponse());
        $this::assertEquals(2, $resultResponseVariable->getCorrectResponse()->getValues()->count());

        $this::assertTrue($resultResponseVariable->hasChoiceSequence());
        $this::assertEquals('value-id-1', $resultResponseVariable->getChoiceSequence()->getValue());
    }

    public function testUnmarshallMinimal(): void
    {
        /** @var ResultResponseVariable $resultResponseVariable */
        $resultResponseVariable = $this->createComponentFromXml('
            <responseVariable cardinality="single" identifier="fixture-identifier">
                <candidateResponse>
                    <value>fixture-value1</value>
                    <value>fixture-value2</value>
                    <value>fixture-value3</value>
                </candidateResponse>
            </responseVariable>
        ');

        $this::assertInstanceOf(ResultResponseVariable::class, $resultResponseVariable);

        $this::assertEquals('fixture-identifier', $resultResponseVariable->getIdentifier()->getValue());
        $this::assertEquals('fixture-identifier', $resultResponseVariable->getIdentifier());

        $this::assertEquals(Cardinality::getConstantByName('single'), $resultResponseVariable->getCardinality());

        $this::assertInstanceOf(CandidateResponse::class, $resultResponseVariable->getCandidateResponse());
        $this::assertEquals(3, $resultResponseVariable->getCandidateResponse()->getValues()->count());

        $this::assertFalse($resultResponseVariable->hasBaseType());
        $this::assertNull($resultResponseVariable->getBaseType());

        $this::assertFalse($resultResponseVariable->hasCorrectResponse());
        $this::assertNull($resultResponseVariable->getCorrectResponse());

        $this::assertFalse($resultResponseVariable->hasChoiceSequence());
        $this::assertNull($resultResponseVariable->getChoiceSequence());
    }

    public function testMarshall(): void
    {
        $component = new ResultResponseVariable(
            new QtiIdentifier('fixture-identifier'),
            0,
            new CandidateResponse(new ValueCollection([
                new Value('fixture-value1'),
                new Value('fixture-value2'),
            ])),
            4,
            new CorrectResponse(new ValueCollection([
                new Value('fixture-value1'),
                new Value('fixture-value2'),
                new Value('fixture-value2'),
            ])),
            new QtiIdentifier('value-id-1')
        );

        /** @var DOMElement $element */
        $element = $this->getMarshallerFactory()->createMarshaller($component)->marshall($component);

        $this::assertInstanceOf(DOMElement::class, $element);

        $this::assertEquals($component->getQtiClassName(), $element->nodeName);

        $this::assertEquals('fixture-identifier', $element->getAttribute('identifier'));
        $this::assertEquals('single', $element->getAttribute('cardinality'));
        $this::assertEquals('string', $element->getAttribute('baseType'));
        $this::assertEquals('value-id-1', $element->getAttribute('choiceSequence'));

        $this::assertEquals(1, $element->getElementsByTagName('candidateResponse')->length);
        $this::assertEquals(2, $element->getElementsByTagName('candidateResponse')->item(0)->getElementsByTagName('value')->length);

        $this::assertEquals(1, $element->getElementsByTagName('correctResponse')->length);
        $this::assertEquals(3, $element->getElementsByTagName('correctResponse')->item(0)->getElementsByTagName('value')->length);
    }

    public function testMarshallMinimal(): void
    {
        $component = new ResultResponseVariable(
            new QtiIdentifier('fixture-identifier'),
            0,
            new CandidateResponse(new ValueCollection([
                new Value('fixture-value1'),
                new Value('fixture-value2'),
            ]))
        );

        /** @var DOMElement $element */
        $element = $this->getMarshallerFactory()->createMarshaller($component)->marshall($component);

        $this::assertInstanceOf(DOMElement::class, $element);

        $this::assertEquals($component->getQtiClassName(), $element->nodeName);

        $attributes = [];
        for ($i = 0; $i < 2; $i++) {
            $attributes[] = $element->attributes->item($i)->name;
        }
        $this::assertEmpty(array_diff($attributes, ['identifier', 'cardinality']));

        $this::assertEquals(1, $element->getElementsByTagName('candidateResponse')->length);
        $this::assertEquals(2, $element->getElementsByTagName('candidateResponse')->item(0)->getElementsByTagName('value')->length);

        $this::assertEquals(0, $element->getElementsByTagName('correctResponse')->length);
    }

    public function testGetExpectedQtiClassName(): void
    {
        $component = new ResultResponseVariable(
            new QtiIdentifier('fixture-identifier'),
            0,
            new CandidateResponse()
        );
        $marshaller = $this->getMarshallerFactory()->createMarshaller($component);
        $this::assertEquals($component->getQtiClassName(), $marshaller->getExpectedQtiClassName());
    }
}
