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

namespace qtismtest\data\content;

use InvalidArgumentException;
use qtism\data\content\enums\AriaLive;
use qtism\data\content\enums\AriaOrientation;
use qtism\data\content\xhtml\text\Span;
use qtismtest\QtiSmTestCase;
use stdClass;

/**
 * Class BodyElementTest
 */
class BodyElementTest extends QtiSmTestCase
{
    public function testRawInstantiation()
    {
        $span = new Span();
        $this::assertSame('', $span->getAriaControls());
        $this::assertSame('', $span->getAriaDescribedBy());
        $this::assertSame('', $span->getAriaFlowTo());
        $this::assertSame('', $span->getAriaLabel());
        $this::assertSame('', $span->getAriaLabelledBy());
        $this::assertSame('', $span->getAriaLevel());
        $this::assertFalse($span->getAriaLive());
        $this::assertFalse($span->getAriaOrientation());
        $this::assertSame('', $span->getAriaOwns());
        $this::assertSame('', $span->getId());
        $this::assertSame('', $span->getClass());
        $this::assertSame('', $span->getLang());
        $this::assertSame('', $span->getLabel());
        $this::assertFalse($span->getAriaHidden());
        $this::assertFalse($span->hasId());
        $this::assertFalse($span->hasClass());
        $this::assertFalse($span->hasLang());
        $this::assertFalse($span->hasLabel());
        $this::assertFalse($span->hasAriaControls());
        $this::assertFalse($span->hasAriaDescribedBy());
        $this::assertFalse($span->hasAriaFlowTo());
        $this::assertFalse($span->hasAriaLabel());
        $this::assertFalse($span->hasAriaLabelledBy());
        $this::assertFalse($span->hasAriaLive());
        $this::assertFalse($span->hasAriaOrientation());
        $this::assertFalse($span->hasAriaOwns());
        $this::assertFalse($span->hasAriaHidden());
    }

    public function testSetId()
    {
        $span = new Span();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("The 'id' argument of a body element must be a valid identifier or an empty string");

        $span->setId(999);
    }

    /**
     * @dataProvider validClassesToTest
     * @param mixed $class
     */
    public function testSetClassWithValidClass($class): void
    {
        $span = new Span('', $class);
        $span->setClass($class);

        $this::assertSame(trim($class), $span->getClass());
    }

    public function validClassesToTest(): array
    {
        return [
            ['x-tao-upload-type-application_zip x-tao-upload-type-text_plain x-tao-upload-type-application_pdf x-tao-upload-type-image_jpeg x-tao-upload-type-image_png x-tao-upload-type-image_gif x-tao-upload-type-image_svg+xml x-tao-upload-type-audio_mpeg x-tao-upload-type-audio_x-ms-wma x-tao-upload-type-audio_x-wav x-tao-upload-type-video_mpeg x-tao-upload-type-video_mp4 x-tao-upload-type-video_quicktime x-tao-upload-type-video_x-ms-wmv x-tao-upload-type-video_x-flv x-tao-upload-type-text_csv x-tao-upload-type-application_msword x-tao-upload-type-application_vnd.ms-excel x-tao-upload-type-application_vnd.ms-powerpoint x-tao-upload-type-application_vnd.oasis.opendocument.text x-tao-upload-type-application_vnd.oasis.opendocument.spreadsheet x-tao-upload-type-text_x-c x-tao-upload-type-text_x-csrc x-tao-upload-type-text_pascal x-tao-upload-type-video_avi x-tao-upload-type-image_bmp x-tao-upload-type-text_css x-tao-upload-type-image_x-emf x-tao-upload-type-application_vnd.geogebra.file x-tao-upload-type-text_x-h x-tao-upload-type-application_winhlp x-tao-upload-type-text_html x-tao-upload-type-text_javascript x-tao-upload-type-application_vnd.ms-access x-tao-upload-type-image_vnd.ms-modi x-tao-upload-type-multipart_related x-tao-upload-type-application_base64 x-tao-upload-type-audio_x-m4a x-tao-upload-type-video_x-sgi-movie x-tao-upload-type-application_vnd.ms-project x-tao-upload-type-application_vnd.oasis.opendocument.database x-tao-upload-type-application_vnd.oasis.opendocument.presentation x-tao-upload-type-application_vnd.oasis.opendocument.text-template x-tao-upload-type-application_octet-stream x-tao-upload-type-application_vnd.rn-realmedia x-tao-upload-type-application_rtf x-tao-upload-type-application_vnd.sun.xml.writer.template x-tao-upload-type-application_x-shockwave-flash x-tao-upload-type-application_x-sibelius-score x-tao-upload-type-application_x-tar x-tao-upload-type-application_vnd.sun.xml.calc x-tao-upload-type-application_vnd.sun.xml.writer x-tao-upload-type-application_x-tex x-tao-upload-type-image_tiff x-tao-upload-type-application_vnd.visio x-tao-upload-type-application_vnd.ms-works x-tao-upload-type-image_x-wmf x-tao-upload-type-application_x-mswrite x-tao-upload-type-text_xml x-tao-upload-type-application_vnd.ms-xpsdocument x-tao-upload-type-application_x-7z-compressed x-tao-upload-type-application_x-gzip x-tao-upload-type-application_x-rar-compressed x-tao-upload-type-application_x-tar x-tao-upload-type-application_x-compress'],
            ['custom-text-box '],
            [''],
            [' '],
            // todo: check that it's intended to be that permissive.
            ['   e v e n   that kind of things */4231\'"("        -(&é(   '],
        ];
    }

    /**
     * @dataProvider invalidClassesToTest
     * @param mixed $class the class to set
     * @param string|null $message the expected message or null if identical to $class to set
     */
    public function testSetClassWithInvalidClass($class, string $message = null): void
    {
        $span = new Span();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The "class" argument must be a valid class name, "' . $message ?? $class . '" given');

        $span->setClass($class);
    }

    public function invalidClassesToTest(): array
    {
        return [
            [999, 'integer'],
            [[999], 'array'],
            ["a\tb"],
            ["  a\tb  "],
        ];
    }

    public function testSetLabelWrongType()
    {
        $span = new Span();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("The 'label' argument must be a string that does not exceed 256 characters.");

        $span->setLabel(999);
    }

    /**
     * @param $value
     * @dataProvider validAriaControlsAttributesProvider
     */
    public function testValidAriaControlsAttributes($value)
    {
        $span = new Span();
        $span->setAriaControls($value);

        $this::assertEquals($value, $span->getAriaControls());
    }

    /**
     * @return array
     */
    public function validAriaControlsAttributesProvider()
    {
        return [
            [''],
            ['_IDREF'],
            ['IDREF1 IDREF2'],
        ];
    }

    /**
     * @param $value
     * @dataProvider validAriaDescribedByAttributesProvider
     */
    public function testValidAriaDescribedByAttributes($value)
    {
        $span = new Span();
        $span->setAriaDescribedBy($value);

        $this::assertEquals($value, $span->getAriaDescribedBy());
    }

    /**
     * @return array
     */
    public function validAriaDescribedByAttributesProvider()
    {
        return [
            [''],
            ['_IDREF'],
            ['IDREF1 IDREF2'],
        ];
    }

    /**
     * @param $value
     * @dataProvider validAriaFlowToAttributesProvider
     */
    public function testValidAriaFlowToAttributes($value)
    {
        $span = new Span();
        $span->setAriaFlowTo($value);

        $this::assertEquals($value, $span->getAriaFlowTo());
    }

    /**
     * @return array
     */
    public function validAriaFlowToAttributesProvider()
    {
        return [
            [''],
            ['_IDREF'],
            ['IDREF1 IDREF2'],
        ];
    }

    /**
     * @param $value
     * @dataProvider validAriaLabelledByAttributesProvider
     */
    public function testValidAriaLabelledByAttributes($value)
    {
        $span = new Span();
        $span->setAriaLabelledBy($value);

        $this::assertEquals($value, $span->getAriaLabelledBy());
    }

    /**
     * @return array
     */
    public function validAriaLabelledByAttributesProvider()
    {
        return [
            [''],
            ['_IDREF'],
            ['IDREF1 IDREF2'],
        ];
    }

    /**
     * @param $value
     * @dataProvider validAriaLabelledByAttributesProvider
     */
    public function testValidAriaOwnsAttributes($value)
    {
        $span = new Span();
        $span->setAriaOwns($value);

        $this::assertEquals($value, $span->getAriaOwns());
    }

    /**
     * @return array
     */
    public function validAriaOwnsAttributesProvider()
    {
        return [
            [''],
            ['_IDREF'],
            ['IDREF1 IDREF2'],
        ];
    }

    /**
     * @param $value
     * @dataProvider validAriaLevelAttributesProvider
     */
    public function testValidAriaLevelAttributes($value)
    {
        $span = new Span();
        $span->setAriaLevel($value);

        $this::assertEquals((string)$value, $span->getAriaLevel());
    }

    /**
     * @return array
     */
    public function validAriaLevelAttributesProvider()
    {
        return [
            [''],
            ['1'],
            [1],
            ['20'],
            [20],
        ];
    }

    /**
     * @param $value
     * @dataProvider validAriaLiveAttributesProvider
     */
    public function testValidAriaLiveAttributes($value)
    {
        $span = new Span();
        $span->setAriaLive($value);

        $this::assertEquals($value, $span->getAriaLive());
    }

    /**
     * @return array
     */
    public function validAriaLiveAttributesProvider()
    {
        return [
            [AriaLive::OFF],
            [AriaLive::POLITE],
            [AriaLive::ASSERTIVE],
        ];
    }

    /**
     * @param $value
     * @dataProvider validAriaOrientationAttributesProvider
     */
    public function testValidAriaOrientationAttributes($value)
    {
        $span = new Span();
        $span->setAriaOrientation($value);

        $this::assertEquals($value, $span->getAriaOrientation());
    }

    /**
     * @return array
     */
    public function validAriaOrientationAttributesProvider()
    {
        return [
            [AriaOrientation::HORIZONTAL],
            [AriaOrientation::VERTICAL],
        ];
    }

    /**
     * @param $value
     * @dataProvider validAriaLabelAttributesProvider
     */
    public function testValidAriaLabelAttributes($value)
    {
        $span = new Span();
        $span->setAriaLabel($value);

        $this::assertEquals($value, $span->getAriaLabel());
    }

    /**
     * @return array
     */
    public function validAriaLabelAttributesProvider()
    {
        return [
            [''],
            ['A normalized string!'],
        ];
    }

    /**
     * @param bool $value
     * @dataProvider validAriaHiddenAttributesProvider
     */
    public function testValidAriaHiddenAttributes($value)
    {
        $span = new Span();
        $span->setAriaHidden($value);

        $this::assertSame($value, $span->getAriaHidden());
    }

    /**
     * @return array
     */
    public function validAriaHiddenAttributesProvider()
    {
        return [
            [false],
            [true],
        ];
    }

    /**
     * @param mixed $value
     * @param string $msg
     * @dataProvider invalidAriaControlsAttributesProvider
     */
    public function testInvalidAriaControlsAttributes($value, $msg = null)
    {
        $msg = $msg ?? "'${value}' is not a valid value for attribute 'aria-controls'.";

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage($msg);

        $span = new Span();
        $span->setAriaControls($value);
    }

    /**
     * @return array
     */
    public function invalidAriaControlsAttributesProvider()
    {
        return [
            ['999999'],
            ['ABCD 999999'],
            [false],
            [10],
            [25.55],
            [null],
            [new stdClass(), "'instance of stdClass' is not a valid value for attribute 'aria-controls'."],
        ];
    }

    /**
     * @param mixed $value
     * @param string $msg
     * @dataProvider invalidAriaDescribedByAttributesProvider
     */
    public function testInvalidAriaDescribedByAttributes($value, $msg = null)
    {
        $msg = $msg ?? "'${value}' is not a valid value for attribute 'aria-describedby'.";

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage($msg);

        $span = new Span();
        $span->setAriaDescribedBy($value);
    }

    /**
     * @return array
     */
    public function invalidAriaDescribedByAttributesProvider()
    {
        return [
            ['999999'],
            ['ABCD 999999'],
            [false],
            [10],
            [25.55],
            [null],
            [new stdClass(), "'instance of stdClass' is not a valid value for attribute 'aria-describedby'."],
        ];
    }

    /**
     * @param mixed $value
     * @param string $msg
     * @dataProvider invalidAriaFlowToAttributesProvider
     */
    public function testInvalidAriaFlowToAttributes($value, $msg = null)
    {
        $msg = $msg ?? "'${value}' is not a valid value for attribute 'aria-flowto'.";

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage($msg);

        $span = new Span();
        $span->setAriaFlowTo($value);
    }

    /**
     * @return array
     */
    public function invalidAriaFlowToAttributesProvider()
    {
        return [
            ['999999'],
            ['ABCD 999999'],
            [false],
            [10],
            [25.55],
            [null],
            [new stdClass(), "'instance of stdClass' is not a valid value for attribute 'aria-flowto'."],
        ];
    }

    /**
     * @param mixed $value
     * @param string $msg
     * @dataProvider invalidAriaLabelledByAttributesProvider
     */
    public function testInvalidAriaLabelledByAttributes($value, $msg = null)
    {
        $msg = $msg ?? "'${value}' is not a valid value for attribute 'aria-labelledby'.";

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage($msg);

        $span = new Span();
        $span->setAriaLabelledBy($value);
    }

    /**
     * @return array
     */
    public function invalidAriaLabelledByAttributesProvider()
    {
        return [
            ['999999'],
            ['ABCD 999999'],
            [false],
            [10],
            [25.55],
            [null],
            [new stdClass(), "'instance of stdClass' is not a valid value for attribute 'aria-labelledby'."],
        ];
    }

    /**
     * @param mixed $value
     * @param string $msg
     * @dataProvider invalidAriaOwnsAttributesProvider
     */
    public function testInvalidAriaOwnsAttributes($value, $msg = null)
    {
        $msg = $msg ?? "'${value}' is not a valid value for attribute 'aria-owns'.";

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage($msg);

        $span = new Span();
        $span->setAriaOwns($value);
    }

    /**
     * @return array
     */
    public function invalidAriaOwnsAttributesProvider()
    {
        return [
            ['999999'],
            ['ABCD 999999'],
            [false],
            [10],
            [25.55],
            [null],
            [new stdClass(), "'instance of stdClass' is not a valid value for attribute 'aria-owns'."],
        ];
    }

    /**
     * @param mixed $value
     * @param string $msg
     * @dataProvider invalidAriaLevelAttributesProvider
     */
    public function testInvalidAriaLevelAttributes($value, $msg = null)
    {
        $msg = $msg ?? "'${value}' is not a valid value for attribute 'aria-level'.";

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage($msg);

        $span = new Span();
        $span->setAriaLevel($value);
    }

    /**
     * @return array
     */
    public function invalidAriaLevelAttributesProvider()
    {
        return [
            ['ABCD 999999'],
            [false],
            [null],
            [new stdClass(), "'instance of stdClass' is not a valid value for attribute 'aria-level'."],
        ];
    }

    /**
     * @param mixed $value
     * @param string $msg
     * @dataProvider invalidAriaLiveAttributesProvider
     */
    public function testInvalidAriaLiveAttributes($value, $msg = null)
    {
        $msg = $msg ?? "'${value}' is not a valid value for attribute 'aria-live'.";

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage($msg);

        $span = new Span();
        $span->setAriaLive($value);
    }

    /**
     * @return array
     */
    public function invalidAriaLiveAttributesProvider()
    {
        return [
            ['ABCD 999999'],
            [''],
            [null],
            [new stdClass(), "'instance of stdClass' is not a valid value for attribute 'aria-live'."],
        ];
    }

    /**
     * @param mixed $value
     * @param string $msg
     * @dataProvider invalidAriaOrientationAttributesProvider
     */
    public function testInvalidAriaOrientationAttributes($value, $msg = null)
    {
        $msg = $msg ?? "'${value}' is not a valid value for attribute 'aria-orientation'.";

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage($msg);

        $span = new Span();
        $span->setAriaOrientation($value);
    }

    /**
     * @return array
     */
    public function invalidAriaOrientationAttributesProvider()
    {
        return [
            ['ABCD 999999'],
            [''],
            [null],
            [new stdClass(), "'instance of stdClass' is not a valid value for attribute 'aria-orientation'."],
        ];
    }

    /**
     * @param mixed $value
     * @param string $msg
     * @dataProvider invalidAriaLabelAttributesProvider
     */
    public function testInvalidAriaLabelAttributes($value, $msg = null)
    {
        $msg = $msg ?? "'${value}' is not a valid value for attribute 'aria-label'.";

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage($msg);

        $span = new Span();
        $span->setAriaLabel($value);
    }

    /**
     * @return array
     */
    public function invalidAriaLabelAttributesProvider()
    {
        return [
            [false],
            [10],
            [-5],
            [55.55],
            [null],
            [new stdClass(), "'instance of stdClass' is not a valid value for attribute 'aria-label'."],
        ];
    }

    /**
     * @param mixed $value
     * @param string $msg
     * @dataProvider invalidAriaHiddenAttributesProvider
     */
    public function testInvalidAriaHiddenAttributes($value, $msg = null)
    {
        $msg = $msg ?? "'${value}' is not a valid value for attribute 'aria-hidden'.";

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage($msg);

        $span = new Span();
        $span->setAriaHidden($value);
    }

    /**
     * @return array
     */
    public function invalidAriaHiddenAttributesProvider()
    {
        return [
            ['false'],
            [10],
            [-5],
            [55.55],
            [null],
            [new stdClass(), "'instance of stdClass' is not a valid value for attribute 'aria-hidden'."],
        ];
    }
}
