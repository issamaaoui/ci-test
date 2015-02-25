<?php 

//require_once 'Customweb/Util/String.php';
//require_once 'Customweb/Util/Url.php';
//require_once 'Customweb/Paybox/Util.php';
//require_once 'Customweb/Payment/Authorization/IInvoiceItem.php';
//require_once 'Customweb/Payment/Util.php';
//require_once 'Customweb/Util/Currency.php';
//require_once 'Customweb/I18n/Translation.php';
//require_once 'Customweb/Payment/Authorization/ITransactionContext.php';


/**
 *
 * @author Thomas Brenner
 * 
 *
 */
class Customweb_Paybox_AbstractParameterBuilder {
	
		private $configuration;
		private $transaction;
	
		public function __construct(Customweb_Paybox_Authorization_Transaction $transaction, Customweb_Paybox_Configuration $configuration) {
			$this->configuration = $configuration;
			$this->transaction = $transaction;
		}

		/**
		 * @return Customweb_Paybox_Configuration
		 */
		protected function getConfiguration(){
			return $this->configuration;
		}
		
		
		protected function getTransaction() {
			return $this->transaction;
		}
		
		protected function getOrderContext() {
			return $this->transaction->getTransactionContext()->getOrderContext();
		}
		
		protected function buildBaseParameters() {
			return array(
				'VERSION'			=> '00104',
				'SITE'				=> $this->getConfiguration()->getSiteNumber($this->getTransaction()),
				'RANG'				=> $this->getConfiguration()->getRangNumber($this->getTransaction()),
				'CLE'				=> $this->getConfiguration()->getPassword($this->getTransaction()),
				'ACTIVITE'			=> '024'
			);
		}
		
		protected function buildTransactionParameters() {
			$amount = 100*$this->getTransaction()->getTransactionContext()->getOrderContext()->getOrderAmountInDecimals();
			return array(
				'DATEQ'				=> date('Ymdhis'),
				'NUMQUESTION'		=> rand(0,1000000),
				'MONTANT'			=> $amount,
				'DEVISE'			=> Customweb_Util_Currency::getNumericCode($this->getTransaction()->getTransactionContext()->getOrderContext()->getCurrencyCode()),
				'REFERENCE'			=> rand(0,1000000)
			);		
		}
		
		protected function build3DParameters($originalParameters) {
			$amount = 100*$this->getTransaction()->getTransactionContext()->getOrderContext()->getOrderAmountInDecimals();
			return array(
				'Amount'		=> $amount,
				'CCExpDate'		=> $this->buildExpDate($originalParameters),
				'CCNumber'		=> $originalParameters['PORTEUR'],
				'Currency'		=> Customweb_Util_Currency::getNumericCode($this->getTransaction()->getTransactionContext()->getOrderContext()->getCurrencyCode()),
				'CVVCode'		=> $originalParameters['CVV'],
				'IdMerchant'	=> $this->getConfiguration()->getPayboxIdentifier($this->getTransaction()),
				'IdSession'		=> Customweb_Paybox_Util::convert($this->getTransactionAppliedSchema($this->getTransaction()))
			);
		}
		
		public function buildAliasParameters($parameters) {
			$numParameters = $this->getTransaction()->getNumParameters();
			
			if(isset($numParameters['NUMQUESTION'])) {
				$numQues = $numParameters['NUMQUESTION'];
			} else {
				$numQues = rand(0,1000000);
			}
			$aliasParameters = array_merge(
					$this->buildBaseParameters(),
					$this->buildPaymentParametersDecrypted($parameters),
					$this->buildTransactionParameters()
			);
			
			$aliasParameters['TYPE'] = "00056";
			$aliasParameters['REFABONNE'] = Customweb_Paybox_Util::convert($this->getTransactionAppliedSchema($this->getTransaction()));
			$aliasParameters['NUMQUESTION'] = $numQues;
			return $aliasParameters;
		}
		
		
		public function buildAliasTransactionParameters(Customweb_Paybox_Authorization_Transaction $oldTransaction, $cvv) {
			$parameters = array_merge(
					$this->buildBaseParameters(),
					$this->buildTransactionParameters()
			);
			$parameters['REFABONNE'] = Customweb_Paybox_Util::convert($this->getTransactionAppliedSchema($oldTransaction));
			$parameters['PORTEUR'] = $oldTransaction->getAliasToken();
			$parameters['DATEVAL'] = $oldTransaction->getDateVal();
			$parameters['CVV'] = $cvv;
			$parameters['TYPE'] = $this->getAliasPaymentType();
			//$parameters['TYPE'] = "00053";
			return $parameters;
		}

//--------------------------------------------------------------------------------- Backend Parameter Functions ---------------------------------------------------------------------------------
		
		public function buildCancellationParameters() {
			return array_merge(
					$this->buildBaseParameters(),
					$this->buildTransactionParameters(),
					$this->getTransaction()->getNumParameters(),
					array(
						"TYPE" => "00005"
					)
			);
		}
		
		public function buildCaptureParameters($amount) {
			$parameters = array_merge(
					$this->buildBaseParameters(),
					$this->buildTransactionParameters(),
					$this->getTransaction()->getNumParameters(),
					array(
						"TYPE" => "00002"
					)
			);
			$parameters['MONTANT'] = $amount;
			return $parameters;
		}
		
		public function buildRefundParameters($amount) {
			$parameters = array_merge(
					$this->buildBaseParameters(),
					$this->buildTransactionParameters(),
					$this->getTransaction()->getNumParameters(),
					array(
						"TYPE" => "00014"
					)
			);
			$parameters['MONTANT'] = $amount;
			return $parameters;
		}

//--------------------------------------------------------------------------------- Help Function ---------------------------------------------------------------------------------
		
		public function buildPaymentParametersDecrypted(array $parameters) {
			$paymentInformation = $this->decryptData($parameters['data']);
			return $paymentInformation;
		}
		
		private function buildExpDate(array $parameters) {
			if(isset($parameters['DATEVAL'])) {
				return $parameters['DATEVAL'];
			} else {
				return $parameters['expm'] . $parameters['expy'];
			}
		
		}		

		protected function buildNotificationParameters(array $parameters) {
			$url = Customweb_Paybox_Util::convert(Customweb_Util_Url::appendParameters($this->getTransaction()->getProcessAuthorizationUrl(), array( 'data' => $this->encryptData($parameters))));
			return array(
				'URLRetour'							=> Customweb_Util_String::cutStartOff($url, 255),
			);
		}
		
		public function encryptData(array $parameters) {
			$data =
			"PORTEUR=" . $parameters['PORTEUR']
			. '&DATEVAL=' . $this->buildExpDate($parameters)
			. '&CVV=' . $parameters['CVV'];
			return $this->getTransaction()->encrypt($data);
		}
		
		public function decryptData($data) {
			$rawString = $this->getTransaction()->decrypt($data);
			$rawArray = explode('&', $rawString);
			$dataArray = array();
			foreach($rawArray as $value) {
				$tempArray = explode('=', $value);
				$dataArray[$tempArray[0]] = $tempArray[1];
			}
			return $dataArray;
		}
		
		public function getPaymentType() {
			$parameters = array();
			if ($this->getTransaction()->getTransactionContext()->getCapturingMode() === null) {
				$capturingMode = $this->getOrderContext()->getPaymentMethod()->getPaymentMethodConfigurationValue('capturing');
				if ($capturingMode == 'order') {
					$parameters['TYPE'] = "00001";
				}
				else if ($capturingMode == 'authorization') {
					$parameters['TYPE'] = "00001";
				}
				else {
					$parameters['TYPE'] = "00003";
				}
			}
			else {
				if ($this->getTransactionContext()->getCapturingMode() == Customweb_Payment_Authorization_ITransactionContext::CAPTURING_MODE_DEFERRED) {
					$parameters['TYPE'] = "00001";
				}
				else {
					$parameters['TYPE'] = "00003";
				}
			}
			return $parameters;
		}
		
		public function getAliasPaymentType() {
			if ($this->getTransaction()->getTransactionContext()->getCapturingMode() === null) {
				$capturingMode = $this->getOrderContext()->getPaymentMethod()->getPaymentMethodConfigurationValue('capturing');
				if ($capturingMode == 'order') {
					return "00051";
				}
				else if ($capturingMode == 'authorization') {
					return "00051";
				}
				else {
					return "00053";
				}
			}
			else {
				if ($this->getTransactionContext()->getCapturingMode() == Customweb_Payment_Authorization_ITransactionContext::CAPTURING_MODE_DEFERRED) {
					return  "00051";
				}
				else {
					return  "00053";
				}
			}
		}
		
		public function getPaymentAction() {
			if ($this->getTransaction()->getTransactionContext()->getCapturingMode() === null) {
				$capturingMode = $this->getOrderContext()->getPaymentMethod()->getPaymentMethodConfigurationValue('capturing');
				if ($capturingMode == 'order') {
					$paymentAction = "O";
				}
				else if ($capturingMode == 'authorization') {
					$paymentAction = "O";
				}
				else {
					$paymentAction = "N";
				}
			}
			else {
				if ($this->getTransactionContext()->getCapturingMode() == Customweb_Payment_Authorization_ITransactionContext::CAPTURING_MODE_DEFERRED) {
					$paymentAction = "O";
				}
				else {
					$paymentAction = "N";
				}
			}
			return $paymentAction;
		}
		
		/**
		 * @return string
		 */
		protected final function getTransactionAppliedSchema(Customweb_Paybox_Authorization_Transaction $transaction)
		{
			$schema = $this->getConfiguration()->getOrderIdSchema();
			$id = $transaction->getExternalTransactionId();
		
			return Customweb_Payment_Util::applyOrderSchema($schema, $id, 17);
		}
		
		public function getTotalAmountOfLineItems(Customweb_Payment_Authorization_ITransaction $transaction) {
			$totalAmount = 0;
			foreach ($transaction->getTransactionContext()->getOrderContext()->getInvoiceItems() as $item) {
		
				if ($item->getType() == Customweb_Payment_Authorization_IInvoiceItem::TYPE_DISCOUNT) {
					$totalAmount -= $item->getAmountIncludingTax();
				}
				else {
					$totalAmount += $item->getAmountIncludingTax();
				}
			}
			return number_format((float)$totalAmount, 2, '.', '');
		}
		
		public function saveAliasForDisplay(array $parameters) {
			$cardInfo = $this->buildPaymentParametersDecrypted($parameters);
			$cardNo = $cardInfo['PORTEUR'];
			if(strlen($cardNo) > 10) {
				$aliasForDisplay = substr($cardNo, 0, 8);
				for($i = 0; $i < (strlen($cardNo)-10); ++$i) {
					$aliasForDisplay .= "X";
				}
				$aliasForDisplay .= substr($cardNo, -2);
				$this->getTransaction()->setAliasForDisplay($aliasForDisplay);
			} else {
				throw new Exception(Customweb_I18n_Translation::__("Masked Card was not long enough"));
			}
		}
		
		private function debug($text) {
			echo "<script language=\"JavaScript\">
				<!--
				alert(\"" . $text . "\");
				//-->
				</script>
			";
		}
			
}