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
 * @author Tom Verhoof <tomv@taotesting.com>
 * @license GPLv2
 */

namespace qtism\runtime\rendering\qtipl\rules;

use qtism\runtime\rendering\qtipl\AbstractQtiPLRenderer;
use qtism\runtime\rendering\qtipl\QtiPLRenderer;
use qtism\runtime\rendering\RenderingException;

/**
 * The OutcomeCondition's QtiPLRenderer. Transforms the OutcomeCondition's
 * expression into QtiPL.
 */
class OutcomeConditionQtiPLRenderer extends AbstractQtiPLRenderer
{
    /**
     * Render a QtiComponent object into another constitution.
     *
     * @param mixed $something Something to render into another consitution.
     * @return mixed The rendered component into another constitution.
     * @throws RenderingException If something goes wrong while rendering the component.
     */
    public function render($something)
    {
        $renderer = new QtiPLRenderer($this->getCRO());
        $qtipl = $renderer->render($something->getOutcomeIf());

        foreach ($something->getOutcomeElseIfs() as $elseif) {
            $qtipl .= " " . $renderer->render($elseif);
        }

        $qtipl .= ($something->getOutcomeElse() == null) ? "" : $renderer->render($something->getOutcomeElse());
        return $qtipl;
    }
}
