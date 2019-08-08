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

namespace qtismtest\data\storage\xml\marshalling;

use qtism\data\results\Context;
use qtism\data\results\SessionIdentifierCollection;
use qtism\data\results\SessionIdentifier;
use qtism\common\datatypes\QtiIdentifier;
use qtism\common\datatypes\QtiUri;
use qtismtest\QtiSmTestCase;

class ContextMarshallerTest extends QtiSmTestCase
{
    public function testUnmarshall()
    {
        /** @var Context $context */
        $context = $this->createComponentFromXml('
            <context sourcedId="fixture-sourcedId">
                <sessionIdentifier sourceID="sessionIdentifier1-sourceID" identifier="sessionIdentifier1-id"/>
                <sessionIdentifier sourceID="sessionIdentifier2-sourceID" identifier="sessionIdentifier2-id"/>
            </context>
        ');

        $this->assertInstanceOf(Context::class, $context);

        $this->assertInstanceOf(QtiIdentifier::class, $context->getSourcedId());
        $this->assertTrue($context->hasSourcedId());
        $this->assertEquals('fixture-sourcedId', $context->getSourcedId()->getValue());
        $this->assertEquals('fixture-sourcedId', $context->getSourcedId());

        $this->assertTrue($context->hasSessionIdentifiers());
        $this->assertInstanceOf(SessionIdentifierCollection::class, $context->getSessionIdentifiers());
        $this->assertEquals(2, $context->getSessionIdentifiers()->count());
    }

    public function testUnmarshallMinimal()
    {
        /** @var Context $context */
        $context = $this->createComponentFromXml('
            <context />
        ');

        $this->assertInstanceOf(Context::class, $context);

        $this->assertFalse($context->hasSourcedId());
        $this->assertNull($context->getSourcedId());

        $this->assertFalse($context->hasSessionIdentifiers());
        $this->assertNull($context->getSessionIdentifiers());
    }

    public function testMarshall()
    {
        $sourcedId = 'fixture-sourcedId';

        $component = new Context(
            new QtiIdentifier($sourcedId),
            new SessionIdentifierCollection(array(
                new SessionIdentifier(new QtiUri('sessionIdentifier1-sourceID'), new QtiIdentifier('sessionIdentifier1-id')),
                new SessionIdentifier(new QtiUri('sessionIdentifier2-sourceID'), new QtiIdentifier('sessionIdentifier2-id')),
            ))
        );

        /** @var DOMElement $element */
        $element = $this->getMarshallerFactory()->createMarshaller($component)->marshall($component);

        $this->assertInstanceOf(\DOMElement::class, $element);

        $this->assertEquals($component->getQtiClassName(), $element->nodeName);
        $this->assertEquals($sourcedId, $element->getAttribute('sourcedId'));

        $this->assertEquals(2, $element->getElementsByTagName('sessionIdentifier')->length);
        /** @var DOMElement $node */
        foreach ($element->childNodes as $node) {
            $this->assertEquals('sessionIdentifier', $node->nodeName);
        }
    }

    public function testMarshallMinimal()
    {
        $component = new Context();

        /** @var DOMElement $element */
        $element = $this->getMarshallerFactory()->createMarshaller($component)->marshall($component);

        $this->assertInstanceOf(\DOMElement::class, $element);

        $this->assertEquals($component->getQtiClassName(), $element->nodeName);
        $this->assertFalse($element->hasAttributes());
        $this->assertFalse($element->hasChildNodes());
    }

    public function testGetExpectedQtiClassName()
    {
        $component = new Context();
        $marshaller = $this->getMarshallerFactory()->createMarshaller($component);
        $this->assertEquals($component->getQtiClassName(), $marshaller->getExpectedQtiClassName());
    }
}