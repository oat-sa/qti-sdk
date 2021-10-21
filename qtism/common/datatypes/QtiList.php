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

namespace qtism\common\datatypes;

use qtism\common\enums\BaseType;
use qtism\common\enums\Cardinality;

class QtiList implements QtiDatatype
{
    /**
     * A value from the BaseType enumeration.
     *
     * @var int
     */
    private $baseType;

    /**
     * An array of QTI base type elements.
     *
     * @var array
     */
    private $items;

    /**
     * Create a new QtiList object.
     *
     * @param string $type
     * @param array $items
     */
    public function __construct(string $type, array $items)
    {
        $this->setBaseType($type);
        $this->setItems($items);
    }

    /**
     * Return array of items that QtiList contains.
     *
     * @return array
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * Check if two instance of QtiList are equal.
     *
     * @param mixed $obj
     * @return bool
     */
    public function equals($obj)
    {
        if ($obj instanceof QtiList) {
            $originalItems = $obj->getItems();
            $itemsToCompare = $this->getItems();

            for ($i = 0; $i < count($originalItems); $i++) {
                if (!$originalItems[$i]->equals($itemsToCompare[$i])) {
                    return false;
                }
            }

            return true;
        } else {
            return false;
        }
    }

    /**
     * Get the baseType of the value. This method systematically returns
     * the BaseType value.
     *
     * @return int A value from the BaseType enumeration.
     */
    public function getBaseType()
    {
        return $this->baseType;
    }

    /**
     * Get the cardinality of the value. This method systematically returns
     * the Cardinality::MULTIPLE value.
     *
     * @return int
     */
    public function getCardinality()
    {
        return Cardinality::MULTIPLE;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return "[" . implode(', ', $this->items) . "]";
    }

    /**
     * Set the baseType of the value.
     *
     * @param string $type
     */
    private function setBaseType(string $type)
    {
        $this->baseType = BaseType::getConstantByName($type);
    }

    /**
     * Set an array of QTI base type elemets.
     *
     * @param array $items
     */
    private function setItems(array $items)
    {
        $this->items = $items;
    }
}
