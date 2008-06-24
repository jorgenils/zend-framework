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
 * @package    Zend_Controller
 * @subpackage Zend_Controller_Action_Helper
 * @copyright  Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * @see Zend_Session_Namespace
 */
require_once 'Zend/Session/Namespace.php';

/**
 * @see Zend_Controller_Action_Helper_Abstract
 */
require_once 'Zend/Controller/Action/Helper/Abstract.php';

/**
 * @see Zend_Controller_Front
 */
require_once 'Zend/Controller/Front.php';

/**
 * TODO: Check to see if there are any methods that should be protected
 * TODO: Cleanup unused attribs and methods (if any)
 * TODO: Straighten out confusing naming issues (if any)
 * TODO: Complete unit testing
 * 
 * @category   Zend
 * @package    Zend_Controller
 * @subpackage Zend_Controller_Action_Helper
 * @copyright  Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Controller_Action_Helper_MultiPageForm extends Zend_Controller_Action_Helper_Abstract
{
    /**
     * Navigation action constants
     *
     */
    const ACTION_PREFIX   = 'prefix';
    const ACTION_NEXT     = 'next';
    const ACTION_PREVIOUS = 'previous';
    const ACTION_SUBMIT   = 'submit';
    const ACTION_CANCEL   = 'cancel';
    
    const PREFIX   = '_';
    const NEXT     = '_next';
    const PREVIOUS = '_previous';
    const SUBMIT   = '_submit';
    const CANCEL   = '_cancel';
    
    /**
     * Navigation element names
     *
     * @var array
     */
    protected $_navigationElements = array(self::ACTION_PREFIX   => self::PREFIX,
                                           self::ACTION_NEXT     => self::NEXT,
                                           self::ACTION_PREVIOUS => self::PREVIOUS,
                                           self::ACTION_SUBMIT   => self::SUBMIT,
                                           self::ACTION_CANCEL   => self::CANCEL);

    /**
     * Zend_Session storage object
     *
     * @var Zend_Session
     */
    protected $_session = null;

    /**
     * The complete Zend_Form instance
     *
     * @var Zend_Form
     */
    protected $_form = null;

    /**
     * The current subform instance
     *
     * @var Zend_Form
     */
    protected $_currentSubForm = null;

    /**
     * The form data
     *
     * @var array
     */
    protected $_tempFormData = array();

    /**
     * The mapping of subform to controller action
     *
     * @var array
     */
    protected $_subFormActions = array();
    
    /**
     * The order in which the forms appear
     *
     * @var array
     */
    protected $_subFormOrder = array();

    /**
     * The action that will be used for processing the form
     *
     * @var string
     */
    protected $_processAction = 'process';

    /**
     * The action for canceling the form
     *
     * @var string
     */
    protected $_cancelAction = null;

    /**
     * Last valid form name
     *
     * @var string
     */
    protected $_lastValidForm = null;

    /**
     * The currently active form
     *
     * @var string
     */
    protected $_activeFormName = null;
    
    /**
     * The form route part
     *
     * @var string
     */
    protected $_formNameRoutePart = 'form';
    
    /**
     * The router
     *
     * @var Zend_Controller_Router_Interface
     */
    protected $_router = null;
    
    /**
     * Construct and set default session object
     */
    public function __construct()
    {
        $this->_session = new Zend_Session_Namespace($this->getName());
        $this->_router  = Zend_Controller_Front::getInstance()->getRouter();
    }

    /**
     * Executed at preDispatch
     *
     */
    public function preDispatch()
    {
        // Check if there are actions assigned to the subforms
        if (empty($this->_subFormActions)) {
            /**
             * @see Zend_Controller_Action_Exception
             */
            require_once 'Zend/Controller/Action/Exception.php';

            throw new Zend_Controller_Action_Exception('Multiform has not been assigned any actions');
        }

        $action = $this->getRequest()->getActionName();
        $activeFormName = $this->getActiveFormName();
        
        if ($this->isSubFormAction($action) && $this->isSubForm($activeFormName)) {
            // If this is a subform action
            // Check to see if the action has been successfully validated.
            if (!$this->isValidForm($activeFormName)) { // FIXME: THIS FAILS : (
                // It's not a valid action, redirect to the latest valid action
                $this->redirect($this->getLastValidAction());
            } else {
                // Handle the form
                $this->handle();
            }

            // Assign the current subform to the view
            $this->getActionController()->view->form = $this->getCurrentSubForm();
        }
    }

    /**
     * Set a navigation action.
     * If no specific action is specified, the method expects an array with actions.
     *
     * @param string|array $value
     * @param string $action
     * @return Zend_Controller_Action_Helper_MultiPageForm
     */
    public function setNavigationAction($element, $action = null)
    {
        // Get the most recent navigation element names
        $navigationActions = $this->_navigationElements;
        
        if ($action == null) {
            // Merge the new navigation action array with the existing one
            // This way there are no missing entries 
            $navigationActions = array_merge($this->_navigationElements, (array) $element);
        } else {
            // Only set the navigation key for the specified action
            $navigationActions[$action] = (string) $element;
        }
        
        // Commit the new configuration back to the navigationelement attribute
        $this->_navigationElements = $navigationActions;
        
        // Chaining
        return $this;
    }
    
    /**
     * Get the navigation actions.
     * If no specific action is specified, all actions will be returned.
     *
     * @param string $action
     * @return string|array
     */
    public function getNavigationElement($action = null)
    {
        // If no specific action is specified, return the entire action array
        if ($action == null) {
            return $this->_navigationElements;
        }
        
        // Check for the existence of the navigation element
        if (!isset($this->_navigationElements[$action])) {
            /**
             * @see Zend_Controller_Action_Exception
             */
            require_once 'Zend/Controller/Action/Exception.php';

            throw new Zend_Controller_Action_Exception('An element for action "' . $action . '" is not set.');
        }
        
        // Return the element name for the provided action
        return $this->_navigationElements[$action];
    }
    
    /**
     * Set the form name route part
     *
     * @param string $name
     * @return Zend_Controller_Action_Helper_MultiPageForm
     */
    public function setFormNameRoutePart($part)
    {
        $this->_formNameRoutePart = $part;
        
        return $this;
    }
    
    /**
     * Get the form name route part
     *
     * @return string
     */
    public function getFormNameRoutePart()
    {
        return $this->_formNameRoutePart;
    }
    
    /**
     * Determine if a form has been validated
     *
     * @param string $current
     * @return string
     */
    public function isValidForm($form)
    {
        // FIXME: Doesn't seem to work as expected
        // Loop through the form->action mapping
        foreach ($this->_subFormActions as $formName => $action) {
            $this->_lastValidForm = $formName;

            // If this loop hasn't found an invalid action yet, and the currentAction and action match
            // we can assume this is the currently active form.
            if ($form == $formName) {
                break;
            }

            // If the provided form isn't complete yet, we're too far.
            // This means that the provided action is invalid. 
            if (!$this->isCompleteForm($formName)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Determine if a form has been submitted and successfully validated
     *
     * @param string $action
     * @return mixed
     */
    public function isCompleteForm($formName)
    {
        // Retrieve the validation state from the session
        if (isset($this->_session->valid[$formName])) {
            return $this->_session->valid[$formName];
        }

        // If it's not in the session, it's not completed
        return false;
    }

    /**
     * Set the action used for processing the complete form
     *
     * @param string $action
     * @return Zend_Controller_Action_Helper_MultiPageForm
     */
    public function setProcessAction($action)
    {
        $this->_processAction = $action;

        return $this;
    }

    /**
     * Get the processing action
     *
     * @return unknown
     */
    public function getProcessAction()
    {
        return $this->_processAction;
    }

    /**
     * Set a custom cancel action
     *
     * @param string $action
     * @return Zend_Controller_Action_Helper_MultiPageForm
     */
    public function setCancelAction($action)
    {
        $this->_cancelAction = $action;

        return $this;
    }

    /**
     * Get the custom cancel action
     *
     * @return string
     */
    public function getCancelAction()
    {
        return $this->_cancelAction;
    }
    
    /**
     * Set all forms to submit to one action.
     *
     * @param string $action
     * @return Zend_Controller_Action_Helper_MultiPageForm
     */
    public function setSingleAction($action = 'index')
    {
        $subFormActions = array();
        
        foreach ($this->_subFormActions as $formAction) {
        	$subFormActions[$formAction] = $action;
        }
        
        $this->_processAction  = $action;
        $this->_subFormActions = $subFormActions;
        
        return $this;
    }

    /**
     * Set sequence of actions.
     * If a numeric array is passed, the form name is mapped to an action with the same name.
     * If an associative array is passed, the key will be used for the formname and the value for action name.
     *
     * @param array $actions
     * @return Zend_Controller_Action_Helper_MultiPageForm
     */
    public function setFormActionMapping(array $formActionMapping)
    {
        // Create two brand new arrays
        $subFormActions = array();
        $subFormOrder   = array();
        
        // Loop through the mapping.
        foreach ($formActionMapping as $key => $value) {
            // If the key is numeric, we will map the formname to an action with the same name
            // If it's associative, the key is the form name and the value is the corresponding action
        	if (is_numeric($key)) {
        	    $subFormActions[$value] = $value;
        	    $subFormOrder[] = $value;
        	} else {
        	    $subFormActions[$key] = $value;
        	    $subFormOrder[] = $key;
        	}
        }
        
        $this->_subFormActions = $subFormActions;
        $this->_subFormOrder   = $subFormOrder;

        // Reset the session if this is the first time the forms/actions are mapped
        if (is_null($this->_session->valid) ||
            !array_key_exists($subFormOrder[0], $this->_session->valid)) {
            $this->clear();
        }

        // Chaining
        return $this;
    }

    /**
     * Set values for an action
     *
     * @param Zend_Form $form
     * @param boolean $valid
     * @return Zend_Controller_Action_Helper_MultiPageForm
     */
    public function setValues(Zend_Form $form, $valid = false)
    {
        $formName = $form->getName();

        // Check if the provided form is part of the subform chain
        if (!$this->isSubForm($formName)) {
            /**
             * @see Zend_Controller_Action_Exception
             */
            require_once 'Zend/Controller/Action/Exception.php';

            throw new Zend_Controller_Action_Exception('"' . $formName . '" is not a valid subform');
        }

        // Get the form values and their element names
        $formValues = $form->getValues();
        $formKeys = array_keys($formValues);

        // Loop through the element names to see if there are action elements (default prefixed with _)
        foreach ($formKeys as $key) {
            // If an element with the action prefix is found, remove it from the array.
            if (strpos($key, $this->_navigationElements[self::ACTION_PREFIX]) === 0) {
                // We don't want to store actions in the session.
                unset($formValues[$key]);
            }
        }

        // Elaborate setup to write some values to the session arrays
        // This is needed to work around a bug/feature in PHP 5.2.0 iirc
        $validForms = $this->_session->valid;
        $sessionFormValues = $this->_session->value;

        $validForms[$formName] = (bool) $valid;
        $sessionFormValues[$formName] = $formValues[$formName];
        
        // Write the validation state and form values to the session
        $this->_session->valid = $validForms;
        $this->_session->value = $sessionFormValues;

        // Chaining
        return $this;
    }

    /**
     * Retrieve form values from the session
     *
     * @param string $action
     * @return mixed
     */
    public function getSessionValues($formName = null)
    {
        // If no form name is specified, just return all form data from the session
        if ($formName === null) {
            return $this->_session->value;
        }

        // A formname is no good if it's not in the session
        if (!isset($this->_session->value[$formName])) {
            /**
             * @see Zend_Controller_Action_Exception
             */
            require_once 'Zend/Controller/Action/Exception.php';

            throw new Zend_Controller_Action_Exception('No entry for "' . $formName . '" found in the session.');
        }

        // Return the values for the given form
        return $this->_session->value[$formName];
    }

    /**
     * Retrieve current lsat valid action
     *
     * @return string
     */
    public function getLastValidAction()
    {
        return $this->_subFormActions[$this->_lastValidForm];
    }

    /**
     * Set a form instance
     *
     * @param Zend_Form $form
     * @return Zend_Controller_Action_Helper_MultiPageForm
     */
    public function setForm(Zend_Form $form)
    {
        $this->_form = $form;

        $subForms = $form->getSubForms();

        // Loop through all the subforms and check if they all have a name
        foreach ($subForms as $subForm) {
            $formName = $subForm->getName();

            if (empty($formName)) {
                /**
                 * @see Zend_Controller_Action_Exception
                 */
                require_once 'Zend/Controller/Action/Exception.php';

                throw new Zend_Controller_Action_Exception('A subform needs to have a name.');
            }
        }

        // Chaining
        return $this;
    }

    /**
     * Get the form instance
     *
     * @return Zend_Form
     */
    public function getForm()
    {
        if (!$this->_form) {
            /**
             * @see Zend_Controller_Action_Exception
             */
            require_once 'Zend/Controller/Action/Exception.php';

            throw new Zend_Controller_Action_Exception('No form instance set.');
        }

        return $this->_form;
    }

    /**
     * Get the current subform
     * 
     * @return Zend_Form
     */
    public function getCurrentSubForm()
    {
        // If no current subform is set, try to retrieve it
        if (!$this->_currentSubForm) {
            // Get the active form's name
            $formName = $this->getActiveFormName();
            
            // Fetch the form instance and assign it as being the currently active subform
            $this->_currentSubForm = $this->getSubForm($formName);
        }

        // Return the current subform
        return $this->_currentSubForm;
    }

    /**
     * Get a subform by name.
     *
     * @param string $formName
     * @return Zend_Form
     */
    public function getSubForm($formName)
    {
        // Get the subform by name
        $subForm = $this->_form->getSubForm($formName);

        if (empty($subForm)) {
            /**
             * @see Zend_Controller_Action_Exception
             */
            require_once 'Zend/Controller/Action/Exception.php';

            throw new Zend_Controller_Action_Exception('No form by the name of "' . $formName . '" was registered.');
        }

        // Populate the subform with session data
        $subForm->populate($this->getSessionValues($formName));

        // Return the subform
        return $subForm;
    }

    /**
     * Check if the entire form is valid
     *
     * @return boolean
     */
    public function isValid()
    {
        // Get the form data from the session
        $formData = $this->getFormSessionData();

        // Validate the form using the session data
        return $this->_form->isValid($formData);
    }
    
    /**
     * Checks if all forms are completed and validated
     *
     * @return bool
     */
    public function isSubmitted()
    {
        return $this->_session->action == $this->_navigationElements[self::ACTION_SUBMIT];
    }

    /**
     * Get all data from the subforms from the session.
     * 
     * @return array
     */
    public function getFormSessionData()
    {
        $formData = array();

        foreach ($this->_subFormActions as $formName => $action) {
            $formData[$formName] = $this->getSessionValues($formName);
        }

        return $formData;
    }

    /**
     * Get the form data. If it's empty and a submitted form is available,
     * populate it first from POST.
     *
     * @return array
     */
    public function getPostData()
    {
        if (empty($this->_tempFormData) && $this->getRequest()->isPost()) {
            $currentSubForm = $this->getCurrentSubForm();
            $postData = $this->getRequest()->getPost();

            $elements = $currentSubForm->getElements();
            
            $this->_tempFormData = array_intersect_key($postData[$currentSubForm->getName()], $elements);
        }

        return $this->_tempFormData;
    }

    /**
     * Use the redirector helper to navigate the controller
     *
     * @param string $action
     * @param string $controller
     * @param string $module
     */
    public function redirect($action, $controller = null, $module = null)
    {
        $redirector = $this->getActionController()->getHelper('Redirector');

        return $redirector->gotoAndExit($action,
                                        $controller,
                                        $module);
    }

    /**
     * Get the action used to submit the form
     *
     * @return string|false
     */
    public function getSubmitAction()
    {
        $formData = $this->getPostData();

        if (!empty($formData)) {
            $formDataKeys = array_keys($formData);

            foreach ($formDataKeys as $key) {
                if (strpos($key, $this->_navigationElements[self::ACTION_PREFIX]) === 0) {
                    return $key;
                }
            }
        }

        return false;
    }

    /**
     * Handle the form
     *
     * @return boolean
     */
    public function handle()
    {
        $action = $this->getSubmitAction();

        if ($action === false) {
            return false;
        }

        $currentSubForm = $this->getCurrentSubForm();
        $valid = $currentSubForm->isValid($this->getPostData());

        $this->setValues($currentSubForm, $valid);

        $this->_session->action = $action;
        
        switch ($action) {
            case $this->_navigationElements[self::ACTION_PREVIOUS]:
                $position = array_search($currentSubForm->getName(), $this->_subFormOrder);

                if ($position <= 0) {
                    $subForm = $this->_subFormOrder[0];
                } else {
                    $subForm = $this->_subFormOrder[$position - 1];
                }
                
                $this->setActiveFormName($subForm);
                
                $target = $this->_subFormActions[$subForm];
                break;

            case $this->_navigationElements[self::ACTION_NEXT]:
                if (!$valid) {
                    return false;
                }

                $position = array_search($currentSubForm->getName(), $this->_subFormOrder);
                
                $subFormCount = count($this->_subFormOrder);
                if ($position == $subFormCount - 1) {
                    $subForm = $this->_subFormOrder[$subFormCount - 1];
                } else {
                    $subForm = $this->_subFormOrder[$position + 1];
                }
                
                $this->setActiveFormName($subForm);
                $target = $this->_subFormActions[$subForm];
                break;

            case $this->_navigationElements[self::ACTION_SUBMIT]:
                if (!$valid) {
                    return false;
                }

                $target = $this->getProcessAction();
                break;

            case $this->_navigationElements[self::ACTION_CANCEL]:
                if (empty($this->_cancelAction)) {
                    $this->clear();
                    $subForm = $this->_subFormOrder[0];
                    $target = $this->_subFormActions[$subForm];
                    
                    $this->setActiveFormName($subForm);
                } else {
                    $target = $this->getCancelAction();
                }
                break;

            default:
                if (!in_array($action, $this->_subFormOrder)) {
                    $this->redirect($this->getLastValidAction());
                }
                break;
        }
        
        return $this->redirect($target);
    }

    /**
     * Check if the action is an action for this form.
     *
     * @param string $action
     * @return boolean
     */
    public function isSubFormAction($action)
    {
        return isset($this->_subFormActions[$action]);
    }
    
    /**
     * Check if the action is an action for this form.
     *
     * @param string $action
     * @return boolean
     */
    public function isSubForm($formName)
    {
        return array_key_exists($formName, $this->_subFormActions);
    }
    
    /**
     * Get the active form name
     *
     * @return string
     */
    public function getActiveFormName()
    {
        if (!$this->_activeFormName) {
            $formName = $this->_router->getParam($this->_formRoutePart);
            
            if ($formName != null) {
                $this->_session->active = $formName;
            }
            
            $this->_activeFormName = $this->_session->active;
        }
        
        return $this->_activeFormName;
    }
    
    /**
     * Set the active form name
     *
     * @param string $formName
     * @return Zend_Controller_Action_Helper_MultiPageForm
     */
    public function setActiveFormName($formName)
    {
        $this->_session->active = $formName;
        
        return $this;
    }

    /**
     * Reset all session data
     * 
     * @return Zend_Controller_Action_Helper_MultiPageForm
     */
    public function clear()
    {
        // Create two brand new arrays
        $valid = array();
        $value = array();

        // Loop through the formnames, so we can reset their session data
        foreach ($this->_subFormOrder as $formName) {
            $valid[$formName] = false;
            $value[$formName] = array();
        }

        // Write all default variables to the session
        $this->_session->valid  = $valid;
        $this->_session->value  = $value;
        $this->_session->active = $this->_subFormOrder[0];
        $this->_session->action = '';
        
        // Chaining
        return $this;
    }
}