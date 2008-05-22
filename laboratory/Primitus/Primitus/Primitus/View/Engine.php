<?php
/**
 * Primitus
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 *
 * @category   Primitus
 * @package    Application
 * @copyright  Copyright (c) 2006 John Coggeshall
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

require_once 'Smarty.class.php';
require_once 'Primitus/View/Plugin/Render.php';
require_once 'Primitus/View/Exception.php';

/**
 * The Primitus view engine is based on the Smarty templating engine, which allows
 * us to create view-level functions such as {render} needed to trigger the rendering
 * of controllers in the correct locations. We also override Smarty's error handling and
 * plug it into Primitus's error handling mechansims.
 * 
 * @category   Primitus
 * @package    Primitus_View
 * @subpackage Engine
 * @copyright  Copyright (c) 2006 John Coggeshall
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Primitus_View_Engine extends Smarty {
	
	public $template_dir  = "%APP_VIEW_DIR%";
    public $compile_dir   = "%APP_VIEW_TMP_DIR%";
    public $config_dir    = "%APP_CFG_DIR%";
	
    /**
     * The Smarty error handler wrapper
     *
     * @param string $msg The error message
     * @param int $type The type of error which occurred
     */
	public function trigger_error($msg, $type = E_WARNING) {
		throw new Primitus_View_Exception($msg, (int)$type);
	}
	
	/**
	 * Builds the object, and registers the Primitus internal functions
	 * used in the rendering of the view (i.e. {render}
	 */
	public function __construct() {
		parent::Smarty();
		$this->register_function("render", "Primitus_View_Plugin_Render");
	}
}

?>