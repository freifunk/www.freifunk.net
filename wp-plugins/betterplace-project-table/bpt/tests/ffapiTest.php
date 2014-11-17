<?php
/**
 * Created by PhpStorm.
 * User: andi
 * Date: 16.11.14
 * Time: 22:41
 */

include_once("class.ffapi.php");

class ffapiTest extends PHPUnit_Framework_TestCase {

    private $api;

    function __construct()
    {
        $this->api = new ffapi("http://weimarnetz.de/ffmap/ffSummarizedDirTest.json");
    }


    public function testGetEmails()
    {
        $expected = array("kontakt@freifunk-mainz.de", "kontakt@weimarnetz.de");
        $results = $this->api->getValues("contact.email");
        $this->assertEquals($results, $expected);
    }

    public function testGetCampaigns() {
        $expected = array(array("14895"));
        $results = $this->api->getValues("support.donations.campaigns.projectid");
        $this->assertEquals($results, $expected);
    }

    public function testGetServices() {
        $expected = array(array("DynDNS Server", "Weimarnetz Radio powered by Bernd"));
        $results = $this->api->getValues("services.serviceName");
        $this->assertEquals($results, $expected);
    }

}
 