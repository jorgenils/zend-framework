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

require_once 'Zend/Feed/Builder/Interface.php';
require_once 'Zend/Feed/Exception.php';

/**
 * A simple implementation of Zend_Feed_Builder_Interface.
 *
 * Users are encouraged to make their own classes to implement Zend_Feed_Builder_Interface
 *
 * @category   Zend
 * @package    Zend_Feed
 * @copyright  Copyright (c) 2005-2007 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

class Zend_Feed_Builder implements Zend_Feed_Builder_Interface
{
    /**
     * The data of the feed
     *
     * @var $_data array
     */
    private $_data;

    /**
     * Constructor
     *
     * @param $data array
     * @throws Zend_Feed_Exception
     */
    public function __construct(array $data)
    {
        $this->_data = $data;
        $this->_validate();
    }

    /**
     * Returns feed data
     *
     * @return array
     */
    public function getFeedData()
    {
        return $this->_data;
    }

    /**
     * Validate the content of the data array
     *
     * @throws Zend_Feed_Exception
     */
    private function _validate()
    {
        /* general properties */
        $mandatories = array('title', 'link', 'charset');
        foreach ($mandatories as $mandatory) {
            if (empty($this->_data[$mandatory])) {
                throw new Zend_Feed_Exception("you have to set \"$mandatory\" key to a non empty value");
            }
        }

        if (isset($this->_data['email'])) {
            Zend::loadClass('Zend_Validate_EmailAddress');
            $validate = new Zend_Validate_EmailAddress();
            if (!$validate->isValid($this->_data['email'])) {
                throw new Zend_Feed_Exception("you have to set a valid email address into the email property");
            }
        }

        /* validate rss specific properties */
        $this->_validateRssProperties();

        if (!isset($this->_data['entries'])) {
            throw new Zend_Feed_Exception("you have to set entries key");
        }

        /* entry properties */
        foreach ($this->_data['entries'] as $idx => $entry) {
            $mandatories = array('title', 'link', 'description');
            foreach ($mandatories as $mandatory) {
                if (empty($entry[$mandatory])) {
                    throw new Zend_Feed_Exception("you have to set \"$mandatory\" key (entry $idx) to a non empty value");
                }
            }

            if (isset($entry['category'])) {
                /* validate category entries */
                foreach ($entry['category'] as $i => $category) {
                    if (empty($category['term'])) {
                        throw new Zend_Feed_Exception("you have to set \"term\" key (entry $idx, category $i) to a non empty value");
                    }
                }
            }

            if (isset($entry['source'])) {
                /* validate source property */
                $mandatories = array('title', 'url');
                foreach ($mandatories as $mandatory) {
                    if (empty($entry['source'][$mandatory])) {
                        throw new Zend_Feed_Exception("you have to set \"$mandatory\" key of the source property (entry $idx) to a non empty value");
                    }
                }
            }

            if (isset($entry['enclosure'])) {
                /* validate enclosure property */
                foreach ($entry['enclosure'] as $i => $enclosure) {
                    if (empty($enclosure['url'])) {
                        throw new Zend_Feed_Exception("you have to set \"url\" key of the enclosure property (entry $idx, enclosure $i) to a non empty value");
                    }
                }
            }
        }
    }

    /**
     * Validate the rss specific properties of the channel node
     *
     * @throws Zend_Feed_Exception
     */
    private function _validateRssProperties()
    {
        /* webmaster email */
        if (isset($this->_data['webmaster'])) {
            Zend::loadClass('Zend_Validate_EmailAddress');
            $validate = new Zend_Validate_EmailAddress();
            if (!$validate->isValid($this->_data['webmaster'])) {
                throw new Zend_Feed_Exception("you have to set a valid email address into the webmaster property");
            }
        }

        /* rss cloud node */
        if (isset($this->_data['cloud'])) {
            $mandatories = array('domain', 'path', 'registerProcedure', 'protocol');
            foreach ($mandatories as $mandatory) {
                if (empty($this->_data['cloud'][$mandatory])) {
                    throw new Zend_Feed_Exception("you have to set \"$mandatory\" key of the cloud property");
                }
            }
        }

        /* rss textInput */
        if (isset($this->_data['textInput'])) {
            $mandatories = array('title', 'description', 'name', 'link');
            foreach ($mandatories as $mandatory) {
                if (empty($this->_data['textInput'][$mandatory])) {
                    throw new Zend_Feed_Exception("you have to set \"$mandatory\" key of the textInput property");
                }
            }
        }

        /* rss skipHours */
        if (isset($this->_data['skipHours'])) {
            if (count($this->_data['skipHours']) > 24) {
                throw new Zend_Feed_Exception("you can not have more than 24 rows in the skipHours property");
            }
            foreach ($this->_data['skipHours'] as $hour) {
                if ($hour < 0 || $hour > 23) {
                    throw new Zend_Feed_Exception("$hour has te be between 0 and 23");
                }
            }
        }

        /* rss skipDays */
        if (isset($this->_data['skipDays'])) {
            if (count($this->_data['skipDays']) > 7) {
                throw new Zend_Feed_Exception("you can not have more than 7 rows in the skipDays property");
            }
            $days = array('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday');
            foreach ($this->_data['skipDays'] as $day) {
                if (!in_array($day, $days)) {
                    throw new Zend_Feed_Exception("$day is not a valid day");
                }
            }
        }

        /* rss ttl */
        if (isset($this->_data['ttl'])) {
            Zend::loadClass('Zend_Validate_Int');
            $validate = new Zend_Validate_Int();
            if (!$validate->isValid($this->_data['ttl'])) {
                throw new Zend_Feed_Exception("you have to set an integer value to the ttl property");
            }
        }

        /* itunes extension */
        if (isset($this->_data['itunes'])) {
            /* category validation */
            if (empty($this->_data['itunes']['category'])) {
                throw new Zend_Feed_Exception("you have to set at least one itunes category");
            }
            if (count($this->_data['itunes']['category']) > 3) {
                throw new Zend_Feed_Exception("you have to set at most three itunes categories");
            }
            foreach ($this->_data['itunes']['category'] as $i => $category) {
                if (empty($category['main'])) {
                    throw new Zend_Feed_Exception("you have to set the main category (category #$i)");
                }
            }

            /* owner validation */
            if (!empty($this->_data['itunes']['owner']) && !empty($this->_data['itunes']['owner']['email'])) {
                Zend::loadClass('Zend_Validate_EmailAddress');
                $validate = new Zend_Validate_EmailAddress();
                if (!$validate->isValid($this->_data['itunes']['owner']['email'])) {
                    throw new Zend_Feed_Exception("you have to set a valid email address into the itunes owner's email property");
                }
            }

            /* block validation */
            if (!empty($this->_data['itunes']['block'])) {
                if (!in_array(strtolower($this->_data['itunes']['block']), array('yes', 'no'))) {
                    throw new Zend_Feed_Exception("you have to set yes or no to the itunes block property");
                }
            }

            /* explicit validation */
            if (!empty($this->_data['itunes']['explicit'])) {
                if (!in_array(strtolower($this->_data['itunes']['explicit']), array('yes', 'no', 'clean'))) {
                    throw new Zend_Feed_Exception("you have to set yes, no or clean to the itunes explicit property");
                }
            }
        }
    }
}