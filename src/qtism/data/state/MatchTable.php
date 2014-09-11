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

namespace qtism\data\state;

use qtism\data\QtiComponentCollection;
use \InvalidArgumentException;

/**
 * From IMS QTI:
 *
 * A matchTable transforms a source integer by finding the first matchTableEntry with
 * an exact match to the source.
 *
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class MatchTable extends LookupTable
{
    /**
	 * A collection of MatchTableEntry objects.
	 *
	 * @var \qtism\data\state\MatchTableEntryCollection
	 * @qtism-bean-property
	 */
    private $matchTableEntries;

    /**
	 * Create a new instance of MatchTable.
	 *
	 * @param \qtism\data\state\MatchTableEntryCollection $matchTableEntries A collection of MatchTableEntry objects.
	 * @param mixed $defaultValue The default oucome value to be used when no matching table entry is found.
	 * @throws \InvalidArgumentException If $matchTableEntries is an empty collection.
	 */
    public function __construct(MatchTableEntryCollection $matchTableEntries, $defaultValue = null)
    {
        parent::__construct($defaultValue);
        $this->setMatchTableEntries($matchTableEntries);
    }

    /**
	 * Get the collection of MatchTableEntry objects.
	 *
	 * @return \qtism\data\state\MatchTableEntryCollection A collection of MatchTableEntry objects.
	 */
    public function getMatchTableEntries()
    {
        return $this->matchTableEntries;
    }

    /**
	 * Set the collection of MatchTableEntry objects.
	 *
	 * @param \qtism\data\state\MatchTableEntryCollection $matchTableEntries A collection of MatchTableEntry objects.
	 * @throws \InvalidArgumentException If $matchTableEntries is an empty collection.
	 */
    public function setMatchTableEntries(MatchTableEntryCollection $matchTableEntries)
    {
        if (count($matchTableEntries) > 0) {
            $this->matchTableEntries = $matchTableEntries;
        } else {
            $msg = "A MatchTable object must contain at least one MatchTableEntry object.";
            throw new InvalidArgumentException($msg);
        }
    }

    /**
	 * @see \qtism\data\state\LookupTable::getQtiClassName()
	 */
    public function getQtiClassName()
    {
        return 'matchTable';
    }

    /**
	 * @see \qtism\data\state\LookupTable::getComponents()
	 */
    public function getComponents()
    {
        $comp = array_merge(
                    parent::getComponents()->getArrayCopy(),
                    $this->getMatchTableEntries()->getArrayCopy()
                );

        return new QtiComponentCollection($comp);
    }
}
