<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Search_Lucene
 * @subpackage Index
 * @copyright  Copyright (c) 2006 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */


/** Zend_Search_Lucene_Index_SegmentWriter */
require_once 'Zend/Search/Lucene/Index/SegmentWriter.php';

/** Zend_Search_Lucene_Index_SegmentInfo */
require_once 'Zend/Search/Lucene/Index/SegmentInfo.php';

/** Zend_Search_Lucene_Index_SegmentMerger */
require_once 'Zend/Search/Lucene/Index/SegmentMerger.php';



/**
 * @category   Zend
 * @package    Zend_Search_Lucene
 * @subpackage Index
 * @copyright  Copyright (c) 2006 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Search_Lucene_Index_Writer
{
    /**
     * @todo Implement Analyzer substitution
     * @todo Implement Zend_Search_Lucene_Storage_DirectoryRAM and Zend_Search_Lucene_Storage_FileRAM to use it for
     *       temporary index files
     * @todo Directory lock processing
     */

    /**
     * Number of documents required before the buffered in-memory
     * documents are written into a new Segment
     *
     * Default value is 10
     *
     * @var integer
     */
    public $maxBufferedDocs = 10;

    /**
     * Largest number of documents ever merged by addDocument().
     * Small values (e.g., less than 10,000) are best for interactive indexing,
     * as this limits the length of pauses while indexing to a few seconds.
     * Larger values are best for batched indexing and speedier searches.
     *
     * Default value is PHP_INT_MAX
     *
     * @var integer
     */
    public $maxMergeDocs = PHP_INT_MAX;

    /**
     * Determines how often segment indices are merged by addDocument().
     *
     * With smaller values, less RAM is used while indexing,
     * and searches on unoptimized indices are faster,
     * but indexing speed is slower.
     *
     * With larger values, more RAM is used during indexing,
     * and while searches on unoptimized indices are slower,
     * indexing is faster.
     *
     * Thus larger values (> 10) are best for batch index creation,
     * and smaller values (< 10) for indices that are interactively maintained.
     *
     * Default value is 10
     *
     * @var integer
     */
    public $mergeFactor = 10;

    /**
     * File system adapter.
     *
     * @var Zend_Search_Lucene_Storage_Directory
     */
    private $_directory = null;


    /**
     * Changes counter.
     *
     * @var integer
     */
    private $_versionUpdate = 0;

    /**
     * List of the segments, created by index writer
     * Array of Zend_Search_Lucene_Index_SegmentInfo objects
     *
     * @var array
     */
    private $_newSegments = array();

    /**
     * List of segments to be deleted on commit
     *
     * @var array
     */
    private $_segmentsToDelete = array();

    /**
     * Current segment to add documents
     *
     * @var Zend_Search_Lucene_Index_SegmentWriter_DocumentWriter
     */
    private $_currentSegment = null;

    /**
     * Array of Zend_Search_Lucene_Index_SegmentInfo objects for this index.
     *
     * It's a reference to the corresponding Zend_Search_Lucene::$_segmentInfos array
     *
     * @var array Zend_Search_Lucene_Index_SegmentInfo
     */
    private $_segmentInfos;

    /**
     * List of indexfiles extensions
     *
     * @var array
     */
    private static $_indexExtensions = array('.cfs' => '.cfs',
                                             '.fnm' => '.fnm',
                                             '.fdx' => '.fdx',
                                             '.fdt' => '.fdt',
                                             '.tis' => '.tis',
                                             '.tii' => '.tii',
                                             '.frq' => '.frq',
                                             '.prx' => '.prx',
                                             '.tvx' => '.tvx',
                                             '.tvd' => '.tvd',
                                             '.tvf' => '.tvf',
                                             '.del' => '.del'  );

    /**
     * Opens the index for writing
     *
     * IndexWriter constructor needs Directory as a parameter. It should be
     * a string with a path to the index folder or a Directory object.
     * Second constructor parameter create is optional - true to create the
     * index or overwrite the existing one.
     *
     * @param Zend_Search_Lucene_Storage_Directory $directory
     * @param array $segmentInfos
     * @param boolean $create
     */
    public function __construct(Zend_Search_Lucene_Storage_Directory $directory, &$segmentInfos, $create = false)
    {
        $this->_directory    = $directory;
        $this->_segmentInfos = &$segmentInfos;

        if ($create) {
            foreach ($this->_directory->fileList() as $file) {
                if ($file == 'deletable' ||
                    $file == 'segments'  ||
                    isset(self::$_indexExtensions[ substr($file, strlen($file)-4)]) ||
                    preg_match('/\.f\d+$/i', $file) /* matches <segment_name>.f<decimal_nmber> file names */) {
                        $this->_directory->deleteFile($file);
                    }
            }
            $segmentsFile = $this->_directory->createFile('segments');
            $segmentsFile->writeInt((int)0xFFFFFFFF);
            // write version
            $segmentsFile->writeLong(0);
            // write name counter
            $segmentsFile->writeInt(0);
            // write segment counter
            $segmentsFile->writeInt(0);

            $deletableFile = $this->_directory->createFile('deletable');
            // write counter
            $deletableFile->writeInt(0);
        } else {
            $segmentsFile = $this->_directory->getFileObject('segments');
            $format = $segmentsFile->readInt();
            if ($format != (int)0xFFFFFFFF) {
                throw new Zend_Search_Lucene_Exception('Wrong segments file format');
            }
        }
    }

    /**
     * Adds a document to this index.
     *
     * @param Zend_Search_Lucene_Document $document
     */
    public function addDocument(Zend_Search_Lucene_Document $document)
    {
        if ($this->_currentSegment === null) {
            $this->_currentSegment =
                new Zend_Search_Lucene_Index_SegmentWriter_DocumentWriter($this->_directory, $this->_newSegmentName());
        }
        $this->_currentSegment->addDocument($document);

        if ($this->_currentSegment->count() >= $this->maxBufferedDocs) {
            $this->commit();
        }

        $this->_versionUpdate++;

        $this->_maybeMergeSegments();
    }


    /**
     * Merge segments if necessary
     */
    private function _maybeMergeSegments()
    {
        $segmentSizes = array();
        foreach ($this->_segmentInfos as $segId => $segmentInfo) {
            $segmentSizes[$segId] = $segmentInfo->count();
        }

        $mergePool   = array();
        $poolSize    = 0;
        $sizeToMerge = $this->maxBufferedDocs;
        asort($segmentSizes, SORT_NUMERIC);
        foreach ($segmentSizes as $segId => $size) {
            // Check, if segment comes into a new merging block
            while ($size >= $sizeToMerge) {
                // Merge previous block if it's large enough
                if ($poolSize >= $sizeToMerge) {
                    $this->_mergeSegments($mergePool);
                }
                $mergePool   = array();
                $poolSize    = 0;

                $sizeToMerge *= $this->mergeFactor;

                if ($sizeToMerge > $this->maxMergeDocs) {
                    return;
                }
            }

            $mergePool[] = $this->_segmentInfos[$segId];
            $poolSize += $size;
        }

        if ($poolSize >= $sizeToMerge) {
            $this->_mergeSegments($mergePool);
        }
    }

    /**
     * Merge specified segments
     *
     * $segments is an array of SegmentInfo objects
     *
     * @param array $segments
     */
    private function _mergeSegments($segments)
    {
        // Try to get exclusive non-blocking lock to the 'index.optimization.lock'
        // Skip optimization if it's performed by other process right now
        $optimizationLock = $this->_directory->createFile('index.optimization.lock');
        if (!$optimizationLock->lock(LOCK_EX,true)) {
            return;
        }
//        echo "optimization started... " . microtime(true) . "\n";

        $newName = $this->_newSegmentName();
        $merger = new Zend_Search_Lucene_Index_SegmentMerger($this->_directory,
                                                             $newName);
        foreach ($segments as $segmentInfo) {
            $merger->addSource($segmentInfo);
            $this->_segmentsToDelete[$segmentInfo->getName()] = $segmentInfo->getName();
        }

        $newSegment = $merger->merge();
        if ($newSegment !== null) {
            $this->_newSegments[$newSegment->getName()] = $newSegment;
        }

        $this->commit();
/*
        for ($count = 1; $count < 3; $count++) {
            echo '.';
            sleep(1);
        }
        echo "\n";
*/
        $optimizationLock->unlock();
//        echo "optimization finished " . microtime(true) . "\n";
    }

    /**
     * Update segments file by adding current segment to a list
     *
     * @throws Zend_Search_Lucene_Exception
     */
    private function _updateSegments()
    {
//        echo "Exclusive Index Lock...  ";
        // Get exclusive index lock
        // Wait, until all parallel searchers or indexers won't stop
        // and stop all next searchers, while we are updating segments file
        $lock = $this->_directory->getFileObject('index.lock');
        if (!$lock->lock(LOCK_EX)) {
            throw new Zend_Search_Lucene_Exception('Can\'t obtain exclusive index lock');
        }
//        echo "obtained " . microtime(true) . "\n";


        if (count($this->_segmentsToDelete) != 0) {
/*
            for ($count = 1; $count < 15; $count++) {
                echo '.';
                sleep(1);
            }
            echo "\n";
*/
        }

        $segmentsFile   = $this->_directory->getFileObject('segments');
        $newSegmentFile = $this->_directory->createFile('segments.new');

        // Write format marker
        $newSegmentFile->writeInt((int)0xFFFFFFFF);

        // Write index version
        $segmentsFile->seek(4, SEEK_CUR);
        $version = $segmentsFile->readLong() + $this->_versionUpdate;
        $this->_versionUpdate = 0;
        $newSegmentFile->writeLong($version);

        // Write segment name counter
        $newSegmentFile->writeInt($segmentsFile->readInt());

        // Write number of segemnts
        $segments = $segmentsFile->readInt();
        $newSegmentFile->writeInt($segments + count($this->_newSegments) - count($this->_segmentsToDelete));

        for ($count = 0; $count < $segments; $count++) {
            $segName = $segmentsFile->readString();
            $segSize = $segmentsFile->readInt();

            if (!array_key_exists($segName, $this->_segmentsToDelete)) {
                $newSegmentFile->writeString($segName);
                $newSegmentFile->writeInt($segSize);
            }
        }

        foreach ($this->_newSegments as $segmentName => $segmentInfo) {
            $newSegmentFile->writeString($segmentName);
            $newSegmentFile->writeInt($segmentInfo->count());

            $this->_segmentInfos[] = $segmentInfo;
        }
        $this->_newSegments = array();

        $fileList = $this->_directory->fileList();
        foreach ($this->_segmentsToDelete as $nameToDelete) {
            foreach ($this->_segmentInfos as $segId => $segInfo) {
                if ($segInfo->getName() == $nameToDelete) {
                    unset($this->_segmentInfos[$segId]);
                }
            }

            foreach (self::$_indexExtensions as $ext) {
                if ($this->_directory->fileExists($nameToDelete . $ext)) {
                    $this->_directory->deleteFile($nameToDelete . $ext);
                }
            }

            foreach ($fileList as $file) {
                if (substr($file, 0, strlen($nameToDelete) + 2) == ($nameToDelete . '.f') &&
                    ctype_digit( substr($file, strlen($nameToDelete) + 2) )) {
                        $this->_directory->deleteFile($file);
                    }
            }
        }
        $this->_segmentsToDelete = array();

        $this->_directory->renameFile('segments.new', 'segments');

//        echo "Exclusive Index Lock...  ";
        // Switch back to shared lock mode
        $lock->lock(LOCK_SH);
//        echo "removed " . microtime(true) . "\n";

    }


    /**
     * Commit current changes
     */
    public function commit()
    {
        if ($this->_currentSegment !== null) {
            $newSegment = $this->_currentSegment->close();
            if ($newSegment !== null) {
                $this->_newSegments[$newSegment->getName()] = $newSegment;
            }
            $this->_currentSegment = null;
        }

        if (count($this->_newSegments)      != 0 ||
            count($this->_segmentsToDelete) != 0) {
            $this->_updateSegments();
        }
    }


    /**
     * Merges the provided indexes into this index.
     *
     * @param array $readers
     * @return void
     */
    public function addIndexes($readers)
    {
        /**
         * @todo implementation
         */
    }

    /**
     * Merges all segments together into a single segment, optimizing
     * an index for search.
     * Input is an array of Zend_Search_Lucene_Index_SegmentInfo objects
     *
     * @throws Zend_Search_Lucene_Exception
     */
    public function optimize()
    {
        $this->_mergeSegments($this->_segmentInfos);
    }

    /**
     * Get name for new segment
     *
     * @return string
     */
    private function _newSegmentName()
    {
        $segmentsFile = $this->_directory->getFileObject('segments');

        // Get exclusive segments file lock
        // We have guarantee, that we will not intersect with _updateSegments() call
        // of other process, because it needs exclusive index lock and waits
        // until all other searchers won't stop
        if (!$segmentsFile->lock(LOCK_EX)) {
            throw new Zend_Search_Lucene_Exception('Can\'t obtain exclusive index lock');
        }

        $segmentsFile->seek(12); // 12 = 4 (int, file format marker) + 8 (long, index version)
        $segmentNameCounter = $segmentsFile->readInt();

        $segmentsFile->seek(12); // 12 = 4 (int, file format marker) + 8 (long, index version)
        $segmentsFile->writeInt($segmentNameCounter + 1);

        $segmentsFile->unlock();

        return '_' . base_convert($segmentNameCounter, 10, 36);
    }

}
