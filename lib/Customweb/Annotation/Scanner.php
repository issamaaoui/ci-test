<?php 

//require_once 'Customweb/Core/Util/Class.php';
//require_once 'Customweb/Annotation/Cache/Reader.php';
//require_once 'Customweb/Annotation/ReflectionAnnotatedClass.php';


class Customweb_Annotation_Scanner {
	
	
	/**
	 * Returns a map with all annotation found for the given annotation name. The
	 * key of the map is the class name and the value is an instance of the annotation 
	 * class.
	 * 
	 * @param string $annotationName
	 * @param array $targetPackageNames
	 * @return Customweb_IAnnotation[]
	 */
	public function find($annotationName, $targetPackageNames = null) {
		
		try {
			Customweb_Core_Util_Class::loadLibraryClassByName($annotationName);
		}
		catch (Customweb_Core_Exception_ClassNotFoundException $e) {
			
		}
		
		$rs = array();
		$candiates = $this->findTargetClassCandidates($annotationName, $targetPackageNames);
		
		if (strstr($annotationName, '_') !== false) {
			$alternativeAnnotationName = substr($annotationName, strrpos($annotationName, '_') + 1);
			$candiates = array_merge($candiates, $this->findTargetClassCandidates($alternativeAnnotationName, $targetPackageNames));
		}
		
		foreach ($candiates as $candidate) {
			if (strpos($candidate, '::') === false) {
				Customweb_Core_Util_Class::loadLibraryClassByName($candidate);
				$reflection = new Customweb_Annotation_ReflectionAnnotatedClass($candidate);
				if ($reflection->hasAnnotation($annotationName)) {
					$rs[$reflection->getName()] = $reflection->getAnnotation($annotationName);
				}
			} else {
				$candiateClass = substr($candidate, 0, strpos($candidate, '::'));
				$candidateMethod = substr($candidate, strlen($candiateClass)+2);
				Customweb_Core_Util_Class::loadLibraryClassByName($candiateClass);
				$reflectionClass = new Customweb_Annotation_ReflectionAnnotatedClass($candiateClass);
				$reflectionMethod = $reflectionClass->getMethod($candidateMethod);
				if ($reflectionMethod->hasAnnotation($annotationName)) {
					$rs[$candidate] = $reflectionMethod->getAnnotation($annotationName);
				}
			}
		}
		
		return $rs;
	}
	
	private function findTargetClassCandidates($annotationName, $targetPackageNames) {
		$targets = Customweb_Annotation_Cache_Reader::getTargetsByAnnotationName($annotationName);
		
		if (is_array($targetPackageNames) && count($targetPackageNames) > 0) {
			$candidates = array();
			foreach ($targets as $target) {
				foreach ($targetPackageNames as $packageName) {
					if (strpos($target, $packageName) === 0) {
						$candidates[] = $target;
					}
				}
			}
			return $candidates;
		}
		else {
			return $targets;
		}
		
		
	}
	
}