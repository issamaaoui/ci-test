<?php 

//require_once 'Customweb/Paybox/AbstractParameterBuilder.php';


/**
 *
 * @author Thomas Brenner
 *
 */
class Customweb_Paybox_Authorization_AbstractParameterBuilder extends Customweb_Paybox_AbstractParameterBuilder {
		
	public function __construct(Customweb_Paybox_Authorization_Transaction $transaction, Customweb_Paybox_Configuration $configuration) {
		parent::__construct($transaction, $configuration);
	}
		

}