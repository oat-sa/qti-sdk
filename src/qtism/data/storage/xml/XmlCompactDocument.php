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
 * Copyright (c) 2013-2016 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 * @license GPLv2
 */

namespace qtism\data\storage\xml;

use qtism\common\collections\IdentifierCollection;
use qtism\data\AssessmentItem;
use qtism\data\TestFeedbackRef;
use qtism\data\content\RubricBlockRef;
use qtism\data\QtiComponentIterator;
use qtism\data\QtiComponent;
use qtism\data\TestPart;
use qtism\data\ExtendedAssessmentSection;
use qtism\data\AssessmentSectionRef;
use qtism\data\storage\FileResolver;
use qtism\data\ExtendedAssessmentItemRef;
use qtism\data\AssessmentSection;
use qtism\data\AssessmentItemRef;
use qtism\data\storage\LocalFileResolver;
use qtism\data\AssessmentTest;
use qtism\data\ExtendedAssessmentTest;
use qtism\data\ExtendedTestPart;
use qtism\data\storage\xml\marshalling\CompactMarshallerFactory;
use qtism\data\state\Utils as StateUtils;
use \Exception;
use \DOMElement;
use \SplObjectStorage;

/**
 * The XmlCompactDocument class represents a test and the needed information from its items to be runnable.
 * 
 * Compacting a test within items information to represent a test as a single unit. This class references
 * the minimal data required to make a test runnable. By runnable, we mean a test that can be instantiated,
 * to represent the item flow that will be presented to the candidate. In other words, the intrinsic content 
 * of the test in addition with item's:
 * 
 * * Response Processing
 * * Variable Declarations
 * * Modal feedback conditions
 *
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class XmlCompactDocument extends XmlDocument
{
    /**
     * Whether or not the rubricBlock elements
     * must be separated from the core document.
     *
     * @var boolean
     */
    private $explodeRubricBlocks = false;
    
    /**
     * Whether or not the testFeedback elements
     * must be separated from the core document.
     * 
     * @var boolean
     */
    private $explodeTestFeedbacks = false;

    /**
     * Whether or not the rubrickBlock components contained in the document should be separated from the document.
     *
     * If $explodedRubricBlocks is set to true, a call to XmlCompactDocument::save() will make the following rules to be applied:
     *
     * * rubricBlock components will be removed from the document.
     * * a replacement of the rubricBlock components by rubricBlockRef components with a suitable value for identifier and href attributes will occur.
     * * place the substituted rubricBlock content in separate QTI-XML files, in a valid location and with a valid name regarding the generated rubricBlockRef components.
     *
     * @param boolean $explodeRubricBlocks Wheter rubrickBlock components must be exploded into multiple documents and replaced by rubricBlockRef components.
     */
    public function setExplodeRubricBlocks($explodeRubricBlocks)
    {
        $this->explodeRubricBlocks = $explodeRubricBlocks;
    }

    /**
     * Whether or not the rubricBlock components contained in the document should be separated from the document.
     *
     * @return boolean
     */
    public function mustExplodeRubricBlocks()
    {
        return $this->explodeRubricBlocks;
    }
    
    /**
     * Whether or not the testFeedback components contained in the document should be separated from the document.
     * 
     * If $explodeTestFeedbacs is set to true, a call to XmlCompactDocument::save() will make the following rules to be applied:
     * 
     * * testFeedback elements will be removed from the document.
     * * a replacement of the testFeedback components by testFeedbackRef components with a suitable value for the href attribute will occur.
     * * place the substituted testFeedback contents in separate QTI-XML files, in a valid location and with a valid name regarding the generated testFeedbackRef components.
     * 
     * @param boolean $explodeTestFeedbacks
     */
    public function setExplodeTestFeedbacks($explodeTestFeedbacks)
    {
        $this->explodeTestFeedbacks = $explodeTestFeedbacks;
    }
    
    /**
     * Whether or not the testFeedback components contained in the document should be separated from the document.
     * 
     * @return boolean
     */
    public function mustExplodeTestFeedbacks()
    {
        return $this->explodeTestFeedbacks;
    }

    /**
	 * Override of XmlDocument::createMarshallerFactory in order
	 * to return an appropriate CompactMarshallerFactory.
	 *
	 * @return \qtism\data\storage\xml\marshalling\CompactMarshallerFactory A CompactMarshallerFactory object.
	 */
    protected function createMarshallerFactory()
    {
        return new CompactMarshallerFactory();
    }

    /**
	 * Create a new instance of XmlCompactDocument from an XmlAssessmentTestDocument.
	 *
	 * @param \qtism\data\storage\xml\XmlDocument $xmlAssessmentTestDocument An XmlAssessmentTestDocument object you want to store as a compact XML file.
     * @param \qtism\data\storage\FileResolver (optional) $resolver A resolver aiming at resolving assessmentSectionRef and assessmentItemRef components.
	 * @return \qtism\data\storage\xml\XmlCompactDocument An XmlCompactAssessmentTestDocument object.
	 * @throws \qtism\data\storage\xml\XmlStorageException If an error occurs while transforming the XmlAssessmentTestDocument object into an XmlCompactAssessmentTestDocument object.
	 */
    public static function createFromXmlAssessmentTestDocument(XmlDocument $xmlAssessmentTestDocument, FileResolver $resolver = null)
    {
        $compactAssessmentTest = new XmlCompactDocument();
        $identifier = $xmlAssessmentTestDocument->getDocumentComponent()->getIdentifier();
        $title = $xmlAssessmentTestDocument->getDocumentComponent()->getTitle();

        $assessmentTest = new AssessmentTest($identifier, $title);
        $assessmentTest->setOutcomeDeclarations($xmlAssessmentTestDocument->getDocumentComponent()->getOutcomeDeclarations());
        $assessmentTest->setOutcomeProcessing($xmlAssessmentTestDocument->getDocumentComponent()->getOutcomeProcessing());
        $assessmentTest->setTestFeedbacks($xmlAssessmentTestDocument->getDocumentComponent()->getTestFeedbacks());
        $assessmentTest->setTestParts($xmlAssessmentTestDocument->getDocumentComponent()->getTestParts());
        $assessmentTest->setTimeLimits($xmlAssessmentTestDocument->getDocumentComponent()->getTimeLimits());
        $assessmentTest->setToolName($xmlAssessmentTestDocument->getDocumentComponent()->getToolName());
        $assessmentTest->setToolVersion($xmlAssessmentTestDocument->getDocumentComponent()->getToolVersion());

        if (is_null($resolver) === true) {
            $resolver = new LocalFileResolver($xmlAssessmentTestDocument->getUrl());
        } else {
            $resolver->setBasePath($xmlAssessmentTestDocument->getUrl());
        }

        // It simply consists of replacing assessmentItemRef and assessmentSectionRef elements.
        $trail = array(); // trailEntry[0] = a component, trailEntry[1] = from where we are coming (parent).
        $mark = array();
        $root = $xmlAssessmentTestDocument->getDocumentComponent();
        
        // Stores the resolved assessmentSection <-> XmlDocument documents during the compaction process.
        $sectionStore = new SplObjectStorage();

        array_push($trail, array($root, $root));

        while (count($trail) > 0) {
            $trailer = array_pop($trail);
            $component = $trailer[0];
            $previous = $trailer[1];

            if (!in_array($component, $mark) && count($component->getComponents()) > 0) {

                // First pass on a hierarchical node... go deeper in the n-ary tree.
                array_push($mark, $component);

                // We want to go back on this component.
                array_push($trail, $trailer);

                // Prepare further exploration.
                foreach ($component->getComponents()->getArrayCopy() as $comp) {
                    array_push($trail, array($comp, $component));
                }
            } elseif (in_array($component, $mark) || count($component->getComponents()) === 0) {

                // Second pass on a hierarchical node (we are bubbling up accross the n-ary tree)
                // OR
                // Leaf node
                if ($component instanceof AssessmentItemRef) {

                    // Transform the ref in an compact extended ref.
                    $compactRef = ExtendedAssessmentItemRef::createFromAssessmentItemRef($component);
                    // find the old one and replace it.
                    $previousParts = $previous->getSectionParts();
                    foreach ($previousParts as $k => $previousPart) {
                        if ($previousParts[$k] === $component) {

                            // If the previous processed component is an XmlAssessmentSectionDocument,
                            // it means that the given baseUri must be adapted.
                            $baseUri = $xmlAssessmentTestDocument->getUrl();
                            if ($previous instanceof AssessmentSection && isset($sectionStore[$previous])) {
                                $baseUri = $sectionStore[$previous]->getUrl();
                            }

                            $resolver->setBasePath($baseUri);
                            self::resolveAssessmentItemRef($compactRef, $resolver);

                            $previousParts->replace($component, $compactRef);
                            break;
                        }
                    }
                } elseif ($component instanceof AssessmentSectionRef) {
                    // We follow the unreferenced AssessmentSection as if it was
                    // the 1st pass.
                    $baseUri = $xmlAssessmentTestDocument->getUrl();
                    if ($previous instanceof AssessmentSection && isset($sectionStore[$previous])) {
                        $baseUri = $sectionStore[$previous]->getUrl();
                    }
                    
                    $resolver->setBasePath($baseUri);
                    
                    $assessmentSectionDocument = self::resolveAssessmentSectionRef($component, $resolver);
                    $assessmentSection = $assessmentSectionDocument->getDocumentComponent();
                    $sectionStore[$assessmentSection] = $assessmentSectionDocument;
                    
                    $previousParts = $previous->getSectionParts();
                    foreach ($previousParts as $k => $previousPart) {
                        if ($previousParts[$k] === $component) {
                            $previousParts->replace($component, $assessmentSection);
                            break;
                        }
                    }

                    array_push($trail, array($assessmentSection, $previous));
                } elseif ($component instanceof AssessmentSection) {
                    $assessmentSection = ExtendedAssessmentSection::createFromAssessmentSection($component);

                    $previousParts = ($previous instanceof TestPart) ? $previous->getAssessmentSections() : $previous->getSectionParts();
                    foreach ($previousParts as $k => $previousPart) {
                        if ($previousParts[$k] === $component) {
                            $previousParts->replace($component, $assessmentSection);
                            break;
                        }
                    }
                } elseif ($component instanceof TestPart) {
                    $testPart = ExtendedTestPart::createFromTestPart($component);
                    $root->getTestParts()->replace($component, $testPart);
                } elseif ($component === $root) {
                    // 2nd pass on the root, we have to stop.
                    $compactAssessmentTest->setDocumentComponent(ExtendedAssessmentTest::createFromAssessmentTest($assessmentTest));

                    return $compactAssessmentTest;
                }
            }
        }
    }

    /**
	 * Dereference the file referenced by an assessmentItemRef and add
	 * outcome/responseDeclarations to the compact one.
	 *
	 * @param \qtism\data\ExtendedAssessmentItemRef $compactAssessmentItemRef A previously instantiated ExtendedAssessmentItemRef object.
	 * @param \qtism\data\storage\FileResolver $resolver The Resolver to be used to resolver AssessmentItemRef's href attribute.
	 * @throws \qtism\data\storage\xml\XmlStorageException If an error occurs (e.g. file not found at URI or unmarshalling issue) during the dereferencing.
	 */
    protected static function resolveAssessmentItemRef(ExtendedAssessmentItemRef $compactAssessmentItemRef, FileResolver $resolver)
    {
        try {
            $href = $resolver->resolve($compactAssessmentItemRef->getHref());

            $doc = new XmlDocument();
            $doc->load($href);
            
            // Resolve external documents.
            $doc->xInclude();
            $doc->resolveTemplateLocation();

            /** @var AssessmentItem $item */
            $item = $doc->getDocumentComponent();

            foreach ($item->getResponseDeclarations() as $resp) {
                $compactAssessmentItemRef->addResponseDeclaration($resp);
            }

            foreach ($item->getOutcomeDeclarations() as $out) {
                $compactAssessmentItemRef->addOutcomeDeclaration($out);
            }
            
            foreach ($item->getTemplateDeclarations() as $tpl) {
                $compactAssessmentItemRef->addTemplateDeclaration($tpl);
            }
            
            foreach ($item->getModalFeedbackRules() as $modalFeedbackRule) {
                $compactAssessmentItemRef->addModalFeedbackRule($modalFeedbackRule);
            }

            if ($item->hasResponseProcessing() === true) {
                $compactAssessmentItemRef->setResponseProcessing($item->getResponseProcessing());
            }
            
            if ($item->hasTemplateProcessing() === true) {
                $compactAssessmentItemRef->setTemplateProcessing($item->getTemplateProcessing());
            }
            
            $compactAssessmentItemRef->setShufflings($item->getShufflings());
            $compactAssessmentItemRef->setAdaptive($item->isAdaptive());
            $compactAssessmentItemRef->setTimeDependent($item->isTimeDependent());
            $compactAssessmentItemRef->setEndAttemptIdentifiers($item->getEndAttemptIdentifiers());
            $compactAssessmentItemRef->setResponseValidityConstraints($item->getResponseValidityConstraints());
            $compactAssessmentItemRef->setTitle($item->getTitle());
            $compactAssessmentItemRef->setLabel($item->getLabel());
        } catch (Exception $e) {
            $msg = "An error occured while unreferencing item reference with identifier '" . $compactAssessmentItemRef->getIdentifier() . "'.";
            throw new XmlStorageException($msg, XmlStorageException::RESOLUTION, $e);
        }
    }

    /**
	 * Dereference the file referenced by an assessmentSectionRef.
     * 
     * The xinclude elements in the target assessmentSection file will be resolved at the same time.
	 *
	 * @param \qtism\data\AssessmentSectionRef $assessmentSectionRef An AssessmentSectionRef object to dereference.
	 * @param \qtism\data\storage\FileResolver $resolver The Resolver object to be used to resolve AssessmentSectionRef's href attribute.
	 * @throws \qtism\data\storage\xml\XmlStorageException If an error occurs while dereferencing the referenced file.
	 * @return \qtism\data\XmlDocument The AssessmentSection referenced by $assessmentSectionRef as an XmlDocument object.
	 */
    protected static function resolveAssessmentSectionRef(AssessmentSectionRef $assessmentSectionRef, FileResolver $resolver)
    {
        try {
            $href = $resolver->resolve($assessmentSectionRef->getHref());

            $doc = new XmlDocument();
            $doc->load($href);
            $doc->xInclude();

            return $doc;
        } catch (XmlStorageException $e) {
            $msg = "An error occured while unreferencing section reference with identifier '" . $assessmentSectionRef->getIdentifier() . "'.";
            throw new XmlStorageException($msg, XmlStorageException::RESOLUTION, $e);
        }
    }

    /**
	 * Validate the compact AssessmentTest XML document according to the relevant XSD schema.
	 * If $filename is provided, the file pointed by $filename will be used instead
	 * of the default schema.
	 *
	 * @param string $filename An optional filename to force the validation against a particular schema.
	 * @return boolean
	 */
    public function schemaValidate($filename = '')
    {
        if (empty($filename)) {
            $dS = DIRECTORY_SEPARATOR;
            // default xsd for AssessmentTest.
            $filename = dirname(__FILE__) . $dS . 'schemes' . $dS . 'qticompact_v1p0.xsd';
        }

        parent::schemaValidate($filename);
    }

    /**
	 * Override of XmlDocument.
	 *
	 * Specifices the correct XSD schema locations and main namespace
	 * for the root element of a Compact XML document.
	 *
	 * @param \DOMElement $rootElement The root element of a compact XML document.
	 */
    public function decorateRootElement(DOMElement $rootElement)
    {
        $rootElement->setAttribute('xmlns', "http://www.imsglobal.org/xsd/imsqti_v2p1");
        $rootElement->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
        $rootElement->setAttributeNS('http://www.w3.org/2001/XMLSchema-instance', 'xsi:schemaLocation', "http://www.taotesting.com/xsd/qticompact_v1p0.xsd");
    }

    /**
	 * @see \qtism\data\storage\xml\XmlDocument::beforeSave()
	 */
    public function beforeSave(QtiComponent $documentComponent, $uri)
    {
        // Take care of rubricBlock explosion. Transform actual rubricBlocks in rubricBlockRefs.
        if ($this->mustExplodeRubricBlocks() === true) {
            foreach ($this->explodeRubricBlocks() as $href => $rubricBlock) {
                try {
                    $doc = new XmlDocument();
                    $doc->setDocumentComponent($rubricBlock);

                    $pathinfo = pathinfo($uri);
                    $doc->save($pathinfo['dirname'] . DIRECTORY_SEPARATOR . $href);
                } catch (XmlStorageException $e) {
                    $msg = "An error occured while creating external rubrickBlock definition(s).";
                    throw new XmlStorageException($msg, XmlStorageException::UNKNOWN, $e);
                }
            }
        }
        
        // Take care of testFeedback explosion. Transform actual testFeedbacks in testFeedbackRefs.
        if ($this->mustExplodeTestFeedbacks() === true) {
            $iterator = new QtiComponentIterator($documentComponent, array('testFeedback'));
            $testPartCount = new SplObjectStorage();
            $testCount = 0;
            
            foreach ($iterator as $testFeedback) {
                $parent = $iterator->parent();
                
                if ($parent instanceof TestPart) {
                    if (isset($testPartCount[$parent]) === false) {
                        $testPartCount[$parent] = 0;
                    }
                    
                    $testPartCount[$parent] = $testPartCount[$parent] + 1;
                    $occurence = $testPartCount[$parent];
                } else {
                    // It's a testFeedback related to an assessmentTest.
                    $testCount += 1;
                    $occurence = $testCount;
                }
                
                $parentId = $parent->getIdentifier();
                $href = "./testFeedback_TF_${parentId}_${occurence}.xml";
                
                // Generate the document.
                $doc = new XmlDocument();
                $doc->setDocumentComponent($testFeedback);
                
                try {
                    $pathinfo = pathinfo($uri);
                    $doc->save($pathinfo['dirname'] . DIRECTORY_SEPARATOR . $href);
                    
                    $parent->getTestFeedbacks()->remove($testFeedback);
                    $testFeedbackRefs = $parent->getTestFeedbackRefs();
                    $testFeedbackRefs[] = new TestFeedbackRef(
                        $testFeedback->getIdentifier(),
                        $testFeedback->getOutcomeIdentifier(),
                        $testFeedback->getAccess(),
                        $testFeedback->getShowHide(),
                        $href
                    );
                } catch (XmlStorageException $e) {
                    $msg = "An error occured while creating external testFeedback definition(s).";
                    throw new XmlStorageException($msg, XmlStorageException::UNKNOWN, $e);
                } 
            }
        }
    }

    /**
     * Explode Rubric Blocks into RubricBlockRefs.
     *
     * @return array where keys are RubricBlockRefs href and values are RubricBlocs as QtiComponent objets.
     */
    public function explodeRubricBlocks() {
        // Get all rubricBlock elements...
        $iterator = new QtiComponentIterator($this->getDocumentComponent(), array('rubricBlock'));
        $sectionCount = new SplObjectStorage();
        $references = array();

        foreach ($iterator as $rubricBlock) {
            // $section contains the assessmentSection the rubricBlock is related to.
            $section = $iterator->parent();

            // determine the occurence number of the rubricBlock relative to its section.
            if (isset($sectionCount[$section]) === false) {
                $sectionCount[$section] = 0;
            }

            $sectionCount[$section] = $sectionCount[$section] + 1;
            $occurence = $sectionCount[$section];

            // determine a suitable file name for the external rubricBlock definition.
            $rubricBlockRefId = 'RB_' . $section->getIdentifier() . '_' . $occurence;
            $href = './rubricBlock_' . $rubricBlockRefId . '.xml';

            // replace the rubric block with a reference.
            $sectionRubricBlocks = $section->getRubricBlocks();
            $sectionRubricBlocks->remove($rubricBlock);

            $sectionRubricBlockRefs = $section->getRubricBlockRefs();
            $sectionRubricBlockRefs[] = new RubricBlockRef($rubricBlockRefId, $href);

            $references[$href] = $rubricBlock;
        }

        return $references;
    }
    
    protected function inferVersion()
    {
        return '2.1';
    }
}
