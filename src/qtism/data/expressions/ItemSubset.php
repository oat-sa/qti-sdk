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

namespace qtism\data\expressions;

use qtism\common\utils\Format;
use qtism\common\collections\IdentifierCollection;
use \InvalidArgumentException;

/**
 * From IMS QTI:
 *
 * This class defines the concept of a sub-set of the items selected in an assessmentTest.
 * The attributes define criteria that must be matched by all members of the sub-set.
 * It is used to control a number of expressions in outcomeProcessing for returning
 * information about the test as a whole, or arbitrary subsets of it.
 *
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class ItemSubset extends Expression implements Pure
{
    /**
	 * From IMS QTI:
	 *
	 * If specified, only variables from items in the assessmentSection with matching
	 * identifier are matched. Items in sub-sections are included in this definition.
	 *
	 * @var string
	 * @qtism-bean-property
	 */
    private $sectionIdentifier = '';

    /**
	 * From IMS QTI:
	 *
	 * If specified, only variables from items with a matching category are included.
	 *
	 * @var \qtism\common\collections\IdentifierCollection
	 * @qtism-bean-property
	 */
    private $includeCategories = null;

    /**
	 * From IMS QTI:
	 *
	 * If specified, only variables from items with no matching category are included.
	 *
	 * @var \qtism\common\collections\IdentifierCollection
	 * @qtism-bean-property
	 */
    private $excludeCategories = null;

    /**
	 * Create a new instance of ItemSubset.
	 */
    public function __construct()
    {
        $this->setIncludeCategories(new IdentifierCollection());
        $this->setExcludeCategories(new IdentifierCollection());
    }

    /**
	 * Set the assessment section identifier to match.
	 *
	 * @param string $sectionIdentifier A QTI Identifier.
	 * @throws \InvalidArgumentException If $sectionIdentifier is not a valid QTI Identifier.
	 */
    public function setSectionIdentifier($sectionIdentifier)
    {
        if (Format::isIdentifier($sectionIdentifier) || empty($sectionIdentifier)) {
            $this->sectionIdentifier = $sectionIdentifier;
        } else {
            $msg = "'${sectionIndentifier}' is not a valid QTI Identifier.";
            throw new InvalidArgumentException($msg);
        }
    }

    /**
	 * Get the assessment section identifier to match.
	 *
	 * @return string
	 */
    public function getSectionIdentifier()
    {
        return $this->sectionIdentifier;
    }

    /**
	 * Set the matching categories.
	 *
	 * @param \qtism\common\collections\IdentifierCollection $includeCategories A collection of QTI Identifiers.
	 */
    public function setIncludeCategories(IdentifierCollection $includeCategories)
    {
        $this->includeCategories = $includeCategories;
    }

    /**
	 * Get the matching categories.
	 *
	 * @return \qtism\common\collections\IdentifierCollection
	 */
    public function getIncludeCategories()
    {
        return $this->includeCategories;
    }

    /**
	 * Set the categories that must not be matched.
	 *
	 * @param \qtism\common\collections\IdentifierCollection $excludeCategories A collection of QTI Identifiers.
	 */
    public function setExcludeCategories(IdentifierCollection $excludeCategories)
    {
        $this->excludeCategories = $excludeCategories;
    }

    /**
	 * Get the categories that must not be matched.
	 *
	 * @return \qtism\common\collections\IdentifierCollection
	 */
    public function getExcludeCategories()
    {
        return $this->excludeCategories;
    }

    /**
	 * @see \qtism\data\QtiComponent::getQtiClassName()
	 */
    public function getQtiClassName()
    {
        return 'itemSubset';
    }

    /**
     * Checks whether this expression is pure.
     * @link https://en.wikipedia.org/wiki/Pure_function
     *
     * @return boolean True if the expression is pure, false otherwise
     */
    public function isPure()
    {
        return false; // Dependant on the items of the test
    }
}
