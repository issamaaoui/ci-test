<?php

/**
 * Odura
 *
 * @category    Dvl
 * @package     Dvl_Auth
 * @copyright   
 * @license     
 */

/**
 * auth authorize controller
 *
 * @category Dvl
 * @package Dvl_Auth
 * @author Odura
 */
class Dvl_Auth_AuthorizeController extends Mage_Core_Controller_Front_Action
{
    protected $errorCode = array(
        450 => "Access denied",
        451 => "Access denied for this login",
        452 => "Consumer Key missing or invalid",
        453 => "First Name or Last Name missing",
        454 => "Login missing",
        455 => "Password missing",
    );
    
    /**
     * Consumer key
     *
     * @var string
     */
    protected $_consumerKey = "";

    /**
     * Access token
     *
     * @var string
     */
    protected $_accessToken = "";

    /**
     * Secret token
     *
     * @var string
     */
    protected $_secretToken = "";

    /**
     * Login
     *
     * @var string
     */
    protected $_login = "";

    /**
     * Password
     *
     * @var string
     */
    protected $_password = "";

    /**
     * First name
     *
     * @return void
     */
    
    protected $_firstName = "";
    
    /**
     * Last name
     *
     * @return void
     */
    
    protected $_lastName = "";

    /**
     * SKU Product ref.
     *
     * @return void
     */
    
    protected $_sku = "";    

    /**
     * Serial Number
     *
     * @return void
     */
    
    protected $_serialNumber = "";    
    
    /**
     * Anonymous
     *
     * @return void
     */
    
    protected $_anonymous = false;
        
    /**
     * Customer ID
     *
     * @return void
     */

    protected $_customerId = "";

    /**
     * Consumer ID
     *
     * @return void
     */
    
    protected $_consumerId = "";
        
    /**
     * Index action.
     *
     * @return void
     */    
    
    public function indexAction()
    {
        $this->_login = $this->getRequest()->getParam('login');
        $this->_password = $this->getRequest()->getParam('password');
        $this->_consumerKey = $this->getRequest()->getParam('key');
        $this->_firstName = $this->getRequest()->getParam('fname');
        $this->_lastName = $this->getRequest()->getParam('lname');
        $this->_serialNumber = $this->getRequest()->getParam('snumber');
        $this->_sku = $this->getRequest()->getParam('sku');
        $anonymous =$this->getRequest()->getParam('anonymous');
        if(!empty($anonymous)){
            if(($anonymous == 1) || ($anonymous == "true")){
                $this->_anonymous = true;
            }
        }

        $customer = new Mage_Customer_Model_Customer();
        $exist = $customer->loadByEmail($this->_login);
        $this->_customerId = $customer->getId();
        
        $this->_consumerId = $this->getConsumerId();
        if (empty($this->_consumerId)) {
            $result = $this->getError(452);
        } elseif (empty($this->_customerId) || $this->_anonymous) { /* New customer */
            $result = $this->createCustomer();
        } elseif ($customer->validatePassword($this->_password)) { /* Existing customer */

            $token = new Mage_Oauth_Model_Token();
            $tokenCollection = $token->getCollection()
                ->addFilterByConsumerId($this->_consumerId)
                ->addFilterByCustomerId($this->_customerId)
                ->addFilterByRevoked(false)
                ->addFilterByType(Mage_Oauth_Model_Token::TYPE_ACCESS)
                ->getData();
            
            if (empty($tokenCollection)) {
                //$result = $this->getError(451);
                $result = $this->createToken();
            } else {
                $tokenCustomer = current($tokenCollection);
                $result = array(
                    "token" => array(
                        "access" => $tokenCustomer['token'],
                        "secret" => $tokenCustomer['secret']
                    )
                );
            }

        } else {
            $result = $this->getError(451);
        }
        
        $this->render($result);
    }

    public function createToken(){
        // @TODO : delete old token
        $helper = Mage::helper('oauth');
        $oAuthtoken = $helper->generateToken();
        $oAuthtokenSecret = $helper->generateTokenSecret();
        
        $resource = Mage::getSingleton('core/resource');
        $writeConnection = $resource->getConnection('core_write');
        $query = "INSERT INTO " . $resource->getTableName('oauth/token') . "
            (customer_id, consumer_id, type, token, secret, revoked, authorized)
                VALUES
            (" . $this->_customerId . ", " . $this->_consumerId . ", '" . Mage_Oauth_Model_Token::TYPE_ACCESS . "', '".$oAuthtoken."', '".$oAuthtokenSecret."', 0, 1);";
        
        try{
            $writeConnection->query($query);
            $result = array(
                "token" => array(
                "access" => $oAuthtoken,
                "secret" => $oAuthtokenSecret
                )
            );
        }
        catch (Exception $e) {
            $result = $this->getError($e->getMessage(), $e->getCode());
        }
        return $result;
    }
    
    public function createCustomer()
    {
        $websiteId = Mage::app()->getStore()->getWebsiteId();
        $store = Mage::app()->getStore();
        if($this->_anonymous){
            // @TODO Back var to intagrate
            $this->_login = "anonymous.".time()."@devialet.com";
            $this->_password = md5("anonymous.".time());
            $this->_firstName = "Anonymous";
            $this->_lastName = "Anonymous"; 
        }

        if (empty($this->_login)) {
            $result = $this->getError(454);
            
        } elseif (empty($this->_password)) {
            $result = $this->getError(455);
            
        } elseif (empty($this->_firstName) || empty($this->_lastName)) {
            $result = $this->getError(453);
            
        } else {
            $customer = Mage::getModel("customer/customer");
            $customer->setWebsiteId($websiteId)
                ->setStore($store)
                ->setFirstname($this->_firstName)
                ->setLastname($this->_lastName)
                ->setEmail($this->_login)
                ->setPassword($this->_password);
            try{
                $customer->save();
            }
            catch (Exception $e) {
                $this->errorCode[$e->getCode()] = $e->getMessage();
                $result = $this->getError($e->getCode());
            }
            $this->_customerId = $customer->getId();
            $result = $this->createToken();
        }
        return $result;
    }

    public function getConsumerId()
    {
        $resource = Mage::getSingleton('core/resource')->getConnection('core_read');
        $consumerSql = "SELECT * FROM  oauth_consumer WHERE  `key` = '" . $this->_consumerKey . "'";
        $consumerCollection = $resource->fetchRow($consumerSql);
        return $consumerCollection["entity_id"];
    }

    public function getError($code = 450)
    {
        $message = $this->errorCode[$code];
        $result = array(
            "messages" => array(
                'error' => array(
                    "code" => $code,
                    "message" => $message
                )
            )
        );
        return $result;
    }

    /**
     * Render.
     *
     * @return void
     */
    public function render($result = null)
    {
        $json = json_encode($result);
        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody($json);
    }
}