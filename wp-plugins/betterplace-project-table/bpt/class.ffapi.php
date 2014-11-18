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

    private function isAssoc($arr){
        return array_keys($arr) !== range(0, count($arr) - 1);
    }

	function __construct( $summarizedApiUrl ) {
		$this->summarizedApiUrl = $summarizedApiUrl;
		$rawFile = file_get_contents($this->getSummarizedApiUrl());
		$json = json_decode($rawFile, true);
		$this->summarizedApi = $json;
	}


	/**
	 * @param $path
	 * @return array
	 *
	 * return values for a path. the path should separate by points
	 */
	public function getValues($path) {
        $result = array();
        foreach($this->summarizedApi as $name => $community) {
            $values = $this->getValuesFromCommunity($community, $path);
            if ( ! empty($values)) {
                $result[$name] = $values;
            }
        }
        return $result;
	}

    /**
     * @param $community
     * @param $path
     * @return array
     *
     */
    private function getValuesFromCommunity($community, $path)
    {
        $result = null;
        $pathArray = explode(".", $path);
        if (count($pathArray) > 1) {
            $element = array_shift($pathArray);
            if (!empty($community[$element])) {
                if ($this->isAssoc($community[$element])) {
                    $result = $this->getValuesFromCommunity($community[$element], implode(".", $pathArray));
                } else {
                    $result = array();
                    foreach($community[$element] as $cElement) {
                        array_push($result, $this->getValuesFromCommunity($cElement, implode(".", $pathArray)));
                    }
                }
            }
        } else {
            if (! empty($community[$pathArray[0]])) {
                $result = $community[$pathArray[0]];
            }
        }
        return $result;
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