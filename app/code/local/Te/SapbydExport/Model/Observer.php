<?php
Class Te_SapbydExport_Model_Observer
{

	/**
	*
	* Export orders
	*
	**/
	public function exportOrders($observer=null)
	{

		if($observer && $observer->getOrders())
		{
			$orders = $observer->getOrders();
		}else{
		//retrieve orders to export
			$orders = mage::getModel("sales/order")->getCollection()
			->addFieldToFilter('state', Mage_Sales_Model_Order::STATE_PROCESSING)
			->addFieldToFilter('sapbyd_order_id', array("null"=>true));
		}

		foreach($orders as $order)
		{

			mage::helper("te_sapbydexport")->exportOrder($order);
		}
	}


	/**
	*
	* Export Customer Only
	*
	**/
	public function exportCustomer($observer)
	{
		$customers = $observer->getCustomers();
		

		$orders = mage::getModel("sales/order")->getCollection()
			->addFieldToFilter('state', Mage_Sales_Model_Order::STATE_PROCESSING)
			->addFieldToFilter('sapbyd_order_id', array("null"=>'123'))
			->addFieldToFilter("customer_id", array("in" => $customers));

		foreach($orders as $order)
		{

			mage::helper("te_sapbydexport")->exportOrder($order);
		}

	}


	/**
	*
	* Send report about export orders error
	*
	**/
	public function sendReport()
    {
		


		$templateId = mage::helper("te_sapbydexport")->getEmailTemplate();
		
		$addToConf = mage::helper("te_sapbydexport")->getEmailRecipients();


		$mailer = Mage::getModel('core/email_template_mailer');
		$emailInfo = Mage::getModel('core/email_info');
		$addTo = explode(";",$addToConf);
		if(count($addTo)>0){
			$emailInfo->addTo($addTo[0]);
		}
		
		for($i=1;$i<count($addTo);$i++){
			  $emailInfo->addBcc($addTo[$i]);
		}

		$mailer->setStoreId(1);
		$mailer->addEmailInfo($emailInfo);
		$mailer->setSender(Mage::getStoreConfig(Mage_Sales_Model_Order::XML_PATH_EMAIL_IDENTITY, 1));		 
        $mailer->setTemplateId($templateId);
        $mailer->setTemplateParams(array(
        		
            )
        );


		  $mailer->send();
        return $this;
    } 
}