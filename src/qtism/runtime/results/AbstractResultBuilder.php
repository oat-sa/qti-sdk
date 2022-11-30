<?php

declare(strict_types=1);

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
 * Copyright (c) 2019-2020 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 * @author Bogaerts Jérôme <jerome@taotesting.com>
 * @license GPLv2
 */

namespace qtism\runtime\results;

use qtism\common\datatypes\QtiIdentifier;
use qtism\data\results\CandidateResponse;
use qtism\data\results\ItemVariableCollection;
use qtism\data\results\ResultOutcomeVariable;
use qtism\data\results\ResultResponseVariable;
use qtism\runtime\common\OutcomeVariable;
use qtism\runtime\common\ResponseVariable;
use qtism\runtime\common\State;
use qtism\runtime\common\VariableCollection;

/**
 * Class AbstractResultBuilder
 *
 * This abstract class aims at providing a base class for QTI Results
 * building from AssessmentItemSession and AssessmentTestSession objects.
 */
abstract class AbstractResultBuilder
{
    /**
     * @var State
     */
    protected $state;

    /**
     * AbstractResultBuilder constructor.
     *
     * Create a new AbstractResultBuilder based object.
     *
     * @param State $state
     */
    public function __construct(State $state)
    {
        $this->state = $state;
    }

    /**
     * Build Variables
     *
     * Build the ItemVariable objects contained in the target State object.
     *
     * @return ItemVariableCollection
     */
    protected function buildVariables(): ItemVariableCollection
    {
        $itemVariables = new ItemVariableCollection();

        foreach ($this->getAllVariables() as $variable) {
            if ($variable instanceof ResponseVariable) {
                $var = new ResultResponseVariable(
                    new QtiIdentifier($variable->getIdentifier()),
                    $variable->getCardinality(),
                    new CandidateResponse($variable->getDataModelValues())
                );

                if ($variable->getBaseType() !== -1) {
                    $var->setBaseType($variable->getBaseType());
                }

                $itemVariables[] = $var;
            } elseif ($variable instanceof OutcomeVariable) {
                $var = new ResultOutcomeVariable(
                    new QtiIdentifier($variable->getIdentifier()),
                    $variable->getCardinality()
                );

                if ($variable->getBaseType() !== -1) {
                    $var->setBaseType($variable->getBaseType());
                }

                $var->setValues($variable->getDataModelValues());

                $itemVariables[] = $var;
            }
        }

        return $itemVariables;
    }

    /**
     * Get All Variables
     *
     * Get all the variables to be serialized as ItemVariable objects.
     *
     * @return VariableCollection
     */
    abstract protected function getAllVariables(): VariableCollection;

    /**
     * Trigger the build.
     *
     * @return mixed
     */
    #[\ReturnTypeWillChange]
    abstract public function buildResult();
}
