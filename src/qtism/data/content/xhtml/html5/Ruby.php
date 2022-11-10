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
 * Copyright (c) 2022 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace qtism\data\content\xhtml\html5;

use qtism\data\content\FlowCollection;
use qtism\data\content\FlowStatic;
use qtism\data\content\FlowTrait;
use qtism\data\content\Inline;
use qtism\data\content\InlineStatic;
use qtism\data\content\TextOrVariable;

class Ruby extends Html5Element implements FlowStatic, Inline, TextOrVariable, InlineStatic
{
    use FlowTrait;

    public const QTI_CLASS_NAME = 'ruby';

    /**
     * The Block components composing the SimpleBlock object.
     *
     * @var FlowCollection
     * @qtism-bean-property
     */
    private $content;

    /**
     * Create a new figure object.
     */
    public function __construct($title = null, $role = null, $id = null, $class = null, $lang = null, $label = null)
    {
        parent::__construct($title, $role, $id, $class, $lang, $label);
        $this->setContent(new FlowCollection());
    }

    public function getQtiClassName()
    {
        return self::QTI_CLASS_NAME;
    }

    public function getComponents()
    {
        return $this->getContent();
    }

    /**
     * Set the collection of Flow objects composing the Div.
     *
     * @param FlowCollection $content A collection of Flow objects.
     */
    public function setContent(FlowCollection $content)
    {
        $this->content = $content;
    }

    /**
     * Get the collection of Flow objects composing the Div.
     *
     * @return FlowCollection
     */
    public function getContent()
    {
        return $this->content;
    }
}
