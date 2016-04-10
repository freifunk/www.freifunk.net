<?php

include_once("class.communityPopup.php");
include_once("class.socialprojectsPopup.php");

final class PopupFactory {

    public function getPopupClass($platform){
        switch($platform) {
            case "community":
                return new communityPopup();
                break;
            case "socialprojects":
                return new socialprojectsPopup();
                break;
            default:
                return null;
        }
    }

} 
