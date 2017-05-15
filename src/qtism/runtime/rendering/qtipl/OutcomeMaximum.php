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
 * Copyright (c) 2013-2017 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 * @author Tom Verhoof <tomv@taotesting.com>
 * @license GPLv2
 *
 */

namespace qtism\runtime\rendering\qtipl;

use qtism\runtime\rendering\Renderable;

/**
 * The OutcomeMaximum's QtiPLRenderer. Transforms the OutcomeMaximum's
 * expression into QtiPL.
 *
 * @author Tom Verhoof <tomv@taotesting.com>
 */
class OutcomeMaximumQtiPLRenderer implements Renderable
{
    /**
     * Render a QtiComponent object into another constitution.
     *
     * @param mixed $something Something to render into another consitution.
     * @return mixed The rendered component into another constitution.
     * @throws \qtism\runtime\rendering\RenderingException If something goes wrong while rendering the component.
     */
    public function render($something)
    {
        $renderer = new QtiPLRenderer();
        $attributes = [];

        if ($something->getSectionIdentifier() != null) {
            $attributes['sectionIdentifier'] = "\"" . $something->getSectionIdentifier() . "\"";
        }

        if ($something->getIncludeCategory() != null) {
            $attributes['includeCategory'] = "\"" . $something->getIncludeCategory() . "\"";
        }

        if ($something->getExcludeCategory() != null) {
            $attributes['excludeCategory'] = "\"" . $something->getExcludeCategory() . "\"";
        }

        $attributes[]  = "\"" . $something->getOutcomeIdentifier() . "\"";

        if ($something->getWeightIdentifier() != null) {
            $attributes['sectionIdentifier'] = "\"" . $something->getWeightIdentifier() . "\"";
        }

        return $something->getQtiClassName() . $renderer->writeAttributes($attributes)
            . $renderer->writeChildElements();
    }
}