<?php

/**
 * Instances of this class are used as a result of make picking info methods
 * @since 2.9.0
 */
class ResultPackings {
	
	/**
	 * Service type id.
	 * @var signed 64-bit integer
	 */
	protected $_serviceTypeId;
	
	/**
	 * Number of parcels.
	 * @var signed 32-bit integer
	 */
	protected $_parcelsCount;
	
	/**
	 * Constructs new instance of this class
	 * @param $stdClassResultAddressString
	 */
	function __construct($stdClassResultPackings) {
		$this->_serviceTypeId = isset($stdClassResultPackings->serviceTypeId) ? $stdClassResultPackings->serviceTypeId : null;
		$this->_parcelsCount = isset($stdClassResultPackings->parcelsCount) ? $stdClassResultPackings->parcelsCount : null;
	}
	
	/**
	 * Get Service type id.
	 * @return 64-bit integer
	 */
	public function getServiceTypeId() {
		return $this->_serviceTypeId;
	}
	
	
	/**
	 * Get Number of parcels.
	 * @return 32-bit integer
	 */
	public function getParcelsCount() {
		return $this->_parcelsCount;
	}
	
}
?>