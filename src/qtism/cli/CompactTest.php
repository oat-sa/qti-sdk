<?php

namespace qtism\cli;

use cli\Arguments;
use qtism\data\storage\xml\XmlCompactDocument;
use qtism\data\storage\xml\XmlDocument;
use qtism\data\storage\xml\XmlStorageException;
use ReflectionException;

class CompactTest extends Cli
{

    protected function run()
    {
        $arguments = $this->getArguments();

        $doc = new XmlDocument();
        try {
            $doc->load($arguments['input']);
            $compactDoc = XmlCompactDocument::createFromXmlAssessmentTestDocument($doc);
            $compactDoc->save($arguments['output']);

        } catch (XmlStorageException $e) {
            $this->fail('Input XML Test document could not be read.');
        } catch (ReflectionException $e) {
            $this->fail('An unexpected error occurred.');
        }
    }

    protected function setupArguments(): Arguments
    {
        $arguments = new Arguments(['strict' => false]);

        // -- Options
        // Session option.
        $arguments->addOption(
            ['input'],
            [
                'description' => 'The input QTI test file.',
            ]
        );

        // XML option.
        $arguments->addOption(
            ['output'],
            [
                'description' => 'The output QTI compact test file.',
            ]
        );

        return $arguments;
    }

    protected function checkArguments()
    {
        $arguments = $this->getArguments();

        if ($arguments['input'] === null) {
            $this->fail('Please provide the --input argument.');
        } else if ($arguments['output'] === null) {
            $this->fail('Please provide the --output argument.');
        }
    }
}