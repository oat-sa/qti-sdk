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

namespace qtism\data\content;

use InvalidArgumentException;
use qtism\data\QtiComponentCollection;

/**
 * From IMS QTI:
 *
 * The templateBlock QTI class.
 */
class TemplateBlock extends TemplateElement implements FlowStatic, BlockStatic
{
    use FlowTrait;

    /**
     * The content of the TemplateBlock.
     *
     * @var FlowStaticCollection
     * @qtism-bean-property
     */
    private $content;

    /**
     * Create a new TemplateBlock object.
     *
     * @param string $identifier The identifier of the templateBlock.
     * @param string $templateIdentifier The identifier of the associated template variable.
     * @param string $id The id of the bodyElement.
     * @param string $class The class of the bodyElement.
     * @param string $lang The language of the bodyElement.
     * @param string $label The label of the bodyElement.
     * @throws InvalidArgumentException If any of the argument is invalid.
     */
    public function __construct($identifier, $templateIdentifier, $id = '', $class = '', $lang = '', $label = '')
    {
        parent::__construct($identifier, $templateIdentifier, $id, $class, $lang, $label);
        $this->setContent(new FlowStaticCollection());
    }

    /**
     * Set the content of the templateBlock.
     *
     * @param FlowStaticCollection $content A collection of BlockStatic objects.
     */
    public function setContent(FlowStaticCollection $content): void
    {
        $this->content = $content;
    }

    /**
     * Get the content of the templateBlock.
     *
     * @return FlowStaticCollection A collection of BlockStatic objects.
     */
    public function getContent(): FlowStaticCollection
    {
        return $this->content;
    }

    /**
     * @return FlowStaticCollection|QtiComponentCollection
     */
    public function getComponents(): QtiComponentCollection
    {
        return $this->getContent();
    }

    /**
     * @return string
     */
    public function getQtiClassName(): string
    {
        return 'templateBlock';
    }
}
