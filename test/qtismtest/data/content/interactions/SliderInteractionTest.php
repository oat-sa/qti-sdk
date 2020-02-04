<?php

namespace qtismtest\data\content\interactions;

use qtismtest\QtiSmTestCase;
use qtism\data\content\interactions\SliderInteraction;

class SliderInteractionTest extends QtiSmTestCase
{
    public function testCreateSliderInteractionLowerBoundWrongType()
    {
        $this->setExpectedException(
            '\\InvalidArgumentException',
            "The 'lowerBound' argument must be a float value, 'integer' given."
        );
        
        $sliderInteraction = new SliderInteraction('RESPONSE', 3, 3.33);
    }
    
    public function testCreateSliderInteractionUpperBoundWrongType()
    {
        $this->setExpectedException(
            '\\InvalidArgumentException',
            "The 'upperBound' argument must be a float value, 'integer' given."
        );
        
        $sliderInteraction = new SliderInteraction('RESPONSE', 3.33, 3);
    }
    
    public function testSetStepNegative()
    {
        $this->setExpectedException(
            '\\InvalidArgumentException',
            "The 'step' argument must be a positive (>= 0) integer, 'integer' given."
        );
        
        $sliderInteraction = new SliderInteraction('RESPONSE', 3.33, 4.33);
        $sliderInteraction->setStep(-5);
    }
    
    public function testSetStepLabelNonBoolean()
    {
        $this->setExpectedException(
            '\\InvalidArgumentException',
            "The 'stepLabel' argument must be a boolean value, 'string' given."
        );
        
        $sliderInteraction = new SliderInteraction('RESPONSE', 3.33, 4.33);
        $sliderInteraction->setStepLabel('true');
    }
    
    public function testSetOrientationWrongValue()
    {
        $this->setExpectedException(
            '\\InvalidArgumentException',
            "The 'orientation' argument must be a value from the Orientation enumeration."
        );
        
        $sliderInteraction = new SliderInteraction('RESPONSE', 3.33, 4.33);
        $sliderInteraction->setOrientation('true');
    }
    
    public function testSetReverseWrongValue()
    {
        $this->setExpectedException(
            '\\InvalidArgumentException',
            "The 'reverse' argument must be a boolean value, 'string' given."
        );
        
        $sliderInteraction = new SliderInteraction('RESPONSE', 3.33, 4.33);
        $sliderInteraction->setReverse('true');
    }
}
