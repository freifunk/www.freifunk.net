<?php
/**
 * Created by PhpStorm.
 * User: andi
 * Date: 19.11.14
 * Time: 23:54
 */

include_once("class.boostCampaign.php");

final class DonationFactory {

    public function getDonationClass($platform, $campaignId, $key){
        switch($platform) {
            case "betterplace":
                return new bpProject($campaignId, $key);
                break;
            case "boost":
                return new boostCampaign($campaignId, $key);
                break;
            default:
                return null;
        }
    }

} 