<?php 



class Customweb_Database_Util {
	
	private function __construct() {
		
	}
	
	public static function createPdo($inputHost, $inputDatabase, $inputUser, $inputPassword, $inputType = 'mysql') {
		return new PDO($inputType . ':' . self::generateDns($inputHost, $inputDatabase), $inputUser, $inputPassword);
	}
	
	public static function generateDns($inputHost, $inputDatabase) {
		$parameters = array();
		if(stripos($inputHost, ':') !== false) {
			list($host, $port) = explode(':', $inputHost);
				
			if (empty($port)) {
				$parameters['host'] = $host;
			} elseif (preg_match('/^[0-9]+$/', $port)) {
				$parameters['host'] = $host;
				$parameters['port'] = $port;
			} else {
				$parameters['unix_socket'] = $port;
			}
		} else {
			$parameters['host'] = $inputHost;
		}
		
		$parameters['dbname'] = $inputDatabase;
		
		$assignments = array();
		foreach ($parameters as $key => $value) {
			$assignments[] = $key . '=' . $value;
		}
		
		return implode(';', $assignments);
	}
	
}