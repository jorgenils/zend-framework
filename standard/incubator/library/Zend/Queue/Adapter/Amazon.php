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
 * @package    Zend_Queue
 * @subpackage Adapter
 * @copyright  Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * @see Zend_Queue_Adapter_Abstract
 */
require_once 'Zend/Queue/Adapter/Abstract.php';

/**
 * @see Zend_Http_Client
 */
require_once 'Zend/Http/Client.php';

/**
 * Class for connecting to the Amazon Simple Queue Service (SQS)
 *
 * @category   Zend
 * @package    Zend_Queue
 * @subpackage Adapter
 * @copyright  Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Queue_Adapter_Amazon extends Zend_Queue_Adapter_Abstract implements Countable {

    /**
     * HTTP end point for the Amazon SQS service
     */
    const SQS_ENDPOINT = 'http://queue.amazonaws.com';

    /**
     * Constructor
     *
     * @param array $config
     */
    public function __construct($config)
    {
        parent::__construct($config);

        $options = &$this->_config['driver_options'];
        if (!array_key_exists('access_key', $options)) {
            /**
             * @see Zend_Queue_Adapter_Exception
             */
            require_once 'Zend/Queue/Adapter/Exception.php';
            throw new Zend_Queue_Adapter_Exception("Configuration array must have a key for 'access_key' for the Amazon AWS Access Key to use");
        }
        if (!array_key_exists('secret_key', $options)) {
            /**
             * @see Zend_Queue_Adapter_Exception
             */
            require_once 'Zend/Queue/Adapter/Exception.php';
            throw new Zend_Queue_Adapter_Exception("Configuration array must have a key for 'secret_key' for the Amazon AWS Secret Key to use");
        }
    }

    /**
     * Get the available queues
     *
     * @return array
     * @throws Zend_Queue_Adapter_Exception
     */
    public function getQueues()
    {
        $result = $this->_makeRequest('ListQueues');

        if ($result->ListQueuesResult->QueueUrl === null) {
            /**
             * @see Zend_Queue_Adapter_Exception
             */
            require_once 'Zend/Queue/Adapter/Exception.php';
            throw new Zend_Queue_Adapter_Exception($result->Error->Code);
        }

        return (array)$result->ListQueuesResult->QueueUrl;
    }

    /**
     * Create a new queue
     *
     * @param  string  $name    queue name
     * @param  integer $timeout default visiblity timeout
     * @return boolean
     * @throws Zend_Queue_Adapter_Exception
     */
    public function create($name, $timeout=null)
    {
        $params = array();
        $params['QueueName'] = $name;
        $timeout = ($timeout === null) ? 30 : (int)$timeout;
        $params['DefaultVisibilityTimeout'] = $timeout;

        $result = $this->_makeRequest('CreateQueue', $params);

        if ($result->CreateQueueResult->QueueUrl === null) {
            /**
             * @see Zend_Queue_Adapter_Exception
             */
            require_once 'Zend/Queue/Adapter_Exception';
            throw new Zend_Queue_Adapter_Exception($result->Error->Code);
        }

        $this->setActiveQueue($name);
        return true;
    }

    /**
     * Delete a queue
     *
     * @param  string $name
     * @return boolean
     * @throws Zend_Queue_Adapter_Exception
     */
    public function delete($name)
    {
        $this->setActiveQueue($name);

        $result = $this->_makeRequest('DeleteQueue');

        if ($result->Error->Code !== null) {
            /**
             * @see Zend_Queue_Adapter_Exception
             */
            require_once 'Zend/Queue/Adapter/Exception.php';
            throw new Zend_Queue_Adapter_Exception($result->Error->Code);
        }
        return true;
    }

    /**
     * Send a message to the queue
     *
     * @param  string $message message
     * @param  string $name    queue name
     * @return Zend_Queue_Message
     * @throws Zend_Queue_Adapter_Exception
     */
    public function send($message, $name=null)
    {
        if ($name !== null) {
            $this->setActiveQueue($name);
        }

        $params = array();
        $params['MessageBody'] = urlencode($message);

        $result = $this->_makeRequest('SendMessage', $params);

        if ($result->SendMessageResult->MessageId === null) {
            /**
             * @see Zend_Queue_Adapter_Exception
             */
            require_once 'Zend/Queue/Adapter/Exception.php';
            throw new Zend_Queue_Adapter_Exception($result->Error->Code);
        }

        $config = array(
            'queue' => $this,
            'data'  => array(
                'message_id' => (string)$result->SendMessageResult->MessageId,
                'body'       => urldecode((string)$result->SendMessageResult->Body),
                'md5'        => (string)$result->SendMessageResult->MD5OfBody,
                'handle'     => (string)$result->SendMessageResult->ReceiptHandle
            )
        );
        Zend_Loader::loadClass($this->_msgClass);
        return new $this->_msgClass($config);
    }

    /**
     * Get the attributes for the queue
     *
     * @param  string $attribute
     * @return string
     * @throws Zend_Queue_Adapter_Exception
     */
    public function getAttribute($attribute='All')
    {
        $params = array();
        $params['AttributeName'] = $attribute;

        $result = $this->_makeRequest('GetQueueAttributes', $params);

        if ($result->GetQueueAttributesResult->Attribute === null) {
            /**
             * @see Zend_Queue_Adapter_Exception
             */
            require_once 'Zend/Queue/Adapter/Exception.php';
            throw new Zend_Queue_Adapter_Exception($result->Error->Code);
        }

        return (string)$result->GetQueueAttributesResult->Attribute->Value;
    }

    /**
     * Returns the approximate number of messages in the queue
     *
     * @return integer
     * @throws Zend_Queue_Adapter_Exception
     */
    public function count()
    {
        return (int)$this->getAttribute('ApproximateNumberOfMessages');
    }

    /**
     * Get messages in the queue
     *
     * @param  integer $max_msgs
     * @param  integer $timeout
     * @return Zend_Queue_Message_Iterator
     * @throws Zend_Queue_Adapter_Exception
     */
    public function receive($max_msgs=null, $timeout=null)
    {
        $params = array();

        // If not set, the visibility timeout on the queue is used
        if ($timeout !== null) {
            $params['VisibilityTimeout'] = (int)$timeout;
        }

        // SQS will default to only returning one message
        if ($max_msgs !== null) {
            $params['MaxNumberOfMessages'] = (int)$max_msgs;
        }

        $result = $this->_makeRequest('ReceiveMessage', $params);

        if ($result->ReceiveMessageResult->Message === null) {
            /**
             * @see Zend_Queue_Adapter_Exception
             */
            require_once 'Zend/Queue/Adapter/Exception.php';
            throw new Zend_Queue_Adapter_Exception($result->Error->Code);
        }

        $data = array();
        foreach ($result->ReceiveMessageResult->Message as $message) {
            $data[] = array(
                'message_id' => (string)$message->MessageId,
                'handle'     => (string)$message->ReceiptHandle,
                'md5'        => (string)$message->MD5OfBody,
                'body'       => urldecode((string)$message->Body)
            );
        }

        $config = array(
            'queue'    => $this,
            'data'     => $data,
            'msgClass' => $this->_msgClass
        );
        Zend_Loader::loadClass($this->_msgsetClass);
        return new $this->_msgsetClass($config);
    }

    /**
     * Delete a given message from the queue
     *
     * @param  string $handle
     * @return boolean
     * @throws Zend_Queue_Adapter_Exception
     */
    public function deleteMessage($handle)
    {
        $params = array();
        $params['ReceiptHandle'] = $handle;

        $result= $this->_makeRequest('DeleteMessage', $params);

        if ($result->Error->Code !== null) {
            /**
             * @see Zend_Queue_Adapter_Exception
             */
            require_once 'Zend/Queue/Adapter/Exception.php';
            throw new Zend_Queue_Adapter_Exception($result->Error->Code);
        }
        return true;
    }

    /**
     * Make a request to Amazon SQS
     *
     * @param  string $action
     * @param  array $params
     * @return SimpleXMLElement
     */
    private function _makeRequest($action, $params=array())
    {
        $options = &$this->_config['driver_options'];

        $retry_count = 0;

        do {
            $retry = false;

            $params['Action'] = $action;
            $params['Expires'] = gmdate(DATE_ISO8601, time()+10);
            $params['Version'] = '2008-01-01';
            $params['AWSAccessKeyId'] = $options['access_key'];
            $params['SignatureVersion'] = '1';

            // parameters need to be in alphabetical order
            uksort($params, 'strcasecmp');
            $sig_str = '';
            foreach ($params as $key=>$val) {
                $sig_str .= $key.$val;
            }
            // We can use Zend_Crypt once it is available inplace of the hash_hmac function
            $params['Signature'] = base64_encode(hash_hmac('sha1', utf8_encode($sig_str), $options['secret_key'], true));

            $client = new Zend_Http_Client();
            if ($action == 'ListQueues' || $action == 'CreateQueue') {
                $client->setUri(self::SQS_ENDPOINT);
            }
            else {
                $client->setUri($this->getActiveQueue());
            }
            $client->setParameterGet($params);

            $response = $client->request('GET');

            $response_code = $response->getStatus();

            // Some 5xx errors are expected, so retry automatically
            if ($response_code >= 500 && $response_code < 600 && $retry_count <= 5) {
                $retry = true;
                $retry_count++;
                //echo $response_code, ' : retrying ', $action, ' request (', $retry_count, ')', "\n<br />\n";
                sleep($retry_count / 4 * $retry_count);
            }

        }
        while ($retry);

        return new SimpleXMLElement($response->getBody());
    }

    /**
     * Get the full queue URL
     *
     * @return string
     */
    public function getActiveQueue()
    {
        return self::SQS_ENDPOINT.'/'.$this->_config['name'];
    }
}