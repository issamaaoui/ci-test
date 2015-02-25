<?php

//require_once 'Customweb/Annotation/Parser/AnnotationMatcher.php';
//require_once 'Customweb/Annotation/AnnotationsBuilder.php';


class Customweb_Annotation_Parser_NestedAnnotationMatcher extends Customweb_Annotation_Parser_AnnotationMatcher {

	protected function process($result){
		$builder = new Customweb_Annotation_AnnotationsBuilder();
		
		return $builder->instantiateAnnotation($result[1], $result[2]);
	}
}