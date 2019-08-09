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
 * Copyright (c) 2019 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 * @author Bogaerts Jérôme, <jerome@taotesting.com>
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

abstract class AbstractResultBuilder
{
    /**
     * @var State
     */
    protected $state;

    public function __construct(State $state)
    {
        $this->state = $state;
    }

    protected function buildVariables() {
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

    abstract protected function getAllVariables();

    abstract public function buildResult();
}