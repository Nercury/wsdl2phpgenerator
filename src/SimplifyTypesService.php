<?php


class SimplifyTypesService {
	
	
	/**
	 * @var array list of extended simpleTypes
	 */
	private $extendetSimpleTypes = array();
	
	/**
	 * @var array
	 */
	private $typesArray = array();

	/**
	 * makes list of types
	 * @param array $types
	 */
	private function makeFullRestrictionList(array $types) {
		
		foreach ($types as $typeStr) {
			$wsdlNewline = ( strpos( $typeStr, "\r\n" ) ? "\r\n" : "\n" );
			$parts = explode($wsdlNewline, $typeStr);
			$tArr = explode(" ", $parts[0]);
			$restriction = $tArr[0];
			$className = $tArr[1];
			$this->addSimpleTypeArray($restriction, $className);
		}
	}
	
	/**
	 * returns the root of class hirarchie for simple types
	 * @param string $type
	 * @return string (simpleType)
	 */
	public function getRootType($type) {
		if (true === $this->isInSimpleTypeArray($type) && 'struct' != $this->extendetSimpleTypes[$type] ) {
				$restriction = $this->extendetSimpleTypes[$type];
				return $this->getRootType($restriction);
			} 
		return $type;
	}
	
	/**
	 * @param SoapClient $client
	 * @return multitype:string
	 */
	public function getSimplifiedTypes(SoapClient $client){
		
		$types = $client->__getTypes();
		
 		$this->makeFullRestrictionList($types);
		
		foreach($types as $typeStr)
		{
			$wsdlNewline = ( strpos( $typeStr, "\r\n" ) ? "\r\n" : "\n" );
			$parts = explode($wsdlNewline, $typeStr);
			$tArr = explode(" ", $parts[0]);
			$restriction = $tArr[0];
			$className = $tArr[1];
		
			$restriction = $this->getRootType($restriction);
			$numParts = count($parts);
			
			if ($numParts > 1) {
				$complexType = $restriction . ' ' . $className . ' {' . PHP_EOL;
				
				for($i = 1; $i < $numParts - 1; $i++){
					$parts[$i] = trim($parts[$i]);
					list($typename, $name) = explode(" ", substr($parts[$i], 0, strlen($parts[$i])-1) );
					$complexType .= '	' . $this->getRootType($typename) . ' ' . $name . ';' . PHP_EOL ;
				}
				$this->typesArray[] =  $complexType . '}';
				
			} else {
				$this->typesArray[] = $restriction . ' ' . $className;
			}
		}
		return $this->typesArray;
	}

	/**
	 * @return multitype: the $extendetSimpleTypes
	 */
	public function getExtendetSimpleTypes() {
		return $this->extendetSimpleTypes;
	}

	/**
	 * @return multitype: the $typesArray
	 */
	public function getTypesArray() {
		return $this->typesArray;
	}

	// helper for extended simpleTypes
	
	/**
	 * @param string $restriction
	 * @param string $className
	 */
	protected function addSimpleTypeArray($restriction, $className) {
		$this->extendetSimpleTypes[$className] = $restriction;
	}
	
	/**
	 * @param string $className
	 * @return boolean
	 */
	protected function isInSimpleTypeArray($className) {
		return (isset($this->extendetSimpleTypes[$className]))?true:false;
	}
}

?>