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
use qtism\data\content\xhtml\tables\ColCollection;
use qtism\data\content\xhtml\tables\ColgroupCollection;
use qtism\data\content\xhtml\tables\Table;
use qtism\data\content\xhtml\tables\TbodyCollection;
use qtism\data\QtiComponent;

/**
 * Marshalling/Unmarshalling implementation for Table.
 */
class TableMarshaller extends Marshaller
{
    /**
     * Marshall a Table object into a DOMElement object.
     *
     * @param QtiComponent $component A Table object.
     * @return DOMElement The according DOMElement object.
     * @throws MarshallerNotFoundException
     * @throws MarshallingException
     */
    protected function marshall(QtiComponent $component): DOMElement
    {
        $element = $this->createElement($component);

        if ($component->hasSummary() === true) {
            $this->setDOMElementAttribute($element, 'summary', $component->getSummary());
        }

        if ($component->hasXmlBase() === true) {
            self::setXmlBase($element, $component->getXmlBase());
        }

        if ($component->hasCaption() === true) {
            $caption = $component->getCaption();
            $marshaller = $this->getMarshallerFactory()->createMarshaller($caption);
            $element->appendChild($marshaller->marshall($caption));
        }

        $cols = $component->getCols();
        if (count($cols) > 0) {
            foreach ($cols as $c) {
                $marshaller = $this->getMarshallerFactory()->createMarshaller($c);
                $element->appendChild($marshaller->marshall($c));
            }
        }

        $colgroups = $component->getColgroups();
        if (count($colgroups) > 0) {
            foreach ($colgroups as $c) {
                $marshaller = $this->getMarshallerFactory()->createMarshaller($c);
                $element->appendChild($marshaller->marshall($c));
            }
        }

        if ($component->hasThead() === true) {
            $thead = $component->getThead();
            $marshaller = $this->getMarshallerFactory()->createMarshaller($thead);
            $element->appendChild($marshaller->marshall($thead));
        }

        if ($component->hasTfoot() === true) {
            $tfoot = $component->getTfoot();
            $marshaller = $this->getMarshallerFactory()->createMarshaller($tfoot);
            $element->appendChild($marshaller->marshall($tfoot));
        }

        foreach ($component->getTbodies() as $tbody) {
            $marshaller = $this->getMarshallerFactory()->createMarshaller($tbody);
            $element->appendChild($marshaller->marshall($tbody));
        }

        $this->fillElement($element, $component);

        return $element;
    }

    /**
     * Unmarshall a DOMElement object corresponding to an XHTML table element.
     *
     * @param DOMElement $element A DOMElement object.
     * @return Table A Table object.
     * @throws MarshallerNotFoundException
     * @throws UnmarshallingException
     */
    protected function unmarshall(DOMElement $element): Table
    {
        $tbodyElts = $this->getChildElementsByTagName($element, 'tbody');

        if (count($tbodyElts) > 0) {
            $tbodies = new TbodyCollection();

            foreach ($tbodyElts as $tbodyElt) {
                $marshaller = $this->getMarshallerFactory()->createMarshaller($tbodyElt);
                $tbodies[] = $marshaller->unmarshall($tbodyElt);
            }

            $component = new Table($tbodies);

            if (($summary = $this->getDOMElementAttributeAs($element, 'summary')) !== null) {
                $component->setSummary($summary);
            }

            if (($xmlBase = self::getXmlBase($element)) !== false) {
                $component->setXmlBase($xmlBase);
            }

            $captionElts = $this->getChildElementsByTagName($element, 'caption');
            if (count($captionElts) > 0) {
                $marshaller = $this->getMarshallerFactory()->createMarshaller($captionElts[0]);
                $component->setCaption($marshaller->unmarshall($captionElts[0]));
            }

            $colElts = $this->getChildElementsByTagName($element, 'col');
            if (count($colElts) > 0) {
                $cols = new ColCollection();
                foreach ($colElts as $c) {
                    $marshaller = $this->getMarshallerFactory()->createMarshaller($c);
                    $cols[] = $marshaller->unmarshall($c);
                }
                $component->setCols($cols);
            }

            $colgroupElts = $this->getChildElementsByTagName($element, 'colgroup');
            if (count($colgroupElts) > 0) {
                $colgroups = new ColgroupCollection();
                foreach ($colgroupElts as $c) {
                    $marshaller = $this->getMarshallerFactory()->createMarshaller($c);
                    $colgroups[] = $marshaller->unmarshall($c);
                }
                $component->setColgroups($colgroups);
            }

            $theadElts = $this->getChildElementsByTagName($element, 'thead');
            if (count($theadElts) > 0) {
                $marshaller = $this->getMarshallerFactory()->createMarshaller($theadElts[0]);
                $component->setThead($marshaller->unmarshall($theadElts[0]));
            }

            $tfootElts = $this->getChildElementsByTagName($element, 'tfoot');
            if (count($tfootElts) > 0) {
                $marshaller = $this->getMarshallerFactory()->createMarshaller($tfootElts[0]);
                $component->setTfoot($marshaller->unmarshall($tfootElts[0]));
            }

            $this->fillBodyElement($component, $element);

            return $component;
        } else {
            $msg = "A 'table' element must contain at lease one 'tbody' element.";
            throw new UnmarshallingException($msg, $element);
        }
    }

    /**
     * @return string
     */
    public function getExpectedQtiClassName(): string
    {
        return 'table';
    }
}
