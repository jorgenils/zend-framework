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
 * @package    Zend_Feed
 * @copyright  Copyright (c) 2005-2007 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */


/**
 * Zend_Feed_Abstract
 */
require_once 'Zend/Feed/Abstract.php';

/**
 * Zend_Feed_EntryRss
 */
require_once 'Zend/Feed/EntryRss.php';


/**
 * RSS channel class
 *
 * The Zend_Feed_Rss class is a concrete subclass of
 * Zend_Feed_Abstract meant for representing RSS channels. It does not
 * add any methods to its parent, just provides a classname to check
 * against with the instanceof operator, and expects to be handling
 * RSS-formatted data instead of Atom.
 *
 * @category   Zend
 * @package    Zend_Feed
 * @copyright  Copyright (c) 2005-2007 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Feed_Rss extends Zend_Feed_Abstract
{
    /**
     * The classname for individual channel elements.
     *
     * @var string
     */
    protected $_entryClassName = 'Zend_Feed_EntryRss';

    /**
     * The element name for individual channel elements (RSS <item>s).
     *
     * @var string
     */
    protected $_entryElementName = 'item';

    /**
     * The default namespace for RSS channels.
     *
     * @var string
     */
    protected $_defaultNamespace = 'rss';

    /**
     * Override Zend_Feed_Abstract to set up the $_element and $_entries aliases.
     */
    public function __wakeup()
    {
        parent::__wakeup();

        // Find the base channel element and create an alias to it.
        $this->_element = $this->_element->getElementsByTagName('channel')->item(0);
        if (!$this->_element) {
            throw new Zend_Feed_Exception('No root <channel> element found, cannot parse channel.');
        }

        // Find the entries and save a pointer to them for speed and
        // simplicity.
        $this->_buildEntryCache();
    }


    /**
     * Make accessing some individual elements of the channel easier.
     *
     * Special accessors 'item' and 'items' are provided so that if
     * you wish to iterate over an RSS channel's items, you can do so
     * using foreach ($channel->items as $item) or foreach
     * ($channel->item as $item).
     *
     * @param string $var The property to access.
     * @return mixed
     */
    public function __get($var)
    {
        switch ($var) {
            case 'item':
                // fall through to the next case
            case 'items':
                return $this;

            default:
                return parent::__get($var);
        }
    }

    /**
     * Generate the header of the feed when working in write mode
     *
     * @param array $array the data to use - see Zend_Feed_Interface for array structure
     * @return DOMElement root node
     * @internal
     */
    protected function _mapFeedHeaders($array)
    {
        $channel = $this->_element->createElement('channel');
      
        $title = $this->_element->createElement('title', $array['title']);
        $channel->appendChild($title);
        
        $link = $this->_element->createElement('link', $array['link']);
        $channel->appendChild($link);
        
        $description = isset($array['description']) ? $array['description'] : '';
        $description = $this->_element->createElement('description', $description);
        $channel->appendChild($description);
        
        $pubdate = isset($array['pubDate']) ? $array['pubDate'] : time();
        $pubdate = $this->_element->createElement('pubDate', gmdate('r', $pubdate));
        $channel->appendChild($pubdate);
        
        $editor = '';
        if (!empty($array['email'])) {
            $editor .= $array['email'];
        }
        if (!empty($array['author'])) {
            $editor .= ' (' . $array['author'] . ')';
        }
        $author = $this->_element->createElement('managingEditor', ltrim($editor));
        $channel->appendChild($author);
        
        if (!empty($array['copyright'])) {
            $copyright = $this->_element->createElement('copyright', $array['copyright']);
            $channel->appendChild($copyright);
        }
        
        if (!empty($array['image'])) {
            $image = $this->_element->createElement('image');
            $url = $this->_element->createElement('url', $array['image']);
            $image->appendChild($url);
            $imagetitle = $this->_element->createElement('title', $array['title']);
            $image->appendChild($imagetitle);
            $imagelink = $this->_element->createElement('link', $array['link']);
            $image->appendChild($imagelink);
            
            $channel->appendChild($image);
        }
        
        $generator = !empty($array['generator']) ? $array['generator'] : 'Zend_Feed';
        $generator = $this->_element->createElement('generator', $generator);
        $channel->appendChild($generator);
        
        return $channel;
    }

    /**
     * Generate the entries of the feed when working in write mode
     *
     * The following nodes are constructed for each feed entry
     * <item>
     *    <title>entry title</title>
     *    <link>url to feed entry</link>
     *    <guid>url to feed entry</guid>
     *    <description>short text</description>
     *    <content:encoded>long version, can contain html</content:encoded>
     * </item>
     *
     * @param array $array the data to use - see Zend_Feed_Interface for array structure
     * @param DOMElement $root the root node to use
     * @internal
     */
    protected function _mapFeedEntries(DOMElement $root, $array)
    {
        if (empty($array['entries'])) {
            return ;
        }

        Zend_Feed::registerNamespace('content', 'http://purl.org/rss/1.0/modules/content/');
        
        foreach ($array['entries'] as $dataentry) {
            $item = $this->_element->createElement('item');
            
            $title = $this->_element->createElement('title', $dataentry['title']);
            $item->appendChild($title);
            
            $link = $this->_element->createElement('link', $dataentry['link']);
            $item->appendChild($link);
            
            $guid = $this->_element->createElement('guid', isset($dataentry['guid']) ? $dataentry['guid'] : $dataentry['link']);
            $item->appendChild($guid);
            
            $description = $this->_element->createElement('description');
            $description->appendChild($this->_element->createCDATASection($dataentry['description']));
            $item->appendChild($description);
            
            if (isset($dataentry['content'])) {
                $content = $this->_element->createElement('content:encoded');
                $content->appendChild($this->_element->createCDATASection($dataentry['content']));
                $item->appendChild($content);
            }

            $pubdate = isset($dataentry['pubDate']) ? $dataentry['pubDate'] : time();
            $pubdate = $this->_element->createElement('pubDate', gmdate('r', $pubdate));
            $item->appendChild($pubdate);            

            $root->appendChild($item);
        }
    }

    /*
     * Override Zend_Feed_Element to include <rss> root node
     * 
     * @return string
     */
    public function saveXML()
    {
        // Return a complete document including XML prologue.
        $doc = new DOMDocument($this->_element->ownerDocument->version,
                               $this->_element->ownerDocument->actualEncoding);
        $root = $doc->createElement('rss');
        
        // Use rss version 2.0
        $root->setAttribute('version', '2.0');
        
        // Content namespace
        $root->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:content', 'http://purl.org/rss/1.0/modules/content/');
        $root->appendChild($doc->importNode($this->_element, true));
        
        // Append root node
        $doc->appendChild($root);
        
        // Format output
        $doc->formatOutput = true;

        return $doc->saveXML();
    }
    
    /**
     * Send feed to a http client with the correct header
     *
     * @throws Zend_Feed_Exception if headers have already been sent 
     * @return void
     */
    public function send()
    {        
        if (headers_sent()) {
            throw new Zend_Feed_Exception('Cannot send RSS because headers have already been sent.');
        }
        
        header('Content-type: application/rss+xml; charset: ' . $this->_element->ownerDocument->actualEncoding);
        
        echo $this->saveXML();
    } 

}
