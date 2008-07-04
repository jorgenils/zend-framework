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
 * @package    Zend_Dojo
 * @subpackage View
 * @copyright  Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: $
 */

/** Zend_View_Helper_HtmlElement */
require_once 'Zend/View/Helper/HtmlElement.php';

/**
 * Dojo AccordionContainer dijit
 * 
 * @uses       Zend_View_Helper_Abstract
 * @package    Zend_Dojo
 * @subpackage View
 * @copyright  Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
  */
class Zend_Dojo_View_Helper_Abstract extends Zend_View_Helper_HtmlElement
{
    /**
     * @var Zend_Dojo_View_Helper_Dojo_Container
     */
    public $dojo;

    /**
     * Dijit being used
     * @var string
     */
    protected $_dijit;

    /**
     * Dojo module to use
     * @var string
     */
    protected $_module;


    /**
     * Set view
     *
     * Set view and enable dojo
     * 
     * @param  Zend_View_Interface $view 
     * @return Zend_Dojo_View_Helper_Abstract
     */
    public function setView(Zend_View_Interface $view)
    {
        parent::setView($view);
        $this->dojo = $this->view->dojo();
        $this->dojo->enable();
        return $this;
    }

    /**
     * Whether or not to use declarative dijit creation
     * 
     * @return bool
     */
    protected function _useDeclarative()
    {
        return Zend_Dojo_View_Helper_Dojo::useDeclarative();
    }

    /**
     * Whether or not to use programmatic dijit creation
     * 
     * @return bool
     */
    protected function _useProgrammatic()
    {
        return Zend_Dojo_View_Helper_Dojo::useProgrammatic();
    }

    /**
     * Whether or not to use programmatic dijit creation w/o script creation
     * 
     * @return bool
     */
    protected function _useProgrammaticNoScript()
    {
        return Zend_Dojo_View_Helper_Dojo::useProgrammaticNoScript();
    }

    /**
     * Create a layout container
     * 
     * @param  int $id 
     * @param  string $content 
     * @param  array $params 
     * @param  array $attribs 
     * @return string
     */
    protected function _createLayoutContainer($id, $content, array $params, array $attribs)
    {
        $this->view->dojo()->requireModule($this->_module);

        $attribs['id'] = $id;
        if (array_key_exists('id', $params)) {
            unset($params['id']);
        }
        if ($this->_useDeclarative()) {
            $attribs = array_merge($attribs, $params);
            $attribs['dojoType'] = $this->_dijit;
        } elseif (!$this->_useProgrammaticNoScript()) {
            $this->_createDijit($this->_dijit, $id, $params);
        }

        $html = '<div' . $this->_htmlAttribs($attribs) . '>'
              . $content
              . "</div>\n";

        return $html;
    }

    /**
     * Create a dijit programmatically
     * 
     * @param  string $dijit 
     * @param  string $id 
     * @param  array $params 
     * @return void
     */
    protected function _createDijit($dijit, $id, array $params)
    {
        if (empty($params)) {
            $params = '{}';
        } else {
            require_once 'Zend/Json.php';
            $params = Zend_Json::encode($params);
        }

        $lambda =<<<EOJ
function() {
    var zfDijit = new $dijit($params, dojo.byId('$id'));
    zfDijit.startup();
}
EOJ;

        $this->dojo->addOnLoad($lambda);
    }
}
