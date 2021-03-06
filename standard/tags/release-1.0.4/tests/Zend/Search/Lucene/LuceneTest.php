<?php
/**
 * @category   Zend
 * @package    Zend_Search_Lucene
 * @subpackage UnitTests
 */


/**
 * Zend_Search_Lucene
 */
require_once 'Zend/Search/Lucene.php';

/**
 * PHPUnit test case
 */
require_once 'PHPUnit/Framework/TestCase.php';


/**
 * @category   Zend
 * @package    Zend_Search_Lucene
 * @subpackage UnitTests
 */
class Zend_Search_Lucene_LuceneTest extends PHPUnit_Framework_TestCase
{
    public function testCreate()
    {
        $index = Zend_Search_Lucene::create(dirname(__FILE__) . '/_files/_index');

        $this->assertTrue($index instanceof Zend_Search_Lucene_Interface);
    }

    public function testOpen()
    {
        $index = Zend_Search_Lucene::open(dirname(__FILE__) . '/_files/_indexSample');

        $this->assertTrue($index instanceof Zend_Search_Lucene_Interface);
    }

    public function testDefaultSearchField()
    {
        $currentDefaultSearchField = Zend_Search_Lucene::getDefaultSearchField();
        $this->assertEquals($currentDefaultSearchField, null);

        Zend_Search_Lucene::setDefaultSearchField('anotherField');
        $this->assertEquals(Zend_Search_Lucene::getDefaultSearchField(), 'anotherField');

        Zend_Search_Lucene::setDefaultSearchField($currentDefaultSearchField);
    }

    public function testCount()
    {
        $index = Zend_Search_Lucene::open(dirname(__FILE__) . '/_files/_indexSample');

        $this->assertEquals($index->count(), 10);
    }

    public function testMaxDoc()
    {
        $index = Zend_Search_Lucene::open(dirname(__FILE__) . '/_files/_indexSample');

        $this->assertEquals($index->maxDoc(), 10);
    }

    public function testNumDocs()
    {
        $index = Zend_Search_Lucene::open(dirname(__FILE__) . '/_files/_indexSample');

        $this->assertEquals($index->numDocs(), 9);
    }

    public function testIsDeleted()
    {
        $index = Zend_Search_Lucene::open(dirname(__FILE__) . '/_files/_indexSample');

        $this->assertFalse($index->isDeleted(3));
        $this->assertTrue($index->isDeleted(6));
    }

    public function testMaxBufferedDocs()
    {
        $index = Zend_Search_Lucene::open(dirname(__FILE__) . '/_files/_indexSample');

        $currentMaxBufferedDocs = $index->getMaxBufferedDocs();

        $index->setMaxBufferedDocs(234);
        $this->assertEquals($index->getMaxBufferedDocs(), 234);

        $index->setMaxBufferedDocs($currentMaxBufferedDocs);
    }

    public function testMaxMergeDocs()
    {
        $index = Zend_Search_Lucene::open(dirname(__FILE__) . '/_files/_indexSample');

        $currentMaxMergeDocs = $index->getMaxMergeDocs();

        $index->setMaxMergeDocs(34);
        $this->assertEquals($index->getMaxMergeDocs(), 34);

        $index->setMaxMergeDocs($currentMaxMergeDocs);
    }

    public function testMergeFactor()
    {
        $index = Zend_Search_Lucene::open(dirname(__FILE__) . '/_files/_indexSample');

        $currentMergeFactor = $index->getMergeFactor();

        $index->setMergeFactor(113);
        $this->assertEquals($index->getMergeFactor(), 113);

        $index->setMergeFactor($currentMergeFactor);
    }

    public function testFind()
    {
        $index = Zend_Search_Lucene::open(dirname(__FILE__) . '/_files/_indexSample');

        $hits = $index->find('submitting');
        $this->assertEquals(count($hits), 3);
    }

    public function testGetFieldNames()
    {
        $index = Zend_Search_Lucene::open(dirname(__FILE__) . '/_files/_indexSample');

        $this->assertTrue(array_values($index->getFieldNames()) == array('path', 'modified', 'contents'));
    }

    public function testGetDocument()
    {
        $index = Zend_Search_Lucene::open(dirname(__FILE__) . '/_files/_indexSample');

        $doc = $index->getDocument(3);

        $this->assertTrue($doc instanceof Zend_Search_Lucene_Document);
        $this->assertEquals($doc->path, 'IndexSource/about-pear.html');
    }

    public function testHasTerm()
    {
        $index = Zend_Search_Lucene::open(dirname(__FILE__) . '/_files/_indexSample');

        $this->assertTrue($index->hasTerm(new Zend_Search_Lucene_Index_Term('packages', 'contents')));
        $this->assertFalse($index->hasTerm(new Zend_Search_Lucene_Index_Term('nonusedword', 'contents')));
    }

    public function testTermDocs()
    {
        $index = Zend_Search_Lucene::open(dirname(__FILE__) . '/_files/_indexSample');

        $this->assertTrue(array_values( $index->termDocs(new Zend_Search_Lucene_Index_Term('packages', 'contents')) ) ==
                          array(0, 2, 6, 7, 8));
    }

    public function testTermPositions()
    {
        $index = Zend_Search_Lucene::open(dirname(__FILE__) . '/_files/_indexSample');

        $this->assertTrue($index->termPositions(new Zend_Search_Lucene_Index_Term('packages', 'contents')) ==
                          array(0 => array(174),
                                2 => array(40, 742),
                                6 => array(6, 156, 163),
                                7 => array(194),
                                8 => array(55, 190, 405)));
    }

    public function testDocFreq()
    {
        $index = Zend_Search_Lucene::open(dirname(__FILE__) . '/_files/_indexSample');

        $this->assertEquals($index->docFreq(new Zend_Search_Lucene_Index_Term('packages', 'contents')), 5);
    }

    public function testGetSimilarity()
    {
        $index = Zend_Search_Lucene::open(dirname(__FILE__) . '/_files/_indexSample');

        $this->assertTrue($index->getSimilarity() instanceof Zend_Search_Lucene_Search_Similarity);
    }

    public function testNorm()
    {
        $index = Zend_Search_Lucene::open(dirname(__FILE__) . '/_files/_indexSample');

        $this->assertTrue(abs($index->norm(3, 'contents') - 0.054688) < 0.000001);
    }

    public function testHasDeletions()
    {
        $index = Zend_Search_Lucene::open(dirname(__FILE__) . '/_files/_indexSample');

        $this->assertTrue($index->hasDeletions());
    }

    public function testDelete()
    {
        $index = Zend_Search_Lucene::open(dirname(__FILE__) . '/_files/_indexSample');

        $this->assertFalse($index->isDeleted(2));
        $index->delete(2);
        $this->assertTrue($index->isDeleted(2));

        unset($index);


        $index1 = Zend_Search_Lucene::open(dirname(__FILE__) . '/_files/_indexSample');
        $this->assertTrue($index1->isDeleted(2));
        unset($index1);

        unlink(dirname(__FILE__) . '/_files/_indexSample/_1.del');
    }

    public function testAddDocument()
    {
        $index = Zend_Search_Lucene::create(dirname(__FILE__) . '/_files/_index');

        $indexSourceDir = dirname(__FILE__) . '/_files/_indexSource';
        $dir = opendir($indexSourceDir);
        while (($file = readdir($dir)) !== false) {
            if (is_dir($indexSourceDir . '/' . $file)) {
                continue;
            }
            if (strcasecmp(substr($file, strlen($file)-5), '.html') != 0) {
                continue;
            }

            // Create new Document from a file
            $doc = new Zend_Search_Lucene_Document();
            $doc->addField(Zend_Search_Lucene_Field::Text('path', 'IndexSource/' . $file));
            $doc->addField(Zend_Search_Lucene_Field::Keyword( 'modified', filemtime($indexSourceDir . '/' . $file) ));

            $f = fopen($indexSourceDir . '/' . $file,'rb');
            $byteCount = filesize($indexSourceDir . '/' . $file);

            $data = '';
            while ( $byteCount > 0 && ($nextBlock = fread($f, $byteCount)) != false ) {
                $data .= $nextBlock;
                $byteCount -= strlen($nextBlock);
            }
            fclose($f);

            $doc->addField(Zend_Search_Lucene_Field::Text('contents', $data, 'ISO-8859-1'));

            // Add document to the index
            $index->addDocument($doc);
        }
        closedir($dir);

        unset($index);

        $index1 = Zend_Search_Lucene::open(dirname(__FILE__) . '/_files/_index');
        $this->assertTrue($index1 instanceof Zend_Search_Lucene_Interface);
    }

    public function testOptimize()
    {
        $index = Zend_Search_Lucene::create(dirname(__FILE__) . '/_files/_index');

        $index->setMaxBufferedDocs(2);

        $indexSourceDir = dirname(__FILE__) . '/_files/_indexSource';
        $dir = opendir($indexSourceDir);
        while (($file = readdir($dir)) !== false) {
            if (is_dir($indexSourceDir . '/' . $file)) {
                continue;
            }
            if (strcasecmp(substr($file, strlen($file)-5), '.html') != 0) {
                continue;
            }

            // Create new Document from a file
            $doc = new Zend_Search_Lucene_Document();
            $doc->addField(Zend_Search_Lucene_Field::Keyword('path', 'IndexSource/' . $file));
            $doc->addField(Zend_Search_Lucene_Field::Keyword( 'modified', filemtime($indexSourceDir . '/' . $file) ));

            $f = fopen($indexSourceDir . '/' . $file,'rb');
            $byteCount = filesize($indexSourceDir . '/' . $file);

            $data = '';
            while ( $byteCount > 0 && ($nextBlock = fread($f, $byteCount)) != false ) {
                $data .= $nextBlock;
                $byteCount -= strlen($nextBlock);
            }
            fclose($f);

            $doc->addField(Zend_Search_Lucene_Field::Text('contents', $data, 'ISO-8859-1'));

            // Add document to the index
            $index->addDocument($doc);
        }
        closedir($dir);
        unset($index);

        $index1 = Zend_Search_Lucene::open(dirname(__FILE__) . '/_files/_index');
        $this->assertTrue($index1 instanceof Zend_Search_Lucene_Interface);
        $pathTerm = new Zend_Search_Lucene_Index_Term('IndexSource/contributing.html', 'path');
        $contributingDocs = $index1->termDocs($pathTerm);
        foreach ($contributingDocs as $id) {
            $index1->delete($id);
        }
        $index1->optimize();
        unset($index1);

        $index2 = Zend_Search_Lucene::open(dirname(__FILE__) . '/_files/_index');
        $this->assertTrue($index2 instanceof Zend_Search_Lucene_Interface);

        $hits = $index2->find('submitting');
        $this->assertEquals(count($hits), 3);
    }

    public function testTerms()
    {
        $index = Zend_Search_Lucene::open(dirname(__FILE__) . '/_files/_indexSample');

        $this->assertEquals(count($index->terms()), 607);
    }
}
