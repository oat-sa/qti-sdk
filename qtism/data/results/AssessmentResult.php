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

use qtism\data\QtiComponent;
use qtism\data\QtiComponentCollection;

/**
 * Class AssessmentResult
 *
 * This is the root class to contain the assessment result data. An Assessment Result
 * is used to report the results of a candidate's interaction with a test and/or one or more items attempted.
 * Information about the test is optional, in some systems it may be possible to interact
 * with items that are not organized into a test at all. For example, items that are organized
 * with learning resources and presented individually in a formative context.
 */
class AssessmentResult extends QtiComponent
{
    /**
     * Contains the contextual information for the associated itemTest and itemResults. Contextual information must be supplied.
     *
     * Multiplicity [1]
     *
     * @var Context
     */
    protected $context;

    /**
     * A summary report for a test is represented by an assessment result containing a testResult but no itemResults.
     *
     * Multiplicity [0,1]
     *
     * @var TestResult
     */
    protected $testResult;

    /**
     * When a test result is given the following item results must relate only to items
     * that were selected for presentation as part of the corresponding test session.
     * Furthermore, all items selected for presentation should be reported with a corresponding itemResult.
     *
     * Multiplicity [0,*]
     *
     * @var ItemResultCollection
     */
    protected $itemResults;

    /**
     * AssessmentResult constructor.
     *
     * A xml representation of QTI test results. ItemResults and TestResults are optionals
     *
     * @param Context $context
     * @param TestResult|null $testResult
     * @param ItemResultCollection|null $itemResults
     */
    public function __construct(Context $context, TestResult $testResult = null, ItemResultCollection $itemResults = null)
    {
        $this->setContext($context);
        $this->setTestResult($testResult);
        $this->setItemResults($itemResults);
    }

    /**
     * Returns the QTI class name as per QTI 2.1 specification.
     *
     * @return string A QTI class name.
     */
    public function getQtiClassName()
    {
        return 'assessmentResult';
    }

    /**
     * Get the direct child components of this one.
     *
     * @return QtiComponentCollection A collection of QtiComponent objects.
     */
    public function getComponents()
    {
        $components = [
            $this->getContext(),
        ];

        if ($this->hasTestResult()) {
            $components[] = $this->getTestResult();
        }

        if ($this->hasItemResults()) {
            $components[] = $this->getItemResults()->getArrayCopy();
        }

        return new QtiComponentCollection($components);
    }

    /**
     * Get the context of assessment results.
     *
     * Contains data that identify session, candidate and deliveryExecution
     *
     * @return Context
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * Set the context
     *
     * @param Context $context
     * @return $this
     */
    public function setContext(Context $context)
    {
        $this->context = $context;
        return $this;
    }

    /**
     * Get the test result
     *
     * @return TestResult
     */
    public function getTestResult()
    {
        return $this->testResult;
    }

    /**
     * Set the test result
     *
     * @param TestResult $testResult
     * @return $this
     */
    public function setTestResult(TestResult $testResult = null)
    {
        $this->testResult = $testResult;
        return $this;
    }

    /**
     * Check if a test result has been set for current result
     *
     * @return bool
     */
    public function hasTestResult()
    {
        return !is_null($this->testResult);
    }

    /**
     * Get the item results
     *
     * @return ItemResultCollection
     */
    public function getItemResults()
    {
        return $this->itemResults;
    }

    /**
     * Set item results
     *
     * @param ItemResultCollection|null $itemResults
     * @return $this
     */
    public function setItemResults(ItemResultCollection $itemResults = null)
    {
        $this->itemResults = $itemResults;
        return $this;
    }

    /**
     * Check if item results has been set for current result
     *
     * @return bool
     */
    public function hasItemResults()
    {
        return !is_null($this->itemResults);
    }
}
