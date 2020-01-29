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

namespace qtism\data\results;

use oat\dtms\DateTime;
use qtism\common\datatypes\QtiIdentifier;
use qtism\data\QtiComponent;
use qtism\data\QtiComponentCollection;

/**
 * Class TestResult
 *
 * The container for the Test result. When a test result is given the following item results must relate only to items
 * that were selected for presentation as part of the corresponding test session.
 * Furthermore, all items selected for presentation should be reported with a corresponding itemResult.
 */
class TestResult extends QtiComponent
{
    /**
     * The identifier of the test for which this is a result.
     *
     * Multiplicity [1]
     *
     * @var QtiIdentifier
     */
    protected $identifier;

    /**
     * The date stamp of when this result was recorded.
     *
     * Multiplicity [1]
     *
     * @var DateTime
     */
    protected $datestamp;

    /**
     * The values of the test outcomes and any durations that were tracked during the test.
     * Note that durations are reported as built-in test-level response variables with name duration.
     * The duration of individual test parts or sections being distinguished by prefixing them
     * with the associated identifier as described in Assessment Test, Section and Item Information Model.
     * This is an abstract attribute and so a child named 'itemVariable' will not appear in an instance.
     *
     * Multiplicity [0,*]
     *
     * @var ItemVariable
     */
    protected $itemVariables = null;

    /**
     * TestResult constructor.
     *
     * @param string $identifier The identifier of TestResult
     * @param DateTime $datestamp The timestamp when testResult has been registered
     * @param ItemVariableCollection|null $itemVariables All variables
     */
    public function __construct($identifier, DateTime $datestamp, ItemVariableCollection $itemVariables = null)
    {
        $this->setIdentifier($identifier);
        $this->setDatestamp($datestamp);
        $this->setItemVariables($itemVariables);
    }

    /**
     * Returns the QTI class name as per QTI 2.1 specification.
     *
     * @return string A QTI class name.
     */
    public function getQtiClassName()
    {
        return 'testResult';
    }

    /**
     * Get the direct child components of this one.
     *
     * @return QtiComponentCollection A collection of QtiComponent objects.
     */
    public function getComponents()
    {
        if ($this->hasItemVariables()) {
            $components = $this->getItemVariables()->toArray();
        } else {
            $components = [];
        }
        return new QtiComponentCollection($components);
    }

    /**
     * Get the Qti identifier of testResult
     *
     * @return QtiIdentifier
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * Set the identifier to testResult component
     *
     * @param QtiIdentifier $identifier
     * @return $this
     */
    public function setIdentifier(QtiIdentifier $identifier)
    {
        $this->identifier = $identifier;
        return $this;
    }

    /**
     * The date stamp of when this result was recorded.
     *
     * @return DateTime
     */
    public function getDatestamp()
    {
        return $this->datestamp;
    }

    /**
     * Set the datestamp that must be a Datetime object
     *
     * @param DateTime $datestamp
     * @return $this
     */
    public function setDatestamp(DateTime $datestamp)
    {
        $this->datestamp = $datestamp;
        return $this;
    }

    /**
     * Get all test variables. Can be outcome, response, candidate or tempalte variable
     *
     * @return ItemVariableCollection
     */
    public function getItemVariables()
    {
        return $this->itemVariables;
    }

    /**
     * Set all test variables
     *
     * @param ItemVariableCollection $itemVariables
     * @return $this
     */
    public function setItemVariables(ItemVariableCollection $itemVariables = null)
    {
        $this->itemVariables = $itemVariables;
        return $this;
    }

    /**
     * Check if the test result has item variables
     *
     * @return bool
     */
    public function hasItemVariables()
    {
        return !is_null($this->itemVariables);
    }
}
