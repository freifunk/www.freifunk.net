<?php

include_once("class.popups.php");

class socialprojectsPopup extends Popups {

  public function assemblePopup() {
    $this->popup = '<script type="text/template" class="template" id="community-popup">';
    $this->popup .= '<div class="community-popup" data-id="<%- props.shortname %>">'.PHP_EOL;
    $this->popup .= '<% if ( props.name ) { %>'.PHP_EOL;
    $this->popup .= '<h2><a href="<%- props.url %>" target="_window"><%- props.name %></a></h2>'.PHP_EOL;
    $this->popup .= '<% } %>'.PHP_EOL;
    $this->popup .= '<% if (props.metacommunity) { %>'.PHP_EOL;
    $this->popup .= '<h3><%- props.metacommunity %></h3>'.PHP_EOL;
    $this->popup .= '<% } %>'.PHP_EOL;
    $this->popup .= '<% if (props.city) { %>'.PHP_EOL;
    $this->popup .= '<div class="city"><%- props.city  %></div>'.PHP_EOL;
    $this->popup .= '<% } %>'.PHP_EOL;
    $this->popup .= '<% if (props.socialprojects && props.socialprojects.number) { %>'.PHP_EOL;
    $this->popup .= '<div class="nodes">Projekte: <%- props.socialprojects.number  %>'.PHP_EOL;
    $this->popup .= '</div>'.PHP_EOL;
    $this->popup .= '<% } %>'.PHP_EOL;
    $this->popup .= '<% if (props.phone) { %>'.PHP_EOL;
    $this->popup .= '<div class="phone">&#9742; <%- props.phone  %></div>'.PHP_EOL;
    $this->popup .= '<% } %>'.PHP_EOL;
    $this->popup .= '<% if (props.socialprojects.website) { %>'.PHP_EOL;
    $this->popup .= '<div><a href="<%- props.socialprojects.website %>" target="_window">Infoseite für soziale Träger</a></div>'.PHP_EOL;
    $this->popup .= '<% }; %>'.PHP_EOL;
    $this->popup .= '<% if (props.socialprojects.contact) { %>'.PHP_EOL;
    $this->popup .= '<div><a href="mailto:<%- props.socialprojects.contact %>" target="_window">Email für Kontaktanfragen</a></div>'.PHP_EOL;
    $this->popup .= '<% }; %>'.PHP_EOL;
    $this->popup .= '<div class="events">'.PHP_EOL;
    $this->popup .= '</div>'.PHP_EOL;
    $this->popup .= '</div>'.PHP_EOL;
    $this->popup .= '</script>'.PHP_EOL;
    return $this->popup;
  } 

}
