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

require_once 'Zend/Feed/Builder/Exception.php';

/**
 * ITunes rss extension
 *
 * Classes used to describe the itunes channel extension
 *
 * @category   Zend
 * @package    Zend_Feed
 * @copyright  Copyright (c) 2005-2007 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Feed_Builder_Header_Itunes extends ArrayObject
{
    /**
     * Constructor
     *
     * @param array $categories Categories columns and in iTunes Music Store Browse
     */
    public function __construct(array $categories)
    {
        $this->setCategories($categories);
    }

    /**
     * Sets the categories column and in iTunes Music Store Browse
     * $categories must conform to the following format:
     * <code>
     * array(array('main' => 'main category',
     *             'sub' => 'sub category' // optionnal
     *            ),
     *       // up to 3 rows
     *      )
     * </code>
     *
     * @param array $categories
     * @return Zend_Feed_Builder_Header_Itunes
     * @throws Zend_Feed_Builder_Exception
     */
    public function setCategories(array $categories)
    {
        $nb = count($categories);
        if (!$nb || $nb > 3) {
            if ($nb) {
                throw new Zend_Feed_Builder_Exception("you have to set at most three itunes categories");
            } else {
                throw new Zend_Feed_Builder_Exception("you have to set at least one itunes category");
            }
        }
        foreach ($categories as $i => $category) {
            if (empty($category['main'])) {
                throw new Zend_Feed_Builder_Exception("you have to set the main category (category #$i)");
            }
        }
        $this->offsetSet('category', $categories);
    }

    /**
     * Sets the artist value, default to the feed's author value
     *
     * @param string $author
     * @return Zend_Feed_Builder_Header_Itunes
     */
    public function setAuthor($author)
    {
        $this->offsetSet('author', $author);
        return $this;
    }

    /**
     * Sets the owner of the postcast
     *
     * @param string $name default to the feed's author value
     * @param string $email default to the feed's email value
     * @return Zend_Feed_Builder_Header_Itunes
     * @throws Zend_Feed_Builder_Exception
     */
    public function setOwner($name = '', $email = '')
    {
        if (!empty($email)) {
            Zend::loadClass('Zend_Validate_EmailAddress');
            $validate = new Zend_Validate_EmailAddress();
            if (!$validate->isValid($email)) {
                throw new Zend_Feed_Builder_Exception("you have to set a valid email address into the itunes owner's email property");
            }
        }
        $this->offsetSet('owner', array('name' => $name, 'email' => $email));
        return $this;
    }

    /**
     * Sets the album/podcast art picture
     * Default to the feed's image value
     *
     * @param string $image
     * @return Zend_Feed_Builder_Header_Itunes
     */
    public function setImage($image)
    {
        $this->offsetSet('image', $image);
        return $this;
    }

    /**
     * Sets the short description of the podcast
     * Default to the feed's description
     *
     * @param string $subtitle
     * @return Zend_Feed_Builder_Header_Itunes
     */
    public function setSubtitle($subtitle)
    {
        $this->offsetSet('subtitle', $subtitle);
        return $this;
    }

    /**
     * Sets the longer description of the podcast
     * Default to the feed's description
     *
     * @param string $summary
     * @return Zend_Feed_Builder_Header_Itunes
     */
    public function setSummary($summary)
    {
        $this->offsetSet('summary', $summary);
        return $this;
    }

    /**
     * Prevent a feed from appearing
     *
     * @param string $block can be 'yes' or 'no'
     * @return Zend_Feed_Builder_Header_Itunes
     * @throws Zend_Feed_Builder_Exception
     */
    public function setBlock($block)
    {
        $block = strtolower($block);
        if (!in_array($block, array('yes', 'no'))) {
            throw new Zend_Feed_Builder_Exception("you have to set yes or no to the itunes block property");
        }
        $this->offsetSet('block', $block);
        return $this;
    }

    /**
     * Configuration of the parental advisory graphic
     *
     * @param string $explicit can be 'yes', 'no' or 'clean'
     * @return Zend_Feed_Builder_Header_Itunes
     * @throws Zend_Feed_Builder_Exception
     */
    public function setExplicit($explicit)
    {
        $explicit = strtolower($explicit);
        if (!in_array($explicit, array('yes', 'no', 'clean'))) {
            throw new Zend_Feed_Builder_Exception("you have to set yes, no or clean to the itunes explicit property");
        }
        $this->offsetSet('explicit', $explicit);
        return $this;
    }

    /**
     * Sets a comma separated list of 12 keywords maximum
     *
     * @param string $keywords
     * @return Zend_Feed_Builder_Header_Itunes
     */
    public function setKeywords($keywords)
    {
        $this->offsetSet('keywords', $keywords);
        return $this;
    }

    /**
     * Sets the new feed URL location
     *
     * @param string $url
     * @return Zend_Feed_Builder_Header_Itunes
     */
    public function setNewFeedUrl($url)
    {
        $this->offsetSet('new-feed-url', $url);
        return $this;
    }
}