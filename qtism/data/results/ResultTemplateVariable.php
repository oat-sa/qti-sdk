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

namespace qtism\data\results;

use qtism\common\datatypes\QtiIdentifier;
use qtism\data\QtiComponentCollection;
use qtism\data\state\ValueCollection;

/**
 * Class ResultTemplateVariable
 *
 * The Item result information related to a 'Template Variable'.
 *
 * @package qtism\data\results
 */
class ResultTemplateVariable extends ItemVariable
{
    /**
     * The value(s) of the template variable.
     * The order of the values is significant only if the template variable was declared with ordered cardinality.
     *
     * Multiplicity [0,*]
     * @var ValueCollection
     */
    protected $values=null;

    /**
     * ResultTemplateVariable constructor.
     *
     * @param QtiIdentifier $identifier
     * @param $cardinality
     * @param null $baseType
     * @param ValueCollection|null $values
     */
    public function __construct(QtiIdentifier $identifier, $cardinality, $baseType=null, ValueCollection $values=null)
    {
        parent::__construct($identifier, $cardinality, $baseType);
        $this->setValues($values);
    }


    /**
     * Returns the QTI class name as per QTI 2.1 specification.
     *
     * @return string A QTI class name.
     */
    public function getQtiClassName()
    {
        return 'templateVariable';
    }

    /**
     * Get the direct child components of this one.
     *
     * @return QtiComponentCollection A collection of QtiComponent objects.
     */
    public function getComponents()
    {
        $components = [];
        if ($this->hasValues()) {
            $components = $this->getValues()->getArrayCopy();
        }
        return new QtiComponentCollection($components);
    }

    /**
     * Get the template values
     *
     * @return ValueCollection
     */
    public function getValues()
    {
        return $this->values;
    }

    /**
     * Set the template values
     *
     * @param ValueCollection $values
     * @return $this
     */
    public function setValues(ValueCollection $values=null)
    {
        $this->values = $values;
        return $this;
    }

    /**
     * Check if the values are set
     *
     * @return bool
     */
    public function hasValues()
    {
        return !is_null($this->values);
    }
}