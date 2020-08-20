<?php

namespace qtismtest\data\content\interactions;

use InvalidArgumentException;
use qtism\data\content\interactions\SliderInteraction;
use qtismtest\QtiSmTestCase;

class SliderInteractionTest extends QtiSmTestCase
{
    public function testCreateSliderInteractionLowerBoundWrongType()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("The 'lowerBound' argument must be a float value, 'integer' given.");

        $sliderInteraction = new SliderInteraction('RESPONSE', 3, 3.33);
    }

    public function testCreateSliderInteractionUpperBoundWrongType()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("The 'upperBound' argument must be a float value, 'integer' given.");

        $sliderInteraction = new SliderInteraction('RESPONSE', 3.33, 3);
    }

    public function testSetStepNegative()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("The 'step' argument must be a positive (>= 0) integer, 'integer' given.");

        $sliderInteraction = new SliderInteraction('RESPONSE', 3.33, 4.33);
        $sliderInteraction->setStep(-5);
    }

    public function testSetStepLabelNonBoolean()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("The 'stepLabel' argument must be a boolean value, 'string' given.");

        $sliderInteraction = new SliderInteraction('RESPONSE', 3.33, 4.33);
        $sliderInteraction->setStepLabel('true');
    }

    public function testSetOrientationWrongValue()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("The 'orientation' argument must be a value from the Orientation enumeration.");

        $sliderInteraction = new SliderInteraction('RESPONSE', 3.33, 4.33);
        $sliderInteraction->setOrientation('true');
    }

    public function testSetReverseWrongValue()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("The 'reverse' argument must be a boolean value, 'string' given.");

        $sliderInteraction = new SliderInteraction('RESPONSE', 3.33, 4.33);
        $sliderInteraction->setReverse('true');
    }
}
