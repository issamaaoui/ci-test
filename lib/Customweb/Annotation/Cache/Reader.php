<?php

//require_once 'Customweb/Annotation/Cache/Reader.php';
//require_once 'Customweb/Annotation/Util.php';

//require_once 'Customweb/Annotation/Cache/Annotation.php';

class Customweb_Annotation_Cache_Reader {
	private static $data = null;
	private static $sortByTarget = null;
	private static $scannedIncludePath = null;

	private function __construct(){}
	
	public static function getAnnotationsByTarget($target) {
		$key = Customweb_Annotation_Util::createName($target);
		$data = self::read();
		if (isset($data[$key])) {
			return $data[$key];
		}
		else {
			return array();
		}
	}
	
	public static function getTargetsByAnnotationName($annotationName) {
		$data = self::read();
		if (self::$sortByTarget === null) {
			self::$sortByTarget = array();
			
			foreach ($data as $targetName => $annotations) {
				foreach ($annotations as $annotation) {
					$key = strtolower($annotation->getName());
					if (!isset(self::$sortByTarget[$key])) {
						self::$sortByTarget[$key] = array();
					}
					self::$sortByTarget[$key][] = $targetName;
				}
			}
		}
		
		$key = strtolower($annotationName);
		if (isset(self::$sortByTarget[$key])) {
			return self::$sortByTarget[$key];
		}
		else {
			return array();
		}
	}

	private static function read(){
		if (self::$data === null || self::$scannedIncludePath !== get_include_path()) {
			$reader = new Customweb_Annotation_Cache_Reader();
			self::$data = $reader->loadData();
			self::$sortByTarget = null;
			self::$scannedIncludePath = get_include_path();
		}
		
		return self::$data;
	}

	private function loadData(){
		
		$data = array();
		$files = $this->findAllCacheFiles();
		
		foreach ($files as $file) {
			$data = array_merge($data, unserialize(file_get_contents($file)));
		}
		
		return $data;
	}

	/**
	 * Searches for all possible cache files.
	 * 
	 * @return string[]
	 */
	private function findAllCacheFiles(){
		$folderName = 'Customweb/Annotation/Cache/data/';
		$folderNames = array();
		$include_path = explode(PATH_SEPARATOR, get_include_path());
		foreach ($include_path as $path) {
			$file = realpath($path . '/' . $folderName);
			if (@file_exists($file) && @is_dir($file)) {
				$folderNames[] = $file;
			}
		}
		
		$files = array();
		foreach ($folderNames as $folderName) {
			if ($handle = opendir($folderName)) {
				while (false !== ($file = readdir($handle))) {
					if (strstr($file, '.php') !== false) {
						$files[] = $folderName . '/' . $file;
					}
				}
				closedir($handle);
			}
		}
		
		return $files;
	}
}