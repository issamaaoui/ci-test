<?php
/**
 *
 * @author te <renaud.fasimana@technology-everywhere.fr>
 * @since 5 févr. 2015
 *
 **/
class Te_Sapbyd_Model_Service_Soap_Client_QueryInvoiceIn
    extends Te_Sapbyd_Model_Service_Soap_Client_Abstract
{
    protected $_mainNode = 'CustomerInvoice';

    public function getInvoice($incrementID)
    {
        return $this->FindByElements($incrementID);
    }

    /**
     * Perform arguments pre-processing
     *
     * @param array $arguments
     */
    protected function _preProcessArguments($arguments)
    {
        $processedArguments = array(
            'CustomerInvoiceSelectionByElements' => array(
                'IDcommandeMagento_CR0EY8D0SJQNS8MJQV3SXP1B4' => array(
                    'SelectionByText' => array(
                        'InclusionExclusionCode' => 'I',
                        'IntervalBoundaryTypeCode' => 1,
                        'LowerBoundaryName' => $arguments[0],
                    ),
                ),
            ),
        );

        return array($processedArguments);
    }

    protected function _getFormattedResponse($result)
    {
        if (is_object($result) && property_exists($result, 'ID')) {
            $data = array();
            $data['sapbyd_id'] = $result->ID;
            $data['date'] = $result->Date;
            $data['increment_id'] = $result->IDcommandeMagento;
            $data['billing_address'] = $this->_extractAddress($result->BillToParty->Address);
            $data['items'] = $this->_getItems($result->Item);
            $data['total_price'] = $result->PriceAndTax->NetAmount;
            $data['total_tax_amount'] = $result->PriceAndTax->TaxAmount;
            $data['total_price_incl_tax'] = $result->PriceAndTax->GrossAmount;

            return new Varien_Object($data);
        }

        if (is_array($result)) {
            foreach ($result as $invoice) {
                //Pour éviter les doublons à cause d'un problème de données sur webservice de dev
                if ($invoice->ReferenceBusinessTransactionDocumentID == $invoice->IDcommandeMagento) {
                    return $this->_getFormattedResponse($invoice);
                }
            }
        }
    }

    /**
     *
     * @param object $items
     * @return Varien_Object
     */
    private function _getItems($items)
    {
        if (!is_array($items)) {
            $items = array($items);
        }
        $data = array();
        foreach ($items as $k => $item) {
            $data[$k]['name'] = $item->Description;
            $data[$k]['qty'] = $item->Quantity->Quantity;
            $data[$k]['price'] = $item->PriceAndTax->NetAmount;
            $data[$k]['tax_amount'] = $item->PriceAndTax->TaxAmount;
            $data[$k]['price_incl_tax'] = $item->PriceAndTax->GrossAmount;
            $data[$k]['currency'] = $item->PriceAndTax
                ->getAttribute('NetAmount', 'currencyCode');
        }

        return new Varien_Object($data);
    }

    /**
     *
     * @param object $bill
     * @return Varien_Object
     */
    private function _extractAddress($bill)
    {
        $data['name'] = $bill->DisplayName;
        $data['street'] = $bill->PostalAddress->StreetName;
        $data['postalcode'] = $bill->PostalAddress->StreetPostalCode;
        $data['city'] = $bill->PostalAddress->CityName;
        $data['country'] = Mage::app()->getLocale()
            ->getCountryTranslation($bill->PostalAddress->CountryCode);
        $data['phone'] = $bill->ConventionalPhoneFormattedNumberDescription;

        return new Varien_Object($data);
    }
}