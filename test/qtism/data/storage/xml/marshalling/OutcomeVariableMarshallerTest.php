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

use qtism\common\datatypes\QtiFloat;
use qtism\common\datatypes\QtiIdentifier;
use qtism\common\datatypes\QtiString;
use qtism\common\datatypes\QtiUri;
use qtism\common\enums\BaseType;
use qtism\common\enums\Cardinality;
use qtism\data\results\ResultOutcomeVariable;
use qtism\data\state\Value;
use qtism\data\state\ValueCollection;
use qtism\data\View;

require_once __DIR__ . '/../../../../../QtiSmTestCase.php';

class OutcomeVariableMarshallerTest extends QtiSmTestCase
{
    public function testUnmarshall()
    {
        /** @var ResultOutcomeVariable $resultOutcomeVariable */
        $resultOutcomeVariable = $this->createComponentFromXml('
            <outcomeVariable 
                cardinality="single" 
                identifier="fixture-identifier" 
                baseType="string" 
                view="candidate"
                interpretation="fixture-interpretation"
                longInterpretation="http://fixture-interpretation"
                normalMinimum="2"
                normalMaximum="3"
                masteryValue="4"
            >
                <value>fixture-value1</value>
                <value>fixture-value2</value>
                <value>fixture-value3</value>
            </outcomeVariable>
        ');

        $this->assertInstanceOf(ResultOutcomeVariable::class, $resultOutcomeVariable);

        $this->assertEquals('fixture-identifier', $resultOutcomeVariable->getIdentifier()->getValue());
        $this->assertEquals('fixture-identifier', $resultOutcomeVariable->getIdentifier());

        $this->assertEquals(Cardinality::getConstantByName('single'), $resultOutcomeVariable->getCardinality());

        $this->assertTrue($resultOutcomeVariable->hasBaseType());
        $this->assertEquals(BaseType::getConstantByName('string'), $resultOutcomeVariable->getBaseType());

        $this->assertTrue($resultOutcomeVariable->hasView());
        $this->assertEquals(View::getConstantByName('candidate'), $resultOutcomeVariable->getView());

        $this->assertTrue($resultOutcomeVariable->hasInterpretation());
        $this->assertEquals('fixture-interpretation', $resultOutcomeVariable->getInterpretation());

        $this->assertTrue($resultOutcomeVariable->hasLongInterpretation());
        $this->assertEquals('http://fixture-interpretation', $resultOutcomeVariable->getLongInterpretation()->getValue());

        $this->assertTrue($resultOutcomeVariable->hasNormalMinimum());
        $this->assertEquals(2, $resultOutcomeVariable->getNormalMinimum()->getValue());

        $this->assertTrue($resultOutcomeVariable->hasNormalMaximum());
        $this->assertEquals(3, $resultOutcomeVariable->getNormalMaximum()->getValue());

        $this->assertTrue($resultOutcomeVariable->hasMasteryValue());
        $this->assertEquals(4, $resultOutcomeVariable->getMasteryValue()->getValue());

        $this->assertTrue($resultOutcomeVariable->hasValues());
        $this->assertInstanceOf(ValueCollection::class, $resultOutcomeVariable->getValues());
        $this->assertEquals(3, $resultOutcomeVariable->getValues()->count());
    }

    public function testUnmarshallMinimal()
    {
        /** @var ResultOutcomeVariable $resultOutcomeVariable */
        $resultOutcomeVariable = $this->createComponentFromXml('
            <outcomeVariable cardinality="single" identifier="fixture-identifier" />
        ');

        $this->assertInstanceOf(ResultOutcomeVariable::class, $resultOutcomeVariable);

        $this->assertEquals('fixture-identifier', $resultOutcomeVariable->getIdentifier()->getValue());
        $this->assertEquals('fixture-identifier', $resultOutcomeVariable->getIdentifier());

        $this->assertEquals(Cardinality::getConstantByName('single'), $resultOutcomeVariable->getCardinality());

        $this->assertFalse($resultOutcomeVariable->hasBaseType());
        $this->assertNull($resultOutcomeVariable->getBaseType());

        $this->assertFalse($resultOutcomeVariable->hasView());
        $this->assertNull($resultOutcomeVariable->getView());

        $this->assertFalse($resultOutcomeVariable->hasInterpretation());
        $this->assertNull($resultOutcomeVariable->getInterpretation());

        $this->assertFalse($resultOutcomeVariable->hasLongInterpretation());
        $this->assertNull($resultOutcomeVariable->getLongInterpretation());

        $this->assertFalse($resultOutcomeVariable->hasNormalMinimum());
        $this->assertNull($resultOutcomeVariable->getNormalMinimum());

        $this->assertFalse($resultOutcomeVariable->hasNormalMaximum());
        $this->assertNull($resultOutcomeVariable->getNormalMaximum());

        $this->assertFalse($resultOutcomeVariable->hasMasteryValue());
        $this->assertNull($resultOutcomeVariable->getMasteryValue());

        $this->assertFalse($resultOutcomeVariable->hasValues());
        $this->assertNull($resultOutcomeVariable->getValues());
    }

    public function testMarshall()
    {
        $component = new ResultOutcomeVariable(
            new QtiIdentifier('fixture-identifier'),
            0,
            4,
            new ValueCollection([
                new Value('fixture-value1'),
                new Value('fixture-value2'),
            ]),
            1,
            new QtiString('fixture-interpretation'),
            new QtiUri('http://long-interpretation'),
            new QtiFloat(2.0),
            new QtiFloat(3.0),
            new QtiFloat(4.0)
        );

        /** @var DOMElement $element */
        $element = $this->getMarshallerFactory()->createMarshaller($component)->marshall($component);

        $this->assertInstanceOf(DOMElement::class, $element);

        $this->assertEquals($component->getQtiClassName(), $element->nodeName);

        $this->assertEquals('fixture-identifier', $element->getAttribute('identifier'));
        $this->assertEquals('single', $element->getAttribute('cardinality'));
        $this->assertEquals('string', $element->getAttribute('baseType'));

        $this->assertEquals(2, $element->getElementsByTagName('value')->length);
    }

    public function testMarshallMinimal()
    {
        $component = new ResultOutcomeVariable(
            new QtiIdentifier('fixture-identifier'),
            0
        );

        /** @var DOMElement $element */
        $element = $this->getMarshallerFactory()->createMarshaller($component)->marshall($component);

        $this->assertInstanceOf(DOMElement::class, $element);

        $this->assertEquals($component->getQtiClassName(), $element->nodeName);

        $attributes = [];
        for ($i = 0; $i < 2; $i++) {
            $attributes[] = $element->attributes->item($i)->name;
        }
        $this->assertEmpty(array_diff($attributes, ['identifier', 'cardinality']));

        $this->assertEquals(0, $element->getElementsByTagName('value')->length);
    }

    public function testGetExpectedQtiClassName()
    {
        $component = new ResultOutcomeVariable(
            new QtiIdentifier('fixture-identifier'),
            0
        );
        $marshaller = $this->getMarshallerFactory()->createMarshaller($component);
        $this->assertEquals($component->getQtiClassName(), $marshaller->getExpectedQtiClassName());
    }
}
