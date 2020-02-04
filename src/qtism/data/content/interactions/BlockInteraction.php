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
use qtism\data\content\Block;
use qtism\data\content\Flow;
use qtism\data\content\FlowTrait;
use qtism\data\QtiComponentCollection;

/**
 * From IMS QTI:
 *
 * An interaction that behaves like a block in the content model.
 * Most interactions are of this type.
 */
abstract class BlockInteraction extends Interaction implements Block, Flow
{
    use FlowTrait;

    /**
     * From IMS QTI:
     *
     * An optional prompt for the interaction.
     *
     * @var Prompt
     * @qtism-bean-property
     */
    private $prompt = null;

    /**
     * Create a new BlockInteraction object.
     *
     * @param string $responseIdentifier The identifier of the associated response.
     * @param string $id The id of the bodyElement.
     * @param string $class The class of the bodyElement.
     * @param string $lang The language of the bodyElement.
     * @param string $label The label of the bodyElement.
     * @throws InvalidArgumentException If one of the argument is invalid.
     */
    public function __construct($responseIdentifier, $id = '', $class = '', $lang = '', $label = '')
    {
        parent::__construct($responseIdentifier, $id, $class, $lang, $label);
    }

    /**
     * Get the prompt for the interaction.
     *
     * @return Prompt
     */
    public function getPrompt()
    {
        return $this->prompt;
    }

    /**
     * Set the prompt for the interaction.
     *
     * @param Prompt $prompt
     */
    public function setPrompt($prompt = null)
    {
        $this->prompt = $prompt;
    }

    /**
     * Whether the BlockInteraction has a prompt.
     *
     * @return boolean
     */
    public function hasPrompt()
    {
        return $this->getPrompt() !== null;
    }

    /**
     * @see \qtism\data\QtiComponent::getComponents()
     */
    public function getComponents()
    {
        $array = [];
        if ($this->hasPrompt() === true) {
            $array[] = $this->getPrompt();
        }

        return new QtiComponentCollection($array);
    }
}
