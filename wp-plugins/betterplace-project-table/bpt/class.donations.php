<?php
/**
 * Created by PhpStorm.
 * User: andi
 * Date: 19.11.14
 * Time: 22:57
 */

abstract class DonationCampaigns {

    private $campaignId;
    private $key;
    private $projectLink;
    private $donationLink;
    private $projectImage;
    private $projectTitle;
    private $openAmount;
    private $incompleteNeed;
    private $progress;
    private $donors;

    function __construct($campaignId, $key)
    {
        $this->campaignId = $campaignId;
        $this->key = $key;
        $this->getProjectDetails();
    }

    public function getProjectArray() {
        $return = array();
        $return['key'] = $this->key;
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

    public abstract function getProjectDetails();

    /**
     * @return mixed
     */
    public function getCampaignId()
    {
        return $this->campaignId;
    }

    /**
     * @param mixed $campaignId
     */
    public function setCampaignId($campaignId)
    {
        $this->campaignId = $campaignId;
    }

    /**
     * @return mixed
     */
    public function getDonationLink()
    {
        return $this->donationLink;
    }

    /**
     * @param mixed $donationLink
     */
    public function setDonationLink($donationLink)
    {
        $this->donationLink = $donationLink;
    }

    /**
     * @return mixed
     */
    public function getDonors()
    {
        return $this->donors;
    }

    /**
     * @param mixed $donors
     */
    public function setDonors($donors)
    {
        $this->donors = $donors;
    }

    /**
     * @return mixed
     */
    public function getIncompleteNeed()
    {
        return $this->incompleteNeed;
    }

    /**
     * @param mixed $incompleteNeed
     */
    public function setIncompleteNeed($incompleteNeed)
    {
        $this->incompleteNeed = $incompleteNeed;
    }

    /**
     * @return mixed
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @param mixed $key
     */
    public function setKey($key)
    {
        $this->key = $key;
    }

    /**
     * @return mixed
     */
    public function getOpenAmount()
    {
        return $this->openAmount;
    }

    /**
     * @param mixed $openAmount
     */
    public function setOpenAmount($openAmount)
    {
        $this->openAmount = $openAmount;
    }

    /**
     * @return mixed
     */
    public function getProgress()
    {
        return $this->progress;
    }

    /**
     * @param mixed $progress
     */
    public function setProgress($progress)
    {
        $this->progress = $progress;
    }

    /**
     * @return mixed
     */
    public function getProjectImage()
    {
        return $this->projectImage;
    }

    /**
     * @param mixed $projectImage
     */
    public function setProjectImage($projectImage)
    {
        $this->projectImage = $projectImage;
    }

    /**
     * @return mixed
     */
    public function getProjectLink()
    {
        return $this->projectLink;
    }

    /**
     * @param mixed $projectLink
     */
    public function setProjectLink($projectLink)
    {
        $this->projectLink = $projectLink;
    }

    /**
     * @return mixed
     */
    public function getProjectTitle()
    {
        return $this->projectTitle;
    }

    /**
     * @param mixed $projectTitle
     */
    public function setProjectTitle($projectTitle)
    {
        $this->projectTitle = $projectTitle;
    }



} 