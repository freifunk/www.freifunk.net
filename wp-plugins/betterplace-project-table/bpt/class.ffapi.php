<?php
/**
 * Created by PhpStorm.
 * User: andi
 * Date: 16.11.14
 * Time: 18:16
 */

class ffapi {

	private $summarizedApiUrl;
	private $summarizedApi;

	function __construct( $summarizedApiUrl ) {
		$this->summarizedApiUrl = $summarizedApiUrl;
		$rawFile = file_get_contents($this->getSummarizedApiUrl());
		$json = json_decode($rawFile, true);
		$this->SummarizedApi = $json;
	}


	/**
	 * @param $path
	 * @return array
	 *
	 * return values for a path. the path should separate by points
	 */
	public function getValues($path) {
        $result = array();
        foreach($this->summarizedApi as $community) {
            array_push($result, $this->getValuesFromCommunity($community, $path));
        }
        return $result;
	}

    /**
     * @param $community
     * @param $path
     * @return array
     *
     */
    private function getValuesFromCommunity($community, $path){
        $pathArray = explode(".", $path);
        return array();
    }

	/**
	 * @return mixed
	 */
	public function getSummarizedApi() {
		return $this->summarizedApi;
	}

	/**
	 * @param mixed $summarizedApi
	 */
	public function setSummarizedApi( $summarizedApi ) {
		$this->summarizedApi = $summarizedApi;
	}

	/**
	 * @return mixed
	 */
	public function getSummarizedApiUrl() {
		return $this->summarizedApiUrl;
	}

	/**
	 * @param mixed $summarizedApiUrl
	 */
	public function setSummarizedApiUrl( $summarizedApiUrl ) {
		$this->summarizedApiUrl = $summarizedApiUrl;
	}


}