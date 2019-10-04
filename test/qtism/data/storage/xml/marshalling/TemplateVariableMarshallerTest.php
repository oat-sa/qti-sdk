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

use qtism\common\enums\Cardinality;
use qtism\common\enums\BaseType;
use qtism\data\state\Value;
use qtism\data\state\ValueCollection;
use qtism\common\datatypes\QtiIdentifier;
use qtism\data\results\ResultTemplateVariable;

require_once (dirname(__FILE__) . '/../../../../../QtiSmTestCase.php');

class TemplateVariableMarshallerTest extends QtiSmTestCase
{
    public function testUnmarshall()
    {
        /** @var ResultTemplateVariable $resultTemplateVariable */
        $resultTemplateVariable = $this->createComponentFromXml('
            <templateVariable cardinality="single" identifier="fixture-identifier" baseType="string">
                <value>fixture-value1</value>
                <value>fixture-value2</value>
                <value>fixture-value3</value>
            </templateVariable>
        ');

        $this->assertInstanceOf(ResultTemplateVariable::class, $resultTemplateVariable);

        $this->assertEquals('fixture-identifier', $resultTemplateVariable->getIdentifier()->getValue());
        $this->assertEquals('fixture-identifier', $resultTemplateVariable->getIdentifier());

        $this->assertEquals(Cardinality::getConstantByName('single'), $resultTemplateVariable->getCardinality());

        $this->assertTrue($resultTemplateVariable->hasBaseType());
        $this->assertEquals(BaseType::getConstantByName('string'), $resultTemplateVariable->getBaseType());

        $this->assertTrue($resultTemplateVariable->hasValues());
        $this->assertInstanceOf(ValueCollection::class, $resultTemplateVariable->getValues());
        $this->assertEquals(3, $resultTemplateVariable->getValues()->count());
    }

    public function testUnmarshallMinimal()
    {
        /** @var ResultTemplateVariable $resultTemplateVariable */
        $resultTemplateVariable = $this->createComponentFromXml('
            <templateVariable cardinality="single" identifier="fixture-identifier"/>
        ');

        $this->assertInstanceOf(ResultTemplateVariable::class, $resultTemplateVariable);

        $this->assertEquals('fixture-identifier', $resultTemplateVariable->getIdentifier()->getValue());
        $this->assertEquals('fixture-identifier', $resultTemplateVariable->getIdentifier());

        $this->assertEquals(Cardinality::getConstantByName('single'), $resultTemplateVariable->getCardinality());

        $this->assertFalse($resultTemplateVariable->hasBaseType());
        $this->assertNull($resultTemplateVariable->getBaseType());

        $this->assertFalse($resultTemplateVariable->hasValues());
        $this->assertNull($resultTemplateVariable->getValues());
    }

    public function testMarshall()
    {
        $component = new ResultTemplateVariable(
            new QtiIdentifier('fixture-identifier'),
            0,
            4,
            new ValueCollection(array(
                new Value('fixture-value1'),
                new Value('fixture-value2'),
            ))
        );

        /** @var DOMElement $element */
        $element = $this->getMarshallerFactory()->createMarshaller($component)->marshall($component);

        $this->assertInstanceOf(DOMElement::class, $element);

        $this->assertEquals($component->getQtiClassName(), $element->nodeName);

        $this->assertEquals('fixture-identifier', $element->getAttribute('identifier'));
        $this->assertEquals('single', $element->getAttribute('cardinality'));
        $this->assertEquals('string', $element->getAttribute('baseType'));

        $this->assertEquals(2,$element->getElementsByTagName('value')->length);
    }

    public function testMarshallMinimal()
    {
        $component = new ResultTemplateVariable(
            new QtiIdentifier('fixture-identifier'),
            0
        );

        /** @var DOMElement $element */
        $element = $this->getMarshallerFactory()->createMarshaller($component)->marshall($component);

        $this->assertInstanceOf(DOMElement::class, $element);

        $this->assertEquals($component->getQtiClassName(), $element->nodeName);

        $attributes = [];
        for ($i=0; $i<2; $i++) {
            $attributes[] = $element->attributes->item($i)->name;
        }
        $this->assertEmpty(array_diff($attributes, array('identifier', 'cardinality')));

        $this->assertEquals(0,$element->getElementsByTagName('value')->length);
    }

    public function testGetExpectedQtiClassName()
    {
        $component = new ResultTemplateVariable(
            new QtiIdentifier('fixture-identifier'),
            0
        );
        $marshaller = $this->getMarshallerFactory()->createMarshaller($component);
        $this->assertEquals($component->getQtiClassName(), $marshaller->getExpectedQtiClassName());
    }
}