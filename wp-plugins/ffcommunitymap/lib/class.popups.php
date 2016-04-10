<?php

abstract class Popups {
  
  private $popup;

  public abstract function assemblePopup();

  public function getPopup() {
    return $this->popup;
  }

}


