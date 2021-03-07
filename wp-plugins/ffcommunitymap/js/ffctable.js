//"use strict";
var FFCTABLE = {


  init: function (myTargetId, myUrl, myEmail, myNumberOfCommunities) {
    newTable = Object.create(this);
    newTable.targetId = myTargetId;
    newTable.tableTemplate = jQuery("script.template#" + newTable.targetId).html();
    newTable.url = myUrl;
    newTable.email = myEmail;
    newTable.numberOfCommunities = myNumberOfCommunities;
    newTable.communityData = null;
    newTable.communityDataDisplay = null;
    newTable.addressFound = null;
    newTable.footable = null;
    return newTable;
  },

  getData: function(callback) {
    jQuery.ajax({
      url: this.url,
      callback: callback,
      table: this,
      dataType: "jsonp",
      success: function(Response){
        var rows = Response;
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
          if (item.contact.irc && !item.contact.irc.match(/^irc([s]?):.*/)) {
            item.contact.irc = "irc:" + item.contact.irc;
          }
          if (item.contact.jabber && !item.contact.jabber.match(/^jabber:.*/)) {
            item.contact.jabber = "xmpp:" + item.contact.jabber;
          }
          if (item.contact.identica && !item.contact.identica.match(/^identica:.*/)) {
            item.contact.identica = "identica:" + item.contact.identica;
          }
          if (item.contact.matrix && !item.contact.matrix.match(/^http([s]?):\/\/.*/)) {
            item.contact.matrix = "https://" + item.contact.matrix;
          }


          item.contacts =  [];
          if (item.url) {
            item.contacts.push({
              type: 'home',
               url : item.url
            });
          }

          if (item.contact.email) {
            item.contacts.push({
              type: 'envelope',
              url : item.contact.email
            });
          }

          if (item.contact.ml) {
            item.contacts.push({
              type: 'comments-o',
              url : item.contact.ml
            });
          }

          if (item.contact.facebook) {
            item.contacts.push({
              type: 'facebook',
              url : item.contact.facebook
            });
          }

          if (item.contact.twitter) {
            item.contacts.push({
              type: 'twitter',
              url : item.contact.twitter
            });
          }

          if (item.contact.irc) {
            item.contacts.push({
              type: 'commenting-o',
              url : item.contact.irc
            });
          }

          if (item.contact.jabber) {
            item.contacts.push({
              type: 'xmpp',
              url : item.contact.jabber
            });
          }

          if (item.contact.identica) {
            item.contacts.push({
              type: 'identica',
              url : item.contact.identicy
            });
          }

          if (item.contact.matrix) {
            item.contacts.push({
              type: 'matrix-org',
              url : item.contact.matrix
            });
          }

          item.distance = 40008000;
          item.rank = 0;
        });
        console.log(rows);
        this.table.communityData = rows;
        this.table.communityDataDisplay = rows;
        this.callback(this.table);

      },
      error: function(XMLHttpRequest, textStatus, errorThrown){
        alert("Error" + textStatus);
      }

    });
  },

  getDistanceByZip: function(eventdata, callback) {
    var zip = jQuery("#zipinput").val().replace(/[^a-z0-9äöáéíóúñü \.,_-]/gim,"");
    var email;
    if (eventdata.data) {
      email = eventdata.data.email;
    } else {
      email = this.email;
    }
    jQuery.ajax({
      url: "https://nominatim.openstreetmap.org/?format=json&limit=1&addressdetails=0&q="+zip+"&email="+email,
      table: this,
      callback: callback,
      jsonp: 'json_callback',
      dataType: "jsonp",
      success: function(address){
        if ( typeof address !== 'undefined' && address.length > 0 ) {
          jQuery("#zipresult").text("Ergebnis: " +  address[0].display_name);
          if (eventdata.data ) {
            eventdata.data.calculateDistance(Number(address[0].lat), Number(address[0].lon));
            eventdata.data.addressFound = address[0];
            this.callback(eventdata.data, "calculateDistance");
          } else {
            this.table.calculateDistance(Number(address[0].lat), Number(address[0].lon));
            this.table.addressFound = address[0];
            this.callback(this.table, "calculateDistance");
          }
        } else {
          jQuery("#zipresult").text("Leider kein Ergebnis");
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
    this.communityDataDisplay.splice(this.numberOfCommunities);
  },

  reset: function(data, callback) {
    if ( ! data.data) {
      data.data = this;
    }
    data.data.communityData = _.sortBy(data.data.communityData, function(o){ return o.location.city;});
    _.each(data.data.communityData, function(item, key, list) {
      item.rank = 0;
      item.distance = "";
    });
    data.data.communityDataDisplay = data.data.communityData.slice(0);
    callback(data.data, "reset");
  },

  printTable: function(data, type) {
    if ( ! data.data ) {
      data = this.table;
    } else if (data.data){
      data = data.data;
    }
    _.templateSettings.variable = "items";
    var templ = _.template(data.tableTemplate);
    if (type == "calculateDistance") {
      jQuery('#hcity').data('sorted', 'false');
      jQuery('#hdistance').data('sorted', 'true');
      jQuery('#hdistance').data('direction', 'ASC');
      jQuery('#hdistance').data('visible', 'true');
    } else if (type == "reset") {
      jQuery("#zipresult").text("");
      jQuery('#hdistance').data('sorted', 'false');
      jQuery('#hcity').data('sorted', 'true');
      jQuery('#hdistance').data('visible', 'false');
    }
    jQuery("table.community-table tbody").html(templ(data.communityDataDisplay));
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
