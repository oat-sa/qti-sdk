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
use qtism\data\results\ItemResult;
use qtism\data\results\SessionStatus;
use qtism\runtime\tests\AssessmentItemSession;

/**
 * Class ItemResultBuilder
 *
 * This class aims at building ItemResult objects from
 * AssessmentItemSession objects.
 */
class ItemResultBuilder extends AbstractResultBuilder
{
    /**
     * Build ItemResult
     *
     * @return ItemResult
     */
    public function buildResult()
    {
        /** @var AssessmentItemSession $state */
        $state = $this->state;

        $itemResultIdentifier = new QtiIdentifier(
            $state->getAssessmentItem()->getIdentifier()
        );

        $itemResult = new ItemResult(
            $itemResultIdentifier,
            $this->getLastProcessingTime(),
            SessionStatus::STATUS_FINAL
        );

        $itemResult->setItemVariables($this->buildVariables());

        return $itemResult;
    }

    /**
     * Get all variables
     *
     * Get all the variables held by the AssessmentItemSession.
     *
     * @return \qtism\runtime\common\VariableCollection
     */
    protected function getAllVariables()
    {
        return $this->state->getAllVariables();
    }
}
