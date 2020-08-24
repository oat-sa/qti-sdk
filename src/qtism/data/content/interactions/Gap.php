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
 * Copyright (c) 2013-2020 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 * @license GPLv2
 */

namespace qtism\data\content\interactions;

use InvalidArgumentException;
use qtism\common\collections\IdentifierCollection;
use qtism\data\content\InlineStatic;
use qtism\data\QtiComponentCollection;

/**
 * From IMS QTI:
 *
 * gap is an inlineStatic element that must only appear within a gapMatchInteraction.
 */
class Gap extends Choice implements AssociableChoice, InlineStatic
{
    /**
     * From IMS QTI:
     *
     * If true then this gap must be filled by the candidate inorder to form a
     * valid response to the interaction.
     *
     * @var bool
     * @qtism-bean-property
     */
    private $required = false;

    /**
     * From IMS QTI:
     *
     * A set of choices that this choice may be associated with, all others are
     * excluded. If no matchGroup is given, or if it is empty, then all other
     * choices may be associated with this one subject to their own matching
     * constraints.
     *
     * @var IdentifierCollection
     * @qtism-bean-property
     */
    private $matchGroup;

    /**
     * Create a new Gap object.
     *
     * @param string $identifier The identifier of the gap.
     * @param bool $required Whether or not the Gap is required to be filled to form a valid response.
     * @param string $id The identifier of the bodyElement.
     * @param string $class The class of the bodyElement.
     * @param string $lang The language of the bodyElement.
     * @param string $label The label of the bodyElement.
     * @throws InvalidArgumentException If one of the constructor's argument is invalid.
     */
    public function __construct($identifier, $required = false, $id = '', $class = '', $lang = '', $label = '')
    {
        parent::__construct($identifier, $id, $class, $lang, $label);
        $this->setRequired($required);
        $this->setMatchGroup(new IdentifierCollection());
    }

    /**
     * Set whether the gap must be filled by the candidate or not.
     *
     * @param bool $required
     * @throws InvalidArgumentException If $required is not a boolean value.
     */
    public function setRequired($required)
    {
        if (is_bool($required) === true) {
            $this->required = $required;
        } else {
            $msg = "The 'required' argument must be a boolean value, '" . gettype($required) . "' given.";
            throw new InvalidArgumentException($msg);
        }
    }

    /**
     * Whether the gap must be filled by the candidate.
     *
     * @return bool.
     */
    public function isRequired()
    {
        return $this->required;
    }

    public function setMatchGroup(IdentifierCollection $matchGroup)
    {
        $this->matchGroup = $matchGroup;
    }

    public function getMatchGroup()
    {
        return $this->matchGroup;
    }

    public function getComponents()
    {
        return new QtiComponentCollection();
    }

    public function getQtiClassName()
    {
        return 'gap';
    }
}
