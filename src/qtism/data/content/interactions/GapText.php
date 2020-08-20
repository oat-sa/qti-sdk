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
use qtism\data\content\TextOrVariableCollection;
use qtism\data\QtiComponentCollection;

/**
 * From IMS QTI:
 *
 * A simple run of text to be inserted into a gap by the user, may be subject
 * to variable value substitution with printedVariable.
 */
class GapText extends GapChoice
{
    /**
     * The textOrVariable objects composing the GapText.
     *
     * @var QtiComponentCollection
     * @qtism-bean-property
     */
    private $content;

    /**
     * Create a new GapText object.
     *
     * @param string $identifier The identifier of the GapText.
     * @param int $matchMax The matchMax attribute.
     * @param string $id The id of the bodyElement.
     * @param string $class The class of the bodyElement.
     * @param string $lang The language of the bodyElement.
     * @param string $label The label of the bodyElement.
     * @throws InvalidArgumentException
     */
    public function __construct($identifier, $matchMax, $id = '', $class = '', $lang = '', $label = '')
    {
        parent::__construct($identifier, $matchMax, $id, $class, $lang, $label);
        $this->setContent(new TextOrVariableCollection());
    }

    /**
     * Get the objects composing the GapText.
     *
     * @return QtiComponentCollection
     */
    public function getComponents()
    {
        return $this->getContent();
    }

    /**
     * Set the objects composing the GapText.
     *
     * @param QtiComponentCollection $content
     */
    public function setContent(QtiComponentCollection $content)
    {
        $this->content = $content;
    }

    /**
     * Get the objects composing the GapText.
     *
     * @return QtiComponentCollection
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @see \qtism\data\QtiComponent::getQtiClassName()
     */
    public function getQtiClassName()
    {
        return 'gapText';
    }
}
