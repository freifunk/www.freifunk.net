<?php
/**
 * Created by PhpStorm.
 * User: andi
 * Date: 16.11.14
 * Time: 18:21
 */

include_once("class.donations.php");

class bpProject extends DonationCampaigns {

    const bpApiUrl = "https://api.betterplace.org/de/api_v4/projects/";

    public function getProjectDetails() {
        $prjDetails = file_get_contents(self::bpApiUrl . $this->getCampaignId() . ".json");
        $prjDetailsJson = json_decode($prjDetails, true);
        foreach($prjDetailsJson['links'] as $links) {
            if ($links['rel'] == 'platform') {
                $this->setProjectLink($links['href']);
            } elseif ($links['rel'] == 'new_donation' ) {
                $this->setDonationLink($links['href']);
            }
        }
        foreach($prjDetailsJson['profile_picture']['links'] as $pic) {
            if ($pic['rel'] == 'fill_270x141') {
                $this->setProjectImage($pic['href']);
            }
        }

        $this->setProjectTitle($prjDetailsJson['title']);
        $this->setOpenAmount($prjDetailsJson['open_amount_in_cents']);
        $this->setIncompleteNeed($prjDetailsJson['incomplete_need_count']);
        $this->setProgress($prjDetailsJson['progress_percentage']);
        $this->setDonors($prjDetailsJson['donor_count']);
        $this->setOrganization($prjDetailsJson['carrier']['name']);
        return $this;
    }


} 