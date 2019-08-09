<?php

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