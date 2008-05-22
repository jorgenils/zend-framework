<?php
/**
 * Zend Framework ZFDemo Tutorial
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
 * @copyright  Copyright (c) 2007 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: ZFDemo_Mail_Transport_File.php 121 2007-04-12 21:48:01Z gavin $
 *
 * http://framework.zend.com/wiki/display/ZFDEV/Tutorial
 */


/**
 * Zend_Mail
 */
require_once 'Zend/Mail.php'; 


/**
 * Zend_Mail_Transport_Abstract
 */
require_once 'Zend/Mail/Transport/Sendmail.php';


/**
 * Class for sending emails to a log file.
 * Enables ZFDemo to work on Windoze, or whenever mail servers are not conveniently available.
 * Also, this transport simplifies creating unit tests for your application :)
 */
class ZFDemo_Mail_Transport_File extends Zend_Mail_Transport_Sendmail
{
    /**
     * File to write mails to .
     * @var string
     * @access private
     */
    private $filename = null;


    /**
     * This class extends an abstract class, so it must provide a constructor.
     * @param  array  OPTIONAL parameters - place holder, so that ZFDemo can easily switch to using different transport
     * @param  string $filename    Where to append the email       
     */
    public function __construct($parameters, $filename)
    {
        $this->parameters = $parameters;
        $this->filename = $filename;
    }


    /**
     * Send mail using PHP native mail()
     *
     * @access public
     * @return void
     * @throws Zend_Mail_Transport_Exception on mail() failure
     */
    public function _sendMail()
    {
        if (!file_put_contents( $this->filename,
              'To: ' . $this->recipients . "\n"
            . 'Subject: ' . $this->_mail->getSubject() . "\n"
            . 'Parameters: ' .  str_replace("\n", '; ', print_r($this->parameters, true)) . "\n"
            . $this->header . "\n"
            . $this->body
            , FILE_APPEND))
        {
            throw new Zend_Mail_Transport_Exception(_('Unable to send mail'));
        }
    }
}
