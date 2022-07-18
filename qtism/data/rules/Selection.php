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

namespace qtism\data\rules;

use InvalidArgumentException;
use qtism\common\dom\SerializableDomDocument;
use qtism\data\ExternalQtiComponent;
use qtism\data\IExternal;
use qtism\data\QtiComponent;
use qtism\data\QtiComponentCollection;
use RuntimeException;

/**
 * From IMS QTI:
 *
 * The selection class specifies the rules used to select the child elements of a
 * section for each test session. If no selection rules are given we assume that
 * all elements are to be selected.
 */
class Selection extends QtiComponent implements IExternal
{
    /**
     * @var string
     * @qtism-bean-property
     */
    private $xmlString = '';

    /**
     * @var ExternalQtiComponent
     */
    private $externalComponent = null;

    /**
     * The number of child elements to be selected.
     *
     * @var int
     * @qtism-bean-property
     */
    private $select;

    /**
     * Selection (combinations) with or without replacement.
     *
     * @var bool
     * @qtism-bean-property
     */
    private $withReplacement = false;

    /**
     * Create a new instance of selection.
     *
     * @param int $select The number of child elements to be selected.
     * @param bool $withReplacement Selection (combinations) with or without replacement.
     * @param string $xmlString
     * @throws InvalidArgumentException If $select is not a valid integer or if $withReplacement is not a valid boolean.
     */
    public function __construct($select, $withReplacement = false, $xmlString = '')
    {
        $this->setSelect($select);
        $this->setWithReplacement($withReplacement);

        if ($xmlString !== '') {
            $this->setXmlString($xmlString);
            $this->setExternalComponent(new ExternalQtiComponent($xmlString));
        }
    }

    /**
     * Get the number of child elements to be selected.
     *
     * @return int An integer.
     */
    public function getSelect()
    {
        return $this->select;
    }

    /**
     * Set the number of child elements to be selected.
     *
     * @param int $select An integer.
     * @throws InvalidArgumentException If $select is not an integer.
     */
    public function setSelect($select)
    {
        if (is_int($select)) {
            $this->select = $select;
        } else {
            $msg = "Select must be an integer, '" . gettype($select) . "' given.";
            throw new InvalidArgumentException($msg);
        }
    }

    /**
     * Is the selection of items with or without replacements?
     *
     * @return bool true if it must be with replacements, false otherwise.
     */
    public function isWithReplacement()
    {
        return $this->withReplacement;
    }

    /**
     * Set if the selection of items must be with or without replacements.
     *
     * @param bool $withReplacement true if it must be with replacements, false otherwise.
     * @throws InvalidArgumentException If $withReplacement is not a boolean.
     */
    public function setWithReplacement($withReplacement)
    {
        if (is_bool($withReplacement)) {
            $this->withReplacement = $withReplacement;
        } else {
            $msg = "WithReplacement must be a boolean, '" . gettype($withReplacement) . "' given.";
            throw new InvalidArgumentException($msg);
        }
    }

    /**
     * Set the xml string content of the selection itself and its content.
     *
     * @param string $xmlString
     */
    public function setXmlString($xmlString)
    {
        $this->xmlString = $xmlString;

        if ($this->externalComponent !== null) {
            $this->getExternalComponent()->setXmlString($xmlString);
        }
    }

    /**
     * Get the xml string content of the selection itself and its content.
     *
     * @return string
     */
    public function getXmlString()
    {
        return $this->xmlString;
    }

    /**
     * Set the encapsulated external component.
     *
     * @param ExternalQtiComponent $externalComponent
     */
    private function setExternalComponent(ExternalQtiComponent $externalComponent)
    {
        $this->externalComponent = $externalComponent;
    }

    /**
     * Get the encapsulated external component.
     *
     * @return ExternalQtiComponent
     */
    private function getExternalComponent()
    {
        return $this->externalComponent;
    }

    /**
     * Get the XML content of the selection itself and its content.
     *
     * return A DOMDocument (serializable) object representing the selection itself or null if there is no external component.
     * @throws RuntimeException If the XML content of the selection and/or its content cannot be transformed into a valid DOMDocument.
     */
    public function getXml(): ?SerializableDomDocument
    {
        if (($externalComponent = $this->getExternalComponent()) !== null) {
            return $this->getExternalComponent()->getXml();
        } else {
            return null;
        }
    }

    /**
     * @return string
     */
    public function getQtiClassName()
    {
        return 'selection';
    }

    /**
     * @return QtiComponentCollection
     */
    public function getComponents()
    {
        return new QtiComponentCollection();
    }
}
