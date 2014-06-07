<?php


class Booker {

	var $responses = array();
	var $headers = array();

	/*
	 * Do all the init stuff that has to be done.
	 */
	function init() {
	}

	function update($roomId, $startDateYear, $startDateMonth, $startDateDay, $endDateYear, $endDateMonth, $endDateDay, $nbBeds = null) {
	}

	/**
	 * Do the cleanup (close connection, etc.)
	 */
	function shutdown() {
	}

	function addResponse($key, $response, $header) {
		$this->responses[$key] = $response;
		$this->headers[$key] = $header;
	}

	function saveResponsesInfoFile($prefix) {
		$retVal = array();
		foreach($this->responses as $key => $response) {
			$filename = 'responses/' . $prefix . '_response_' . $key . '.html';
			file_put_contents($filename, $response);
			$retVal[$key] = $filename;
		}
//		foreach($this->headers as $key => $header) {
//			file_put_contents('responses/' . $prefix . '_header_' . $key . '.txt', $header);
//		}
		return $retVal;
	}
}

if(!function_exists('file_put_contents')) {
	function file_put_contents($filename, $data) {
		$fh = fopen($filename, 'w');
		fwrite($fh, $data);
		fclose($fh);
	}

}

?>
