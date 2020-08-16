<?php

/**
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * Copyright (c) 2013-2020 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 * @license GPLv2
 */

namespace qtismtest\data\storage\xml\marshalling;

use DOMDocument;
use qtism\data\content\xhtml\Img;
use qtismtest\QtiSmTestCase;

class ImgMarshallerTest extends QtiSmTestCase
{
    public function testMarshall21()
    {
        $img = new Img('my/image.png', "An Image...", "my-img");
        $img->setClass('beautiful');
        $img->setHeight('40%');
        $img->setWidth(30);
        $img->setLang('en-YO');
        $img->setLongdesc("A Long Description...");
        $img->setXmlBase('/home/jerome');

        // aria-* attributes are ignored in QTI 2.1
        $img->setAriaOwns('IDFREF');

        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($img);
        $element = $marshaller->marshall($img);

        $dom = new DOMDocument('1.0', 'UTF-8');
        $element = $dom->importNode($element, true);
        $this->assertEquals('<img src="my/image.png" alt="An Image..." width="30" height="40%" longdesc="A Long Description..." xml:base="/home/jerome" id="my-img" class="beautiful" xml:lang="en-YO"/>', $dom->saveXML($element));
    }

    public function testMarshall22()
    {
        $img = new Img('my/image.png', "An Image...", "my-img");

        // aria-* attributes are NOT ignored in QTI 2.2.1
        $img->setAriaOwns('IDREF');
        $img->setAriaHidden(true);

        // aria-flowsto is prefered instead of aria-flowto (see XSD) for img.
        $img->setAriaFlowTo('IDREF2');

        $marshaller = $this->getMarshallerFactory('2.2.1')->createMarshaller($img);
        $element = $marshaller->marshall($img);

        $dom = new DOMDocument('1.0', 'UTF-8');
        $element = $dom->importNode($element, true);
        $this->assertEquals('<img src="my/image.png" alt="An Image..." id="my-img" aria-flowsto="IDREF2" aria-owns="IDREF" aria-hidden="true"/>', $dom->saveXML($element));

        // In case of aria-hidden is false, it is not rendered.
        $img->setAriaHidden(false);
        $element = $marshaller->marshall($img);
        $element = $dom->importNode($element, true);

        $this->assertEquals('<img src="my/image.png" alt="An Image..." id="my-img" aria-flowsto="IDREF2" aria-owns="IDREF"/>', $dom->saveXML($element));
    }

    public function testUnmarshall21()
    {
        $element = $this->createDOMElement('
            <img xml:base="/home/jerome" src="my/image.png" alt="An Image..." width="30" height="40%" longdesc="A Long Description..." id="my-img" class="beautiful" xml:lang="en-YO" aria-owns="IDREF" aria-hidden="true"/>
	    ');

        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($element);

        /** @var Img $img */
        $img = $marshaller->unmarshall($element);

        $this->assertInstanceOf('qtism\\data\\content\\xhtml\\Img', $img);
        $this->assertEquals('my/image.png', $img->getSrc());
        $this->assertEquals('An Image...', $img->getAlt());
        $this->assertSame(30, $img->getWidth());
        $this->assertEquals('40%', $img->getHeight());
        $this->assertEquals('A Long Description...', $img->getLongDesc());
        $this->assertEquals('my-img', $img->getId());
        $this->assertEquals('beautiful', $img->getClass());
        $this->assertEquals('en-YO', $img->getLang());
        $this->assertEquals('/home/jerome', $img->getXmlBase());

        // aria-* attributes are ignored in QTI 2.1
        $this->assertFalse($img->hasAriaOwns());
        $this->assertFalse($img->getAriaHidden());
        $this->assertFalse($img->hasAriaHidden());
    }

    public function testUnmarshall22()
    {
        $element = $this->createDOMElement('
            <img xml:base="/home/jerome" src="my/image.png" alt="An Image..." aria-owns="IDREF" aria-flowsto="IDREF2" aria-hidden="true"/>
	    ');

        $marshaller = $this->getMarshallerFactory('2.2.2')->createMarshaller($element);

        /** @var Img $img */
        $img = $marshaller->unmarshall($element);

        // aria-* attributes are NOT ignored in QTI 2.1
        $this->assertTrue($img->hasAriaOwns());
        $this->assertEquals('IDREF2', $img->getAriaFlowTo());
        $this->assertTrue($img->hasAriaHidden());
        $this->assertTrue($img->getAriaHidden());
    }

    public function testUnmarshall22PreferFlowsTo()
    {
        $element = $this->createDOMElement('
            <img src="my/image.png" alt="An Image..." aria-owns="IDREF" aria-flowsto="IDREF2" aria-flowto="IDREF3"/>
	    ');

        $marshaller = $this->getMarshallerFactory('2.2.2')->createMarshaller($element);

        /** @var Img $img */
        $img = $marshaller->unmarshall($element);

        // For img components, we prefer aria-flowsto.
        $this->assertEquals('IDREF2', $img->getAriaFlowTo());
    }

    public function testUnmarshall22FallbackFlowTo()
    {
        $element = $this->createDOMElement('
            <img src="my/image.png" alt="An Image..." aria-owns="IDREF" aria-flowto="IDREF3"/>
	    ');

        $marshaller = $this->getMarshallerFactory('2.2.2')->createMarshaller($element);

        /** @var Img $img */
        $img = $marshaller->unmarshall($element);

        // For img components, we prefer aria-flowsto. However, we fall back
        // to aria-flowto in cas it exists.
        $this->assertEquals('IDREF3', $img->getAriaFlowTo());
    }

    /**
     * @depends testUnmarshall21
     */
    public function testUnmarshallPercentage()
    {
        $element = $this->createDOMElement('
            <img src="my/image.png" alt="An Image..." width="30%" height="40%"/>
	    ');

        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($element);
        $img = $marshaller->unmarshall($element);

        $this->assertEquals('40%', $img->getHeight());
        $this->assertEquals('30%', $img->getWidth());
    }

    /**
     * @depends testUnmarshall21
     */
    public function testUnmarshallMissingSrc()
    {
        $element = $this->createDOMElement('
            <img/>
	    ');

        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($element);

        $this->setExpectedException(
            'qtism\\data\\storage\\xml\\marshalling\\UnmarshallingException',
            "The 'mandatory' attribute 'src' is missing from element 'img'."
        );

        $marshaller->unmarshall($element);
    }
}
