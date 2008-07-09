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
 * @package    Zend_Form
 * @copyright  Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/** Zend_Form_Decorator_Form */
require_once 'Zend/Form/Decorator/Form.php';

/**
 * Zend_Dojo_Form_Decorator_DijitContainer
 *
 * Render a dojo dijit layout container via a view helper
 *
 * Accepts the following options:
 * - separator: string with which to separate passed in content and generated content
 * - helper:    the name of the view helper to use
 *
 * Assumes the view helper accepts three parameters, the name, value, and 
 * optional attributes; these will be provided by the element.
 * 
 * @package    Zend_Dojo
 * @subpackage Form_Decorator
 * @copyright  Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: $
 */
class Zend_Dojo_Form_Decorator_DijitContainer extends Zend_Form_Decorator_Form
{
    /**
     * View helper
     * @var string
     */
    protected $_helper = 'ContentPane';

    /**
     * Dijit option parameters
     * @var array
     */
    protected $_dijitParams = array();

    /**
     * Get view helper for rendering container
     * 
     * @return string
     */
    public function getHelper()
    {
        if (null === $this->_helper) {
            if (null === ($helper = $this->getOption('helper'))) {
                require_once 'Zend/Form/Decorator/Exception.php';
                throw new Zend_Form_Decorator_Exception('No view helper specified fo DijitContainer decorator');
            } 
            $this->setHelper($helper);
            $this->removeOption('helper');
        }
        return $this->_helper;
    }

    /**
     * Retrieve decorator options
     *
     * Assures that form action and method are set, and sets appropriate 
     * encoding type if current method is POST.
     * 
     * @return array
     */
    public function getOptions()
    {
        $helper = $this->getHelper();

        if (null !== ($element = $this->getElement())) {
            if (($helper == 'Form') && $element instanceof Zend_Form) {
                $element->getAction();
                $method = $element->getMethod();
                if ($method == Zend_Form::METHOD_POST) {
                    $this->setOption('enctype', 'application/x-www-form-urlencoded');
                }
                foreach ($element->getAttribs() as $key => $value) {
                    if ('dijitParams' == $key) {
                        $this->setDijitParams($value);
                    } else {
                        $this->setOption($key, $value);
                    }
                }
            } elseif (($element instanceof Zend_Form_SubForm)
                || ($element instanceof Zend_Form_DisplayGroup)
            ) {
                foreach ($element->getAttribs() as $key => $value) {
                    if ('dijitParams' == $key) {
                        $this->setDijitParams($value);
                    } else {
                        $this->setOption($key, $value);
                    }
                }
            }
        }

        if (isset($this->_options['method'])) {
            $this->_options['method'] = strtolower($this->_options['method']);
        }

        if (array_key_exists('dijitParams', $this->_options)) {
            $this->setDijitParams($this->_options['dijitParams']);
            unset($this->_options['dijitParams']);
        }

        return $this->_options;
    }

    /**
     * Set a single dijit option parameter
     * 
     * @param  string $key 
     * @param  mixed $value 
     * @return Zend_Dojo_Form_Decorator_DijitContainer
     */
    public function setDijitParam($key, $value)
    {
        $this->_dijitParams[(string) $key] = $value;
        return $this;
    }

    /**
     * Set dijit option parameters
     * 
     * @param  array $params 
     * @return Zend_Dojo_Form_Decorator_DijitContainer
     */
    public function setDijitParams(array $params)
    {
        $this->_dijitParams = array_merge($this->_dijitParams, $params);
        return $this;
    }

    /**
     * Retrieve a single dijit option parameter
     * 
     * @param  string $key 
     * @return mixed|null
     */
    public function getDijitParam($key)
    {
        $this->getOptions();
        $key = (string) $key;
        if (array_key_exists($key, $this->_dijitParams)) {
            return $this->_dijitParams[$key];
        }

        return null;
    }

    /**
     * Get dijit option parameters
     * 
     * @return array
     */
    public function getDijitParams()
    {
        $this->getOptions();
        return $this->_dijitParams;
    }

    /**
     * Render a dijit layout container
     *
     * Replaces $content entirely from currently set element.
     * 
     * @param  string $content 
     * @return string
     */
    public function render($content)
    {
        $element = $this->getElement();
        $view    = $element->getView();
        if (null === $view) {
            return $content;
        }

        $dijitParams = $this->getDijitParams();
        $attribs     = $this->getOptions();
        $helper      = $this->getHelper();

        return $view->$helper($element->getName(), $content, $dijitParams, $attribs); 
    }
}
