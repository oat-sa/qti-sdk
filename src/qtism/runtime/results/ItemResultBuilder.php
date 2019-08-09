<?php

namespace qtism\runtime\results;

use qtism\common\datatypes\QtiIdentifier;
use qtism\data\results\ItemResult;
use qtism\data\results\SessionStatus;
use qtism\runtime\tests\AssessmentItemSession;

class ItemResultBuilder extends AbstractResultBuilder
{
    public function buildResult()
    {
        /** @var AssessmentItemSession $state */
        $state = $this->state;

        $itemResultIdentifier = new QtiIdentifier(
            $state->getAssessmentItem()->getIdentifier()
        );

        $itemResult = new ItemResult(
            $itemResultIdentifier,
            new \DateTime(),
            SessionStatus::STATUS_FINAL
        );

        $itemResult->setItemVariables($this->buildVariables());

        return $itemResult;
    }

    protected function getAllVariables()
    {
        return $this->state->getAllVariables();
    }
}