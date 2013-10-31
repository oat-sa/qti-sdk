<?php

require_once (dirname(__FILE__) . '/../../../../../QtiSmTestCase.php');

class TableMarshallerTest extends QtiSmTestCase {

	public function testMarshall() {
        
	}
	
	public function testUnmarshall() {
	    $table = $this->createComponentFromXml('
	        <table id="my-table" class="qti table">
                <tbody>
	                <tr>
	                    <td>John</td>
	                    <td>Dunbar</td>
	                </tr>
	                <tr>
                        <td>Flash</td>
	                    <td>Gordon</td>
	                </tr>
	            </tbody>
	        </table>
	    ');
	    
	    $this->assertInstanceOf('qtism\\data\\content\\xhtml\\tables\\Table', $table);
	    $this->assertEquals('my-table', $table->getId());
	    $this->assertEquals('qti table', $table->getClass());
	    
	    $tbodies = $table->getTbodies();
	    $this->assertEquals(1, count($tbodies));
	    
	    $trs = $tbodies[0]->getContent();
	    $this->assertEquals(2, count($trs));
	    
	    $tr1 = $trs[0];
	    $this->assertEquals(2, count($tr1->getContent()));
	    
	    $tds = $tr1->getContent();
	    $tdContent = $tds[0]->getContent();
	    $this->assertEquals('John', $tdContent[0]->getContent());
	    
	    $tdContent = $tds[1]->getContent();
	    $this->assertEquals('Dunbar', $tdContent[0]->getContent());
	    
	    $tr2 = $trs[1];
	    $this->assertEquals(2, count($tr2->getContent()));
	}
}