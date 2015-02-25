<?php
/**
  * You are allowed to use this API in your web application.
 *
 * Copyright (C) 2013 by customweb GmbH
 *
 * This program is licenced under the customweb software licence. With the
 * purchase or the installation of the software in your application you
 * accept the licence agreement. The allowed usage is outlined in the
 * customweb software licence which can be found under
 * http://www.customweb.ch/en/software-license-agreement
 *
 * Any modification or distribution is strictly forbidden. The license
 * grants you the installation in one application. For multiuse you will need
 * to purchase further licences at http://www.customweb.com/shop.
 *
 * See the customweb software licence agreement for more details.
 *
 */

//require_once 'Customweb/Payment/Authorization/DefaultTransaction.php';
//require_once 'Customweb/Util/Url.php';
//require_once 'Customweb/I18n/Translation.php';
//require_once 'Customweb/Util/Rand.php';


class Customweb_Paybox_Authorization_Transaction extends Customweb_Payment_Authorization_DefaultTransaction
{
	private $authorizationType;
	private $handler;
	private $brandName = null;
	private $sellerprotection;
	private $_3DParameters = array();
	private $numParameters = array();
	private $ID3D;	
	private $dateVal;
	private $aliasToken;
	private $processAuthorizationUrl = null;
	private $processAliasAuthorizationUrl = null;
	
	
	public function __construct(Customweb_Payment_Authorization_ITransactionContext $transactionContext) {
		parent::__construct($transactionContext);
	
		$this->key = Customweb_Util_Rand::getRandomString(32, '');
		$this->setCheck3DSecure(0);
		$this->authorizationType = "";
	}
	
	public function getCaptureSetting() {
		return $this->getTransactionContext()->getOrderContext()->getPaymentMethod()->getPaymentMethodConfigurationValue('capturing');
	}
	
	public function createRoutingUrls(Customweb_DependencyInjection_IContainer $container) {
		$endpointAdapter = $container->getBean('Customweb_Payment_Endpoint_IAdapter');
		$this->processAuthorizationUrl = $endpointAdapter->getUrl('process', 'index', array('cw_transaction_id' => $this->getExternalTransactionId()));
		$this->processAliasAuthorizationUrl = $endpointAdapter->getUrl('process', 'alias', array('cw_transaction_id' => $this->getExternalTransactionId()));
	}
	
	public function getProcessAuthorizationUrl() {
		return $this->processAuthorizationUrl;
	}
	
	
	public function setCheck3DSecure($flag){
		$this->check3DSecure = $flag;
	}
	
	public function is3DSecure(){
		return $this->check3DSecure;
		
	}
	
	public function getKey() {
		return $this->key;
	}
	
	public function resetKey() {
		$this->key = Customweb_Util_Rand::getRandomString(32, '');
		return $this;
	}
	
	public function getSuccessUrl() {
		return Customweb_Util_Url::appendParameters(
				$this->getTransactionContext()->getSuccessUrl(),
				$this->getTransactionContext()->getCustomParameters()
		);
	}
	
	public function setAuthorizationType($authorizationType) {
		$this->authorizationType = $authorizationType;		
	}
	
	public function getAuthorizationType() {
		return $this->authorizationType;
	}
	
	public function getFailedUrl() {
		return Customweb_Util_Url::appendParameters(
				$this->getTransactionContext()->getFailedUrl(),
				$this->getTransactionContext()->getCustomParameters()
		);
	}
	
	public function setSellerProtection($sellerprotection) {
		$this->sellerprotection = $sellerprotection;
	}
	
	public function getSellerProtection(){
		return $this->sellerprotection;
	}
	
	protected function getTransactionSpecificLabels() {
		$labels = array();
		if ($this->getSellerProtection() !== null) {
			// TODO: Add a description here for what the seller protection stands for.
			$labels['seller_protection'] = array(
				'label' => Customweb_I18n_Translation::__('Seller Protection'),
				'value' => $this->getSellerProtection(),
			);
		}
		
		return $labels;
	}
	
	public function encrypt($string) {
		return base64_encode($this->getCipher()->encrypt($string));
	}
	
	public function decrypt($string) {
		return $this->getCipher()->decrypt(base64_decode($string));
	}
	
	/**
	 * @return Crypt_AES
	 */
	private function getCipher() {
		//require_once 'Crypt/AES.php';
		$cipher = new Crypt_AES(CRYPT_AES_MODE_CTR);
		$cipher->setKey($this->getKey());
		return $cipher;
	}

	public function getBrandName() {
		if (!empty($this->brandName)) {
			return $this->brandName;
		}
		$params = $this->getAuthorizationParameters();
		if (isset($params['cardtype'])) {
			return $params['cardtype'];
		}
		else {
			return null;
		}
	}
	
	public function setBrandName($brandName) {
		$this->brandName = $brandName;
		return $this;
	}

	public function getNumQuestion(){
		return $this->numQuestion;
	}

	public function setNumQuestion($numQuestion){
		$this->numQuestion = $numQuestion;
		return $this;
	}

	public function getStatus(){
		return $this->status;
	}

	public function setStatus($status){
		$this->status = $status;
		return $this;
	}
	
	public function get3DParameters() {
		return $this->_3DParameters;
	}
	
	public function save3DParameters(array $parameters) {
		$this->_3DParameters = array(
			'3DSTATUS'				=> $parameters['3DSTATUS'],
			'3DSIGNVAL' 			=> $parameters['3DSIGNVAL'],
			'3DENROLLED'			=> $parameters['3DENROLLED'],
			'3DERROR'				=> $parameters['3DERROR'],
			'3DECI'					=> $parameters['3DECI'],
			'3DXID'					=> $parameters['3DXID'],
			'3DCAVV'				=> $parameters['3DCAVV']
		);
	}

	public function getID3D(){
		return $this->ID3D;
	}

	public function setID3D($ID3D){
		$this->ID3D = $ID3D;
	}

	public function getNumParameters(){
		return $this->numParameters;
	}

	public function setNumParameters($results){
		$numParameters = array(
			"NUMTRANS" 		=> $results['NUMTRANS'],
			"NUMAPPEL" 		=> $results['NUMAPPEL'],
			"NUMQUESTION"	=> $results['NUMQUESTION']
		);
		$this->numParameters = $numParameters;
		return $this;
	}
	
	public function setNumParametersPaymentPage($numTrans, $numAppel) {
		$numParameters = array(
			"NUMTRANS" 		=> $numTrans,
			"NUMAPPEL" 		=> $numAppel,
		);
		$this->numParameters = $numParameters;
	}
	
	public function isRefundClosable() {
		return false;
	}
	
	public function isCaptureClosable() {
		return false;
	}

	public function getDateVal(){
		return $this->dateVal;
	}

	public function setDateVal($dateVal){
		$this->dateVal = $dateVal;
		return $this;
	}

	public function getAliasToken(){
		return $this->aliasToken;
	}

	public function setAliasToken($aliasToken){
		$this->aliasToken = $aliasToken;
		return $this;
	}
	
	
	
}