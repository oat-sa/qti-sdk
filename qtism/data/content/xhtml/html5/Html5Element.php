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
 * Copyright (c) 2020 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 * @author Julien Sébire <julien@taotesting.com>
 * @license GPLv2
 */

namespace qtism\data\content\xhtml\html5;

use InvalidArgumentException;
use qtism\common\utils\Format;
use qtism\data\content\BodyElement;
use qtism\data\storage\xml\QtiNamespaced;

/**
 * The base Html 5 element.
 */
abstract class Html5Element extends BodyElement implements QtiNamespaced
{
    private const HTML5_NAMESPACE = 'http://www.imsglobal.org/xsd/imsqtiv2p2_html5_v1p0';

    /**
     * The title characteristic represents advisory information for the tag,
     * such as would be appropriate for a tooltip. On a link, this could be the
     * title or a description of the target resource; on an image, it could be
     * the image credit or a description of the image; on a paragraph, it could
     * be a footnote or commentary on the text; on a citation, it could be
     * further information about the source; on interactive content, it could
     * be a label for, or instructions for, use of the element; and so forth.
     * The value is text.
     *
     * @var string
     */
    private $title = '';

    /**
     * Create a new Html5 element.
     *
     * For the reason why using null instead of default values, see:
     *
     * @see https://stackoverflow.com/questions/45320353/php-7-1-nullable-default-function-parameter#45320694
     *
     * @param mixed $title A title in the sense of Html title attribute
     * @param mixed $id A QTI identifier.
     * @param mixed $class One or more class names separated by spaces.
     * @param mixed $lang An RFC3066 language.
     * @param mixed $label A label that does not exceed 256 characters.
     */
    public function __construct(
        $title = null,
        $id = null,
        $class = null,
        $lang = null,
        $label = null
    ) {
        parent::__construct($id ?? '', $class ?? '', $lang ?? '', $label ?? '');

        $this->setTitle($title);
    }

    /**
     * @param mixed $title
     *
     * @throws InvalidArgumentException when $title cannot be converted to a string.
     */
    public function setTitle($title): void
    {
        $this->title = $this->acceptNormalizedStringOrNull($title, 'title', '');
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function hasTitle(): bool
    {
        return $this->title !== '';
    }

    protected function acceptNormalizedStringOrNull($value, string $argumentName, string $default = null): string
    {
        if ($value === null) {
            return $default;
        }

        if (!Format::isNormalizedString($value)) {
            $given = is_string($value)
                ? $value
                : gettype($value);

            throw new InvalidArgumentException(
                sprintf(
                    'The "%s" argument must be a non-empty, normalized string (no line break nor tabulation), "%s" given.',
                    $argumentName,
                    $given
                )
            );
        }

        return $value ?? $default;
    }

    public function getTargetNamespace(): string
    {
        return self::HTML5_NAMESPACE;
    }

    public function getTargetNamespacePrefix(): string
    {
        return 'qh5';
    }
}