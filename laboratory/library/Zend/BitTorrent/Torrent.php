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
 * @package    Zend_BitTorrent
 * @copyright  Copyright (c) 2005-2007 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/** @see Zend_BitTorrent_Decoder */
require_once 'Zend/BitTorrent/Decoder.php';

/** @see Zend_BitTorrent_Encoder */
require_once 'Zend/BitTorrent/Encoder.php';

/**
 * A class that represents a single torrent file
 *
 * @category   Zend
 * @package    Zend_BitTorrent
 * @copyright  Copyright (c) 2005-2007 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_BitTorrent_Torrent
{
    /**
     * Constant used in the factory method
     *
     * @var string
     */
    const CREATE_FROM_FILE = 'fromFile';

    /**
     * Constant used in the factory method
     *
     * @var string
     */
    const CREATE_FROM_PATH = 'fromPath';

    /**
     * Constant used in the factory method
     *
     * @var string
     */
    const CREATE_NEW = 'new';

    /**
     * Constant used with the $path
     *
     * @var int
     */
    const FILE_PATH = 1;

    /**
     * Constant used with the $path
     *
     * @var int
     */
    const DIR_PATH = 2;

    /**
     * The exponent to use when making the pieces
     *
     * @var int
     */
    protected $pieceLengthExp = 18;

    /**
     * The announce url
     *
     * @var string
     */
    protected $announce = null;

    /**
     * Optional comment
     *
     * @var string
     */
    protected $comment = null;

    /**
     * Optional string that informs clients who or what created this torrent
     *
     * @var string
     */
    protected $createdBy = 'Zend Framework BitTorrent component';

    /**
     * The unix timestamp of when the torrent was created
     *
     * @var int
     */
    protected $creationDate = null;

    /**
     * Info about the file(s) in the torrent
     *
     * @var array
     */
    protected $info = null;

    /**
     * The path to the file or directory we want to make a torrent of.
     *
     * @var string
     */
    protected $path = null;

    /**
     * Variable telling us the type of the $path
     *
     * @var int
     */
    protected $pathType = null;

    /**
     * Flag telling us if the torrent is built
     *
     * @var boolean
     */
    protected $isBuilt = false;

    /**
     * Path to the torrent file we might want to build from
     *
     * @var string
     */
    protected $torrentFilePath = null;

    /**
     * Class constructor
     *
     * The constructor can not be called by the user. The factory method will use this if someone
     * wants to create a torrent from blank using the CREATE_BLANK constant.
     *
     */
    protected function __construct()
    {}

    /**
     * See if the instance of the object is ready to be built
     *
     * @return boolean
     */
    public function isReadyToBeBuilt()
    {
        if (
            ($this->getTorrentFilePath() === null && $this->getPath() === null) ||
            $this->getAnnounce() === null
        ) {
            return false;
        }

        return true;
    }

    /**
     * Convenient method to use when building an instance of the torrent object
     *
     * This method will see what build* method we need to use to build the object. If the $path
     * property is set we will use the buildFromPath method and if the $torrentFilePath property is
     * set we will use the buildFromTorrent method.
     *
     * @return Zend_BitTorrent_Torrent
     * @throws Zend_BitTorrent_Torrent_Exception
     */
    public function build()
    {
        /* See if the torrent is already built */
        if ($this->isBuilt === true) {
            /** @see Zend_BitTorrent_Torrent_Exception */
            require_once 'Zend/BitTorrent/Torrent/Exception.php';

            throw new Zend_BitTorrent_Torrent_Exception('Torrent is already built.');
        }

        /* See if the torrent is ready to be built */
        if ($this->isReadyToBeBuilt() === false) {
            /** @see Zend_BitTorrent_Torrent_Exception */
            require_once 'Zend/BitTorrent/Torrent/Exception.php';

            throw new Zend_BitTorrent_Torrent_Exception('The torrent is not ready to be built.');
        }

        /* Decide how to build the torrent */
        if ($this->torrentFilePath !== null) {
            $this->buildFromTorrent();
        } else if ($this->path !== null) {
            $this->buildFromPath();
        }

        /* Set the creation date property if it does not exist */
        if ($this->getCreationDate() === null) {
            $this->setCreationDate(time());
        }

        /* Update the isBuilt flag so we can't build twice with the same object */
        $this->isBuilt = true;

        return $this;
    }

    /**
     * Create a torrent object from a torrent file
     *
     * @return Zend_BitTorrent_Torrent
     */
    protected function buildFromTorrent()
    {
        /* Decode the file */
        $decodedFile = Zend_BitTorrent_Decoder::decodeFile($this->getTorrentFilePath());

        /* Populate the object with data from the file */
        if (isset($decodedFile['announce'])) {
            $this->setAnnounce($decodedFile['announce']);
        }

        if (isset($decodedFile['comment'])) {
            $this->setComment($decodedFile['comment']);
        }

        if (isset($decodedFile['created by'])) {
            $this->setCreatedBy($decodedFile['created by']);
        }

        if (isset($decodedFile['creation date'])) {
            $this->setCreationDate($decodedFile['creation date']);
        }

        if (isset($decodedFile['info'])) {
            $this->setInfo($decodedFile['info']);
        }

        return $this;
    }

    /**
     * Build a torrent from a path
     *
     * Some of the code in this method is ported directly from the official btmakemetafile script
     * by Bram Cohen.
     *
     * @return Zend_BitTorrent_Torrent
     */
    protected function buildFromPath()
    {
        $path = $this->getPath();

        /* Generate an absolute path */
        $absolutePath = realpath($path);

        /* An array of the files to include in the torrent */
        $files = array();

        /* If the $absolutePath is a single file we're all set to go */
        if ($this->getPathType() === self::FILE_PATH) {
            $files[] = basename($absolutePath);
        } else if ($this->getPathType() === self::DIR_PATH) {
            /*
                We have a directory. Lets find all files in that directory and put them in the
                $files array. Realpath also remove a possible trailing '/'.
            */
            $dirList = array(realpath($absolutePath));

            /* Loop as long as we have directories in our list */
            while ($dirList) {
                $currentDir = array_pop($dirList);

                /* Open the directory we are currently in */
                $dh = opendir($currentDir);

                /* Loop throght the contents of the directory */
                while (($file = readdir($dh)) !== false) {
                    if ($file === '.' || $file === '..') {
                        continue;
                    }

                    $currentFile = $currentDir . DIRECTORY_SEPARATOR . $file;

                    /* If we have a file, add it to the list. If we have a directory, add it to the list so the loop will continue */
                    if (is_file($currentFile)) {
                        /* Remove the prepending path to make a torrent exactly like btmakemetafile does. */
                        $files[] = str_replace($absolutePath . DIRECTORY_SEPARATOR, '', $currentFile);
                    } else if (is_dir($currentFile)) {
                        $dirList[] = $currentFile;
                    }
                }

                /* Close the directory handle */
                closedir($dh);
            }
        }

        /* Initialize the info part of the torrent */
        $info = array();

        $info['piece length'] = pow(2, $this->getPieceLengthExp());

        /* If we only have a single file, get the size of the file and set the name property */
        if ($this->getPathType() === self::FILE_PATH) {
            /* Regenerate the path to the file */
            $filePath = dirname($absolutePath);

            /* The name of the file in the torrent */
            $info['name'] = $files[0];

            /* The size of the file */
            $info['length'] = filesize($filePath . DIRECTORY_SEPARATOR . $files[0]);

            /* Initialize the pieces */
            $pieces = array();

            /* The current position in the file */
            $position = 0;

            /* Open the file */
            $fp = fopen($filePath . DIRECTORY_SEPARATOR . $files[0], 'rb');

            while ($position < $info['length']) {
                $part = fread($fp, min($info['piece length'], $info['length'] - $position));
                $pieces[] = sha1($part, true);

                $position += $info['piece length'];

                if ($position > $info['length']) {
                    $position = $info['length'];
                }
            }

            /* Close the file handle */
            fclose($fp);

            $pieces = implode('', $pieces);
        } else {
            /* Initialize the pieces array */
            $pieces = array();

            /* The name of the directory in the torrent */
            $info['name'] = basename($absolutePath);

            /* Sort the filelist to mimic btmakemetafile */
            sort($files);

            /* Initialize some helper variables for the hashing or the parts of each file */
            $part = '';
            $done = 0;

            /*
                Loop through all the files in the $files array to generate the pieces and the other
                stuff in the info part of the torrent. Note that two files may be part of the same
                piece since btmakemetafile uses cyclic buffers.
            */
            foreach ($files as $file) {
                $filesize = filesize($absolutePath . DIRECTORY_SEPARATOR . $file);

                $info['files'][] = array(
                    'length' => $filesize,
                    'path'   => explode(DIRECTORY_SEPARATOR, $file));

                /* Reset the position in the current file */
                $position = 0;

                /* Open the current file */
                $fp = fopen($absolutePath . DIRECTORY_SEPARATOR . $file, 'rb');

                /* Loop through the file */
                while ($position < $filesize) {
                    $bytes = min(($filesize - $position), ($info['piece length'] - $done));
                    $part .= fread($fp, $bytes);

                    $done += $bytes;
                    $position += $bytes;

                    /* We have a piece. Add it to the array and reset the helper variables */
                    if ($done === $info['piece length']) {
                        $pieces[] = sha1($part, true);
                        $part = '';
                        $done = 0;
                    }
                }

                /* Close the file handle */
                fclose($fp);
            }

            /* If there is a part still not hashed then add it to the pieces array */
            if ($done > 0) {
                $pieces[] = sha1($part, true);
            }

            /* Make a string of the pieces */
            $pieces = implode('', $pieces);
        }

        /* Store the pieces in the $info array */
        $info['pieces'] = $pieces;

        /* Sort the info array */
        ksort($info);

        /* Set the info */
        $this->setInfo($info);

        return $this;
    }

    /**
     * Factory method to use when creating a new torrent object
     *
     * @param string $type
     * @param array $params
     * @return Zend_BitTorrent_Torrent
     * @throws Zend_BitTorrent_Torrent_Exception
     */
    public static function factory($type, $params = array())
    {
        if ($params !== null && !is_array($params)) {
            /** @see Zend_BitTorrent_Torrent_Exception */
            require_once 'Zend/BitTorrent/Torrent/Exception.php';

            throw new Zend_BitTorrent_Torrent_Exception('$params must either be null or an array.');
        }

        $torrent = new self();

        if ($type === self::CREATE_FROM_FILE) {
            if (!isset($params['file'])) {
                /** @see Zend_BitTorrent_Torrent_Exception */
                require_once 'Zend/BitTorrent/Torrent/Exception.php';

                throw new Zend_BitTorrent_Torrent_Exception('Missing "file" parameter.');
            }

            $torrent->setTorrentFilePath($params['file']);
            $torrent->buildFromTorrent();
        } else if ($type === self::CREATE_FROM_PATH) {
            if (!isset($params['path']) || !isset($params['announce'])) {
                /** @see Zend_BitTorrent_Torrent_Exception */
                require_once 'Zend/BitTorrent/Torrent/Exception.php';

                throw new Zend_BitTorrent_Torrent_Exception('Missing "path" and/or "announce" parameters.');
            }

            $torrent->setPath($params['path'])
                    ->setAnnounce($params['announce'])
                    ->buildFromPath();
        } else if ($type === self::CREATE_NEW) {
            /* Do nothing */
        } else {
            /** @see Zend_BitTorrent_Torrent_Exception */
            require_once 'Zend/BitTorrent/Torrent/Exception.php';

            throw new Zend_BitTorrent_Torrent_Exception('Invalid type.');
        }

        return $torrent;
    }

    /**
     * Set the piece length exponent
     *
     * @param int $pieceLengthExp
     * @return Zend_BitTorrent_Torrent
     * @throws Zend_BitTorrent_Torrent_Exception
     */
    public function setPieceLengthExp($pieceLengthExp)
    {
        if (!is_int($pieceLengthExp)) {
            /** @see Zend_BitTorrent_Torrent_Exception */
            require_once 'Zend/BitTorrent/Torrent/Exception.php';

            throw new Zend_BitTorrent_Torrent_Exception('Expected int, got: ' . gettype($pieceLengthExp));
        }

        $this->pieceLengthExp = $pieceLengthExp;

        return $this;
    }

    /**
     * Get the piece length exponent
     *
     * @return int
     */
    public function getPieceLengthExp()
    {
        return $this->pieceLengthExp;
    }

    /**
     * Set the announce url
     *
     * @param string $announceUrl
     * @return Zend_BitTorrent_Torrent
     */
    public function setAnnounce($announceUrl)
    {
        $this->announce = $announceUrl;

        return $this;
    }

    /**
     * Get the announce url
     *
     * @return mixed Returns null if the announce url is not set or a string otherwise
     */
    public function getAnnounce()
    {
        return $this->announce;
    }

    /**
     * Set the comment
     *
     * @param string $comment
     * @return Zend_BitTorrent_Torrent
     */
    public function setComment($comment)
    {
        $this->comment = $comment;

        return $this;
    }

    /**
     * Get the comment
     *
     * @return mixed Returns null if the comment is not set or a string otherwise
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * Set the created by property
     *
     * @param string $createdBy
     * @return Zend_BitTorrent_Torrent
     */
    public function setCreatedBy($createdBy)
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    /**
     * Get the created by property
     *
     * @return string
     */
    public function getCreatedBy()
    {
        return $this->createdBy;
    }

    /**
     * Set the creation date property
     *
     * @param int $creationDate
     * @return Zend_BitTorrent_Torrent
     * @throws Zend_BitTorrent_Torrent_Exception
     */
    public function setCreationDate($creationDate)
    {
        if (!is_numeric($creationDate)) {
            /** @see Zend_BitTorrent_Torrent_Exception */
            require_once 'Zend/BitTorrent/Torrent/Exception.php';

            throw new Zend_BitTorrent_Torrent_Exception('Expected numeric value (unix timestamp), got: ' . gettype($creationDate));
        }

        $this->creationDate = $creationDate;

        return $this;
    }

    /**
     * Get the creation date property
     *
     * @return int Returns a unix timestamp
     */
    public function getCreationDate()
    {
        return $this->creationDate;
    }

    /**
     * Set the info part of the torrent
     *
     * @param array $info
     * @return Zend_BitTorrent_Torrent
     * @throws Zend_BitTorrent_Torrent_Exception
     */
    public function setInfo($info)
    {
        if (!is_array($info)) {
            /** @see Zend_BitTorrent_Torrent_Exception */
            require_once 'Zend/BitTorrent/Torrent/Exception.php';

            throw new Zend_BitTorrent_Torrent_Exception('Expected array, got: ' . gettype($info));
        }

        $this->info = $info;

        return $this;
    }

    /**
     * Get the info part
     *
     * @return array
     */
    public function getInfo()
    {
        return $this->info;
    }

    /**
     * Set the path
     *
     * @param string $path
     * @return Zend_BitTorrent_Torrent
     * @throws Zend_BitTorrent_Torrent_Exception
     */
    public function setPath($path)
    {
        if (is_file($path)) {
            $this->pathType = self::FILE_PATH;
        } else if (is_dir($path)) {
            $this->pathType = self::DIR_PATH;
        } else {
            /** @see Zend_BitTorrent_Torrent_Exception */
            require_once 'Zend/BitTorrent/Torrent/Exception.php';

            throw new Zend_BitTorrent_Torrent_Exception('$path must lead to a file or a directory.');
        }

        $this->path = $path;

        return $this;
    }

    /**
     * Get the path
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Get the path type
     *
     * @return int The return value maps to one of the *_PATH constants.
     */
    public function getPathType()
    {
        return $this->pathType;
    }

    /**
     * Set the path to the torrent file we might want to build from
     *
     * @param string $path
     * @return Zend_BitTorrent_Torrent
     * @throws Zend_BitTorrent_Torrent_Exception
     */
    public function setTorrentFilePath($path)
    {
        if (!is_file($path)) {
            /** @see Zend_BitTorrent_Torrent_Exception */
            require_once 'Zend/BitTorrent/Torrent/Exception.php';

            throw new Zend_BitTorrent_Torrent_Exception('File does not exist: ' . $path);
        }

        $this->torrentFilePath = $path;

        return $this;
    }

    /**
     * Get the path to the torrent file we might want to build from
     *
     * @return string
     */
    public function getTorrentFilePath()
    {
        return $this->torrentFilePath;
    }

    /**
     * Save the current torrent object to the filename specified
     *
     * This method will save the current object to a file. If the file specified exists it will be overwritten.
     *
     * @param string $filename
     * @throws Zend_BitTorrent_Torrent_Exception
     * @return Zend_BitTorrent_Torrent
     */
    public function save($filename)
    {
        /* Build the torrent if it is not yet built */
        if (!$this->isBuilt) {
            $this->build();
        }

        /* Open the file if it is writeable */
        if (!is_writeable($filename)) {
            /** @see Zend_BitTorrent_Torrent_Exception */
            require_once 'Zend/BitTorrent/Torrent/Exception.php';

            throw new Zend_BitTorrent_Torrent_Exception('Could not open file "' . $filename . '" for writing.');
        }

        /* Open file for writing */
        $fp = fopen($filename, 'wb');

        $torrent = array(
            'announce'      => $this->getAnnounce(),
            'creation date' => $this->getCreationDate(),
            'info'          => $this->getInfo()
        );

        if (($comment = $this->getComment()) !== null) {
            $torrent['comment'] = $comment;
        }

        if (($createdBy = $this->getCreatedBy()) !== null) {
            $torrent['created by'] = $createdBy;
        }

        /* Create the encoded dictionary */
        $dictionary = Zend_BitTorrent_Encoder::encodeDictionary($torrent);

        /* Write the encoded data to the file */
        fwrite($fp, $dictionary, strlen($dictionary));

        /* Close the file handle */
        fclose($fp);

        return $this;
    }

    /**
     * Get the files listed in the torrent
     *
     * If the torrent is a multifile torrent, return the files array. If it contains a single file,
     * return the name element from the info array.
     *
     * @return mixed Returns a string if the torrent only contains one file or an array of files otherwise.
     * @throws Zend_BitTorrent_Torrent_Exception
     */
    public function getFileList()
    {
        if (($info = $this->getInfo()) === null) {
            /** @see Zend_BitTorrent_Torrent_Exception */
            require_once 'Zend/BitTorrent/Torrent/Exception.php';

            throw new Zend_BitTorrent_Torrent_Exception('The info part of the torrent is not set.');
        }

        if (isset($info['length'])) {
            return $info['name'];
        }

        return $info['files'];
    }

    /**
     * Get the size of the files in the torrent
     *
     * @return int
     * @throws Zend_BitTorrent_Torrent_Exception
     */
    public function getSize()
    {
        if (($info = $this->getInfo()) === null) {
            /** @see Zend_BitTorrent_Torrent_Exception */
            require_once 'Zend/BitTorrent/Torrent/Exception.php';

            throw new Zend_BitTorrent_Torrent_Exception('The info part of the torrent is not set.');
        }

        /* If the length element is set, return that one. If not, loop through the files and generate the total */
        if (isset($info['length'])) {
            return $info['length'];
        }

        $files = $this->getFileList();
        $size = 0;

        foreach ($files as $file) {
            $size += $file['length'];
        }

        return $size;
    }

    /**
     * Get the name that the content will be saved as
     *
     *
     * @return string
     * @throws Zend_BitTorrent_Torrent_Exception
     */
    public function getName()
    {
        if (($info = $this->getInfo()) === null) {
            /** @see Zend_BitTorrent_Torrent_Exception */
            require_once 'Zend/BitTorrent/Torrent/Exception.php';

            throw new Zend_BitTorrent_Torrent_Exception('The info part of the torrent is not set.');
        }

        return $info['name'];
    }
}