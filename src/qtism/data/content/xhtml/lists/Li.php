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

namespace qtism\data\content\xhtml\lists;

use InvalidArgumentException;
use qtism\data\content\BodyElement;
use qtism\data\content\FlowCollection;

/**
 * The XHTML li class.
 */
class Li extends BodyElement
{
    /**
     * The Flow objects composing the Li.
     *
     * @var FlowCollection
     * @qtism-bean-property
     */
    private $content;

    /**
     * Create a new Li object.
     *
     * @param string $id The identifier of the bodyElement.
     * @param string $class The class of the bodyElement.
     * @param string $lang The language of the bodyElement.
     * @param string $label The label of the bodyElement.
     * @throws InvalidArgumentException
     */
    public function __construct($id = '', $class = '', $lang = '', $label = '')
    {
        parent::__construct($id, $class, $lang, $label);
        $this->setContent(new FlowCollection());
    }

    /**
     * Get the Flow objects composing the Li.
     *
     * @return FlowCollection A collection of Flow objects.
     */
    public function getComponents(): FlowCollection
    {
        return $this->getContent();
    }

    /**
     * Set the Flow objects composing the Li.
     *
     * @param FlowCollection $content
     */
    public function setContent(FlowCollection $content): void
    {
        $this->content = $content;
    }

    /**
     * Get the Flow objects composing the Li.
     *
     * @return FlowCollection
     */
    public function getContent(): FlowCollection
    {
        return $this->content;
    }

    /**
     * @return string
     */
    public function getQtiClassName(): string
    {
        return 'li';
    }
}
