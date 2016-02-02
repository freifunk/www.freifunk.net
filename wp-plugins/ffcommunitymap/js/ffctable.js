//"use strict";
var FFCTABLE = {


  init: function (myTargetId, myUrl) {
    newTable = Object.create(this);
    newTable.targetId = myTargetId;
    newTable.tableTemplate = jQuery("script.template#" + newTable.targetId).html();
    newTable.url = myUrl;
    newTable.communityData = null;
    newTable.communityDataDisplay = null;
    newTable.footable = null;
    return newTable;
  },

  getData: function() {
    jQuery.ajax({
      url: this.url,
      table: this,
      dataType: "jsonp",
      success: function(Response){
        var rows = Response;
        console.log(this.table);
        rows = _.sortBy(rows, function(o){ return o.location.city;});
        _.each(rows, function(item, key, list) {
          if (item.url && !item.url.match(/^http([s]?):\/\/.*/)) {
            item.url = "http://" + item.url;
          }
          if (item.contact.ml && !item.contact.ml.match(/^mailto:.*/) && item.contact.ml.match(/.*\@.*/)) {
            item.contact.ml = "mailto:" + item.contact.ml;
          } else if (item.contact.ml && !item.contact.ml.match(/^http([s]?):\/\/.*/) ) {
            item.contact.ml = "http://" + item.contact.ml;
          }
          if (item.contact.email && !item.contact.email.match(/^mailto:.*/)) {
            item.contact.email = "mailto:" + item.contact.email;
          }
          if (item.contact.twitter && !item.contact.twitter.match(/^http([s]?):\/\/.*/)) {
            item.contact.twitter = "https://twitter.com/" + item.contact.twitter;
          }
          if (item.contact.irc && !item.contact.irc.match(/^irc:.*/)) {
            item.contact.irc = "irc:" + item.contact.irc;
          }
          if (item.contact.jabber && !item.contact.jabber.match(/^jabber:.*/)) {
            item.contact.jabber = "xmpp:" + item.contact.jabber;
          }
          if (item.contact.identica && !item.contact.identica.match(/^identica:.*/)) {
            item.contact.identica = "identica:" + item.contact.identica;
          }
          item.distance = 40008000;
          item.rank = 0;
        });
        console.log(rows);
        this.table.communityData = rows;
        this.table.communityDataDisplay = rows;
        this.table.printTable();

      },
      error: function(XMLHttpRequest, textStatus, errorThrown){
        alert("Error" + textStatus);
      }

    });
  },

  getDistanceByZip: function(eventdata) {
    var zip = jQuery("#zipinput").val();
    jQuery.ajax({ 
      url: "https://nominatim.openstreetmap.org/?format=json&countrycodes=de,ch,at&limit=1&addressdetails=0&postalcode="+zip,
      table: this,
      jsonp: 'json_callback',
      dataType: "jsonp",
      success: function(address){
        if ( typeof address !== 'undefined' && address.length > 0 ) {
          if (eventdata.data ) {
            eventdata.data.calculateDistance(Number(address[0].lat), Number(address[0].lon));
          } else {
            this.table.calculateDistance(Number(address[0].lat), Number(address[0].lon));
          }
          console.log(eventdata.data);
        }
      },
      error: function(XMLHttpRequest, textStatus, errorThrown){
        alert("Error" + textStatus);
      }
    });
  },

  calculateDistance: function(lat, lon) {
    var radius = 6371000; //Earth radius in meters
    Number.prototype.toRad = function() {
      return this * Math.PI / 180;
    }
    _.each(this.communityData, function(item, key, list){
      if ( item.location.lat && item.location.lon && typeof item.location.lat === "number" && typeof item.location.lon === "number" ) {
        var sinDeltaLat = Math.sin((item.location.lat-lat).toRad()/2);
        var sinDeltaLon = Math.sin((item.location.lon-lon).toRad()/2);
        item.distance = 2*radius*Math.asin(Math.sqrt(sinDeltaLat*sinDeltaLat + Math.cos(lat.toRad()) * Math.cos(item.location.lat.toRad()) * sinDeltaLon * sinDeltaLon));
      } else {
        item.distance = radius;
      }
      item.distance = Math.round(item.distance/1000);
    });
    this.communityData = _.sortBy(this.communityData, function(o) {return o.distance;});
    _.each(this.communityData, function(item, key, list) {
      item.rank = key;
    });
    
    this.communityDataDisplay = this.communityData.slice(0);
    this.communityDataDisplay.splice(3);
    this.printTable();
  },

  reset: function() {
    this.communityData = _.sortBy(this.communityData, function(o){ return o.location.city;});
    _.each(this.communityData, function(item, key, list) {
      item.rank = 0;
      item.distance = 40008000;
    });
    this.communityDataDisplay = this.communityData.slice(0);
    this.printTable();
  },

  printTable: function() {
    _.templateSettings.variable = "items";
    var templ = _.template(this.tableTemplate);
    jQuery("table.community-table tbody").html(templ(this.communityDataDisplay));
    jQuery("#ctable").footable();
  }

}


/*
   jQuery(document).ready(function(){
   _.templateSettings.variable = "items";
   var templ = _.template(tableTemplate);
   jQuery("table.community-table tbody").html(templ(rows));
   jQuery("#ctable").footable();
   } ),
   });
   });*/
