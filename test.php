<?php

use qtism\data\storage\xml\XmlResultDocument;

require_once __DIR__ . '/vendor/autoload.php';

$xmlDocument = new XmlResultDocument();
$xml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<assessmentResult xmlns="http://www.imsglobal.org/xsd/imsqti_result_v2p1">
  <context sourcedId="i5e4195ae583c374de82f78712be21a"/>
  <itemResult identifier="item-1" datestamp="2020-02-11T09:12:15.703" sessionStatus="final">
    <responseVariable identifier="RESPONSE" cardinality="multiple" baseType="identifier">
      <candidateResponse>
        <value><![CDATA[]]></value>
      </candidateResponse>
    </responseVariable>
  </itemResult>
</assessmentResult>
XML;
$xmlDocument->loadFromString($xml);

return $xmlDocument->getDocumentComponent();
