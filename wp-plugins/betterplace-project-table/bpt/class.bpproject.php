<?php
/**
 * Created by PhpStorm.
 * User: andi
 * Date: 16.11.14
 * Time: 18:21
 */

class bpProject {

    const bpApiUrl = "https://api.betterplace.org/de/api_v4/projects/";
    private $campaignId;
    private $projectLink;
    private $donationLink;
    private $projectImage;
    private $projectTitle;
    private $openAmount;
    private $incompleteNeed;
    private $progress;
    private $donors;function

    __construct($campaignId)
    {
        $this->campaignId = $campaignId;
        $this->getProjectDetails();
    }

    public function getProjectDetails() {
        $prjDetails = file_get_contents(self::bpApiUrl . $this->campaignId . ".json");
        $prjDetailsJson = json_decode($prjDetails, true);
        foreach($prjDetailsJson['links'] as $links) {
            if ($links['rel'] == 'platform') {
                $this->projectLink = $links['href'];
            } elseif ($links['rel'] == 'new_donation' ) {
                $this->donationLink = $links['href'];
            }
        }
        foreach($prjDetailsJson['profile_picture']['links'] as $pic) {
            if ($pic['rel'] == 'fill_270x141') {
                $this->projectImage = $pic['href'];
            }
        }

        $this->projectTitle = $prjDetailsJson['title'];
        $this->openAmount = $prjDetailsJson['open_amount_in_cents'];
        $this->incompleteNeed = $prjDetailsJson['incomplete_need_count'];
        $this->progress = $prjDetailsJson['progress_percentage'];
        $this->donors = $prjDetailsJson['donor_count'];
        return $this;
    }

    public function getProjectArray() {
        $return = array();
        $return['projectLink'] = $this->projectLink;
        $return['donationLink'] = $this->donationLink;
        $return['projectImage'] = $this->projectImage;
        $return['projectTitle'] = $this->projectTitle;
        $return['openAmount'] = $this->openAmount;
        $return['incompleteNeed'] = $this->incompleteNeed;
        $return['progress'] = $this->progress;
        $return['donors'] = $this->donors;
        return $return;
    }
} 