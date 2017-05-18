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
 * Copyright (c) 2013-2014 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 * @license GPLv2
 */

namespace qtism\data\rules;

use qtism\data\QtiComponentCollection;
use \InvalidArgumentException as InvalidArgumentException;
use qtism\data\QtiPLisable;

/**
 * A collection of OutcomeRule objects.
 *
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class OutcomeRuleCollection extends QtiComponentCollection implements QtiPLisable
{
    /**
	 * Check if a given $value is an instance of OutcomeRule.
	 *
	 * @throws \InvalidArgumentException If the given $value is not an instance of OutcomeRule.
	 */
    protected function checkType($value)
    {
        if (!$value instanceof OutcomeRule) {
            $msg = "OutcomeRuleCollection only accepts to store OutcomeRule objects, '" . gettype($value) . "' given.";
            throw new InvalidArgumentException($msg);
        }
    }

    /**
     * Transforms this QtiComponent into a Qti-PL string.
     *
     *@return string A Qti-PL representation of the QtiComponent
     */
    public function toQtiPL()
    {
        $qtipl = "";

        foreach ($this as $condition) {
            $qtipl .= "\t" . $condition->toQtiPL() . ";\n";
        }

        return $qtipl;
    }
}
