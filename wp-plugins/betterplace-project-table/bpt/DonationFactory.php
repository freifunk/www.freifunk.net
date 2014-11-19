<?php
/**
 * Created by PhpStorm.
 * User: andi
 * Date: 19.11.14
 * Time: 23:54
 */

final class DonationFactory {

    public function getDonationClass($platform, $campaignId, $key){
        switch($platform) {
            case "betterplace":
                return new bpProject($campaignId, $key);
                break;
            default:
                return null;
        }
    }

} 