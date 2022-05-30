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

use qtism\data\content\FlowStatic;
use qtism\data\content\FlowTrait;
use qtism\data\content\InlineCollection;

class Figcaption extends Html5Element implements FlowStatic
{
    use FlowTrait;

    public const QTI_CLASS_NAME_FIGCAPTION = 'figcaption';

    /**
     * The Block components composing the SimpleBlock object.
     *
     * @var InlineCollection
     * @qtism-bean-property
     */
    private $content;

    /**
     * Create a new figcaption object.
     */
    public function __construct($title = null, $role = null, $id = null, $class = null, $lang = null, $label = null)
    {
        parent::__construct($title, $role, $id, $class, $lang, $label);
        $this->setContent(new InlineCollection());
    }

    public function getQtiClassName()
    {
        return self::QTI_CLASS_NAME_FIGCAPTION;
    }

    public function getComponents()
    {
        return $this->getContent();
    }

    /**
     * Set the collection of Flow objects composing the Div.
     *
     * @param InlineCollection $content A collection of Flow objects.
     */
    public function setContent(InlineCollection $content)
    {
        $this->content = $content;
    }

    /**
     * Get the collection of Flow objects composing the Div.
     *
     * @return InlineCollection
     */
    public function getContent()
    {
        return $this->content;
    }
}
