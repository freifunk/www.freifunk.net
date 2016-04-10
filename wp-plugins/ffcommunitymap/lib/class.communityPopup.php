<?php

include_once("class.popups.php");

class communityPopup extends Popups {

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
    $this->popup .= '<% if (props.nodes) { %>'.PHP_EOL;
    $this->popup .= '<div class="nodes">Zug&auml;nge: <%- props.nodes  %>'.PHP_EOL;
    $this->popup .= '<% if (props.state && props.age) { %>'.PHP_EOL;
    $this->popup .= '<span class="state <%- props.state  %>" title="Die letzte Aktualisierung der Daten war vor <%- props.age  %> Tagen">(<%- props.state  %>)</span>'.PHP_EOL;
    $this->popup .= ' <% } %>'.PHP_EOL;
    $this->popup .= '</div>'.PHP_EOL;
    $this->popup .= '<% } %>'.PHP_EOL;
    $this->popup .= '<% if (props.phone) { %>'.PHP_EOL;
    $this->popup .= '<div class="phone">&#9742; <%- props.phone  %></div>'.PHP_EOL;
    $this->popup .= '<% } %>'.PHP_EOL;
    $this->popup .= '<ul class="contacts" style="height:<%- Math.round(props.contacts.length/6+0.4)*30+10 %>px; width: <%- 6*(30+5)%>px;">'.PHP_EOL;
    $this->popup .= '<% _.each(props.contacts, function(contact, index, list) { %>'.PHP_EOL;
    $this->popup .= '<li class="contact">'.PHP_EOL;
    $this->popup .= '<a href="<%- contact.url %>" class="button <%- contact.type %>" target="_window"></a>'.PHP_EOL;
    $this->popup .= '</li>'.PHP_EOL;
    $this->popup .= '<% }); %>'.PHP_EOL;
    $this->popup .= '</ul>'.PHP_EOL;
    $this->popup .= '<div class="events">'.PHP_EOL;
    $this->popup .= '</div>'.PHP_EOL;
    $this->popup .= '</div>'.PHP_EOL;
    $this->popup .= '</script>'.PHP_EOL;
    return $this->popup;
  } 

}
