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
 * Input feed data interface
 *
 * Classes implementing this interface can be passe to Zend_Feed::factory
 * as an input data source for the Zend_Feed construction
 *
 * @category   Zend
 * @package    Zend_Feed
 * @copyright  Copyright (c) 2005-2007 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
interface Zend_Feed_Builder_Interface
{
    /**
     * Returns feed data
     * 
     * The returned data must have the following format:
     * <code>
     *  array( 
     *  'title'       => 'title of the feed', //required
     *  'link'        => 'canonical url to the feed', //required
     *  'lastUpdate'  => 'timestamp of the update date', // optional
     *  'published'   => 'timestamp of the publication date', //optional
     *  'charset'     => 'charset', // required
     *  'description' => 'short description of the feed', //optional
     *  'author'      => 'author/publisher of the feed', //optional
     *  'email'       => 'email of the author', //optional
     *  'webmaster'   => 'email address for person responsible for technical issues' // optional, ignored if atom is used
     *  'copyright'   => 'copyright notice', //optional
     *  'image'       => 'url to image', //optional
     *  'generator'   => 'generator', // optional
     *  'language'    => 'language the feed is written in', // optional
     *  'ttl'         => 'how long in minutes a feed can be cached before refreshing', // optional, ignored if atom is used
     *  'rating'      => 'The PICS rating for the channel.', // optional, ignored if atom is used
     *  'cloud'       => array(
     *                    'domain'            => 'domain of the cloud, e.g. rpc.sys.com' // required
     *                    'port'              => 'port to connect to' // optional, default to 80
     *                    'path'              => 'path of the cloud, e.g. /RPC2 //required
     *                    'registerProcedure' => 'procedure to call, e.g. myCloud.rssPleaseNotify' // required
     *                    'protocol'          => 'protocol to use, e.g. soap or xml-rpc' // required
     *                    ), a cloud to be notified of updates // optional, ignored if atom is used
     *  'textInput'   => array(
     *                    'title'       => 'the label of the Submit button in the text input area' // required,
     *                    'description' => 'explains the text input area' // required
     *                    'name'        => 'the name of the text object in the text input area' // required
     *                    'link'        => 'the URL of the CGI script that processes text input requests' // required
     *                    ) // a text input box that can be displayed with the feed // optional, ignored if atom is used
     *  'skipHours'   => array(
     *                    'hour in 24 format', // e.g 13 (1pm)
     *                    // up to 24 rows whose value is a number between 0 and 23
     *                    ) // Hint telling aggregators which hours they can skip // optional, ignored if atom is used
     *  'skipDays '   => array(
     *                    'a day to skip', // e.g Monday
     *                    // up to 7 rows whose value is a Monday, Tuesday, Wednesday, Thursday, Friday, Saturday or Sunday
     *                    ) // Hint telling aggregators which days they can skip // optional, ignored if atom is used
     *  'entries'     => array(
     *                   array(
     *                    'title'        => 'title of the feed entry', //required
     *                    'link'         => 'url to a feed entry', //required
     *                    'description'  => 'short version of a feed entry', // only text, no html, required
     *                    'guid'         => 'id of the article, if not given link value will used', //optional
     *                    'content'      => 'long version', // can contain html, optional
     *                    'lastUpdate'   => 'timestamp of the publication date', // optional
     *                    'comments'     => 'comments page of the feed entry', // optional
     *                    'commentRss'   => 'the feed url of the associated comments', // optional
     *                    'source'       => array(
     *                                        'title' => 'title of the original source' // required,
     *                                        'url' => 'url of the original source' // required
     *                                           ) // original source of the feed entry // optional
     *                    'category'     => array(
     *                                      array(
     *                                        'term' => 'first category label' // required,
     *                                        'scheme' => 'url that identifies a categorization scheme' // optional
     *                                            ), 
     *                                      array(
     *                                         //data for the second category and so on
     *                                           )
     *                                        ) // list of the attached categories // optional
     *                    'enclosure'    => array(
     *                                      array(
     *                                        'url' => 'url of the linked enclosure' // required
     *                                        'type' => 'mime type of the enclosure' // optional
     *                                        'length' => 'length of the linked content in octets' // optional
     *                                           ),
     *                                      array(
     *                                         //data for the second enclosure and so on
     *                                           )
     *                                        ) // list of the enclosures of the feed entry // optional 
     *                   ),
     *                   array(
     *                   //data for the second entry and so on
     *                   )
     *                 )
     * );
     * </code>
     * 
     * @return array
     */
    public function getFeedData();
}