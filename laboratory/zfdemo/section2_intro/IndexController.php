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
 * @version    $Id: IndexController.php 122 2007-04-13 14:27:45Z gavin $
 */

require_once 'Zend/Controller/Action.php';

class IndexController extends Zend_Controller_Action
{
    /**
     * Instance property of this action controller, holding a reference to the view object
     * created in the bootstrap.php, and passed using:
     *     $frontController->setParam('view', $view);
     * @var Zend_View_Interface
     */
    protected $_view = null;

    public function init()
    {
        parent::init(); // just good habit (nothing there currently)

        // maps to arg 'view' from: $frontController->setParam('view', $view);
        $this->_view = $this->getInvokeArg('view');
    }

    public function helloAction()
    {
        echo 'Hello World!';
    }

    // the default action is "indexAction", unless explcitly set to something else
    public function indexAction()
    {
        // STAGE 3: Choose, create, and optionally update models using business logic.
        $cities = parse_ini_file('cities.ini'); // $cities contains our simple data model

        // STAGE 4: Apply business logic to create a presentation model for the view.
        ksort($_SERVER);
        $this->_view->SERVER = $_SERVER;
        $this->_view->date = date('Y-m-d H:i:s');
        $this->_view->cities = array();
        $this->_view->distances = array();
        if (isset($_REQUEST['distance'])) {
            $maxDistance = intval($_REQUEST['distance']);
        } else {
            $this->_redirect('/?distance=10000');
        }
        $this->_view->maxDistance = $maxDistance;

        foreach($cities as $city => $distance) {
            // business logic specifies to filter the data model satisfying distance criteria
            if ($distance < $maxDistance) {
                $this->_view->cities[] = $city;
                $this->_view->distances[] = $distance;
                #echo "Distance from London, UK to $city is $distance km.<br>\n";
            }
        }

        // STAGE 5: Choose view and submit presentation model to view.
        $this->_response->appendBody($this->_view->render('indexIndex.phtml'));
    }

    // redirect bogus URLs back to the application's "home" page
    public function noRouteAction()
    {
        $this->_redirect('/');
    }
}
