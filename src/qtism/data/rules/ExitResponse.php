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
use qtism\data\QtiComponent;

/**
 * The special exitResponse QTI response rule.
 *
 * From IMS QTI:
 *
 * The exit response rule terminates response processing immediately (for this invocation).
 *
 * Additional Note: This class is empty, it only exists as a 'marker'.
 *
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class ExitResponse extends QtiComponent implements ResponseRule
{
    /**
     * @see \qtism\data\QtiComponent::getQtiClassName()
     */
    public function getQtiClassName()
    {
        return 'exitResponse';
    }

    /**
	 * Create a new ExitResponse object.
	 *
	 */
    public function __construct()
    {
    }

    /**
	 * @see \qtism\data\QtiComponent::getComponents()
	 */
    public function getComponents()
    {
        return new QtiComponentCollection();
    }

    /**
     * Transforms this rule into a Qti-PL string.
     *
     *@return string A Qti-PL representation of the rule
     */
    public function toQtiPL()
    {
        return $this->getQtiClassName() . "()";
    }
}
