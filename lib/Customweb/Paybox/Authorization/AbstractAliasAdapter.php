<?php 

//require_once 'Customweb/Http/Response.php';
//require_once 'Customweb/Http/Request.php';
//require_once 'Customweb/Http/Url.php';


/**
 *
 * @author Thomas Brenner
 * @Bean
 *
 */
class Customweb_Paybox_Authorization_AbstractAliasAdapter {
	
	private $transaction;
	private $configuration;
	
	public function __construct(Customweb_Paybox_Authorization_Transaction $transaction, Customweb_Paybox_Configuration $configuration) {
		$this->transaction = $transaction;
		$this->configuration = $configuration;
	}
	
	protected function getTransaction(){
		return $this->transaction;
	}
	
	protected function getConfiguration(){
		return $this->configuration;
	}
	
	protected function getOrderContext() {
		return $this->getTransaction()->getTransactionContext()->getOrderContext();	
	}

	public function processXmlRequest($url, $xmlBody) {
		$requestUrl = new Customweb_Http_Url($url);
		$request = new Customweb_Http_Request($requestUrl);
		$request->setBody($xmlBody);
		$request->setMethod("POST");
		$request->appendCustomHeaders(array(
			'Content-Type' => "application/xml",
		));
		$handler = new Customweb_Http_Response();
		$handler = $request->send();
		return $handler->getBody();
	}
		
}