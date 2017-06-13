<?php

/**
 * Instances of this class are used as a result of make picking info methods
 * @since 2.9.0
 */
class ResultOptionsBeforePayment {
	
	/**
	 * Indicates if the client is allowed to open the package before payment.
	 * @var boolean (nullable)
	 */
	protected $_open;
	
	/**
	 * Indicates if the client is allowed to test the package before payment.
	 * @var boolean (nullable)
	 */
	protected $_test;
	
	/**
	 * Constructs new instance of this class
	 * @param unknown $stdClassResultAddressString
	 */
	function __construct($stdClassResultOptionsBeforePayment) {
		$this->_open = isset($stdClassResultOptionsBeforePayment->open) ? $stdClassResultOptionsBeforePayment->open : null;
		$this->_test = isset($stdClassResultOptionsBeforePayment->test) ? $stdClassResultOptionsBeforePayment->test : null;
	}
	
	/**
	 * Gets Indicates if the client is allowed to open the package before payment.
	 * @return boolean (nullable)
	 */
	public function getOpen() {
		return $this->_open;
	}
	
	
	/**
	 * Gets Indicates if the client is allowed to test the package before payment.
	 * @return boolean (nullable)
	 */
	public function getTest() {
		return $this->_test;
	}
	
}
?>