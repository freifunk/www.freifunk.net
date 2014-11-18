<?php
/**
 * Created by PhpStorm.
 * User: andi
 * Date: 17.11.14
 * Time: 20:49
 */

include_once("class.bpproject.php");

class bpProjectTest extends PHPUnit_Framework_TestCase {

    private $project;

    function __construct()
    {
        $this->project = new bpProject("14895", "weimarnetz");
    }

    public function testCampaign()
    {
        $result = $this->project->getProjectDetails()->getProjectArray();
        $this->assertEquals("https://www.betterplace.org/de/projects/14895-weimarnetz-e-v-freies-wlan-in-weimar", $result['projectLink']);
        $this->assertEquals("https://asset1.betterplace.org/uploads/project/profile_picture/000/014/895/fill_270x141_WCW2014.jpg", $result['projectImage']);
        $this->assertEquals("https://www.betterplace.org/de/projects/14895/donations/new", $result['donationLink']);
    }


}
 