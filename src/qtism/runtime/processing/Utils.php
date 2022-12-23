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
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 * @license GPLv2
 */

namespace qtism\runtime\processing;

use qtism\data\processing\TemplateProcessing;

/**
 * A collection of utility methods focusing on runtime processing.
 */
class Utils
{
    /**
     * Obtain a list of the identifiers of variables that might have their values changed by
     * a templateProcessing description.
     *
     * A variable is considered to be possibly impacted if:
     *
     * * It is the target of a setTemplateValue template rule.
     * * It is the target of a setCorrectResponse template rule.
     * * It is the target of a setDefaultValue template rule.
     *
     * @param TemplateProcessing $templateProcessing
     * @return array A list of QTI identifiers.
     */
    public static function templateProcessingImpactedVariables(TemplateProcessing $templateProcessing): array
    {
        $identifiers = [];
        $classNames = [
            'setTemplateValue',
            'setCorrectResponse',
            'setDefaultValue',
        ];

        $iterator = $templateProcessing->getComponentsByClassName($classNames);

        foreach ($iterator as $templateRule) {
            $identifiers[] = $templateRule->getIdentifier();
        }

        return array_unique($identifiers);
    }
}
