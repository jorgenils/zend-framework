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
 * @package    Zend_Service
 * @subpackage Amazon_s3
 * @copyright  Copyright (c) 2006 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: PayPal.php 126 2007-06-21 20:23:05Z shahar $
 */

/**
 * @see Zend_Http_Client
 */
require_once 'Zend/Http/Client.php';

/**
 * Amazon S3 PHP stream wrapper
 *
 * @category   Zend
 * @package    Zend_Service
 * @subpackage Amazon_S3
 * @copyright  Copyright (c) 2006 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Service_Amazon_S3
{

    /**
     * @var boolean
     */
    public static $debug = false;

    /**
     * @var string Amazon Access Key
     */
    public static $access_key = null;

    /**
     * @var string Amazon Secret Key
     */
    public static $secret_key = null;

    /**
     * @var boolean Write the buffer on fflush()?
     */
    private $_write_buffer = false;

    /**
     * @var integer Current read/write position
     */
    private $_position = 0;

    /**
     * @var integer Total size of the object as returned by S3 (Content-length)
     */
    private $_object_size = 0;

    /**
     * @var string File name to interact with
     */
    private $_object_name = null;

    /**
     * @var string Current read/write buffer
     */
    private $_object_buffer = null;

    /**
     * @var array Available buckets
     */
    private $_bucket_list = array();

    const S3_ENDPOINT = 'http://s3.amazonaws.com';
    const S3_ACL_PRIVATE = 'private';
    const S3_ACL_PUBLIC_READ = 'public-read';
    const S3_ACL_PUBLIC_WRITE = 'public-read-write';
    const S3_ACL_AUTH_READ = 'authenticated-read';

    /**
     * Set the keys to use when accessing S3.
     *
     * @param  string $access_key
     * @param  string $secret_key
     * @return void
     */
    public static function setKeys($access_key, $secret_key)
    {
        self::$access_key = $access_key;
        self::$secret_key = $secret_key;
    }

    /**
     * Open the stream
     *
     * @param  string $path
     * @param  string $mode
     * @param  integer $options
     * @param  string $opened_path
     * @return boolean
     */
    public function stream_open($path, $mode, $options, $opened_path)
    {
        if (self::$debug) {
            echo "stream_open($path, $mode, $options, $opened_path)<br/>";
        }

        // If we open the file for writing, just return true. Create the file
        // on fflush call
        switch (strtolower($mode)) {
            case 'w':
            case 'w+':
            case 'wb':
            case 'wb+':
            case 'a':
            case 'a+':
            case 'ab':
            case 'ab+':
            case 'x':
            case 'x+':
            case 'xb':
            case 'xb+':
                $this->_object_name = $path;
                $this->_object_buffer = null;
                $this->_object_size = 0;
                $this->_position = 0;
                $this->_write_buffer = true;
                return true;
                break;
        }

        // Otherwise, just see if the file exists or not
        $response = $this->_makeRequest('HEAD', $path);
        if ($response->getStatus() == 200) {
            $this->_object_name = $path;
            $this->_position = 0;
            $this->_object_size = $response->getHeader('Content-length');
            $this->_write_buffer = false;
            $this->_object_buffer = null;
            return true;
        }
        return false;
    }

    /**
     * Close the stream
     *
     * @return void
     */
    public function stream_close()
    {
        if (self::$debug) {
            echo "stream_close()<br/>";
        }
        $this->_object_name = null;
        $this->_object_buffer = null;
        $this->_object_size = 0;
        $this->_position = 0;
        $this->_write_buffer = false;
    }

    /**
     * Read from the stream
     *
     * @param  integer $count
     * @return string
     */
    public function stream_read($count)
    {
        if (self::$debug) {
            echo "stream_read($count)<br/>";
        }

        if (!$this->_object_name) {
            return false;
        }

        $range_start = $this->_position;
        $range_end = $this->_position+$count;

        // Only fetch more data from S3 if the range end position is greater
        // than the size of the current object buffer AND if the range end position
        // is less than or equal to the object's size returned by S3
        if (($range_end > strlen($this->_object_buffer)) && ($range_end <= $this->_object_size)) {

            $headers = array(
                'Range' => "$range_start-$range_end"
            );

            $response = $this->_makeRequest('GET', $this->_object_name, $headers);

            if ($response->getStatus() == 200) {
                $this->_object_buffer .= $response->getBody();
            }
        }

        $data = substr($this->_object_buffer, $this->_position, $count);
        $this->_position += strlen($data);
        return $data;
    }

    /**
     * Write to the stream
     *
     * @param  string $data
     * @return integer
     */
    public function stream_write($data)
    {
        if (self::$debug) {
            echo "stream_write($data)<br/>";
        }

        if (!$this->_object_name) {
            return 0;
        }
        $len = strlen($data);
        $this->_object_buffer .= $data;
        $this->_object_size += $len;
        return $len;
    }

    /**
     * End of the stream?
     *
     * @return boolean
     */
    public function stream_eof()
    {
        if (self::$debug) {
            echo "stream_eof<br/>";
        }

        if (!$this->_object_name) {
            return true;
        }

        return ($this->_position >= $this->_object_size);
    }

    /**
     * What is the current read/write position of the stream
     *
     * @return integer
     */
    public function stream_tell()
    {
        if (self::$debug) {
            echo "stream_tell()<br/>";
        }
        return $this->_position;
    }

    /**
     * Update the read/write position of the stream
     *
     * @param  integer $offset
     * @param  integer $whence
     * @return boolean
     */
    public function stream_seek($offset, $whence)
    {
        if (self::$debug) {
            echo "stream_seek($offset, $whence)<br/>";
        }

        switch ($whence) {
            case SEEK_CUR:
                // Set position to current location plus $offset
                $new_pos = $this->_position + $offset;
                break;
            case SEEK_END:
                // Set position to end-of-file plus $offset
                $new_pos = $this->_object_size + $offset;
                break;
            case SEEK_SET:
            default:
                // Set position equal to $offset
                $new_pos = $offset;
                break;
        }
        $ret = ($new_pos >= 0 && $new_pos <= $this->_object_size);
        if ($ret) {
            $this->_position = $new_pos;
        }
        return $ret;
    }

    /**
     * Flush current cached stream data to storage
     *
     * @return boolean
     */
    public function stream_flush()
    {
        if (self::$debug) {
            echo "stream_flush()<br/>";
        }

        // If the stream wasn't opened for writing, just return false
        if (!$this->_write_buffer) {
            return false;
        }

        $headers = array(
            'Content-MD5'  => base64_encode(md5($this->_object_buffer, true)),
            'Content-type' => self::getMimeType($this->_object_name),
            'Expect'       => '100-continue'
        );

        $response = $this->_makeRequest('PUT', $this->_object_name, $headers);

        // Check the MD5 Etag returned by S3 against and MD5 of the buffer
        if ($response->getStatus() == 200) {
            // It is escaped by double quotes for some reason
            $etag = str_replace('"', '', $response->getHeader('Etag'));

            if ($etag == md5($this->_object_buffer)) {
                $this->_object_buffer = null;
                return true;
            }
        }

        $this->_object_buffer = null;
        return false;
    }

    /**
     * Returns data array of stream variables
     *
     * @return array
     */
    public function stream_stat()
    {
        if (self::$debug) {
            echo "stream_stat()<br/>";
        }

        $stat = array();
        $stat['dev'] = 0;
        $stat['ino'] = 0;
        $stat['mode'] = 0;
        $stat['nlink'] = 0;
        $stat['uid'] = 0;
        $stat['gid'] = 0;
        $stat['rdev'] = 0;
        $stat['size'] = 0;
        $stat['atime'] = 0;
        $stat['mtime'] = 0;
        $stat['ctime'] = 0;
        $stat['blksize'] = 0;
        $stat['blocks'] = 0;

        $response = $this->_makeRequest('HEAD', $path);

        if ($response->getStatus() == 200) {
            $stat['size'] = $response->getHeader('Content-length');
            $stat['atime'] = time();
            $stat['mtime'] = strtotime($response->getHeader('Last-modified'));
        }

        return $stat;
    }

    /**
     * Attempt to delete the item
     *
     * @param  string $path
     * @return boolean
     */
    public function unlink($path)
    {
        if (self::$debug) {
            echo "unlink($path)<br/>";
        }

        $response = $this->_makeRequest('DELETE', $path);

        // Look for a 204 No Content response
        return ($response->getStatus() == 204);
    }

    /**
     * Attempt to rename the item
     *
     * @param  string $path_from
     * @param  string $path_to
     * @return boolean False
     */
    public function rename($path_from, $path_to)
    {
        if (self::$debug) {
            echo "rename($path_from, $path_to)<br/>";
        }

        // Renaming isn't supported, always return false
        return false;
    }

    /**
     * Create a new directory
     *
     * @param  string $path
     * @param  integer $mode
     * @param  integer $options
     * @return boolean
     */
    public function mkdir($path, $mode, $options)
    {
        if (self::$debug) {
            echo "mkdir($path, $mode, $options)<br/>";
        }

        $response = $this->_makeRequest('PUT', $path);

        return ($response->getStatus() == 200);
    }

    /**
     * Remove a directory
     *
     * @param  string $path
     * @param  integer $options
     * @return boolean
     */
    public function rmdir($path, $options)
    {
        if (self::$debug) {
            echo "rmdir($path, $options)<br/>";
        }

        $response = $this->_makeRequest('DELETE', $path);

        // Look for a 204 No Content response
        return ($response->getStatus() == 204);
    }

    /**
     * Attempt to open a directory
     *
     * @param  string $path
     * @param  integer $options
     * @return boolean
     */
    public function dir_opendir($path, $options)
    {
        if (self::$debug) {
            echo "dir_opendir($path, $options)<br/>";
        }

        $response = $this->_makeRequest('GET', $path);

        $xml = new SimpleXMLElement($response->getBody());

        $this->_bucket_list = array();
        // If we don't give a path, return an array of buckets available
        if ($path == 's3://') {
            foreach ($xml->Buckets->Bucket as $bucket) {
                $this->_bucket_list[] = (string)$bucket->Name;
            }
        }
        else { // Otherwise list the objects in the buckets
            foreach ($xml->ListBucketResult as $object) {
                $this->_bucket_list[] = (string)$object;
            }
        }

        return ($response->getStatus() == 200);
    }

    /**
     * Return array of URL variables
     *
     * @param  string $path
     * @param  integer $flags
     * @return array
     */
    public function url_stat($path, $flags)
    {
        if (self::$debug) {
            echo "url_stat($path, $flags)<br/>";
        }

        $stat = array();
        $stat['dev'] = 0;
        $stat['ino'] = 0;
        $stat['mode'] = 0;
        $stat['nlink'] = 0;
        $stat['uid'] = 0;
        $stat['gid'] = 0;
        $stat['rdev'] = 0;
        $stat['size'] = 0;
        $stat['atime'] = 0;
        $stat['mtime'] = 0;
        $stat['ctime'] = 0;
        $stat['blksize'] = 0;
        $stat['blocks'] = 0;

        $response = $this->_makeRequest('HEAD', $path);

        if ($response->getStatus() == 200) {
            $stat['size'] = $response->getHeader('Content-length');
            $stat['atime'] = time();
            $stat['mtime'] = strtotime($response->getHeader('Last-modified'));
        }

        return $stat;
    }

    /**
     * Return the next filename in the directory
     *
     * @return string
     */
    public function dir_readdir()
    {
        if (self::$debug) {
            echo "dir_readdir()<br/>";
        }

        $object = current($this->_bucket_list);
        if ($object !== false) {
            next($this->_bucket_list);
        }
        return $object;
    }

    /**
     * Reset the directory pointer
     *
     * @return boolean True
     */
    public function dir_rewinddir()
    {
        if (self::$debug) {
            echo "dir_rewinddir()<br/>";
        }

        reset($this->_bucket_list);
        return true;
    }

    /**
     * Close a directory
     *
     * @return boolean True
     */
    public function dir_closedir()
    {
        if (self::$debug) {
            echo "dir_closedir()<br/>";
        }

        $this->_bucket_list = array();
        return true;
    }

    /**
     * Make a request to Amazon S3
     *
     * @param  string $method
     * @param  string $path
     * @param  array $headers
     * @return Zend_Http_Response
     */
    private function _makeRequest($method, $path, $headers=array())
    {
        $retry_count = 0;

        if (!is_array($headers)) {
            $headers = array($headers);
        }

        // Strip off the beginning s3:// (assuming this is the scheme used)
        if (strpos($path, 's3://') !== false) {
            $path = substr($path, 5);
        }

        do {
            $retry = false;

            $headers['Date'] = gmdate(DATE_RFC1123, time());

            self::addSignature($method, $path, $headers);

            $client = new Zend_Http_Client(self::S3_ENDPOINT.'/'.$path);
            $client->setHeaders($headers);

            if (($method == 'PUT') && ($this->_object_buffer !== null)) {
                if (!isset($headers['Content-type'])) {
                    $headers['Content-type'] = self::getMimeType($path);
                }
                $client->setRawData($this->_object_buffer, $headers['Content-type']);
            }

            $response = $client->request($method);

            if (self::$debug) {
                Zend_Debug::dump($client->getLastRequest());
                Zend_Debug::dump($client->getLastResponse());
            }

            $response_code = $response->getStatus();

            // Some 5xx errors are expected, so retry automatically
            if ($response_code >= 500 && $response_code < 600 && $retry_count <= 5) {
                $retry = true;
                $retry_count++;
                echo $response_code, ' : retrying ', $action, ' request (', $retry_count, ')', "\n<br />\n";
                sleep($retry_count / 4 * $retry_count);
            }
            else if ($response_code == 307) {
                echo "We need to redirect";
            }
            else if ($response_code == 100) {
                echo "OK to Continue";
            }

        }
        while ($retry);

        return $response;
    }

    /**
     * Add the S3 Authorization signature to the request headers
     *
     * @param  string $method
     * @param  string $path
     * @param  array &$headers
     * @return string
     */
    public static function addSignature($method, $path, &$headers)
    {
        if (!is_array($headers)) {
            $headers = array($headers);
        }

        $type = $md5 = $date = '';

        // Search for the Content-type, Content-MD5 and Date headers
        foreach ($headers as $key=>$val) {
            if (strcasecmp($key, 'content-type') == 0) {
                $type = $val;
            }
            else if (strcasecmp($key, 'content-md5') == 0) {
                $md5 = $val;
            }
            else if (strcasecmp($key, 'date') == 0) {
                $date = $val;
            }
        }

        // If we have an x-amz-date header, use that instead of the normal Date
        if (isset($headers['x-amz-date']) && isset($date)) {
            $date = '';
        }

        $sig_str = "$method\n$md5\n$type\n$date\n";

        // For x-amz- headers, combine like keys, lowercase them, sort them
        // alphabetically and remove excess spaces around values
        $amz_headers = array();
        foreach ($headers as $key=>$val) {
            $key = strtolower($key);
            if (substr($key, 0, 6) == 'x-amz-') {
                if (is_array($val)) {
                    $amz_headers[$key] = $val;
                }
                else {
                    $amz_headers[$key][] = preg_replace('/\s+/', ' ', $val);
                }
            }
        }
        if (!empty($amz_headers)) {
            ksort($amz_headers);
            foreach ($amz_headers as $key=>$val) {
                $sig_str .= $key.':'.implode(',', $val)."\n";
            }
        }

        $sig_str .= '/'.parse_url($path, PHP_URL_PATH);
        if (strpos($path, '?location') !== false) {
            $sig_str .= '?location';
        }
        else if (strpos($path, '?acl') !== false) {
            $sig_str .= '?acl';
        }
        else if (strpos($path, '?torrent') !== false) {
            $sig_str .= '?torrent';
        }

        $signature = base64_encode(hash_hmac('sha1', utf8_encode($sig_str), self::$secret_key, true));
        $headers['Authorization'] = 'AWS '.self::$access_key.':'.$signature;

        return $sig_str;
    }

    /**
     * Attempt to get the content-type of a file based on the extension
     *
     * @param  string $path
     * @return string
     */
    public static function getMimeType($path)
    {
        $ext = substr(strrchr($path, '.'), 1);

        switch ($ext) {
            case 'xls':
                $content_type = 'application/excel';
                break;
            case 'hqx':
                $content_type = 'application/macbinhex40';
                break;
            case 'doc':
            case 'dot':
            case 'wrd':
                $content_type = 'application/msword';
                break;
            case 'pdf':
                $content_type = 'application/pdf';
                break;
            case 'pgp':
                $content_type = 'application/pgp';
                break;
            case 'ps':
            case 'eps':
            case 'ai':
                $content_type = 'application/postscript';
                break;
            case 'ppt':
                $content_type = 'application/powerpoint';
                break;
            case 'rtf':
                $content_type = 'application/rtf';
                break;
            case 'tgz':
            case 'gtar':
                $content_type = 'application/x-gtar';
                break;
            case 'gz':
                $content_type = 'application/x-gzip';
                break;
            case 'php':
            case 'php3':
            case 'php4':
                $content_type = 'application/x-httpd-php';
                break;
            case 'js':
                $content_type = 'application/x-javascript';
                break;
            case 'ppd':
            case 'psd':
                $content_type = 'application/x-photoshop';
                break;
            case 'swf':
            case 'swc':
            case 'rf':
                $content_type = 'application/x-shockwave-flash';
                break;
            case 'tar':
                $content_type = 'application/x-tar';
                break;
            case 'zip':
                $content_type = 'application/zip';
                break;
            case 'mid':
            case 'midi':
            case 'kar':
                $content_type = 'audio/midi';
                break;
            case 'mp2':
            case 'mp3':
            case 'mpga':
                $content_type = 'audio/mpeg';
                break;
            case 'ra':
                $content_type = 'audio/x-realaudio';
                break;
            case 'wav':
                $content_type = 'audio/wav';
                break;
            case 'bmp':
                $content_type = 'image/bitmap';
                break;
            case 'gif':
                $content_type = 'image/gif';
                break;
            case 'iff':
                $content_type = 'image/iff';
                break;
            case 'jb2':
                $content_type = 'image/jb2';
                break;
            case 'jpg':
            case 'jpe':
            case 'jpeg':
                $content_type = 'image/jpeg';
                break;
            case 'jpx':
                $content_type = 'image/jpx';
                break;
            case 'png':
                $content_type = 'image/png';
                break;
            case 'tif':
            case 'tiff':
                $content_type = 'image/tiff';
                break;
            case 'wbmp':
                $content_type = 'image/vnd.wap.wbmp';
                break;
            case 'xbm':
                $content_type = 'image/xbm';
                break;
            case 'css':
                $content_type = 'text/css';
                break;
            case 'txt':
                $content_type = 'text/plain';
                break;
            case 'htm':
            case 'html':
                $content_type = 'text/html';
                break;
            case 'xml':
                $content_type = 'text/xml';
                break;
            case 'xsl':
                $content_type = 'text/xsl';
                break;
            case 'mpg':
            case 'mpe':
            case 'mpeg':
                $content_type = 'video/mpeg';
                break;
            case 'qt':
            case 'mov':
                $content_type = 'video/quicktime';
                break;
            case 'avi':
                $content_type = 'video/x-ms-video';
                break;
            case 'eml':
                $content_type = 'message/rfc822';
                break;
            default:
                $content_type = 'binary/octet-stream';
                break;
        }

        if (self::$debug) {
            echo "getMimeType($path)[$ext] = $content_type<br/>";
        }

        return $content_type;
    }
}