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


/**
 * This interface has to be implemented by a listener
 * which wants to register for log messages.
 * 
 * @author Thomas Hunziker
 *
 */
interface Customweb_Core_Logger_IListener {
	
	/**
	 * Adds a log message with the method specified
	 * by the listener.
	 * 
	 * @param string $loggerName
	 * @param string $level
	 * @param string $message
	 * @param Exception $e
	 */
	public function addLogEntry($loggerName, $level, $message, Exception $e = null);
	
}