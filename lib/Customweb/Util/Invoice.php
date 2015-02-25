<?php
/**
 *  * You are allowed to use this API in your web application.
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
//require_once 'Customweb/Util/Currency.php';

/**
 * This util provides utility methods for invoice item handling.
 */
final class Customweb_Util_Invoice {

	/**
	 *
	 * @param Customweb_Payment_Authorization_IInvoiceItem[] $items
	 */
	public static function getTotalAmountIncludingTax($items){
		$sum = 0;
		foreach ($items as $item) {
			if ($item->getType() == Customweb_Payment_Authorization_DefaultInvoiceItem::TYPE_DISCOUNT) {
				$sum -= $item->getAmountIncludingTax();
			}
			else {
				$sum += $item->getAmountIncludingTax();
			}
		}
		return $sum;
	}

	/**
	 *
	 * @param Customweb_Payment_Authorization_IInvoiceItem[] $items
	 */
	public static function getTotalAmountExcludingTax($items){
		$sum = 0;
		foreach ($items as $item) {
			if ($item->getType() == Customweb_Payment_Authorization_DefaultInvoiceItem::TYPE_DISCOUNT) {
				$sum -= $item->getAmountExcludingTax();
			}
			else {
				$sum += $item->getAmountExcludingTax();
			}
		}
		return $sum;
	}

	/**
	 *
	 * @param Customweb_Payment_Authorization_IInvoiceItem[] $items
	 */
	public static function getTotalTaxAmount($items){
		$sum = 0;
		foreach ($items as $item) {
			if ($item->getType() == Customweb_Payment_Authorization_DefaultInvoiceItem::TYPE_DISCOUNT) {
				$sum -= $item->getTaxAmount();
			}
			else {
				$sum += $item->getTaxAmount();
			}
		}
		return $sum;
	}

	/**
	 * This method generates a set of line items, which represents the delta of the amount change.
	 *
	 *
	 * @param Customweb_Payment_Authorization_IInvoiceItem[] $originalLineItems List of line items on which the delta based on.
	 * @param float $amount The reduction amount.
	 * @return Customweb_Payment_Authorization_IInvoiceItem[] The set of line items, which represents the delta.
	 */
	public static function getItemsByReductionAmount(array $originalLineItems, $amount, $currencyCode){
		if (count($originalLineItems) <= 0) {
			throw new Exception("No line items provided.");
		}
		
		$total = self::getTotalAmountIncludingTax($originalLineItems);
		$factor = $amount / $total;
		
		$appliedTotal = 0;
		$newItems = array();
		foreach ($originalLineItems as $item) {
			/* @var $item Customweb_Payment_Authorization_IInvoiceItem */
			$newAmount = Customweb_Util_Currency::roundAmount($item->getAmountIncludingTax() * $factor, $currencyCode);
			$newItem = new Customweb_Payment_Authorization_DefaultInvoiceItem($item->getSku(), $item->getName(), $item->getTaxRate(), $newAmount, 
					$item->getQuantity(), $item->getType(), $item->getOriginalSku());
			$newItems[] = $newItem;
			if ($item->getType() == Customweb_Payment_Authorization_DefaultInvoiceItem::TYPE_DISCOUNT) {
				$appliedTotal -= $newAmount;
			}
			else {
				$appliedTotal += $newAmount;
			}
		}
		
		// Fix rounding error
		$roundingDifference = $amount - $appliedTotal;
		$item = $newItems[0];
		$newAmount = $item->getAmountIncludingTax() + $roundingDifference;
		$newItems[0] = new Customweb_Payment_Authorization_DefaultInvoiceItem($item->getSku(), $item->getName(), $item->getTaxRate(), $newAmount, 
				$item->getQuantity(), $item->getType(), $item->getOriginalSku());
		
		return $newItems;
	}

	/**
	 * This method ensures that all invoice items have a unique sku.
	 *
	 * @param Customweb_Payment_Authorization_IInvoiceItem[] $lineItems
	 * @return Customweb_Payment_Authorization_IInvoiceItem[]
	 */
	public static function ensureUniqueSku(array $lineItems){
		$newLineItems = array();
		$skus = array();
		foreach ($lineItems as $item) {
			$sku = $item->getSku();
			if (empty($sku)) {
				$sku = preg_replace("/[^a-z0-9]/", '', strtolower($item->getName()));
			}
			if (empty($sku)) {
				throw new Exception("There is an invoice item without SKU and name.");
			}
			
			if (isset($skus[$sku])) {
				$sku = $sku . '_' . $skus[$sku];
				$skus[$item->getSku()]++;
			}
			else {
				$skus[$sku] = 1;
			}
			
			$newLineItems[] = new Customweb_Payment_Authorization_DefaultInvoiceItem($sku, $item->getName(), $item->getTaxRate(), 
					$item->getAmountIncludingTax(), $item->getQuantity(), $item->getType(), $item->getOriginalSku());
		}
		
		return $newLineItems;
	}

	/**
	 * This method calculates the resulting line items based on a list of items and the delta line items.
	 * This
	 * method can be used to determine the resulting line items from a set of delta line items.
	 *
	 * @param Customweb_Payment_Authorization_IInvoiceItem[] $originalItems
	 * @param Customweb_Payment_Authorization_IInvoiceItem[] $deltaLineItems
	 * @return Customweb_Payment_Authorization_IInvoiceItem[]
	 */
	public static function getResultingLineItemsByDeltaItems(array $originalItems, array $deltaLineItems){
		$deltaKeys = array();
		foreach ($deltaLineItems as $key => $item) {
			$deltaKeys[self::getIdentifier($item)] = $item;
		}
		
		$resultingLineItems = array();
		foreach ($originalItems as $item) {
			$identifier = self::getIdentifier($item);
			if (isset($deltaKeys[$identifier])) {
				$deltaItem = $deltaKeys[$identifier];
				/* @var $deltaItem Customweb_Payment_Authorization_IInvoiceItem */
				$newAmount = $item->getAmountIncludingTax() - $deltaItem->getAmountIncludingTax();
				$newQuantity = $item->getQuantity() - $deltaItem->getQuantity();
				$resultingLineItems[] = new Customweb_Payment_Authorization_DefaultInvoiceItem($item->getSku(), $item->getName(), 
						$deltaItem->getTaxRate(), $newAmount, $newQuantity, $item->getType(), $item->getOriginalSku());
				unset($deltaKeys[$identifier]);
			}
			else {
				$resultingLineItems[] = $item;
			}
		}
		
		// Add additional capture items
		foreach ($deltaKeys as $item) {
			$resultingLineItems[] = $item;
		}
		
		return $resultingLineItems;
	}

	/**
	 * This method removes the given $itemsToRemove from the $originalItems.
	 * The resulting
	 * list does only contain items from $originalItems.
	 *
	 * @param array $originalItems
	 * @param array $itemsToRemove
	 * @return Customweb_Payment_Authorization_IInvoiceItem[]
	 */
	public static function substractLineItems(array $originalItems, array $itemsToRemove){
		$deltaKeys = array();
		foreach ($itemsToRemove as $key => $item) {
			$deltaKeys[self::getIdentifier($item)] = $item;
		}
		
		$resultingLineItems = array();
		foreach ($originalItems as $item) {
			$identifier = self::getIdentifier($item);
			if (isset($deltaKeys[$identifier])) {
				$deltaItem = $deltaKeys[$identifier];
				/* @var $deltaItem Customweb_Payment_Authorization_IInvoiceItem */
				$newAmount = $item->getAmountIncludingTax() - $deltaItem->getAmountIncludingTax();
				$newQuantity = $item->getQuantity() - $deltaItem->getQuantity();
				if ($newAmount > 0) {
					$resultingLineItems[] = new Customweb_Payment_Authorization_DefaultInvoiceItem($item->getSku(), $item->getName(), 
							$deltaItem->getTaxRate(), $newAmount, $newQuantity, $item->getType(), $item->getOriginalSku());
				}
				unset($deltaKeys[$identifier]);
			}
			else {
				$resultingLineItems[] = $item;
			}
		}
		
		return $resultingLineItems;
	}

	/**
	 * This method adds the given $itemsToAdd to the $originalItems.
	 * The resulting list
	 * may contains also items which are only present in $itemsToAdd.
	 *
	 * @param array $originalItems
	 * @param array $itemsToAdd
	 * @return Customweb_Payment_Authorization_IInvoiceItem[]
	 */
	public static function addLineItems(array $originalItems, array $itemsToAdd){
		$deltaKeys = array();
		foreach ($itemsToAdd as $key => $item) {
			$deltaKeys[self::getIdentifier($item)] = $item;
		}
		
		$resultingLineItems = array();
		foreach ($originalItems as $item) {
			$identifier = self::getIdentifier($item);
			if (isset($deltaKeys[$identifier])) {
				$deltaItem = $deltaKeys[$identifier];
				/* @var $deltaItem Customweb_Payment_Authorization_IInvoiceItem */
				$newAmount = $item->getAmountIncludingTax() + $deltaItem->getAmountIncludingTax();
				$newQuantity = $item->getQuantity() + $deltaItem->getQuantity();
				$resultingLineItems[] = new Customweb_Payment_Authorization_DefaultInvoiceItem($item->getSku(), $item->getName(), 
						$deltaItem->getTaxRate(), $newAmount, $newQuantity, $item->getType(), $item->getOriginalSku());
				unset($deltaKeys[$identifier]);
			}
			else {
				$resultingLineItems[] = $item;
			}
		}
		
		// Add additional capture items
		foreach ($deltaKeys as $item) {
			$resultingLineItems[] = $item;
		}
		
		return $resultingLineItems;
	}

	private static function getIdentifier(Customweb_Payment_Authorization_IInvoiceItem $item){
		$key = $item->getSku();
		if (empty($key)) {
			$key = '';
		}
		$name = $item->getName();
		if (!empty($name)) {
			$key .= $name;
		}
		
		return $key;
	}
}