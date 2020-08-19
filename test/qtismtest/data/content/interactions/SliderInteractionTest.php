<?php

namespace qtismtest\data\content\interactions;

use qtism\data\content\interactions\SliderInteraction;
use qtismtest\QtiSmTestCase;

class SliderInteractionTest extends QtiSmTestCase
{
    public function testCreateSliderInteractionLowerBoundWrongType()
    {
        $this->setExpectedException(
            \InvalidArgumentException::class,
            "The 'lowerBound' argument must be a float value, 'integer' given."
        );

        $sliderInteraction = new SliderInteraction('RESPONSE', 3, 3.33);
    }

    public function testCreateSliderInteractionUpperBoundWrongType()
    {
        $this->setExpectedException(
            \InvalidArgumentException::class,
            "The 'upperBound' argument must be a float value, 'integer' given."
        );

        $sliderInteraction = new SliderInteraction('RESPONSE', 3.33, 3);
    }

    public function testSetStepNegative()
    {
        $this->setExpectedException(
            \InvalidArgumentException::class,
            "The 'step' argument must be a positive (>= 0) integer, 'integer' given."
        );

        $sliderInteraction = new SliderInteraction('RESPONSE', 3.33, 4.33);
        $sliderInteraction->setStep(-5);
    }

    public function testSetStepLabelNonBoolean()
    {
        $this->setExpectedException(
            \InvalidArgumentException::class,
            "The 'stepLabel' argument must be a boolean value, 'string' given."
        );

        $sliderInteraction = new SliderInteraction('RESPONSE', 3.33, 4.33);
        $sliderInteraction->setStepLabel('true');
    }

    public function testSetOrientationWrongValue()
    {
        $this->setExpectedException(
            \InvalidArgumentException::class,
            "The 'orientation' argument must be a value from the Orientation enumeration."
        );

        $sliderInteraction = new SliderInteraction('RESPONSE', 3.33, 4.33);
        $sliderInteraction->setOrientation('true');
    }

    public function testSetReverseWrongValue()
    {
        $this->setExpectedException(
            \InvalidArgumentException::class,
            "The 'reverse' argument must be a boolean value, 'string' given."
        );

        $sliderInteraction = new SliderInteraction('RESPONSE', 3.33, 4.33);
        $sliderInteraction->setReverse('true');
    }
}
