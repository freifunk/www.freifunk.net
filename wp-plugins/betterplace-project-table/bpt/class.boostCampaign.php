<?php
/**
 * Created by PhpStorm.
 * User: andi
 * Date: 06.02.15
 * Time: 13:17
 */

class boostCampaign extends DonationCampaigns{

    const boostCampaignUrl = "https://www.boost-project.com/de/charities/";

    public function getProjectDetails()
    {
        $ctx = stream_context_create(array('http'=>
            array(
                'timeout' => get_option('http_timeout'), // 1 200 Seconds = 20 Minutes
            )
        ));
        $prjDetails = file_get_contents(self::boostCampaignUrl . $this->getCampaignId() . ".json", null, $ctx);
        $prjDetailsJson = json_decode($prjDetails, true);

        $this->setProjectLink(self::boostCampaignUrl . $this->getCampaignId());
        $this->setDonationLink(self::boostCampaignUrl . $this->getCampaignId() . "##");

        $this->setProjectTitle($prjDetailsJson['title']);
        $this->setDonors($prjDetailsJson['advocates']);
        return $this;
    }

}