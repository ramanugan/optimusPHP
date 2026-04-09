var timer_sessionCheck;
function notifyCheck(e) {
  switch (e) {
    case "session_check":
      if (0 == gsValues.session_check) break;
      clearTimeout(timer_sessionCheck);
      $.ajax({
        type: "POST",
        url: "../func/fn_connect.php",
        data: {
          cmd: "session_check",
        },
        cache: !1,
        error: function (e, t) {
          timer_sessionCheck = setTimeout(
            "notifyCheck('session_check');",
            1e3 * gsValues.session_check
          );
        },
        success: function (e) {
          "false" == e
            ? connectLogout()
            : (timer_sessionCheck = setTimeout(
                "notifyCheck('session_check');",
                1e3 * gsValues.session_check
              ));
        },
      });
  }
}
function datalistBottomShowPanel() {
  (document.getElementById("bottom_panel").style.display = "block"),
    (document.getElementById("map").style.bottom = "142px"),
    map.invalidateSize(!0);
}
function datalistBottomHidePanel() {
  (document.getElementById("bottom_panel").style.display = "none"),
    (document.getElementById("map").style.bottom = "0px"),
    map.invalidateSize(!0);
}
function datalistBottomShowData(e, t, a) {
  datalistBottomShowPanel(),
    (document.getElementById("bottom_panel_datalist_object_name").innerHTML =
      la.OBJECT + " " + settingsObjectData[t].name);
  var o = "odd",
    s = "",
    n = "",
    r = settingsUserData.datalist_items.split(",");
  switch (e) {
    case "object":
      n = "bottom_panel_datalist_object_data_list";
  }
  var l = new Array();
  switch (e) {
    case "object":
      if (-1 !== r.indexOf("odometer")) {
        var c = getObjectOdometer(t, !1);
        -1 != c &&
          l.push({
            icon: "icon-odometer",
            name: la.ODOMETER,
            value: c + " " + la.UNIT_DISTANCE,
          });
      }
      if (-1 !== r.indexOf("engine_hours")) {
        var d = getObjectEngineHours(t, !1);
        -1 != d &&
          l.push({
            icon: "icon-engine-hours",
            name: la.ENGINE_HOURS,
            value: d,
          });
      }
      if (-1 !== r.indexOf("status")) {
        var u = objectsData[t].status_string;
        "" != u &&
          l.push({
            icon: "icon-status",
            name: la.STATUS,
            value: u,
          });
      }
      var m = settingsObjectData[t].custom_fields;
      for (var p in m) {
        var g = m[p];
        "true" == g.data_list &&
          l.push({
            icon: "icon-default-custom-fields",
            name: g.name,
            value: textToLinks(g.value),
          });
      }
  }
  if (-1 !== r.indexOf("model")) {
    var _ = settingsObjectData[t].model;
    "" != _ &&
      l.push({
        icon: "icon-model",
        name: la.MODEL,
        value: _,
      });
  }
  if (-1 !== r.indexOf("vin")) {
    var y = settingsObjectData[t].vin;
    "" != y &&
      l.push({
        icon: "icon-vin",
        name: la.VIN,
        value: y,
      });
  }
  if (-1 !== r.indexOf("plate_number")) {
    var v = settingsObjectData[t].plate_number;
    "" != v &&
      l.push({
        icon: "icon-plate-number",
        name: la.PLATE,
        value: v,
      });
  }
  if (-1 !== r.indexOf("sim_number")) {
    var h = settingsObjectData[t].sim_number;
    "" != h &&
      l.push({
        icon: "icon-sim",
        name: la.SIM_CARD_NUMBER,
        value: h,
      });
  }
  if (-1 !== r.indexOf("driver")) {
    var b = getDriver(t, a.params);
    if (0 != b) {
      var D =
        '<a href="#" onclick="utilsShowDriverInfo(\'' +
        b.driver_id +
        "');\">" +
        b.name +
        "</a>";
      l.push({
        icon: "icon-user",
        name: la.DRIVER,
        value: D,
      });
    }
  }
  if (-1 !== r.indexOf("trailer")) {
    var f = getTrailer(t, a.params);
    if (0 != f) {
      var E =
        '<a href="#" onclick="utilsShowTrailerInfo(\'' +
        f.trailer_id +
        "');\">" +
        f.name +
        "</a>";
      l.push({
        icon: "icon-trailer",
        name: la.TRAILER,
        value: E,
      });
    }
  }
  l = sortArrayByElement(l, "name");
  var I = "";
  for (i = 0; i < l.length; i += 1) {
    I += datalistBottomAddItem(
      (o = "odd" == o ? "even" : "odd"),
      (P = l[i]).icon,
      P.name,
      P.value
    );
  }
  s += I;
  var j = new Array();
  switch (e) {
    case "object":
      -1 !== r.indexOf("time_position") &&
        j.push({
          icon: "icon-time",
          name: la.TIME_POSITION,
          value: a.dt_tracker,
        }),
        -1 !== r.indexOf("time_server") &&
          j.push({
            icon: "icon-time",
            name: la.TIME_SERVER,
            value: a.dt_server,
          });
  }
  switch (e) {
    case "object":
      if (
        -1 !== r.indexOf("address") &&
        1 == gsValues.address_display_object_data_list
      ) {
        geocoderGetAddress(a.lat, a.lng, function (e) {
          (document.getElementById(n + "_address").innerHTML = e),
            (document.getElementById(n + "_address").title = e),
            (objectsData[t].address = e);
        });
        var T =
          '<span id="' + n + '_address">' + objectsData[t].address + "</span>";
        j.push({
          icon: "icon-address",
          name: la.ADDRESS,
          value: T,
        });
      }
  }
  -1 !== r.indexOf("position") &&
    j.push({
      icon: "icon-marker",
      name: la.POSITION,
      value: urlPosition(a.lat, a.lng),
    }),
    -1 !== r.indexOf("speed") &&
      j.push({
        icon: "icon-speed",
        name: la.SPEED,
        value: a.speed + " " + la.UNIT_SPEED,
      }),
    -1 !== r.indexOf("altitude") &&
      j.push({
        icon: "icon-altitude ",
        name: la.ALTITUDE,
        value: a.altitude + " " + la.UNIT_HEIGHT,
      }),
    -1 !== r.indexOf("angle") &&
      j.push({
        icon: "icon-angle ",
        name: la.ANGLE,
        value: a.angle + " &deg;",
      }),
    (j = sortArrayByElement(j, "name"));
  var L = "";
  for (i = 0; i < j.length; i += 1) {
    L += datalistBottomAddItem(
      (o = "odd" == o ? "even" : "odd"),
      (P = j[i]).icon,
      P.name,
      P.value
    );
  }
  s += L;
  var k = new Array(),
    R = settingsObjectData[t].sensors;
  for (var p in R) {
    var B = R[p];
    if ("true" == B.data_list) {
      var S = "icon-default-sensor";
      if ("batt" == B.type) {
        if (((S = "icon-battery-lev-3"), "percentage" == B.result_type))
          (O = getSensorValue(a.params, B)).value <= 25
            ? (S = "icon-battery-lev-0")
            : O.value <= 50
            ? (S = "icon-battery-lev-1")
            : O.value <= 75
            ? (S = "icon-battery-lev-2")
            : O.value <= 100
            ? (S = "icon-battery-lev-3")
            : O.value > 100 && (S = "icon-battery-lev-3");
      } else
        "di" == B.type
          ? (S = "icon-di")
          : "do" == B.type
          ? (S = "icon-do")
          : "fuel" == B.type
          ? (S = "icon-fuel")
          : "fuelsumup" == B.type
          ? (S = "icon-fuel")
          : "gsm" == B.type
          ? (S = "icon-gsm")
          : "gps" == B.type
          ? (S = "icon-gps")
          : "acc" == B.type
          ? (S = "icon-engine")
          : "temp" == B.type && (S = "icon-temperature");
      if ("fuelsumup" == B.type) {
        var O = getSensorValueFuelLevelSumUp(t, a.params, B);
        k.push({
          icon: S,
          name: B.name,
          value: O.value_full,
        });
      } else {
        O = getSensorValue(a.params, B);
        k.push({
          icon: S,
          name: B.name,
          value: O.value_full,
        });
      }
    }
  }
  k = sortArrayByElement(k, "name");
  var w = "";
  for (i = 0; i < k.length; i += 1) {
    w += datalistBottomAddItem(
      (o = "odd" == o ? "even" : "odd"),
      (P = k[i]).icon,
      P.name,
      P.value
    );
  }
  s += w;
  var M = new Array();
  switch (e) {
    case "object":
      var A = objectsData[t].service;
      for (var p in A)
        "true" == A[p].data_list &&
          M.push({
            icon: "icon-warning",
            name: A[p].name,
            value: A[p].status,
          });
  }
  M = sortArrayByElement(M, "name");
  var N = "";
  for (i = 0; i < M.length; i += 1) {
    var P;
    N += datalistBottomAddItem(
      (o = "odd" == o ? "even" : "odd"),
      (P = M[i]).icon,
      P.name,
      P.value
    );
  }
  (s += N), (document.getElementById(n).innerHTML = s);
}
function datalistBottomAddItem(e, t, a, o) {
  var s = '<div class="datalist-item ' + e + '">';
  return (
    (s +=
      0 == t
        ? '<span class="datalist-item-icon"></span>'
        : '<span class="datalist-item-icon ' + t + '"></span>'),
    (s += '<div class="datalist-item-name">' + a + "</div>"),
    (s += '<div class="datalist-item-value">' + o + "</div>"),
    (s += "</div>")
  );
}
function datalistClear(e) {
  e;
}
var cmdData = new Array();
function cmdTemplateLoadData() {
  $.ajax({
    type: "POST",
    url: "../func/fn_cmd.php",
    data: {
      cmd: "load_cmd_template_data",
    },
    dataType: "json",
    cache: !1,
    success: function (e) {
      (cmdData.cmd_templates = e), cmdTemplateList();
    },
  });
}
function cmdTemplateList() {
  var e = document.getElementById("page_cmd_object_list").value;
  if (null != settingsObjectData[e]) {
    var t = settingsObjectData[e].device;
    (a = document.getElementById("page_cmd_template_list")),
      (o = a.getElementsByTagName("optgroup"));
    for (s = o.length - 1; s >= 0; s--) {
      a.removeChild(o[s]);
    }
    for (var n in ((a.options.length = 0), gsValues.protocol_list))
      if (
        gsValues.protocol_list[n].name == t &&
        null != gsValues.protocol_list[n].cmd &&
        "" != gsValues.protocol_list[n].cmd
      ) {
        var r = gsValues.protocol_list[n].cmd.split(",");
        if (r.length > 0)
          (i = $('<optgroup label="' + la.DEFAULT + '" />')).appendTo(a);
        for (r.sort(), s = 0; s < r.length; s += 1)
          a.options.add(new Option(la[r[s].toUpperCase()], r[s]));
        break;
      }
    var l = new Array(),
      i = $('<optgroup label="' + la.CUSTOM + '" />');
    for (var n in (i.appendTo(a),
    a.options.add(new Option(la.CUSTOM, "")),
    cmdData.cmd_templates)) {
      var c = cmdData.cmd_templates[n];
      var gateway = document.getElementById("page_cmd_gateway").value;
      if (c.gateway == gateway) {
        c.protocol.toLowerCase() == t.toLowerCase()
          ? l.push({
              name: c.name,
              key: n,
            })
          : "" == c.protocol.toLowerCase() &&
            l.push({
              name: c.name,
              key: n,
            });
      }
    }
    for (l = sortArrayByElement(l, "name"), s = 0; s < l.length; s += 1)
      a.options.add(new Option(l[s].name, l[s].key));
  }
  cmdReset();
}
function cmdGatewayList() {
  var e = document.getElementById("page_cmd_object_list").value;
  var t = settingsObjectData[e].device;
  for (var n in ((a.options.length = 0), gsValues.protocol_list))
    if (
      gsValues.protocol_list[n].name == t &&
      null != gsValues.protocol_list[n].cmd &&
      "" != gsValues.protocol_list[n].cmd
    ) {
      var r = gsValues.protocol_list[n].cmd.split(",");
      if (r.length > 0)
        (i = $('<optgroup label="' + la.DEFAULT + '" />')).appendTo(a);
      for (r.sort(), s = 0; s < r.length; s += 1)
        a.options.add(new Option(la[r[s].toUpperCase()], r[s]));
      break;
    }
  var l = new Array(),
    i = $("");
  for (var n in (i.appendTo(a),
  a.options.add(new Option(la.CUSTOM, "")),
  cmdData.cmd_templates)) {
    var c = cmdData.cmd_templates[n];
    var gateway = document.getElementById("page_cmd_gateway").value;
    if (c.gateway == gateway) {
      c.protocol.toLowerCase() == t.toLowerCase()
        ? l.push({
            name: c.name,
            key: n,
          })
        : "" == c.protocol.toLowerCase() &&
          l.push({
            name: c.name,
            key: n,
          });
    }
  }
  for (l = sortArrayByElement(l, "name"), s = 0; s < l.length; s += 1)
    a.options.add(new Option(l[s].name, l[s].key));
  document.getElementById("page_cmd_cmd").value = "";
}
function cmdTemplateSwitch() {
  var e = document.getElementById("page_cmd_template_list").value,
    t = "",
    a = "";
  for (var o in gsValues.cmd_default) {
    var s = gsValues.cmd_default[o];
    if (e == s.name) {
      (t = s.name), null != s.params && (a = s.params);
      break;
    }
  }
  "" != t
    ? ((document.getElementById("page_cmd_gateway").disabled = !0),
      (document.getElementById("page_cmd_type").disabled = !0),
      (document.getElementById("page_cmd_gateway").value = "gprs"),
      (document.getElementById("page_cmd_type").value = "ascii"),
      "" != a && (t = t + "," + a),
      (document.getElementById("page_cmd_cmd").value = t))
    : "" != e
    ? ((document.getElementById("page_cmd_gateway").disabled = !1),
      (document.getElementById("page_cmd_type").disabled = !1),
      (document.getElementById("page_cmd_gateway").value =
        cmdData.cmd_templates[e].gateway),
      (document.getElementById("page_cmd_type").value =
        cmdData.cmd_templates[e].type),
      (document.getElementById("page_cmd_cmd").value =
        cmdData.cmd_templates[e].cmd))
    : ((document.getElementById("page_cmd_gateway").disabled = !1),
      (document.getElementById("page_cmd_type").disabled = !1),
      (document.getElementById("page_cmd_gateway").value = "gprs"),
      (document.getElementById("page_cmd_type").value = "ascii"),
      (document.getElementById("page_cmd_cmd").value = ""));
}
function cmdReset() {
  (document.getElementById("page_cmd_gateway").disabled = !1),
    (document.getElementById("page_cmd_type").disabled = !1),
    (document.getElementById("page_cmd_template_list").value = ""),
    (document.getElementById("page_cmd_gateway").value = "gprs"),
    (document.getElementById("page_cmd_type").value = "ascii"),
    (document.getElementById("page_cmd_cmd").value = "");
}
function cmdSend() {
  var e = document.getElementById("page_cmd_object_list").value,
    t = $("#page_cmd_template_list :selected").text(),
    a = document.getElementById("page_cmd_gateway").value,
    o = document.getElementById("page_cmd_type").value,
    s = document.getElementById("page_cmd_cmd").value;
  if ("" != e)
    if ("" != s) {
      if ("sms" == a)
        if ("" == settingsObjectData[e].sim_number)
          return void bootbox.alert(la.OBJECT_SIM_CARD_NUMBER_IS_NOT_SET);
      "hex" != o || ((s = s.toUpperCase()), isHexValid(s))
        ? cmdExec(e, t, a, o, s)
        : bootbox.alert(la.COMMAND_HEX_NOT_VALID);
    } else bootbox.alert(la.COMMAND_CANT_BE_EMPTY);
}
function cmdExec(e, t, a, o, s) {
  if (utilsCheckPrivileges("viewer")) {
    if ((loadingData(!0), "gprs" == a))
      var n = {
        cmd: "exec_cmd_gprs",
        imei: e,
        name: t,
        type: o,
        cmd_: s,
      };
    else
      n = {
        cmd: "exec_cmd_sms",
        imei: e,
        name: t,
        cmd_: s,
      };
    $.ajax({
      type: "POST",
      url: "../func/fn_cmd.php",
      data: n,
      success: function (e) {
        loadingData(!1),
          "OK" == e
            ? (cmdReset(), bootbox.alert(la.COMMAND_SENT_FOR_EXECUTION))
            : "ERROR_NOT_SENT" == e &&
              bootbox.alert(la.UNABLE_TO_SEND_SMS_MESSAGE);
      },
      error: function (e, t) {
        loadingData(!1);
      },
    });
  }
}
cmdData.cmd_templates = new Array();
var url = window.location.href.split("#");
function fixGuiSize() {
  if (
    (window.map && map.invalidateSize(!1),
    "block" == document.getElementById("graph_panel").style.display)
  ) {
    var e = document.getElementById("graph_panel").offsetHeight;
    $("#graph_panel_plot").css("height", e - 80);
  }
}
function showMenuPage() {
  (document.getElementById("page_menu").style.display = "block"),
    $(document).mouseup(function (e) {
      hideMenuPage(), $(document).unbind("mouseup");
    });
}
function hideMenuPage() {
  (document.getElementById("page_menu").style.display = "none"),
    switchPage(gsValues.menu);
}
function switchPage(e) {
  if ("history" != e || utilsCheckPrivileges("history"))
    if ("object_control" != e || utilsCheckPrivileges("object_control")) {
      switch (
        ((document.getElementById("page_map").style.display = "none"),
        (document.getElementById("page_objects").style.display = "none"),
        (document.getElementById("page_object_details").style.display = "none"),
        (document.getElementById("page_events").style.display = "none"),
        (document.getElementById("page_places").style.display = "none"),
        (document.getElementById("page_history").style.display = "none"),
        (document.getElementById("page_cmd").style.display = "none"),
        (document.getElementById("page_settings").style.display = "none"),
        "menu" != e && (gsValues.menu = e),
        e)
      ) {
        case "menu":
          (document.getElementById("page_title").innerHTML = ""),
            showMenuPage();
          break;
        case "map":
          (document.getElementById("page_title").innerHTML = la.MAP),
            (document.getElementById("page_map").style.display = "block"),
            fixGuiSize();
          break;
        case "objects":
          (document.getElementById("page_title").innerHTML = la.OBJECTS),
            (document.getElementById("page_objects").style.display = "block");
          break;
        case "object_details":
          (document.getElementById("page_title").innerHTML = la.OBJECTS),
            (document.getElementById("page_object_details").style.display =
              "block");
          break;
        case "events":
          (document.getElementById("page_title").innerHTML = la.EVENTS),
            (document.getElementById("page_events").style.display = "block");
          break;
        case "places":
          (document.getElementById("page_title").innerHTML =
            la.PLACES + " - " + la.MARKERS),
            (document.getElementById("page_places").style.display = "block");
          break;
        case "history":
          (document.getElementById("page_title").innerHTML = la.HISTORY),
            (document.getElementById("page_history").style.display = "block");
          break;
        case "object_control":
          (document.getElementById("page_title").innerHTML = la.OBJECT_CONTROL),
            (document.getElementById("page_cmd").style.display = "block");
          break;
        case "settings":
          (document.getElementById("page_title").innerHTML = la.SETTINGS),
            (document.getElementById("page_settings").style.display = "block");
      }
      (document.getElementById("map_layer").style.display =
        "map" == e ? "" : "none"),
        (document.getElementById("event_list_page").style.display =
          "events" == e ? "" : "none"),
        "places" == e
          ? showPlacesMarkersPanel()
          : ((document.getElementById("marker_list_page").style.display =
              "none"),
            (document.getElementById("route_list_page").style.display = "none"),
            (document.getElementById("zone_list_page").style.display = "none"));
    } else hideMenuPage();
  else hideMenuPage();
}
function addPopupToMap(e, t, a, o, s) {
  if ("" != s && o != s) {
    if (1 == gsValues.map_popup_detailed)
      var n = 'style="display:none;"',
        r = "";
    else (n = ""), (r = 'style="display:none;"');
    (o = '<div id="popup_short" ' + n + ">" + o),
      (o +=
        '<div style="width:100%; text-align: right;"><a href="#" class="" onClick="switchPopupDetailed(true)">' +
        la.DETAILED +
        "</a></div>"),
      (o += "</div>"),
      (o += '<div id="popup_detailed" ' + r + ">" + s),
      (o +=
        '<div style="width:100%; text-align: right;"><a href="#" class="" onClick="switchPopupDetailed(false)">' +
        la.SHORT +
        "</a></div>"),
      (o += "</div>");
  }
  mapPopup = L.popup({
    offset: a,
  })
    .setLatLng([e, t])
    .setContent(o)
    .openOn(map);
}
function switchPopupDetailed(e) {
  switch (e) {
    case !1:
      (document.getElementById("popup_short").style.display = ""),
        (document.getElementById("popup_detailed").style.display = "none"),
        (gsValues.map_popup_detailed = !1);
      break;
    case !0:
      (document.getElementById("popup_short").style.display = "none"),
        (document.getElementById("popup_detailed").style.display = ""),
        (gsValues.map_popup_detailed = !0);
  }
}
function destroyMapPopup() {
  map.closePopup();
}
function loadingData(e) {
  document.getElementById("loading_data_panel").style.display =
    1 == e ? "" : "none";
}
function loadObjectMapMarkerIcons() {
  var e = new Array();
  for (var t in settingsObjectData) {
    var a = settingsObjectData[t];
    e.push(a.icon);
  }
  for (e = uniqueArray(e), i = 0; i < e.length; i++) {
    var o = e[i],
      s = "../" + e[i],
      n = settingsUserData.map_is;
    mapMarkerIcons[o] = L.icon({
      iconUrl: s,
      iconSize: [28 * n, 28 * n],
      iconAnchor: [14 * n, 14 * n],
      popupAnchor: [0, 0],
    });
  }
}
function createCluster(e) {
  var t = settingsUserData.map_is;
  if ("objects" == e)
    var a = "../img/markers/clusters/objects.svg",
      o = "marker-cluster";
  else {
    if ("markers" != e) return !1;
    (a = "../img/markers/clusters/markers.svg"), (o = "marker-cluster");
  }
  if (1 == gsValues.map_clusters) var s = gsValues.map_max_zoom + 1;
  else s = gsValues.map_min_zoom;
  return new L.MarkerClusterGroup({
    spiderfyDistanceMultiplier: 2 * t,
    spiderfyOnMaxZoom: !0,
    showCoverageOnHover: !1,
    maxClusterRadius: 60,
    disableClusteringAtZoom: s,
    iconCreateFunction: function (e) {
      var s = e.getChildCount(),
        n = " cluster-";
      return (
        (n += s < 10 ? "small" : s < 100 ? "medium" : "large"),
        L.divIcon({
          html: '<div><img src="' + a + '"><span>' + s + "</span></div>",
          className: o + n,
          iconSize: L.point(40 * t, 40 * t),
          iconAnchor: [14 * t, 14 * t],
          popupAnchor: [40 * t, 0 * t],
        })
      );
    },
  });
}
function mapViewControls() {
  return L.Control.extend({
    options: {
      position: "topleft",
    },
    onAdd: function (e) {
      var t = L.DomUtil.create("div", "leaflet-control leaflet-bar");
      (linkObjects = L.DomUtil.create("a", "", t)),
        (linkObjects.id = "map_control_objects"),
        (linkObjects.href = "#"),
        (linkObjects.title = la.ENABLE_DISABLE_OBJECTS),
        (linkObjects.className = ""),
        (iconObjects = L.DomUtil.create("span", "", linkObjects)),
        (iconObjects.className = "icon-objects");
      var a = L.DomEvent.stopPropagation;
      L.DomEvent.on(linkObjects, "dblclick", a),
        L.DomEvent.on(linkObjects, "mousedown", a),
        L.DomEvent.on(linkObjects, "click", function (t) {
          1 == e.hasLayer(mapLayers.realtime)
            ? (e.removeLayer(mapLayers.realtime),
              (iconObjects.className = "icon-objects disabled"),
              (gsValues.map_objects = !1))
            : (e.addLayer(mapLayers.realtime),
              (iconObjects.className = "icon-objects"),
              (gsValues.map_objects = !0));
        }),
        (linkObjectLabels = L.DomUtil.create("a", "", t)),
        (linkObjectLabels.id = "map_control_object_labels"),
        (linkObjectLabels.href = "#"),
        (linkObjectLabels.title = la.ENABLE_DISABLE_OBJECT_LABELS),
        (linkObjectLabels.className = ""),
        (iconObjectLabels = L.DomUtil.create("span", "", linkObjectLabels)),
        (iconObjectLabels.className = "icon-text");
      a = L.DomEvent.stopPropagation;
      L.DomEvent.on(linkObjectLabels, "dblclick", a),
        L.DomEvent.on(linkObjectLabels, "mousedown", a),
        L.DomEvent.on(linkObjectLabels, "click", function (e) {
          if (1 == gsValues.map_object_labels) {
            for (var t in objectsData)
              objectsData[t].layers.marker.closeTooltip();
            (iconObjectLabels.className = "icon-text disabled"),
              (gsValues.map_object_labels = !1);
          } else {
            for (var t in objectsData)
              objectsData[t].layers.marker.openTooltip();
            (iconObjectLabels.className = "icon-text"),
              (gsValues.map_object_labels = !0);
          }
        }),
        (linkMarkers = L.DomUtil.create("a", "", t)),
        (linkMarkers.id = "map_control_markers"),
        (linkMarkers.href = "#"),
        (linkMarkers.title = la.ENABLE_DISABLE_MARKERS),
        (linkMarkers.className = ""),
        (iconMarkers = L.DomUtil.create("span", "", linkMarkers)),
        (iconMarkers.className = "icon-markers");
      a = L.DomEvent.stopPropagation;
      L.DomEvent.on(linkMarkers, "dblclick", a),
        L.DomEvent.on(linkMarkers, "mousedown", a),
        L.DomEvent.on(linkMarkers, "click", function (t) {
          1 == e.hasLayer(mapLayers.places_markers)
            ? (e.removeLayer(mapLayers.places_markers),
              (iconMarkers.className = "icon-markers disabled"),
              (gsValues.map_markers = !1))
            : (e.addLayer(mapLayers.places_markers),
              (iconMarkers.className = "icon-markers"),
              (gsValues.map_markers = !0));
        }),
        (linkRoutes = L.DomUtil.create("a", "", t)),
        (linkRoutes.id = "map_control_routes"),
        (linkRoutes.href = "#"),
        (linkRoutes.title = la.ENABLE_DISABLE_ROUTES),
        (linkRoutes.className = ""),
        (iconRoutes = L.DomUtil.create("span", "", linkRoutes)),
        (iconRoutes.className = "icon-routes");
      a = L.DomEvent.stopPropagation;
      L.DomEvent.on(linkRoutes, "dblclick", a),
        L.DomEvent.on(linkRoutes, "mousedown", a),
        L.DomEvent.on(linkRoutes, "click", function (t) {
          1 == e.hasLayer(mapLayers.places_routes)
            ? (e.removeLayer(mapLayers.places_routes),
              (iconRoutes.className = "icon-routes disabled"),
              (gsValues.map_routes = !1))
            : (e.addLayer(mapLayers.places_routes),
              (iconRoutes.className = "icon-routes"),
              (gsValues.map_routes = !0));
        }),
        (linkZones = L.DomUtil.create("a", "", t)),
        (linkZones.id = "map_control_zones"),
        (linkZones.href = "#"),
        (linkZones.title = la.ENABLE_DISABLE_ZONES),
        (linkZones.className = ""),
        (iconZones = L.DomUtil.create("span", "", linkZones)),
        (iconZones.className = "icon-zones");
      a = L.DomEvent.stopPropagation;
      L.DomEvent.on(linkZones, "dblclick", a),
        L.DomEvent.on(linkZones, "mousedown", a),
        L.DomEvent.on(linkZones, "click", function (t) {
          1 == e.hasLayer(mapLayers.places_zones)
            ? (e.removeLayer(mapLayers.places_zones),
              (iconZones.className = "icon-zones disabled"),
              (gsValues.map_zones = !1))
            : (e.addLayer(mapLayers.places_zones),
              (iconZones.className = "icon-zones"),
              (gsValues.map_zones = !0));
        }),
        (linkKML = L.DomUtil.create("a", "", t)),
        (linkKML.id = "map_control_kml"),
        (linkKML.href = "#"),
        (linkKML.title = la.ENABLE_DISABLE_KML),
        (linkKML.className = ""),
        (iconRoutes = L.DomUtil.create("span", "", linkKML)),
        (iconRoutes.className = "icon-kml");
      a = L.DomEvent.stopPropagation;
      L.DomEvent.on(linkKML, "dblclick", a),
        L.DomEvent.on(linkKML, "mousedown", a),
        L.DomEvent.on(linkKML, "click", function (t) {
          1 == e.hasLayer(mapLayers.kml)
            ? (e.removeLayer(mapLayers.kml),
              (iconRoutes.className = "icon-kml disabled"),
              (gsValues.map_kml = !1))
            : (e.addLayer(mapLayers.kml),
              (iconRoutes.className = "icon-kml"),
              (gsValues.map_kml = !0));
        }),
        (linkClusters = L.DomUtil.create("a", "", t)),
        (linkClusters.id = "map_control_clusters"),
        (linkClusters.href = "#"),
        (linkClusters.title = la.ENABLE_DISABLE_CLUSTERS),
        (linkClusters.className = ""),
        (iconClusters = L.DomUtil.create("span", "", linkClusters)),
        1 == gsValues.map_clusters
          ? (iconClusters.className = "icon-clusters")
          : (iconClusters.className = "icon-clusters disabled");
      a = L.DomEvent.stopPropagation;
      if (
        (L.DomEvent.on(linkClusters, "dblclick", a),
        L.DomEvent.on(linkClusters, "mousedown", a),
        L.DomEvent.on(linkClusters, "click", function (e) {
          1 == gsValues.map_clusters
            ? ((mapLayers.realtime.options.disableClusteringAtZoom =
                gsValues.map_min_zoom),
              (mapLayers.places_markers.options.disableClusteringAtZoom =
                gsValues.map_min_zoom),
              (iconClusters.className = "icon-clusters disabled"),
              (gsValues.map_clusters = !1))
            : ((mapLayers.realtime.options.disableClusteringAtZoom =
                gsValues.map_max_zoom + 1),
              (mapLayers.places_markers.options.disableClusteringAtZoom =
                gsValues.map_max_zoom + 1),
              (iconClusters.className = "icon-clusters"),
              (gsValues.map_clusters = !0)),
            objectAddAllToMap(),
            placesMarkerAddAllToMap();
        }),
        gsValues.map_google && gsValues.map_google_traffic)
      ) {
        (linkTraffic = L.DomUtil.create("a", "", t)),
          (linkTraffic.id = "map_control_traffic"),
          (linkTraffic.href = "#"),
          (linkTraffic.title = la.ENABLE_DISABLE_LIVE_TRAFFIC),
          (linkTraffic.className = ""),
          (iconTraffic = L.DomUtil.create("span", "", linkTraffic)),
          (iconTraffic.className = "icon-traffic disabled");
        a = L.DomEvent.stopPropagation;
        L.DomEvent.on(linkTraffic, "dblclick", a),
          L.DomEvent.on(linkTraffic, "mousedown", a),
          L.DomEvent.on(linkTraffic, "click", function (e) {
            1 == gsValues.map_traffic
              ? ((iconTraffic.className = "icon-traffic disabled"),
                (gsValues.map_traffic = !1),
                strMatches("gmap,ghyb,gter", gsValues.map_layer.toString()) &&
                  switchMapLayer(gsValues.map_layer))
              : strMatches("gmap,ghyb,gter", gsValues.map_layer.toString())
              ? ((iconTraffic.className = "icon-traffic"),
                (gsValues.map_traffic = !0),
                switchMapLayer(gsValues.map_layer))
              : bootbox.alert(la.LIVE_TRAFFIC_FOR_THIS_MAP_IS_NOT_AVAILABLE);
          });
      }
      return t;
    },
  });
}
function initGraph(e) {
  if (e) {
    (t = e.data), (a = e.units);
    if ("logic" == e.result_type) (o = !0), (s = !1);
    else (o = !1), (s = !1);
  } else
    var t = [],
      a = "",
      o = !1,
      s = !1;
  var n = {
    xaxis: {
      mode: "time",
      zoomRange: [3e4, 2592e6],
    },
    yaxis: {
      tickFormatter: function (t) {
        var o = "";
        return e && (o = Math.round(100 * t) / 100 + " " + a), o;
      },
      zoomRange: [0, 0],
      panRange: !1,
    },
    selection: {
      mode: "x",
    },
    crosshair: {
      mode: "x",
    },
    lines: {
      show: !0,
      lineWidth: 1,
      fill: !0,
      fillColor: "rgba(43,130,212,0.3)",
      steps: o,
    },
    series: {
      lines: {
        show: !0,
      },
      points: {
        show: s,
        radius: 1,
      },
    },
    colors: ["#2b82d4"],
    grid: {
      hoverable: !0,
      autoHighlight: !0,
      clickable: !0,
    },
    zoom: {
      interactive: !0,
      animate: !0,
      trigger: "dblclick",
      amount: 3,
    },
    pan: {
      interactive: !1,
      animate: !0,
    },
  };
  (historyGraphPlot = $.plot($("#graph_panel_plot"), [t], n)),
    $("#graph_panel_plot").unbind("plothover"),
    $("#graph_panel_plot").bind("plothover", function (e, o, s) {
      if (s) {
        var n = s.datapoint[0],
          r = historyRouteData.graph.data_index[n],
          l = historyRouteData.route[r].dt_tracker;
        s.datapoint[0].toFixed(2), s.datapoint[1].toFixed(2), t[r][1];
        document.getElementById("graph_panel_label").innerHTML =
          t[r][1] + " " + a + " - " + l;
      }
    });
}
function graphPanLeft() {
  historyGraphPlot.pan({
    left: -100,
  });
}
function graphPanRight() {
  historyGraphPlot.pan({
    left: 100,
  });
}
function graphZoomIn() {
  historyGraphPlot.zoom();
}
function graphZoomOut() {
  historyGraphPlot.zoomOut();
}
function initMap() {
  (map = L.map("map", {
    minZoom: gsValues.map_min_zoom,
    maxZoom: gsValues.map_max_zoom,
    editable: !0,
    tap: !0,
    zoomControl: !1,
  })),
    initSelectList("map_layer_list"),
    defineMapLayers(),
    (mapLayers.realtime = createCluster("objects")),
    mapLayers.realtime.addTo(map),
    (mapLayers.places_markers = createCluster("markers")),
    mapLayers.places_markers.addTo(map),
    (mapLayers.places_zones = L.layerGroup()),
    mapLayers.places_zones.addTo(map),
    (mapLayers.places_routes = L.layerGroup()),
    mapLayers.places_routes.addTo(map),
    (mapLayers.history = L.layerGroup()),
    mapLayers.history.addTo(map),
    (mapLayers.kml = L.layerGroup()),
    mapLayers.kml.addTo(map),
    map.addControl(
      L.control.zoom({
        zoomInText: "",
        zoomOutText: "",
        zoomInTitle: la.ZOOM_IN,
        zoomOutTitle: la.ZOOM_OUT,
      })
    ),
    (L.MapViewControls = mapViewControls()),
    map.addControl(new L.MapViewControls()),
    map.setView([gsValues.map_lat, gsValues.map_lng], gsValues.map_zoom),
    switchMapLayer(gsValues.map_layer),
    defineMapKMLLayers(map),
    gsValues.map_objects ||
      document.getElementById("map_control_objects").click(),
    gsValues.map_object_labels ||
      (iconObjectLabels.className = "icon-text disabled"),
    gsValues.map_markers ||
      document.getElementById("map_control_markers").click(),
    gsValues.map_routes ||
      document.getElementById("map_control_routes").click(),
    gsValues.map_zones || document.getElementById("map_control_zones").click(),
    gsValues.map_kml || document.getElementById("map_control_kml").click();
  var e = settingsUserData.map_is,
    t = 28 * e,
    a = 28 * e,
    o = 14 * e,
    s = 14 * e;
  (mapMarkerIcons.arrow_black = L.icon({
    iconUrl: "../img/markers/arrow-black.svg",
    iconSize: [t, a],
    iconAnchor: [o, s],
    popupAnchor: [0, 0],
  })),
    (mapMarkerIcons.arrow_blue = L.icon({
      iconUrl: "../img/markers/arrow-blue.svg",
      iconSize: [t, a],
      iconAnchor: [o, s],
      popupAnchor: [0, 0],
    })),
    (mapMarkerIcons.arrow_green = L.icon({
      iconUrl: "../img/markers/arrow-green.svg",
      iconSize: [t, a],
      iconAnchor: [o, s],
      popupAnchor: [0, 0],
    })),
    (mapMarkerIcons.arrow_grey = L.icon({
      iconUrl: "../img/markers/arrow-grey.svg",
      iconSize: [t, a],
      iconAnchor: [o, s],
      popupAnchor: [0, 0],
    })),
    (mapMarkerIcons.arrow_orange = L.icon({
      iconUrl: "../img/markers/arrow-orange.svg",
      iconSize: [t, a],
      iconAnchor: [o, s],
      popupAnchor: [0, 0],
    })),
    (mapMarkerIcons.arrow_purple = L.icon({
      iconUrl: "../img/markers/arrow-purple.svg",
      iconSize: [t, a],
      iconAnchor: [o, s],
      popupAnchor: [0, 0],
    })),
    (mapMarkerIcons.arrow_red = L.icon({
      iconUrl: "../img/markers/arrow-red.svg",
      iconSize: [t, a],
      iconAnchor: [o, s],
      popupAnchor: [0, 0],
    })),
    (mapMarkerIcons.arrow_yellow = L.icon({
      iconUrl: "../img/markers/arrow-yellow.svg",
      iconSize: [t, a],
      iconAnchor: [o, s],
      popupAnchor: [0, 0],
    })),
    (t = 28 * e),
    (a = 28 * e),
    (o = 14 * e),
    (s = 28 * e),
    (mapMarkerIcons.route_start = L.icon({
      iconUrl: "../img/markers/route-start.svg",
      iconSize: [t, a],
      iconAnchor: [o, s],
      popupAnchor: [0, 0],
    })),
    (mapMarkerIcons.route_end = L.icon({
      iconUrl: "../img/markers/route-end.svg",
      iconSize: [t, a],
      iconAnchor: [o, s],
      popupAnchor: [0, 0],
    })),
    (mapMarkerIcons.route_stop = L.icon({
      iconUrl: "../img/markers/route-stop.svg",
      iconSize: [t, a],
      iconAnchor: [o, s],
      popupAnchor: [0, 0],
    })),
    (mapMarkerIcons.route_event = L.icon({
      iconUrl: "../img/markers/route-event.svg",
      iconSize: [t, a],
      iconAnchor: [o, s],
      popupAnchor: [0, 0],
    })),
    (mapMarkerIcons.route_data_point = L.icon({
      iconUrl: "../img/markers/route-data-point.svg",
      iconSize: [8, 8],
      iconAnchor: [4, 4],
      popupAnchor: [0, 0],
    }));
}
function initGui() {
  $("#page_object_search_clear").click(function () {
    (document.getElementById("page_object_search").value = ""),
      objectLoadList();
  }),
    $("#page_event_search_clear").click(function () {
      (document.getElementById("page_event_search").value = ""),
        eventsLoadList();
    }),
    $("#dt_picker").DateTimePicker({
      dateTimeFormat: "YYYY-MM-DD HH:mm:ss",
      dateFormat: "YYYY-MM-DD",
      titleContentDateTime: "",
      setButtonContent: la.SET,
      clearButtonContent: la.CLEAR,
    }),
    (document.getElementById("page_history_filter").value = "2"),
    switchDateFilter("history");
}
function setCheckIcon(e, t) {
  1 == t ? $(e).addClass("checked") : $(e).removeClass("checked");
}
function initSelectList(e) {
  switch (e) {
    case "map_layer_list":
      ((s = document.getElementById("map_layer")).options.length = 0),
        gsValues.map_osm && s.options.add(new Option("OSM Map", "osm")),
        gsValues.map_bing &&
          (s.options.add(new Option("Bing Road", "broad")),
          s.options.add(new Option("Bing Aerial", "baer")),
          s.options.add(new Option("Bing Hybrid", "bhyb"))),
        gsValues.map_google &&
          (s.options.add(new Option("Google Streets", "gmap")),
          s.options.add(new Option("Google Satellite", "gsat")),
          s.options.add(new Option("Google Hybrid", "ghyb")),
          s.options.add(new Option("Google Terrain", "gter"))),
        gsValues.map_mapbox &&
          (s.options.add(new Option("Mapbox Streets", "mbmap")),
          s.options.add(new Option("Mapbox Satellite", "mbsat"))),
        gsValues.map_yandex && s.options.add(new Option("Yandex", "yandex"));
      for (var t = 0; t < gsValues.map_custom.length; t++) {
        var a = gsValues.map_custom[t].layer_id,
          o = gsValues.map_custom[t].name;
        s.options.add(new Option(o, a));
      }
      break;
    case "history_object_list":
      var s = document.getElementById("page_history_object_list");
      for (var n in ((s.options.length = 0), settingsObjectData)) {
        if (/\d+_\d+/.test(n)) {
          continue;
        }
        "true" == (r = settingsObjectData[n]).active &&
          s.options.add(new Option(r.name, n));
      }
      0 == s.options.length
        ? s.options.add(new Option(la.NO_OBJECTS, ""))
        : sortSelectList(s);
      break;
    case "reports_object_list":
      s = document.getElementById("page_reports_object_list");
      for (var n in ((s.options.length = 0), settingsObjectData)) {
        "true" == (r = settingsObjectData[n]).active &&
          s.options.add(new Option(r.name, n));
      }
      0 == s.options.length
        ? s.options.add(new Option(la.NO_OBJECTS, ""))
        : sortSelectList(s);
      break;
    case "cmd_object_list":
      s = document.getElementById("page_cmd_object_list");
      for (var n in ((s.options.length = 0), settingsObjectData)) {
        if (/\d+_\d+/.test(n)) {
          continue;
        }
        var r;
        "true" == (r = settingsObjectData[n]).active &&
          s.options.add(new Option(r.name, n));
      }
      0 == s.options.length
        ? s.options.add(new Option(la.NO_OBJECTS, ""))
        : sortSelectList(s);
      break;
    case "object_group_list":
      s = document.getElementById("page_object_list_group");
      for (var n in ((s.options.length = 0), settingsObjectGroupData)) {
        var l = settingsObjectGroupData[n];
        l.name != la.UNGROUPED && s.options.add(new Option(l.name, n));
      }
      0 == s.options.length
        ? (s.options.add(new Option(la.ALL, -1), 0),
          (s.value = -1),
          (s.style.display = "none"))
        : (sortSelectList(s),
          s.options.add(new Option(la.UNGROUPED, 0), 0),
          s.options.add(new Option(la.ALL, -1), 0),
          (s.value = -1));
  }
}
function switchDateFilter(e) {
  if ("history" == e) var t = "page_history_";
  switch (document.getElementById(t + "filter").value) {
    case "0":
      (document.getElementById(t + "date_from").value =
        moment().format("YYYY-MM-DD")),
        (document.getElementById(t + "date_to").value =
          moment().format("YYYY-MM-DD")),
        (document.getElementById(t + "date_from").value += " 00:00"),
        (document.getElementById(t + "date_to").value += " 00:00");
      break;
    case "1":
      (document.getElementById(t + "date_from").value =
        moment().format("YYYY-MM-DD")),
        (document.getElementById(t + "date_to").value =
          moment().format("YYYY-MM-DD")),
        (document.getElementById(t + "date_from").value +=
          " " + moment().subtract("hour", 1).format("HH:mm")),
        (document.getElementById(t + "date_to").value +=
          " " + moment().format("HH:mm"));
      break;
    case "2":
      (document.getElementById(t + "date_from").value =
        moment().format("YYYY-MM-DD")),
        (document.getElementById(t + "date_to").value = moment()
          .add("days", 1)
          .format("YYYY-MM-DD")),
        (document.getElementById(t + "date_from").value += " 00:00"),
        (document.getElementById(t + "date_to").value += " 00:00");
      break;
    case "3":
      (document.getElementById(t + "date_from").value = moment()
        .subtract("days", 1)
        .format("YYYY-MM-DD")),
        (document.getElementById(t + "date_to").value =
          moment().format("YYYY-MM-DD")),
        (document.getElementById(t + "date_from").value += " 00:00"),
        (document.getElementById(t + "date_to").value += " 00:00");
      break;
    case "4":
      (document.getElementById(t + "date_from").value = moment()
        .subtract("days", 2)
        .format("YYYY-MM-DD")),
        (document.getElementById(t + "date_to").value = moment()
          .subtract("days", 1)
          .format("YYYY-MM-DD")),
        (document.getElementById(t + "date_from").value += " 00:00"),
        (document.getElementById(t + "date_to").value += " 00:00");
      break;
    case "5":
      (document.getElementById(t + "date_from").value = moment()
        .subtract("days", 3)
        .format("YYYY-MM-DD")),
        (document.getElementById(t + "date_to").value = moment()
          .subtract("days", 2)
          .format("YYYY-MM-DD")),
        (document.getElementById(t + "date_from").value += " 00:00"),
        (document.getElementById(t + "date_to").value += " 00:00");
      break;
    case "6":
      (document.getElementById(t + "date_from").value = moment()
        .isoWeekday(1)
        .format("YYYY-MM-DD")),
        (document.getElementById(t + "date_to").value = moment()
          .add("days", 1)
          .format("YYYY-MM-DD")),
        (document.getElementById(t + "date_from").value += " 00:00"),
        (document.getElementById(t + "date_to").value += " 00:00");
      break;
    case "7":
      (document.getElementById(t + "date_from").value = moment()
        .isoWeekday(1)
        .subtract("week", 1)
        .format("YYYY-MM-DD")),
        (document.getElementById(t + "date_to").value = moment()
          .isoWeekday(1)
          .format("YYYY-MM-DD")),
        (document.getElementById(t + "date_from").value += " 00:00"),
        (document.getElementById(t + "date_to").value += " 00:00");
      break;
    case "8":
      (document.getElementById(t + "date_from").value = moment()
        .startOf("month")
        .format("YYYY-MM-DD")),
        (document.getElementById(t + "date_to").value = moment()
          .add("days", 1)
          .format("YYYY-MM-DD")),
        (document.getElementById(t + "date_from").value += " 00:00"),
        (document.getElementById(t + "date_to").value += " 00:00");
      break;
    case "9":
      (document.getElementById(t + "date_from").value = moment()
        .startOf("month")
        .subtract("month", 1)
        .format("YYYY-MM-DD")),
        (document.getElementById(t + "date_to").value = moment()
          .startOf("month")
          .format("YYYY-MM-DD")),
        (document.getElementById(t + "date_from").value += " 00:00"),
        (document.getElementById(t + "date_to").value += " 00:00");
  }
}
function showPlacesMarkersPanel() {
  (document.getElementById("page_title").innerHTML =
    la.PLACES + " - " + la.MARKERS),
    (document.getElementById("marker_list_page").style.display = ""),
    (document.getElementById("route_list_page").style.display = "none"),
    (document.getElementById("zone_list_page").style.display = "none"),
    (document.getElementById("markers_panel").style.display = "block"),
    (document.getElementById("routes_panel").style.display = "none"),
    (document.getElementById("zones_panel").style.display = "none");
}
function showPlacesRoutesPanel() {
  (document.getElementById("page_title").innerHTML =
    la.PLACES + " - " + la.ROUTES),
    (document.getElementById("marker_list_page").style.display = "none"),
    (document.getElementById("route_list_page").style.display = ""),
    (document.getElementById("zone_list_page").style.display = "none"),
    (document.getElementById("markers_panel").style.display = "none"),
    (document.getElementById("routes_panel").style.display = "block"),
    (document.getElementById("zones_panel").style.display = "none");
}
function showPlacesZonesPanel() {
  (document.getElementById("page_title").innerHTML =
    la.PLACES + " - " + la.ZONES),
    (document.getElementById("marker_list_page").style.display = "none"),
    (document.getElementById("route_list_page").style.display = "none"),
    (document.getElementById("zone_list_page").style.display = ""),
    (document.getElementById("markers_panel").style.display = "none"),
    (document.getElementById("routes_panel").style.display = "none"),
    (document.getElementById("zones_panel").style.display = "block");
}
function showHistoryNavbar() {
  (document.getElementById("history_playback").style.display = "block"),
    (document.getElementById("history_navbar").style.display = "block"),
    (document.getElementById("history_navbar_map").style.display = "none"),
    (document.getElementById("history_navbar_graph").style.display = "block"),
    (document.getElementById("history_navbar_details").style.display = "block");
  var e = document.getElementById("history_navbar").offsetHeight;
  (document.getElementById("map").style.bottom = e + "px"),
    (document.getElementById("graph_panel").style.bottom = e + "px"),
    (document.getElementById("details_panel").style.bottom = e + "px"),
    map.invalidateSize(!0);
}
function hideHistoryNavbar() {
  hideHistoryPanels(),
    (document.getElementById("history_playback").style.display = "none"),
    (document.getElementById("history_navbar").style.display = "none"),
    (document.getElementById("map").style.bottom = "0px"),
    map.invalidateSize(!0);
}
function showHistoryGraphPanel() {
  (document.getElementById("history_navbar_map").style.display = "block"),
    (document.getElementById("history_navbar_graph").style.display = "none"),
    (document.getElementById("history_navbar_details").style.display = "block"),
    (document.getElementById("history_playback").style.display = "none"),
    (document.getElementById("graph_panel").style.display = "block"),
    (document.getElementById("details_panel").style.display = "none");
  var e = document.getElementById("graph_panel").offsetHeight;
  $("#graph_panel_plot").css("height", e - 80), historyRouteChangeGraphSource();
}
function showHistoryDetailsPanel() {
  (document.getElementById("history_navbar_map").style.display = "block"),
    (document.getElementById("history_navbar_graph").style.display = "block"),
    (document.getElementById("history_navbar_details").style.display = "none"),
    (document.getElementById("history_playback").style.display = "none"),
    (document.getElementById("details_panel").style.display = "block"),
    (document.getElementById("graph_panel").style.display = "none");
}
function hideHistoryPanels() {
  (document.getElementById("history_navbar_map").style.display = "none"),
    (document.getElementById("history_navbar_graph").style.display = "block"),
    (document.getElementById("history_navbar_details").style.display = "block"),
    (document.getElementById("history_playback").style.display = "block"),
    (document.getElementById("graph_panel").style.display = "none"),
    (document.getElementById("details_panel").style.display = "none");
}
null != url[1] && window.location.replace(url[0]),
  $(window).bind("orientationchange resize load", fixGuiSize);
var placesMarkerData = new Array();
function placesMarkerLoadList() {
  var e = document.getElementById("page_markers_panel_search").value,
    t = document.getElementById("marker_list_page").value;
  $.ajax({
    type: "POST",
    url:
      "../func/fn_places.php?cmd=load_marker_list&s=" +
      e +
      "&page=" +
      t +
      "&rows=25&sidx=marker_name&sord=asc",
    dataType: "json",
    cache: !1,
    success: function (e) {
      var a = "";
      (document.getElementById("page_markers_panel_list").innerHTML = ""),
        (t = e.page);
      var o = e.total,
        s = (e.records, e.rows),
        n = document.getElementById("marker_list_page");
      n.options.length = 0;
      for (var r = 1; r <= o; r++) n.options.add(new Option(r, r));
      for (var l in ((n.value = t), s)) {
        s[l].id;
        var i = s[l].cell,
          c =
            '<a href="#" onClick="placesMarkerPanTo(\'' +
            i[0] +
            '\');" class="list-group-item clearfix">';
        (c += '<div class="row vertical-align">'),
          (c += '<div class="col-xs-1">'),
          (c +=
            '<center><img style="height: 14px;" src="../' +
            i[3] +
            '"/></center>'),
          (c += "</div>"),
          (c += '<div class="col-xs-11">'),
          (c += i[4]),
          (c += "</div>"),
          (c += "</div>"),
          (a += c += "</a>");
      }
      document.getElementById("page_markers_panel_list").innerHTML += a;
    },
  });
}
function placesMarkerLoadData() {
  $.ajax({
    type: "POST",
    url: "../func/fn_places.php",
    data: {
      cmd: "load_marker_data",
    },
    dataType: "json",
    cache: !1,
    success: function (e) {
      (placesMarkerData.markers = e),
        "" != placesMarkerData.markers
          ? placesMarkerAddAllToMap()
          : placesMarkerRemoveAllFromMap();
    },
  });
}
function placesMarkerAddAllToMap() {
  for (var e in (placesMarkerRemoveAllFromMap(), placesMarkerData.markers)) {
    var t = placesMarkerData.markers[e],
      a = t.data.name,
      o = t.data.desc,
      s = "../" + t.data.icon,
      n = t.data.visible,
      r = t.data.lat,
      l = t.data.lng;
    try {
      placesMarkerAddMarkerToMap(e, a, o, s, n, r, l);
    } catch (e) {}
  }
}
function placesMarkerAddMarkerToMap(e, t, a, o, s, n, r) {
  var l = settingsUserData.map_is,
    i = L.icon({
      iconUrl: o,
      iconSize: [28 * l, 28 * l],
      iconAnchor: [14 * l, 28 * l],
      popupAnchor: [0, 0],
    }),
    c = L.marker([n, r], {
      icon: i,
    }),
    d = "<table><tr><td><strong>" + t + "</strong></td></tr>";
  "" != a && (d += "<tr><td>" + textToLinks(a) + "</td></tr>"),
    (d += "</table>"),
    c.on("click", function (e) {
      addPopupToMap(n, r, [0, -28 * l], d, "");
    }),
    "false" != s && mapLayers.places_markers.addLayer(c),
    (placesMarkerData.markers[e].marker_layer = c);
}
function placesMarkerRemoveAllFromMap() {
  mapLayers.places_markers.clearLayers();
}
function placesMarkerPanTo(e) {
  try {
    switchPage("map");
    var t = placesMarkerData.markers[e].data.lng,
      a = placesMarkerData.markers[e].data.lat;
    map.panTo({
      lat: a,
      lng: t,
    });
  } catch (e) {}
}
placesMarkerData.markers = new Array();
var placesRouteData = new Array();
function placesRouteLoadList() {
  var e = document.getElementById("page_routes_panel_search").value,
    t = document.getElementById("route_list_page").value;
  $.ajax({
    type: "POST",
    url:
      "../func/fn_places.php?cmd=load_route_list&s=" +
      e +
      "&page=" +
      t +
      "&rows=25&sidx=route_name&sord=asc",
    dataType: "json",
    cache: !1,
    success: function (e) {
      var a = "";
      (document.getElementById("page_routes_panel_list").innerHTML = ""),
        (t = e.page);
      var o = e.total,
        s = (e.records, e.rows),
        n = document.getElementById("route_list_page");
      n.options.length = 0;
      for (var r = 1; r <= o; r++) n.options.add(new Option(r, r));
      for (var l in ((n.value = t), s)) {
        s[l].id;
        var i = s[l].cell,
          c =
            '<a href="#" onClick="placesRoutePanTo(\'' +
            i[0] +
            '\');" class="list-group-item clearfix">';
        (c += '<div class="row vertical-align">'),
          (c += '<div class="col-xs-1">'),
          (c +=
            '<div style="margin:auto; width: 12px; height: 12px; background-color:' +
            i[3] +
            ';"></div>'),
          (c += "</div>"),
          (c += '<div class="col-xs-11">'),
          (c += i[4]),
          (c += "</div>"),
          (c += "</div>"),
          (a += c += "</a>");
      }
      document.getElementById("page_routes_panel_list").innerHTML += a;
    },
  });
}
function placesRouteLoadData() {
  $.ajax({
    type: "POST",
    url: "../func/fn_places.php",
    data: {
      cmd: "load_route_data",
    },
    dataType: "json",
    cache: !1,
    success: function (e) {
      (placesRouteData.routes = e),
        "" != placesRouteData.routes
          ? placesRouteAddAllToMap()
          : placesRouteRemoveAllFromMap();
    },
  });
}
function placesRouteAddAllToMap() {
  for (var e in (placesRouteRemoveAllFromMap(), placesRouteData.routes)) {
    var t = placesRouteData.routes[e],
      a = t.data.name,
      o = t.data.color,
      s = t.data.visible,
      n = t.data.name_visible,
      r = t.data.points;
    try {
      placesRouteAddRouteToMap(e, a, o, s, n, r);
    } catch (e) {}
  }
}
function placesRouteAddRouteToMap(e, t, a, o, s, n) {
  var r = placesRoutePointsStringToLatLngs(n),
    l = L.polyline(r, {
      color: a,
      fill: !1,
      opacity: 0.8,
      weight: 3,
    }),
    i = r[0],
    c = L.tooltip({
      permanent: !0,
      direction: "top",
    });
  c.setLatLng(i),
    c.setContent(t),
    "false" != o && mapLayers.places_routes.addLayer(l),
    "false" != s && mapLayers.places_routes.addLayer(c),
    (placesRouteData.routes[e].route_layer = l),
    (placesRouteData.routes[e].label_layer = c);
}
function placesRouteRemoveAllFromMap() {
  mapLayers.places_routes.clearLayers();
}
function placesRoutePointsStringToLatLngs(e) {
  var t = e.split(","),
    a = [];
  for (j = 0; j < t.length; j += 2)
    (lat = t[j]), (lng = t[j + 1]), a.push(L.latLng(lat, lng));
  return a;
}
function placesRoutePanTo(e) {
  try {
    switchPage("map");
    var t = placesRouteData.routes[e].route_layer.getBounds().getCenter();
    map.panTo(t);
  } catch (e) {}
}
placesRouteData.routes = new Array();
var placesZoneData = new Array();
function placesZoneLoadList() {
  var e = document.getElementById("page_zones_panel_search").value,
    t = document.getElementById("zone_list_page").value;
  $.ajax({
    type: "POST",
    url:
      "../func/fn_places.php?cmd=load_zone_list&s=" +
      e +
      "&page=" +
      t +
      "&rows=25&sidx=zone_name&sord=asc",
    dataType: "json",
    cache: !1,
    success: function (e) {
      var a = "";
      (document.getElementById("page_zones_panel_list").innerHTML = ""),
        (t = e.page);
      var o = e.total,
        s = (e.records, e.rows),
        n = document.getElementById("zone_list_page");
      n.options.length = 0;
      for (var r = 1; r <= o; r++) n.options.add(new Option(r, r));
      for (var l in ((n.value = t), s)) {
        s[l].id;
        var i = s[l].cell,
          c =
            '<a href="#" onClick="placesZonePanTo(\'' +
            i[0] +
            '\');" class="list-group-item clearfix">';
        (c += '<div class="row vertical-align">'),
          (c += '<div class="col-xs-1">'),
          (c +=
            '<div style="margin:auto; width: 12px; height: 12px; background-color:' +
            i[3] +
            ';"></div>'),
          (c += "</div>"),
          (c += '<div class="col-xs-11">'),
          (c += i[4]),
          (c += "</div>"),
          (c += "</div>"),
          (a += c += "</a>");
      }
      document.getElementById("page_zones_panel_list").innerHTML += a;
    },
  });
}
function placesZoneLoadData() {
  $.ajax({
    type: "POST",
    url: "../func/fn_places.php",
    data: {
      cmd: "load_zone_data",
    },
    dataType: "json",
    cache: !1,
    success: function (e) {
      (placesZoneData.zones = e),
        "" != placesZoneData.zones
          ? placesZoneAddAllToMap()
          : placesZoneRemoveAllFromMap();
    },
  });
}
function placesZoneAddAllToMap() {
  for (var e in (placesZoneRemoveAllFromMap(), placesZoneData.zones)) {
    var t = placesZoneData.zones[e],
      a = t.data.name,
      o = t.data.color,
      s = t.data.visible,
      n = t.data.name_visible,
      r = t.data.area,
      l = t.data.vertices;
    try {
      placesZoneAddZoneToMap(e, a, o, s, n, r, l);
    } catch (e) {}
  }
}
function placesZoneAddZoneToMap(e, t, a, o, s, n, r) {
  var l = placesZoneVerticesStringToLatLngs(r),
    i = L.polygon(l, {
      color: a,
      fill: !0,
      fillColor: a,
      fillOpacity: 0.4,
      opacity: 0.8,
      weight: 3,
    });
  "false" == s && (t = ""),
    "0" != n &&
      ((measure_area = getAreaFromLatLngs(i.getLatLngs()[0])),
      "1" == n &&
        ((measure_area *= 247105e-9),
        (measure_area = Math.round(100 * measure_area) / 100),
        (measure_area = measure_area + " " + la.UNIT_ACRE)),
      "2" == n &&
        ((measure_area *= 1e-4),
        (measure_area = Math.round(100 * measure_area) / 100),
        (measure_area = measure_area + " " + la.UNIT_HECTARES)),
      "3" == n &&
        ((measure_area = Math.round(100 * measure_area) / 100),
        (measure_area = measure_area + " " + la.UNIT_SQ_M)),
      "4" == n &&
        ((measure_area *= 1e-6),
        (measure_area = Math.round(100 * measure_area) / 100),
        (measure_area = measure_area + " " + la.UNIT_SQ_KM)),
      "5" == n &&
        ((measure_area *= 10.7639),
        (measure_area = Math.round(100 * measure_area) / 100),
        (measure_area = measure_area + " " + la.UNIT_SQ_FT)),
      "6" == n &&
        ((measure_area = 1e-6 * measure_area * 0.386102),
        (measure_area = Math.round(100 * measure_area) / 100),
        (measure_area = measure_area + " " + la.UNIT_SQ_MI)),
      (t = t + " (" + measure_area + ")"));
  var c = i.getBounds().getCenter(),
    d = L.tooltip({
      permanent: !0,
      direction: "center",
    });
  d.setLatLng(c),
    d.setContent(t),
    "false" != o && mapLayers.places_zones.addLayer(i),
    ("false" == s && "0" == n) || mapLayers.places_zones.addLayer(d),
    (placesZoneData.zones[e].zone_layer = i),
    (placesZoneData.zones[e].label_layer = d);
}
function placesZoneRemoveAllFromMap() {
  mapLayers.places_zones.clearLayers();
}
function placesZoneVerticesStringToLatLngs(e) {
  var t = e.split(","),
    a = [];
  for (j = 0; j < t.length; j += 2)
    (lat = t[j]), (lng = t[j + 1]), a.push(L.latLng(lat, lng));
  return a;
}
function placesZonePanTo(e) {
  try {
    switchPage("map");
    var t = placesZoneData.zones[e].zone_layer.getBounds().getCenter();
    map.panTo(t);
  } catch (e) {}
}
placesZoneData.zones = new Array();
var historyGraphPlot,
  timer_historyRoutePlay,
  historyRouteData = new Array();
function historyLoadRoute({ showIntoGPS, device }) {
  if (utilsCheckPrivileges("history")) {
    if (showIntoGPS) {
      // **  estas lineas son las 'buenas' en prod  **
      
             var e = device,
                 t = settingsObjectData[device].name,
                 a = `${moment().format("YYYY-MM-DD")} 00:00:00`,
                 o = `${moment().format("YYYY-MM-DD")} ${moment().format("HH")}:${moment().format("mm")}:${moment().format("ss")}`,
                 s = 1;              
      /*
      //  eliminar las 5 siguientes en prod
      var e = device,
        t = settingsObjectData[device].name,
        a = `2022-11-01 00:00:00`,
        o = `2022-11-06 00:00:00`,
        s = 1; */
    } else {
      var e = document.getElementById("page_history_object_list").value,
        t = document.getElementById("page_history_object_list").options[
          document.getElementById("page_history_object_list").selectedIndex
        ].text,
        a = document.getElementById("page_history_date_from").value + ":00",
        o = document.getElementById("page_history_date_to").value + ":00",
        s = document.getElementById("page_history_stop_duration").value;
    }
    if ("" != e) {
      loadingData(!0);
      var n = {
        cmd: "load_route_data",
        imei: e,
        dtf: a,
        dtt: o,
        min_stop_duration: s,
      };
      $.ajax({
        type: "POST",
        url: "../func/fn_history.php",
        data: n,
        dataType: "json",
        cache: !1,
        success: function (a) {
          historyShowRoute(transformToHistoryRoute(a), e, t);
        },
        error: function (e, t) {
          loadingData(!1),
            bootbox.alert(la.NOTHING_HAS_BEEN_FOUND_ON_YOUR_REQUEST);
        },
      });
    } else bootbox.alert(la.NOTHING_HAS_BEEN_FOUND_ON_YOUR_REQUEST);
  }
}
function historyShowRoute(e, t, a) {
  if (
    (datalistBottomHidePanel(),
    historyHideRoute(),
    objectFollowAll(!1),
    "" == (historyRouteData = e).route || historyRouteData.route.length < 2)
  )
    return (
      loadingData(!1),
      bootbox.alert(la.NOTHING_HAS_BEEN_FOUND_ON_YOUR_REQUEST),
      void (historyRouteData = [])
    );
  (historyRouteData.name = a),
    (historyRouteData.imei = t),
    (historyRouteData.layers = new Array()),
    (historyRouteData.layers.stops = new Array()),
    (historyRouteData.layers.events = new Array()),
    (historyRouteData.play = new Array()),
    (historyRouteData.play.status = !1),
    (historyRouteData.play.position = 0);
  var o = new Array();
  for (i = 0; i < historyRouteData.route.length; i++)
    (lat = historyRouteData.route[i].lat),
      (lng = historyRouteData.route[i].lng),
      o.push(L.latLng(lat, lng));
  var s = L.polyline(o, {
    color: settingsUserData.map_rc,
    opacity: 0.8,
    weight: 3,
  });
  for (
    mapLayers.history.addLayer(s),
      historyRouteAddStartMarkerToMap(),
      historyRouteAddEndMarkerToMap(),
      i = 0;
    i < historyRouteData.stops.length;
    i++
  )
    historyRouteAddStopMarkerToMap(i);
  for (i = 0; i < historyRouteData.events.length; i++)
    historyRouteAddEventMarkerToMap(i);
  historyRouteStops(), historyRouteEvents(), loadingData(!1), switchPage("map");
  var n = s.getBounds();
  map.fitBounds(n),
    showHistoryNavbar(),
    historyRouteCreateGraphSourceList(),
    historyRouteCreateRouteDetails();
}
function historyHideRoute() {
  null != historyRouteData.route &&
    (hideHistoryNavbar(),
    initGraph(),
    (document.getElementById("details_panel_detail_list").innerHTML = ""),
    mapLayers.history.clearLayers(),
    (historyRouteData = []));
}
function historyRouteStops() {
  if (null != historyRouteData.layers) {
    var e = document.getElementById("page_history_stops");
    for (i = 0; i < historyRouteData.layers.stops.length; i++) {
      var t = historyRouteData.layers.stops[i];
      "true" == e.value
        ? mapLayers.history.addLayer(t)
        : mapLayers.history.removeLayer(t);
    }
  }
}
function historyRouteEvents() {
  if (null != historyRouteData.layers) {
    var e = document.getElementById("page_history_events");
    for (i = 0; i < historyRouteData.layers.events.length; i++) {
      var t = historyRouteData.layers.events[i];
      "true" == e.value
        ? mapLayers.history.addLayer(t)
        : mapLayers.history.removeLayer(t);
    }
  }
}
function historyRouteCreateGraphSourceList() {
  var e = historyRouteData.imei,
    t = document.getElementById("graph_panel_data_source");
  (t.options.length = 0),
    t.options.add(new Option(la.SPEED, "speed")),
    t.options.add(new Option(la.ALTITUDE, "altitude"));
  var a = new Array();
  for (var o in settingsObjectData[e].sensors) {
    ((n = settingsObjectData[e].sensors[o]).id = o), a.push(n);
  }
  var s = sortArrayByElement(a, "name");
  for (var o in s) {
    var n;
    "string" != (n = s[o]).result_type &&
      "rel" != n.result_type &&
      t.options.add(new Option(n.name, n.id));
  }
}
function historyRouteChangeGraphSource() {
  historyRouteCreateGraph(
    document.getElementById("graph_panel_data_source").value
  );
}
function historyRouteCreateGraph(e) {
  document.getElementById("graph_panel_label").innerHTML = "";
  var t = historyRouteData.imei;
  if (
    ((historyRouteData.graph = []),
    (historyRouteData.graph.data = []),
    (historyRouteData.graph.data_index = []),
    "speed" != e && "altitude" != e)
  )
    var a = settingsObjectData[t].sensors[e];
  for (var o = 0; o < historyRouteData.route.length; o++) {
    var s = historyRouteData.route[o].dt_tracker,
      n = getTimestampFromDate(s.replace(/-/g, "/") + " UTC");
    if ("speed" == e) var r = historyRouteData.route[o].speed;
    else if ("altitude" == e) r = historyRouteData.route[o].altitude;
    else {
      if ("fuelsumup" == a.type)
        r = getSensorValueFuelLevelSumUp(
          t,
          historyRouteData.route[o].params,
          a
        ).value;
      else r = getSensorValue(historyRouteData.route[o].params, a).value;
      "engh" == a.type && ((r = r / 60 / 60), (r = Math.round(100 * r) / 100));
    }
    historyRouteData.graph.data.push([n, r]),
      (historyRouteData.graph.data_index[n] = o);
  }
  "speed" == e
    ? ((historyRouteData.graph.units = la.UNIT_SPEED),
      (historyRouteData.graph.result_type = ""))
    : "altitude" == e
    ? ((historyRouteData.graph.units = la.UNIT_HEIGHT),
      (historyRouteData.graph.result_type = ""))
    : "odo" == a.type
    ? ((historyRouteData.graph.units = la.UNIT_DISTANCE),
      (historyRouteData.graph.result_type = a.result_type))
    : "engh" == a.type
    ? ((historyRouteData.graph.units = la.UNIT_H),
      (historyRouteData.graph.result_type = a.result_type))
    : ((historyRouteData.graph.units = a.units),
      (historyRouteData.graph.result_type = a.result_type)),
    initGraph(historyRouteData.graph);
}
function historyRouteCreateRouteGeneralItems(e, t) {
  var a = '<li class="list-group-item"><div class="row vertical-align">';
  return (
    (a += '<div class="col-xs-6">'),
    (a += e),
    (a += "</div>"),
    (a += '<div class="col-xs-6">'),
    (a += t),
    (a += "</div>"),
    (a += "</div></li>")
  );
}
function historyRouteCreateRouteDetails() {
  var e = historyRouteData.imei;
  document.getElementById("details_panel_detail_list").innerHTML = "";
  var t =
    '<div class="panel-heading"><b>' +
    la.GENERAL +
    '</b></div><ul class="list-group">';
  (t += historyRouteCreateRouteGeneralItems(
    la.ROUTE_LENGTH,
    historyRouteData.route_length + " " + la.UNIT_DISTANCE
  )),
    (t += historyRouteCreateRouteGeneralItems(
      la.MOVE_DURATION,
      historyRouteData.drives_duration
    )),
    (t += historyRouteCreateRouteGeneralItems(
      la.STOP_DURATION,
      historyRouteData.stops_duration
    )),
    (t += historyRouteCreateRouteGeneralItems(
      la.TOP_SPEED,
      historyRouteData.top_speed + " " + la.UNIT_SPEED
    )),
    (t += historyRouteCreateRouteGeneralItems(
      la.AVG_SPEED,
      historyRouteData.avg_speed + " " + la.UNIT_SPEED
    ));
  var a = historyRouteData.fuel_consumption;
  if (
    (0 != a &&
      (t += historyRouteCreateRouteGeneralItems(
        la.FUEL_CONSUMPTION,
        a + " " + la.UNIT_CAPACITY
      )),
    "l" == settingsUserData.unit_capacity)
  ) {
    var o = historyRouteData.fuel_consumption_per_100km;
    0 != o &&
      (t += historyRouteCreateRouteGeneralItems(
        la.AVG_FUEL_CONSUMPTION_100_KM,
        o + " " + la.UNIT_CAPACITY
      ));
  } else {
    var s = historyRouteData.fuel_consumption_mpg;
    0 != s &&
      (t += historyRouteCreateRouteGeneralItems(
        la.AVG_FUEL_CONSUMPTION_MPG,
        s + " " + la.UNIT_MI
      ));
  }
  var n = historyRouteData.fuel_cost;
  for (
    0 != n &&
      (t += historyRouteCreateRouteGeneralItems(
        la.FUEL_COST,
        n + " " + settingsUserData.currency
      )),
      0 != getSensorFromType(e, "acc") &&
        ((t += historyRouteCreateRouteGeneralItems(
          la.ENGINE_WORK,
          historyRouteData.engine_work
        )),
        (t += historyRouteCreateRouteGeneralItems(
          la.ENGINE_IDLE,
          historyRouteData.engine_idle
        ))),
      t += "</ul>",
      document.getElementById("details_panel_detail_list").innerHTML = t,
      (r = []).push({
        el_type: "point",
        el_id: 0,
        icon: '<img src="../img/markers/route-start.svg" style="width: 20px;"/>',
        datetime: historyRouteData.route[0].dt_tracker,
        info: "",
      }),
      r.push({
        el_type: "point",
        el_id: historyRouteData.route.length - 1,
        icon: '<img src="../img/markers/route-end.svg" style="width: 20px;"/>',
        datetime:
          historyRouteData.route[historyRouteData.route.length - 1].dt_tracker,
        info: "",
      }),
      l = 0;
    l < historyRouteData.stops.length;
    l++
  )
    r.push({
      el_type: "stop",
      el_id: l,
      icon: '<img src="../img/markers/route-stop.svg" style="width: 20px;"/>',
      datetime: historyRouteData.stops[l].dt_start,
      info: historyRouteData.stops[l].duration,
    });
  for (l = 0; l < historyRouteData.events.length; l++)
    r.push({
      el_type: "event",
      el_id: l,
      icon: '<img src="../img/markers/route-event.svg" style="width: 20px;"/>',
      datetime: historyRouteData.events[l].dt_tracker,
      info: historyRouteData.events[l].event_desc,
    });
  for (l = 0; l < historyRouteData.drives.length; l++)
    r.push({
      el_type: "drive",
      el_id: l,
      icon: '<img src="../img/markers/route-drive.svg" style="width: 20px;"/>',
      datetime: historyRouteData.drives[l].dt_start,
      info:
        historyRouteData.drives[l].duration +
        " (" +
        historyRouteData.drives[l].route_length +
        " " +
        la.UNIT_DISTANCE +
        ")",
    });
  var r = sortArrayByElement(r, "datetime");
  document.getElementById("details_panel_detail_ext_list").innerHTML = "";
  for (var l = 0; l <= r.length - 1; l++) {
    var i = "";
    "point" == r[l].el_type
      ? (i =
          "hideHistoryPanels();historyRoutePanToPoint(" +
          r[l].el_id +
          ");historyRouteShowPoint(" +
          r[l].el_id +
          ");")
      : "stop" == r[l].el_type
      ? (i =
          "hideHistoryPanels();historyRoutePanToStop(" +
          r[l].el_id +
          ");historyRouteShowStop(" +
          r[l].el_id +
          ");")
      : "event" == r[l].el_type
      ? (i =
          "hideHistoryPanels();historyRoutePanToEvent(" +
          r[l].el_id +
          ");historyRouteShowEvent(" +
          r[l].el_id +
          ");")
      : "drive" == r[l].el_type &&
        (i = "hideHistoryPanels();historyRouteShowDrive(" + r[l].el_id + ");");
    var c = '<a href="#" onClick="' + i + '" class="list-group-item clearfix">';
    (c += '<div class="row vertical-align">'),
      (c += '<div class="col-xs-1">'),
      (c += r[l].icon),
      (c += "</div>"),
      (c += '<div class="col-xs-5">'),
      (c += r[l].datetime),
      (c += "</div>"),
      (c += '<div class="col-xs-6">'),
      (c += r[l].info),
      (c += "</div>"),
      (c += "</div>"),
      (c += "</a>"),
      (document.getElementById("details_panel_detail_ext_list").innerHTML += c);
  }
}
function historyRoutePlay() {
  if (
    (clearTimeout(timer_historyRoutePlay),
    0 == historyRouteData.play.status && destroyMapPopup(),
    historyRouteData.route.length > 0 &&
      historyRouteData.play.position < historyRouteData.route.length)
  ) {
    if (
      (historyRoutePanToPoint(historyRouteData.play.position),
      historyRouteAddPointMarkerToMap(historyRouteData.play.position),
      (historyRouteData.play.status = !0),
      historyRouteData.play.position == historyRouteData.route.length - 1)
    )
      return (
        clearTimeout(timer_historyRoutePlay),
        (historyRouteData.play.status = !1),
        void (historyRouteData.play.position = 0)
      );
    1 == document.getElementById("history_playback_play_speed").value
      ? (timer_historyRoutePlay = setTimeout("historyRoutePlay()", 2e3))
      : 2 == document.getElementById("history_playback_play_speed").value
      ? (timer_historyRoutePlay = setTimeout("historyRoutePlay()", 1e3))
      : 3 == document.getElementById("history_playback_play_speed").value
      ? (timer_historyRoutePlay = setTimeout("historyRoutePlay()", 500))
      : 4 == document.getElementById("history_playback_play_speed").value
      ? (timer_historyRoutePlay = setTimeout("historyRoutePlay()", 250))
      : 5 == document.getElementById("history_playback_play_speed").value
      ? (timer_historyRoutePlay = setTimeout("historyRoutePlay()", 125))
      : 6 == document.getElementById("history_playback_play_speed").value &&
        (timer_historyRoutePlay = setTimeout("historyRoutePlay()", 65)),
      historyRouteData.play.position++;
  }
}
function historyRoutePause() {
  clearTimeout(timer_historyRoutePlay);
}
function historyRouteStop() {
  clearTimeout(timer_historyRoutePlay),
    (historyRouteData.play.status = !1),
    (historyRouteData.play.position = 0);
}
function historyRouteAddStartMarkerToMap() {
  var e = historyRouteData.route[0].lng,
    t = historyRouteData.route[0].lat,
    a = L.marker([t, e], {
      icon: mapMarkerIcons.route_start,
    });
  a.on("click", function (e) {
    historyRouteShowPoint(0);
  }),
    mapLayers.history.addLayer(a);
}
function historyRouteAddEndMarkerToMap() {
  var e = historyRouteData.route[historyRouteData.route.length - 1].lng,
    t = historyRouteData.route[historyRouteData.route.length - 1].lat,
    a = L.marker([t, e], {
      icon: mapMarkerIcons.route_end,
    });
  a.on("click", function (e) {
    historyRouteShowPoint(historyRouteData.route.length - 1);
  }),
    mapLayers.history.addLayer(a);
}
function historyRouteAddStopMarkerToMap(e) {
  var t = historyRouteData.stops[e].lng,
    a = historyRouteData.stops[e].lat,
    o = L.marker([a, t], {
      icon: mapMarkerIcons.route_stop,
    });
  o.on("click", function (t) {
    historyRouteShowStop(e);
  }),
    mapLayers.history.addLayer(o),
    historyRouteData.layers.stops.push(o);
}
function historyRouteAddEventMarkerToMap(e) {
  var t = historyRouteData.events[e].lng,
    a = historyRouteData.events[e].lat,
    o = L.marker([a, t], {
      icon: mapMarkerIcons.route_event,
    });
  o.on("click", function (t) {
    historyRouteShowEvent(e);
  }),
    mapLayers.history.addLayer(o),
    historyRouteData.layers.events.push(o);
}
function historyRouteAddPointMarkerToMap(e) {
  historyRouteRemovePointMarker();
  var t = historyRouteData.imei,
    a = historyRouteData.route[e].lng,
    o = historyRouteData.route[e].lat,
    s = historyRouteData.route[e].angle,
    n = historyRouteData.route[e].speed,
    r = historyRouteData.route[e].dt_tracker,
    l = (historyRouteData.route[e].params, settingsUserData.map_is),
    i = s;
  "arrow" != settingsObjectData[t].map_icon && (i = 0);
  var c = getMarkerIcon(t, n, !1, !1),
    d = L.marker([o, a], {
      icon: c,
      iconAngle: i,
    }),
    u = n + " " + la.UNIT_SPEED + " - " + r;
  d
    .bindTooltip(u, {
      permanent: !0,
      offset: [20 * l, 0],
      direction: "right",
    })
    .openTooltip(),
    d.on("click", function (t) {
      historyRouteShowPoint(e);
    }),
    mapLayers.history.addLayer(d),
    (historyRouteData.layers.point_marker = d);
}
function historyRouteRemovePointMarker() {
  historyRouteData.layers.point_marker &&
    mapLayers.history.removeLayer(historyRouteData.layers.point_marker);
}
function historyRoutePanToPoint(e) {
  var t = historyRouteData.route[e].lng,
    a = historyRouteData.route[e].lat;
  map.panTo({
    lat: a,
    lng: t,
  });
}
function historyRouteShowPoint(e) {
  historyRouteRemoveDrive();
  var t = historyRouteData.name,
    a = historyRouteData.imei,
    o = historyRouteData.route[e].lng,
    s = historyRouteData.route[e].lat,
    n = historyRouteData.route[e].altitude,
    r = historyRouteData.route[e].angle,
    l = historyRouteData.route[e].speed,
    i = historyRouteData.route[e].dt_tracker,
    c = historyRouteData.route[e].params,
    d = settingsUserData.map_is;
  geocoderGetAddress(s, o, function (u) {
    var m = u,
      p = urlPosition(s, o),
      g = "",
      _ = new Array();
    for (var y in settingsObjectData[a].sensors)
      _.push(settingsObjectData[a].sensors[y]);
    var v = sortArrayByElement(_, "name");
    for (var y in v) {
      var h = v[y];
      if ("true" == h.popup)
        if ("fuelsumup" == h.type) {
          var b = getSensorValueFuelLevelSumUp(a, c, h);
          g +=
            "<tr><td><strong>" +
            h.name +
            ":</strong></td><td>" +
            b.value_full +
            "</td></tr>";
        } else {
          b = getSensorValue(c, h);
          g +=
            "<tr><td><strong>" +
            h.name +
            ":</strong></td><td>" +
            b.value_full +
            "</td></tr>";
        }
    }
    var D =
        "<table>\t\t\t<tr><td><strong>" +
        la.OBJECT +
        ":</strong></td><td>" +
        t +
        "</td></tr>\t\t\t<tr><td><strong>" +
        la.ADDRESS +
        ":</strong></td><td>" +
        m +
        "</td></tr>\t\t\t<tr><td><strong>" +
        la.POSITION +
        ":</strong></td><td>" +
        p +
        "</td></tr>\t\t\t<tr><td><strong>" +
        la.ALTITUDE +
        ":</strong></td><td>" +
        n +
        " " +
        la.UNIT_HEIGHT +
        "</td></tr>\t\t\t<tr><td><strong>" +
        la.ANGLE +
        ":</strong></td><td>" +
        r +
        " &deg;</td></tr>\t\t\t<tr><td><strong>" +
        la.SPEED +
        ":</strong></td><td>" +
        l +
        " " +
        la.UNIT_SPEED +
        "</td></tr>\t\t\t<tr><td><strong>" +
        la.TIME +
        ":</strong></td><td>" +
        i +
        "</td></tr>",
      f = getObjectOdometer(a, c);
    -1 != f &&
      (D +=
        "<tr><td><strong>" +
        la.ODOMETER +
        ":</strong></td><td>" +
        f +
        " " +
        la.UNIT_DISTANCE +
        "</td></tr>");
    var E = getObjectEngineHours(a, c);
    -1 != E &&
      (D +=
        "<tr><td><strong>" +
        la.ENGINE_HOURS +
        ":</strong></td><td>" +
        E +
        "</td></tr>");
    var I = D + g;
    (D += "</table>"),
      (I += "</table>"),
      0 == e || historyRouteData.route.length - 1 == e
        ? addPopupToMap(s, o, [0, -28 * d], D, I)
        : addPopupToMap(s, o, [0, -14 * d], D, I);
  });
}
function historyRoutePanToStop(e) {
  var t = historyRouteData.stops[e].lng,
    a = historyRouteData.stops[e].lat;
  map.panTo({
    lat: a,
    lng: t,
  });
}
function historyRouteShowStop(e) {
  historyRouteRemoveDrive();
  var t = historyRouteData.name,
    a = historyRouteData.imei,
    o = historyRouteData.stops[e].lng,
    s = historyRouteData.stops[e].lat,
    n = historyRouteData.stops[e].altitude,
    r = historyRouteData.stops[e].angle,
    l = historyRouteData.stops[e].dt_start,
    i = historyRouteData.stops[e].dt_end,
    c = historyRouteData.stops[e].duration,
    d = historyRouteData.stops[e].params,
    u = settingsUserData.map_is;
  geocoderGetAddress(s, o, function (e) {
    var m = e,
      p = urlPosition(s, o),
      g = "",
      _ = new Array();
    for (var y in settingsObjectData[a].sensors)
      _.push(settingsObjectData[a].sensors[y]);
    var v = sortArrayByElement(_, "name");
    for (var y in v) {
      var h = v[y];
      if ("true" == h.popup)
        if ("fuelsumup" == h.type) {
          var b = getSensorValueFuelLevelSumUp(a, d, h);
          g +=
            "<tr><td><strong>" +
            h.name +
            ":</strong></td><td>" +
            b.value_full +
            "</td></tr>";
        } else {
          b = getSensorValue(d, h);
          g +=
            "<tr><td><strong>" +
            h.name +
            ":</strong></td><td>" +
            b.value_full +
            "</td></tr>";
        }
    }
    var D =
        "<table>\t\t\t<tr><td><strong>" +
        la.OBJECT +
        ":</strong></td><td>" +
        t +
        "</td></tr>\t\t\t<tr><td><strong>" +
        la.ADDRESS +
        ":</strong></td><td>" +
        m +
        "</td></tr>\t\t\t<tr><td><strong>" +
        la.POSITION +
        ":</strong></td><td>" +
        p +
        "</td></tr>\t\t\t<tr><td><strong>" +
        la.ALTITUDE +
        ":</strong></td><td>" +
        n +
        " " +
        la.UNIT_HEIGHT +
        "</td></tr>\t\t\t<tr><td><strong>" +
        la.ANGLE +
        ":</strong></td><td>" +
        r +
        " &deg;</td></tr>\t\t\t<tr><td><strong>" +
        la.ARRIVED +
        ":</strong></td><td>" +
        l +
        "</td></tr>\t\t\t<tr><td><strong>" +
        la.DEPARTED +
        ":</strong></td><td>" +
        i +
        "</td></tr>\t\t\t<tr><td><strong>" +
        la.DURATION +
        ":</strong></td><td>" +
        c +
        "</td></tr>",
      f = getObjectOdometer(a, d);
    -1 != f &&
      (D +=
        "<tr><td><strong>" +
        la.ODOMETER +
        ":</strong></td><td>" +
        f +
        " " +
        la.UNIT_DISTANCE +
        "</td></tr>");
    var E = getObjectEngineHours(a, d);
    -1 != E &&
      (D +=
        "<tr><td><strong>" +
        la.ENGINE_HOURS +
        ":</strong></td><td>" +
        E +
        "</td></tr>");
    var I = D + g;
    addPopupToMap(s, o, [0, -28 * u], (D += "</table>"), (I += "</table>"));
  });
}
function historyRoutePanToEvent(e) {
  var t = historyRouteData.events[e].lng,
    a = historyRouteData.events[e].lat;
  map.panTo({
    lat: a,
    lng: t,
  });
}
function historyRouteShowEvent(e) {
  historyRouteRemoveDrive();
  var t = historyRouteData.name,
    a = historyRouteData.imei,
    o = historyRouteData.events[e].event_desc,
    s = historyRouteData.events[e].dt_tracker,
    n = historyRouteData.events[e].lng,
    r = historyRouteData.events[e].lat,
    l = historyRouteData.events[e].altitude,
    i = historyRouteData.events[e].angle,
    c = historyRouteData.events[e].speed,
    d = historyRouteData.events[e].params,
    u = settingsUserData.map_is;
  geocoderGetAddress(r, n, function (e) {
    var m = e,
      p = urlPosition(r, n),
      g = "",
      _ = new Array();
    for (var y in settingsObjectData[a].sensors)
      _.push(settingsObjectData[a].sensors[y]);
    var v = sortArrayByElement(_, "name");
    for (var y in v) {
      var h = v[y];
      if ("true" == h.popup)
        if ("fuelsumup" == h.type) {
          var b = getSensorValueFuelLevelSumUp(a, d, h);
          g +=
            "<tr><td><strong>" +
            h.name +
            ":</strong></td><td>" +
            b.value_full +
            "</td></tr>";
        } else {
          b = getSensorValue(d, h);
          g +=
            "<tr><td><strong>" +
            h.name +
            ":</strong></td><td>" +
            b.value_full +
            "</td></tr>";
        }
    }
    var D =
        "<table>\t\t\t<tr><td><strong>" +
        la.OBJECT +
        ":</strong></td><td>" +
        t +
        "</td></tr>\t\t\t<tr><td><strong>" +
        la.EVENT +
        ":</strong></td><td>" +
        o +
        "</td></tr>\t\t\t<tr><td><strong>" +
        la.ADDRESS +
        ":</strong></td><td>" +
        m +
        "</td></tr>\t\t\t<tr><td><strong>" +
        la.POSITION +
        ":</strong></td><td>" +
        p +
        "</td></tr>\t\t\t<tr><td><strong>" +
        la.ALTITUDE +
        ":</strong></td><td>" +
        l +
        " " +
        la.UNIT_HEIGHT +
        "</td></tr>\t\t\t<tr><td><strong>" +
        la.ANGLE +
        ":</strong></td><td>" +
        i +
        " &deg;</td></tr>\t\t\t<tr><td><strong>" +
        la.SPEED +
        ":</strong></td><td>" +
        c +
        " " +
        la.UNIT_SPEED +
        "</td></tr>\t\t\t<tr><td><strong>" +
        la.TIME +
        ":</strong></td><td>" +
        s +
        "</td></tr>",
      f = getObjectOdometer(a, d);
    -1 != f &&
      (D +=
        "<tr><td><strong>" +
        la.ODOMETER +
        ":</strong></td><td>" +
        f +
        " " +
        la.UNIT_DISTANCE +
        "</td></tr>");
    var E = getObjectEngineHours(a, d);
    -1 != E &&
      (D +=
        "<tr><td><strong>" +
        la.ENGINE_HOURS +
        ":</strong></td><td>" +
        E +
        "</td></tr>");
    var I = D + g;
    addPopupToMap(r, n, [0, -28 * u], (D += "</table>"), (I += "</table>"));
  });
}
function historyRouteRemoveDrive() {
  historyRouteData.layers.route_drive &&
    mapLayers.history.removeLayer(historyRouteData.layers.route_drive);
}
function historyRouteShowDrive(e) {
  historyRouteRemoveDrive();
  var t = historyRouteData.drives[e].id_start_s,
    a = historyRouteData.drives[e].id_end,
    o = new Array();
  for (i = 0; i <= a - t; i++) {
    var s = historyRouteData.route[t + i].lat,
      n = historyRouteData.route[t + i].lng;
    o.push(L.latLng(s, n));
  }
  var r = L.polyline(o, {
    color: settingsUserData.map_rhc,
    opacity: 0.8,
    weight: 3,
  });
  mapLayers.history.addLayer(r);
  var l = r.getBounds();
  map.fitBounds(l), (historyRouteData.layers.route_drive = r);
}
var settingsUserData = new Array(),
  settingsObjectData = new Array(),
  settingsObjectGroupData = new Array(),
  settingsObjectDriverData = new Array(),
  settingsObjectTrailerData = new Array(),
  settingsKMLData = new Array();
function settingsReloadUser() {
  setTimeout(function () {
    window.location.reload();
  }, 3e3);
}
function settingsSave() {
  if (utilsCheckPrivileges("viewer")) {
    var e = document.getElementById("page_settings_push_notify_mobile").value,
      t = document.getElementById(
        "page_settings_push_notify_mobile_interval"
      ).value,
      a = document.getElementById("page_settings_map_startup_possition").value,
      o = document.getElementById("page_settings_map_icon_size").value,
      s = document.getElementById("page_settings_startup_tab").value,
      n = document.getElementById("page_settings_language").value,
      r = document.getElementById("page_settings_distance_unit").value;
    (r += "," + document.getElementById("page_settings_capacity_unit").value),
      (r +=
        "," + document.getElementById("page_settings_temperature_unit").value);
    var l = document.getElementById("page_settings_timezone").value,
      i = document.getElementById("page_settings_old_password").value,
      c = document.getElementById("page_settings_new_password").value,
      d = document.getElementById("page_settings_new_password_rep").value;
    if (i.length > 0) {
      if (c.length < 6) return void bootbox.alert(la.PASSWORD_LENGHT_AT_LEAST);
      if (-1 != c.indexOf(" "))
        return void bootbox.alert(la.PASSWORD_SPACE_CHARACTERS);
      if (c != d) return void bootbox.alert(la.REPEATED_PASSWORD_IS_INCORRECT);
    }
    var u = {
      cmd: "save_user_settings",
      sms_gateway: "na",
      sms_gateway_type: "na",
      sms_gateway_url: "na",
      sms_gateway_identifier: "na",
      chat_notify: "na",
      dashboard: "na",
      map_sp: a,
      map_is: o,
      map_rc: "na",
      map_rhc: "na",
      map_ocp: "na",
      groups_collapsed: "na",
      od: "na",
      ohc: "na",
      datalist: "na",
      datalist_items: "na",
      push_notify_desktop: "na",
      push_notify_mobile: e,
      push_notify_mobile_interval: t,
      startup_tab: s,
      language: n,
      units: r,
      currency: "na",
      timezone: l,
      dst: "na",
      dst_start: "na",
      dst_end: "na",
      info: "na",
      old_password: i,
      new_password: c,
    };
    $.ajax({
      type: "POST",
      url: "../func/fn_settings.php",
      data: u,
      cache: !1,
      success: function (e) {
        "OK" == e
          ? (settingsReloadUser(), bootbox.alert(la.CHANGES_SAVED_SUCCESSFULLY))
          : "ERROR_INCORRECT_PASSWORD" == e &&
            bootbox.alert(la.INCORRECT_PASSWORD);
      },
    });
  }
}
function loadSettings(e, t) {
  switch (e) {
    case "cookies":
      var a = getCookie("gs_map");
      null == a &&
        ((a =
          gsValues.map_lat +
          ";" +
          gsValues.map_lng +
          ";" +
          gsValues.map_zoom +
          ";" +
          gsValues.map_layer +
          ";"),
        (a +=
          gsValues.map_objects +
          ";" +
          gsValues.map_object_labels +
          ";" +
          gsValues.map_markers +
          ";" +
          gsValues.map_routes +
          ";" +
          gsValues.map_zones +
          ";" +
          gsValues.map_clusters +
          ";" +
          gsValues.map_kml)),
        (a = a.split(";")),
        "last" == settingsUserData.map_sp &&
          (null != a[0] && "" != a[0] && (gsValues.map_lat = a[0]),
          null != a[1] && "" != a[1] && (gsValues.map_lng = a[1]),
          null != a[2] && "" != a[2] && (gsValues.map_zoom = a[2])),
        null != a[3] && "" != a[3] && (gsValues.map_layer = a[3]),
        null != a[4] &&
          "" != a[4] &&
          (gsValues.map_objects = strToBoolean(a[4])),
        null != a[5] &&
          "" != a[5] &&
          (gsValues.map_object_labels = strToBoolean(a[5])),
        null != a[6] &&
          "" != a[6] &&
          (gsValues.map_markers = strToBoolean(a[6])),
        null != a[7] &&
          "" != a[7] &&
          (gsValues.map_routes = strToBoolean(a[7])),
        null != a[8] && "" != a[8] && (gsValues.map_zones = strToBoolean(a[8])),
        null != a[9] &&
          "" != a[9] &&
          (gsValues.map_clusters = strToBoolean(a[9])),
        null != a[10] &&
          "" != a[10] &&
          (gsValues.map_kml = strToBoolean(a[10])),
        t(!0);
      break;
    case "server":
      var o = {
        cmd: "load_server_data",
      };
      $.ajax({
        type: "POST",
        url: "../func/fn_settings.php",
        data: o,
        dataType: "json",
        cache: !1,
        success: function (e) {
          (gsValues.map_custom = e.map_custom),
            (gsValues.map_osm = strToBoolean(e.map_osm)),
            (gsValues.map_bing = strToBoolean(e.map_bing)),
            (gsValues.map_google = strToBoolean(e.map_google)),
            (gsValues.map_google_traffic = strToBoolean(e.map_google_traffic)),
            (gsValues.map_mapbox = strToBoolean(e.map_mapbox)),
            (gsValues.map_yandex = strToBoolean(e.map_yandex)),
            (gsValues.map_bing_key = e.map_bing_key),
            (gsValues.map_mapbox_key = e.map_mapbox_key),
            (gsValues.map_layer = e.map_layer),
            (gsValues.map_zoom = e.map_zoom),
            (gsValues.map_lat = e.map_lat),
            (gsValues.map_lng = e.map_lng),
            (gsValues.address_display_object_data_list = strToBoolean(
              e.address_display_object_data_list
            )),
            (gsValues.address_display_event_data_list = strToBoolean(
              e.address_display_event_data_list
            )),
            (gsValues.address_display_history_route_data_list = strToBoolean(
              e.address_display_history_route_data_list
            )),
            t(!0);
        },
      });
      break;
    case "user":
      o = {
        cmd: "load_user_data",
      };
      $.ajax({
        type: "POST",
        url: "../func/fn_settings.php",
        data: o,
        dataType: "json",
        cache: !1,
        success: function (e) {
          (settingsUserData = e),
            (document.getElementById("page_settings_push_notify_mobile").value =
              settingsUserData.push_notify_mobile),
            (document.getElementById(
              "page_settings_push_notify_mobile_interval"
            ).value = settingsUserData.push_notify_mobile_interval),
            (document.getElementById(
              "page_settings_map_startup_possition"
            ).value = settingsUserData.map_sp),
            (document.getElementById("page_settings_map_icon_size").value =
              settingsUserData.map_is),
            (document.getElementById("page_settings_startup_tab").value =
              settingsUserData.startup_tab),
            (document.getElementById("page_settings_language").value =
              settingsUserData.language),
            (document.getElementById("page_settings_distance_unit").value =
              settingsUserData.unit_distance),
            (document.getElementById("page_settings_capacity_unit").value =
              settingsUserData.unit_capacity),
            (document.getElementById("page_settings_temperature_unit").value =
              settingsUserData.unit_temperature),
            (document.getElementById("page_settings_timezone").value =
              settingsUserData.timezone),
            t(!0);
        },
      });
      break;
    case "objects":
      o = {
        cmd: "load_object_data",
      };
      $.ajax({
        type: "POST",
        url: "../func/fn_settings.objects.php",
        data: o,
        dataType: "json",
        cache: !1,
        success: function (e) {
          (e = transformsToSettingsObjectData(e)),
            (settingsObjectData = e),
            initSelectList("history_object_list"),
            initSelectList("cmd_object_list"),
            loadObjectMapMarkerIcons(),
            t(!0);
        },
      });
      break;
    case "object_groups":
      o = {
        cmd: "load_object_group_data",
      };
      $.ajax({
        type: "POST",
        url: "../func/fn_settings.groups.php",
        data: o,
        dataType: "json",
        cache: !1,
        success: function (e) {
          (settingsObjectGroupData = e),
            initSelectList("object_group_list"),
            t(!0);
        },
      });
      break;
    case "object_drivers":
      o = {
        cmd: "load_object_driver_data",
      };
      $.ajax({
        type: "POST",
        url: "../func/fn_settings.drivers.php",
        data: o,
        dataType: "json",
        cache: !1,
        success: function (e) {
          (settingsObjectDriverData = e), t(!0);
        },
      });
      break;
    case "object_trailers":
      o = {
        cmd: "load_object_trailer_data",
      };
      $.ajax({
        type: "POST",
        url: "../func/fn_settings.trailers.php",
        data: o,
        dataType: "json",
        cache: !1,
        success: function (e) {
          (settingsObjectTrailerData = e), t(!0);
        },
      });
      break;
    case "kml":
      o = {
        cmd: "load_kml_data",
      };
      $.ajax({
        type: "POST",
        url: "../func/fn_settings.kml.php",
        data: o,
        dataType: "json",
        cache: !1,
        success: function (e) {
          (settingsKMLData = e), t(!0);
        },
      });
  }
}
function settingsSaveCookies() {
  if (null != map && map.getZoom() && map.getCenter() && map.getCenter()) {
    var e =
      map.getCenter().lat +
      ";" +
      map.getCenter().lng +
      ";" +
      map.getZoom() +
      ";" +
      gsValues.map_layer +
      ";";
    (e +=
      gsValues.map_objects +
      ";" +
      gsValues.map_object_labels +
      ";" +
      gsValues.map_markers +
      ";" +
      gsValues.map_routes +
      ";" +
      gsValues.map_zones +
      ";" +
      gsValues.map_clusters +
      ";" +
      gsValues.map_kml),
      setCookie("gs_map", e, 30);
  }
}
function settingsPushWarning() {
  "true" == document.getElementById("page_settings_push_notify_mobile").value &&
    bootbox.alert(la.CONTINUED_USE_OF_PUSH_NOTIFICATIONS_DECREASE_BATTERY_LIFE);
}
(gsValues.map_fit_objects_finished = !1),
  (gsValues.map_objects = !0),
  (gsValues.map_object_labels = !0),
  (gsValues.map_markers = !0),
  (gsValues.map_routes = !0),
  (gsValues.map_zones = !0),
  (gsValues.map_clusters = !1),
  (gsValues.map_traffic = !1),
  (gsValues.objects_visible = !0),
  (gsValues.objects_follow = !1),
  (gsValues.menu = "map");
var map,
  timer_objectLoadData,
  la = [],
  mapMarkerIcons = new Array(),
  mapLayers = new Array(),
  objectsData = new Array();
function load() {
  loadLanguage(function (e) {
    loadSettings("server", function (e) {
      loadSettings("user", function (e) {
        loadSettings("cookies", function (e) {
          loadSettings("object_groups", function (e) {
            loadSettings("object_drivers", function (e) {
              loadSettings("object_trailers", function (e) {
                loadSettings("objects", function (e) {
                  loadSettings("kml", function (e) {
                    load2();
                  });
                });
              });
            });
          });
        });
      });
    });
  });
}
function load2() {
  initMap(),
    initGui(),
    initGraph(),
    objectLoadList(),
    eventsLoadList(),
    eventsLoadData(),
    placesMarkerLoadData(),
    placesRouteLoadData(),
    placesZoneLoadData(),
    placesMarkerLoadList(),
    placesRouteLoadList(),
    placesZoneLoadList(),
    1 == settingsUserData.privileges_object_control && cmdTemplateLoadData(),
    (document.getElementById("loading_panel").style.display = "none"),
    notifyCheck("session_check"),
    null != getUrlVars().page
      ? "events" == getUrlVars().page && switchPage("events")
      : "objects" == settingsUserData.startup_tab && switchPage("objects");
}
function unload() {
  settingsSaveCookies();
}
function objectLoadList() {
  var e = document.getElementById("page_object_search").value,
    t = document.getElementById("page_object_list_group").value,
    a = "";
  document.getElementById("page_object_list").innerHTML = "";
  var o = new Array();
  for (var s in settingsObjectData) {
    if (t == -1) {
      if (/\d+_\d+/.test(s)) {
        continue;
      }
      o.push({
        imei: s,
        name: settingsObjectData[s].name,
      });
    } else if (t == settingsObjectData[s].group_id) {
      o.push({
        imei: s,
        name: settingsObjectData[s].name,
      });
    }
  }
  o = sortArrayByElement(o, "name");
  for (var n = 0; n < o.length; n++) {
    s = o[n].imei;
    var r = settingsObjectData[s];
    if ("true" == r.active) {
      var l =
          '<img src="../' + r.icon + '" style="width: 26px; height: 26px;"/>',
        i = r.name;
      if (-1 != i.toLowerCase().indexOf(e.toLowerCase())) {
        var c = '<li class="list-group-item" id="object_' + s + '">';
        (c += '<div class="row vertical-align">'),
          (c +=
            '<div class="icon" onClick="objectPanToZoom(\'' +
            s +
            "');\">" +
            l +
            "</div>"),
          (c +=
            '<div class="object-list-item"><div class="left" onClick="objectPanToZoom(\'' +
            s +
            '\');"><div class="name">' +
            i +
            '</div><div class="status" id="object_status_' +
            s +
            '">' +
            la.NO_DATA +
            "</div></div>"),
          (c +=
            '<div class="right"><div class="speed" id="object_speed_' +
            s +
            '">0 ' +
            la.UNIT_SPEED +
            "</div>"),
          (c += '<div class="engine" id="object_engine_' + s + '"></div>'),
          (c +=
            '<div class="connection" id="object_connection_' +
            s +
            '">' +
            getConnectionIcon(0) +
            "</div>"),
          (c +=
            '<div class="visible" id="object_visible_' +
            s +
            '" onClick="objectVisibleToggle(\'' +
            s +
            "');\">"),
          (c += '<span class="[ glyphicon glyphicon-ok ]"></span>'),
          (c += "</div>"),
          (c +=
            '<div class="follow" id="object_follow_' +
            s +
            '" onClick="objectFollowToggle(\'' +
            s +
            "');\">"),
          (c += '<span class="[ glyphicon glyphicon-search ]"></span>'),
          (c += "</div>"),
          (c +=
            '<div class="details" onClick="objectSelect(\'' +
            s +
            "');objectShowDetails('" +
            s +
            "');\">"),
          (c += '<span class="[ glyphicon glyphicon-align-justify ]"></span>'),
          (c += "</div></div></div>"),
          (c += "</div>"),
          (a += c += "</li>");
      }
    }
  }
  (document.getElementById("page_object_list").innerHTML = a), objectLoadData();
}
function objectUpdateList() {
  for (var e in objectsData) {
    if ("" != objectsData[e].data) {
      if (null != document.getElementById("object_status_" + e)) {
        (document.getElementById("object_visible_" + e).checked =
          objectsData[e].visible),
          (document.getElementById("object_follow_" + e).checked =
            objectsData[e].follow),
          setCheckIcon("#object_visible_" + e, objectsData[e].visible),
          setCheckIcon("#object_follow_" + e, objectsData[e].follow);
        var t = objectsData[e].status_string;
        "server" == settingsUserData.od
          ? (document.getElementById("object_status_" + e).innerHTML =
              objectsData[e].data[0].dt_server)
          : "status" == settingsUserData.od && "" != t
          ? (document.getElementById("object_status_" + e).innerHTML = t)
          : (document.getElementById("object_status_" + e).innerHTML =
              objectsData[e].data[0].dt_tracker),
          (document.getElementById("object_speed_" + e).innerHTML =
            objectsData[e].data[0].speed + "&nbsp;" + la.UNIT_SPEED);
        var a = getSensorFromType(e, "acc");
        if (0 == a || 0 == objectsData[e].connection) {
          document.getElementById("object_engine_" + e).innerHTML = "";
        } else {
          1 == getSensorValue(objectsData[e].data[0].params, a[0]).value
            ? (document.getElementById("object_engine_" + e).innerHTML =
                getEngineIcon(1))
            : (document.getElementById("object_engine_" + e).innerHTML =
                getEngineIcon(0));
        }
        document.getElementById("object_connection_" + e).innerHTML =
          getConnectionIcon(objectsData[e].connection);
      }
      1 == objectsData[e].selected &&
        ("block" == document.getElementById("bottom_panel").style.display &&
          datalistBottomShowData("object", e, objectsData[e].data[0]),
        "block" ==
          document.getElementById("page_object_details").style.display &&
          objectShowDetails(e));
    } else
      null != document.getElementById("object_status_" + e) &&
        ((document.getElementById("object_visible_" + e).checked =
          objectsData[e].visible),
        setCheckIcon("#object_visible_" + e, objectsData[e].visible),
        (document.getElementById("object_status_" + e).innerHTML = la.NO_DATA),
        (document.getElementById("object_speed_" + e).innerHTML =
          "0 " + la.UNIT_SPEED),
        (document.getElementById("object_engine_" + e).innerHTML = ""),
        (document.getElementById("object_connection_" + e).innerHTML =
          getConnectionIcon(objectsData[e].connection)));
    objectSetListStatus(e, objectsData[e].status, !1);
  }
}
function objectSetListStatus(e, t, a) {
  var o = getObjectListColor(t, a);
  null != document.getElementById("object_status_" + e) &&
    (document.getElementById("object_" + e).style.backgroundColor = o);
}
function objectLoadData() {
  clearTimeout(timer_objectLoadData);
  $.ajax({
    type: "POST",
    url: "../func/fn_objects.php",
    data: {
      cmd: "load_object_data",
    },
    dataType: "json",
    cache: !1,
    error: function (e, t) {
      timer_objectLoadData = setTimeout(
        "objectLoadData();",
        1e3 * gsValues.map_refresh
      );
    },
    success: function (e) {
      for (var t in e) e[t] = transformToObjectData(e[t]);
      if (Object.keys(objectsData).length != Object.keys(e).length)
        objectsData = e;
      else
        for (var t in e) {
          objectsData[t].connection = e[t].connection;
          objectsData[t].status = e[t].status;
          objectsData[t].status_string = e[t].status_string;
          objectsData[t].odometer = e[t].odometer;
          objectsData[t].engine_hours = e[t].engine_hours;
          objectsData[t].service = e[t].service;

          if (objectsData[t].data == "") {
            objectsData[t].data = e[t].data;
          } else {
            if (/\d+_\d+/.test(t)) {
              continue;
            }
            if (
              objectsData[t].data.length <= settingsObjectData[t].tail_points
            ) {
              objectsData[t].data.pop();
              objectsData[t].data.unshift(e[t].data[0]);
            }
          }
        }
      objectUpdateList();
      objectAddAllToMap();
      "fit" == settingsUserData.map_sp &&
        0 == gsValues.map_fit_objects_finished &&
        (fitObjectsOnMap(), (gsValues.map_fit_objects_finished = !0));
      objectFollow();
      timer_objectLoadData = setTimeout(
        "objectLoadData();",
        1e3 * gsValues.map_refresh
      );
    },
  });
}
function objectAddAllToMap() {
  var e = document.getElementById("page_object_search").value;
  objectRemoveAllFromMap();
  for (var t in objectsData) {
    rt = t.split("_");
    if (rt[1]) continue;
    "true" == settingsObjectData[rt[0]].active &&
      (strMatches(settingsObjectData[rt[0]].name, e) || strMatches(rt[0], e)) &&
      (objectAddToMap(rt[0]), objectVisible(rt[0]));
  }
}
function objectRemoveAllFromMap() {
  mapLayers.realtime.clearLayers();
}
function objectAddToMap(e) {
  var t = settingsObjectData[e].name;
  if ("" != objectsData[e].data)
    var a = objectsData[e].data[0].lat,
      o = objectsData[e].data[0].lng,
      s = objectsData[e].data[0].altitude,
      n = objectsData[e].data[0].angle,
      r = objectsData[e].data[0].speed,
      l = objectsData[e].data[0].dt_tracker,
      i = objectsData[e].data[0].params;
  else (a = 0), (o = 0), (r = 0), (i = !1);
  var c = settingsUserData.map_is,
    d = n;
  "arrow" != settingsObjectData[e].map_icon && (d = 0);
  var u = objectsData[e].status,
    m = getMarkerIcon(e, r, u, !1),
    p = L.marker([a, o], {
      icon: m,
      iconAngle: d,
    }),
    g = t + " (" + r + " " + la.UNIT_SPEED + ")";
  p
    .bindTooltip(g, {
      permanent: !0,
      offset: [20 * c, 0],
      direction: "right",
    })
    .openTooltip(),
    p.on("click", function (d) {
      historyLoadRoute({ showIntoGPS: true, device: e });
      /*
      "" != objectsData[e].data &&
        ((("block" == document.getElementById("bottom_panel").style.display &&
          1 == objectsData[e].selected) ||
          null != historyRouteData.route) &&
          geocoderGetAddress(a, o, function (d) {
            var u = d,
              m = urlPosition(a, o),
              p = "",
              g = "",
              _ = "",
              y = new Array();
            for (var v in settingsObjectData[e].sensors)
              y.push(settingsObjectData[e].sensors[v]);
            var h = sortArrayByElement(y, "name");
            for (var v in h) {
              var b = h[v];
              if ("true" == b.popup)
                if ("fuelsumup" == b.type) {
                  var D = getSensorValueFuelLevelSumUp(e, i, b);
                  p +=
                    "<tr><td><strong>" +
                    b.name +
                    ":</strong></td><td>" +
                    D.value_full +
                    "</td></tr>";
                } else {
                  D = getSensorValue(i, b);
                  p +=
                    "<tr><td><strong>" +
                    b.name +
                    ":</strong></td><td>" +
                    D.value_full +
                    "</td></tr>";
                }
            }
            var f = new Array();
            for (var v in settingsObjectData[e].custom_fields)
              f.push(settingsObjectData[e].custom_fields[v]);
            var E = sortArrayByElement(f, "name");
            for (var v in E) {
              var I = E[v];
              "true" == I.popup &&
                (g +=
                  "<tr><td><strong>" +
                  I.name +
                  ":</strong></td><td>" +
                  textToLinks(I.value) +
                  "</td></tr>");
            }
            var j = new Array();
            for (var v in objectsData[e].service)
              j.push(objectsData[e].service[v]);
            var T = sortArrayByElement(j, "name");
            for (var v in T)
              "true" == T[v].popup &&
                (_ +=
                  "<tr><td><strong>" +
                  T[v].name +
                  ":</strong></td><td>" +
                  T[v].status +
                  "</td></tr>");
            var L =
                "<table>\t\t\t\t\t\t<tr><td><strong>" +
                la.OBJECT +
                ":</strong></td><td>" +
                t +
                "</td></tr>\t\t\t\t\t\t<tr><td><strong>" +
                la.ADDRESS +
                ":</strong></td><td>" +
                u +
                "</td></tr>\t\t\t\t\t\t<tr><td><strong>" +
                la.POSITION +
                ":</strong></td><td>" +
                m +
                "</td></tr>\t\t\t\t\t\t<tr><td><strong>" +
                la.ALTITUDE +
                ":</strong></td><td>" +
                s +
                " " +
                la.UNIT_HEIGHT +
                "</td></tr>\t\t\t\t\t\t<tr><td><strong>" +
                la.ANGLE +
                ":</strong></td><td>" +
                n +
                " &deg;</td></tr>\t\t\t\t\t\t<tr><td><strong>" +
                la.SPEED +
                ":</strong></td><td>" +
                r +
                " " +
                la.UNIT_SPEED +
                "</td></tr>\t\t\t\t\t\t<tr><td><strong>" +
                la.TIME +
                ":</strong></td><td>" +
                l +
                "</td></tr>",
              k = getObjectOdometer(e, !1);
            -1 != k &&
              (L +=
                "<tr><td><strong>" +
                la.ODOMETER +
                ":</strong></td><td>" +
                k +
                " " +
                la.UNIT_DISTANCE +
                "</td></tr>");
            var R = getObjectEngineHours(e, !1);
            -1 != R &&
              (L +=
                "<tr><td><strong>" +
                la.ENGINE_HOURS +
                ":</strong></td><td>" +
                R +
                "</td></tr>");
            var B = L + g + p + _;
            addPopupToMap(
              a,
              o,
              [0, -14 * c],
              (L += "</table>"),
              (B += "</table>")
            );
          }),
        null == historyRouteData.route &&
          datalistBottomShowData("object", e, objectsData[e].data[0]),
          objectSelect(e));
      */
    }),
    p.on("add", function (t) {
      0 == gsValues.map_object_labels && p.closeTooltip(),
        objectAddTailToMap(e);
    }),
    p.on("remove", function (t) {
      null != objectsData[e] &&
        objectsData[e].layers.tail &&
        mapLayers.realtime.removeLayer(objectsData[e].layers.tail);
    }),
    mapLayers.realtime.addLayer(p),
    (objectsData[e].layers.marker = p);
}
function objectAddTailToMap(e) {
  if (settingsObjectData[e].tail_points > 0) {
    objectsData[e].layers.tail &&
      mapLayers.realtime.removeLayer(objectsData[e].layers.tail);
    var t,
      a = new Array();
    for (t = 0; t < objectsData[e].data.length; t++) {
      var o = objectsData[e].data[t].lat,
        s = objectsData[e].data[t].lng;
      a.push(L.latLng(o, s));
    }
    var n = L.polyline(a, {
      color: settingsObjectData[e].tail_color,
      opacity: 0.8,
      weight: 3,
    });
    mapLayers.realtime.addLayer(n), (objectsData[e].layers.tail = n);
  }
}
function objectVisibleToggle(e) {
  1 == objectsData[e].visible
    ? objectSetVisible(e, !1)
    : objectSetVisible(e, !0);
  var t = !0;
  for (var a in objectsData) objectsData[a].visible || (t = !1);
  setCheckIcon("#object_visible_all", !!t);
}
function objectVisible(e) {
  1 == objectsData[e].visible
    ? mapLayers.realtime.addLayer(objectsData[e].layers.marker)
    : mapLayers.realtime.removeLayer(objectsData[e].layers.marker);
}
function objectSetVisible(e, t) {
  (objectsData[e].visible = t),
    setCheckIcon("#object_visible_" + e, t),
    objectVisible(e);
}
function objectVisibleAllToggle() {
  1 == gsValues.objects_visible
    ? (setCheckIcon("#object_visible_all", !1), objectVisibleAll(!1))
    : (setCheckIcon("#object_visible_all", !0), objectVisibleAll(!0));
}
function objectVisibleAll(e) {
  for (var t in ((gsValues.objects_visible = e), objectsData))
    (objectsData[t].visible = e), objectSetVisible(t, e), objectVisible(t);
}
function objectFollowToggle(e) {
  1 == objectsData[e].follow ? objectSetFollow(e, !1) : objectSetFollow(e, !0);
  var t = !0;
  for (var a in objectsData) objectsData[a].follow || (t = !1);
  setCheckIcon("#object_follow_all", !!t);
}
function objectFollow() {
  var e = 0,
    t = new Array();
  for (var a in objectsData)
    if ("" != objectsData[a].data && 1 == objectsData[a].follow) {
      var o = objectsData[a].data[0].lat,
        s = objectsData[a].data[0].lng;
      t.push([o, s]), (e += 1);
    }
  e > 1
    ? map.fitBounds(t)
    : 1 == e &&
      map.panTo({
        lat: o,
        lng: s,
      });
}
function objectSetFollow(e, t) {
  (objectsData[e].follow = t),
    setCheckIcon("#object_follow_" + e, t),
    objectFollow();
}
function objectFollowAllToggle() {
  1 == gsValues.objects_follow
    ? (setCheckIcon("#object_follow_all", !1), objectFollowAll(!1))
    : (setCheckIcon("#object_follow_all", !0),
      objectFollowAll(!0),
      objectFollow());
}
function objectFollowAll(e) {
  for (var t in ((gsValues.objects_follow = e), objectsData))
    (objectsData[t].follow = e), setCheckIcon("#object_follow_" + t, e);
}
function objectPanToZoom(e) {
  if ("" != objectsData[e].data) {
    null == historyRouteData.route &&
      datalistBottomShowData("object", e, objectsData[e].data[0]),
      objectSelect(e),
      switchPage("map"),
      objectSetVisible(e, !0);
    var t = objectsData[e].data[0].lat,
      a = objectsData[e].data[0].lng;
    map.setView([t, a], 15);
  }
}
function objectPanTo(e) {
  if ("" != objectsData[e].data) {
    var t = objectsData[e].data[0].lat,
      a = objectsData[e].data[0].lng;
    map.panTo({
      lat: t,
      lng: a,
    });
  }
}
function objectSelect(e) {
  objectUnSelectAll(),
    "" != objectsData[e].data && (objectsData[e].selected = !0);
}
function objectUnSelectAll() {
  for (var e in objectsData) objectsData[e].selected = !1;
}
function objectShowDetailsItems(e, t) {
  var a = '<li class="list-group-item"><div class="row vertical-align">';
  return (
    (a += '<div class="col-xs-6">'),
    (a += e),
    (a += "</div>"),
    (a += '<div class="col-xs-6">'),
    (a += t),
    (a += "</div>"),
    (a += "</div></li>")
  );
}
function objectShowDetails(e) {
  document.getElementById("page_object_detail_list").innerHTML = "";
  var t = settingsObjectData[e].name,
    a =
      '<div class="panel-heading"><b>' +
      la.GENERAL +
      '</b></div><ul class="list-group">';
  a += objectShowDetailsItems(la.OBJECT, t);
  var o = settingsObjectData[e].model;
  "" != o && (a += objectShowDetailsItems(la.MODEL, o));
  var s = settingsObjectData[e].vin;
  "" != s && (a += objectShowDetailsItems(la.VIN, s));
  var n = settingsObjectData[e].plate_number;
  "" != n && (a += objectShowDetailsItems(la.PLATE, n));
  var r = getObjectOdometer(e, !1);
  -1 != r &&
    ((r = r + " " + la.UNIT_DISTANCE),
    (a += objectShowDetailsItems(la.ODOMETER, r)));
  var l = getObjectEngineHours(e, !1);
  -1 != l && (a += objectShowDetailsItems(la.ENGINE_HOURS, l));
  var i = objectsData[e].status_string;
  "" != i && (a += objectShowDetailsItems(la.STATUS, i));
  var c = settingsObjectData[e].device;
  "" != c && (a += objectShowDetailsItems(la.GPS_DEVICE, c));
  var d = settingsObjectData[e].sim_number;
  "" != d && (a += objectShowDetailsItems(la.SIM_CARD_NUMBER, d));
  var u = new Array();
  for (var m in settingsObjectData[e].custom_fields)
    "true" == settingsObjectData[e].custom_fields[m].data_list &&
      u.push(settingsObjectData[e].custom_fields[m]);
  var p = sortArrayByElement(u, "name");
  if ("" != p)
    for (var m in p) {
      var g = p[m];
      a += objectShowDetailsItems(g.name, g.value);
    }
  if (
    ((a += "</ul>"),
    (a +=
      '<div class="panel-heading"><b>' +
      la.LOCATION +
      '</b></div><ul class="list-group">'),
    "" != objectsData[e].data)
  ) {
    var _ = objectsData[e].data[0].dt_server,
      y = objectsData[e].data[0].dt_tracker,
      v = objectsData[e].data[0].lat,
      h = objectsData[e].data[0].lng,
      b = objectsData[e].data[0].altitude + " " + la.UNIT_HEIGHT,
      D = objectsData[e].data[0].angle + " &deg;",
      f = objectsData[e].data[0].speed + " " + la.UNIT_SPEED,
      E = urlPosition(v, h);
    (a += objectShowDetailsItems(
      la.ADDRESS,
      '<span id="page_object_detail_list_address"></span>'
    )),
      (a += objectShowDetailsItems(la.POSITION, E)),
      (a += objectShowDetailsItems(la.ALTITUDE, b)),
      (a += objectShowDetailsItems(la.ANGLE, D)),
      (a += objectShowDetailsItems(la.SPEED, f)),
      (a += objectShowDetailsItems(la.TIME_POSITION, y)),
      (a += objectShowDetailsItems(la.TIME_SERVER, _));
  } else a += objectShowDetailsItems(la.NO_DATA, "");
  if (((a += "</ul>"), "" != objectsData[e].data)) {
    var I = objectsData[e].data[0].params,
      j = new Array();
    for (var m in settingsObjectData[e].sensors)
      "true" == settingsObjectData[e].sensors[m].data_list &&
        j.push(settingsObjectData[e].sensors[m]);
    var T = sortArrayByElement(j, "name");
    if ("" != T) {
      for (var m in ((a +=
        '<div class="panel-heading"><b>' +
        la.SENSORS +
        '</b></div><ul class="list-group">'),
      T)) {
        var L = T[m];
        if ("true" == L.data_list)
          if ("fuelsumup" == L.type) {
            var k = getSensorValueFuelLevelSumUp(e, I, L);
            a += objectShowDetailsItems(L.name, k.value_full);
          } else {
            k = getSensorValue(I, L);
            a += objectShowDetailsItems(L.name, k.value_full);
          }
      }
      a += "</ul>";
    }
  }
  if ("" != objectsData[e].data) {
    var R = new Array();
    for (var m in objectsData[e].service)
      "true" == objectsData[e].service[m].data_list &&
        R.push(objectsData[e].service[m]);
    var B = sortArrayByElement(R, "name");
    if ("" != B) {
      for (var m in ((a +=
        '<div class="panel-heading"><b>' +
        la.SERVICE +
        '</b></div><ul class="list-group">'),
      B))
        a += objectShowDetailsItems(B[m].name, B[m].status);
      a += "</ul>";
    }
  }
  if ("" != objectsData[e].data) {
    I = objectsData[e].data[0].params;
    var S = getDriver(e, I);
    if (0 != S) {
      a +=
        '<div class="panel-heading"><b>' +
        la.DRIVER_INFO +
        '</b></div><ul class="list-group">';
      var O = S.name,
        w = S.idn,
        M = S.address,
        A = S.phone,
        N = S.email,
        P = S.desc;
      (a += objectShowDetailsItems(la.NAME, O)),
        "" != w && (a += objectShowDetailsItems(la.ID_NUMBER, w)),
        "" != M && (a += objectShowDetailsItems(la.ADDRESS, M)),
        "" != A && (a += objectShowDetailsItems(la.PHONE, A)),
        "" != N && (a += objectShowDetailsItems(la.EMAIL, N)),
        "" != P && (a += objectShowDetailsItems(la.DESCRIPTION, P)),
        (a += "</ul>");
    }
  }
  if ("" != objectsData[e].data) {
    I = objectsData[e].data[0].params;
    var C = getTrailer(e, I);
    if (0 != C) {
      a +=
        '<div class="panel-heading"><b>' +
        la.TRAILER_INFO +
        '</b></div><ul class="list-group">';
      var V = C.name,
        x = C.model,
        U = C.vin,
        H = C.plate_number,
        Y = C.desc;
      (a += objectShowDetailsItems(la.NAME, V)),
        "" != x && (a += objectShowDetailsItems(la.MODEL, x)),
        "" != U && (a += objectShowDetailsItems(la.VIN, U)),
        "" != H && (a += objectShowDetailsItems(la.PLATE_NUMBER, H)),
        "" != Y && (a += objectShowDetailsItems(la.DESCRIPTION, Y)),
        (a += "</ul>");
    }
  }
  "" != objectsData[e].data &&
    geocoderGetAddress(v, h, function (e) {
      document.getElementById("page_object_detail_list_address").innerHTML = e;
    }),
    (document.getElementById("page_object_detail_list").innerHTML = a),
    switchPage("object_details");
}
var timer_eventsLoadData,
  eventsData = new Array();
function eventsLoadData() {
  clearTimeout(timer_eventsLoadData),
    (timer_eventsLoadData = setTimeout(
      "eventsLoadData();",
      1e3 * gsValues.event_refresh
    )),
    eventsCheckForNew();
}
function eventsCheckForNew() {
  var e = {
    cmd: "load_last_event",
    last_id: eventsData.last_id,
  };
  $.ajax({
    type: "POST",
    url: "../func/fn_events.php",
    data: e,
    dataType: "json",
    success: function (e) {
      if (0 != e)
        for (var t = 0; t < e.length; t++) {
          if (
            eventsData.last_id < e[t].event_id &&
            1 == eventsData.events_loaded &&
            null != settingsObjectData[e[t].imei] &&
            "true" == settingsObjectData[e[t].imei].active
          ) {
            var a = e[t].notify_system.split(",");
            if ("true" == a[0]) {
              var o = e[t].lat,
                s = e[t].lng,
                n = urlPosition(o, s),
                r =
                  "<center><table>\t\t\t\t\t\t\t\t\t\t\t<tr><td><strong>" +
                  la.OBJECT +
                  ":</strong></td><td>" +
                  e[t].name +
                  "</td></tr>\t\t\t\t\t\t\t\t\t\t\t<tr><td><strong>" +
                  la.EVENT +
                  ":</strong></td><td>" +
                  e[t].event_desc +
                  "</td></tr>\t\t\t\t\t\t\t\t\t\t\t<tr><td><strong>" +
                  la.POSITION +
                  ":</strong></td><td>" +
                  n +
                  "</td></tr>\t\t\t\t\t\t\t\t\t\t\t<tr><td><strong>" +
                  la.TIME +
                  ":</strong></td><td>" +
                  e[t].dt_tracker +
                  "</td></tr>\t\t\t\t\t\t\t\t\t\t\t</table></center>";
              r +=
                '<br/><center><a href="#" onclick="bootbox.hideAll();eventsShowEvent(' +
                e[t].event_id +
                ');">Show event</a></center>';
              if (
                ("true" == a[1] && !0,
                bootbox.alert({
                  message: r,
                  title: la.NEW_EVENT,
                }),
                "true" == a[2])
              )
                null == a[3] && (a[3] = "alarm1.mp3"),
                  new Audio("../snd/" + a[3]).play();
            }
          }
          t == e.length - 1 &&
            ((eventsData.last_id = e[t].event_id), eventsLoadList());
        }
      eventsData.events_loaded = !0;
    },
  });
}
function eventsLoadList() {
  var e = document.getElementById("page_event_search").value,
    t = document.getElementById("event_list_page").value;
  $.ajax({
    type: "POST",
    url:
      "../func/fn_events.php?cmd=load_event_list&s=" +
      e +
      "&page=" +
      t +
      "&rows=25&sidx=dt_tracker&sord=desc",
    dataType: "json",
    cache: !1,
    success: function (e) {
      var a = "";
      (document.getElementById("page_event_list").innerHTML = ""), (t = e.page);
      var o = e.total,
        s = (e.records, e.rows),
        n = document.getElementById("event_list_page");
      n.options.length = 0;
      for (var r = 1; r <= o; r++) n.options.add(new Option(r, r));
      for (var l in ((n.value = t), s)) {
        var i = s[l].id,
          c = s[l].cell,
          d = c[0],
          u = c[1],
          m = c[2],
          p =
            '<a href="#" onClick="eventsShowEvent(\'' +
            i +
            '\');" class="list-group-item clearfix">';
        (p += '<div class="row vertical-align">'),
          (p += '<div class="col-xs-3">'),
          (p += d =
            d.substring(0, 10) == moment().format("YYYY-MM-DD")
              ? d.substring(11, 19)
              : d.substring(2, 10)),
          (p += "</div>"),
          (p += '<div class="col-xs-4">'),
          (p += u),
          (p += "</div>"),
          (p += '<div class="col-xs-5">'),
          (p += m),
          (p += "</div>"),
          (p += "</div>"),
          (a += p += "</a>");
      }
      document.getElementById("page_event_list").innerHTML += a;
    },
  });
}
function eventsShowEvent(e) {
  var t = {
    cmd: "load_event_data",
    event_id: e,
  };
  $.ajax({
    type: "POST",
    url: "../func/fn_events.php",
    data: t,
    dataType: "json",
    cache: !1,
    success: function (e) {
      datalistBottomHidePanel(), historyHideRoute(), switchPage("map");
      var t = e.lat,
        a = e.lng;
      geocoderGetAddress(t, a, function (o) {
        var s = e.imei,
          n = o,
          r = urlPosition(t, a),
          l = e.params,
          i = "",
          c = new Array();
        for (var d in settingsObjectData[s].sensors)
          c.push(settingsObjectData[s].sensors[d]);
        var u = sortArrayByElement(c, "name");
        for (var d in u) {
          var m = u[d];
          if ("true" == m.popup)
            if ("fuelsumup" == m.type) {
              var p = getSensorValueFuelLevelSumUp(s, l, m);
              i +=
                "<tr><td><strong>" +
                m.name +
                ":</strong></td><td>" +
                p.value_full +
                "</td></tr>";
            } else {
              p = getSensorValue(l, m);
              i +=
                "<tr><td><strong>" +
                m.name +
                ":</strong></td><td>" +
                p.value_full +
                "</td></tr>";
            }
        }
        var g =
            "<table>\t\t\t\t\t<tr><td><strong>" +
            la.OBJECT +
            ":</strong></td><td>" +
            e.name +
            "</td></tr>\t\t\t\t\t<tr><td><strong>" +
            la.EVENT +
            ":</strong></td><td>" +
            e.event_desc +
            "</td></tr>\t\t\t\t\t<tr><td><strong>" +
            la.ADDRESS +
            ":</strong></td><td>" +
            n +
            "</td></tr>\t\t\t\t\t<tr><td><strong>" +
            la.POSITION +
            ":</strong></td><td>" +
            r +
            "</td></tr>\t\t\t\t\t<tr><td><strong>" +
            la.ALTITUDE +
            ":</strong></td><td>" +
            e.altitude +
            " " +
            la.UNIT_HEIGHT +
            "</td></tr>\t\t\t\t\t<tr><td><strong>" +
            la.ANGLE +
            ":</strong></td><td>" +
            e.angle +
            " &deg;</td></tr>\t\t\t\t\t<tr><td><strong>" +
            la.SPEED +
            ":</strong></td><td>" +
            e.speed +
            " " +
            la.UNIT_SPEED +
            "</td></tr>\t\t\t\t\t<tr><td><strong>" +
            la.TIME +
            ":</strong></td><td>" +
            e.dt_tracker +
            "</td></tr>",
          _ = getObjectOdometer(s, l);
        -1 != _ &&
          (g +=
            "<tr><td><strong>" +
            la.ODOMETER +
            ":</strong></td><td>" +
            _ +
            " " +
            la.UNIT_DISTANCE +
            "</td></tr>");
        var y = getObjectEngineHours(s, l);
        -1 != y &&
          (g +=
            "<tr><td><strong>" +
            la.ENGINE_HOURS +
            ":</strong></td><td>" +
            y +
            "</td></tr>");
        var v = g + i;
        addPopupToMap(t, a, [0, 0], (g += "</table>"), (v += "</table>")),
          map.panTo({
            lat: t,
            lng: a,
          });
      });
    },
  });
}
function utilsCheckPrivileges(e) {
  switch (e) {
    case "viewer":
      if (
        ("" == settingsUserData.privileges ||
          "viewer" == settingsUserData.privileges) &&
        0 == settingsUserData.cpanel_privileges
      )
        return bootbox.alert(la.THIS_ACCOUNT_HAS_NO_PRIVILEGES_TO_DO_THAT), !1;
      break;
    case "history":
      if (1 != settingsUserData.privileges_history)
        return bootbox.alert(la.THIS_ACCOUNT_HAS_NO_PRIVILEGES_TO_DO_THAT), !1;
      break;
    case "object_control":
      if (1 != settingsUserData.privileges_object_control)
        return bootbox.alert(la.THIS_ACCOUNT_HAS_NO_PRIVILEGES_TO_DO_THAT), !1;
  }
  return !0;
}
function utilsShowDriverInfo(e) {
  var t = settingsObjectDriverData[e].name,
    a = settingsObjectDriverData[e].idn,
    o = settingsObjectDriverData[e].address,
    s = settingsObjectDriverData[e].phone,
    n = settingsObjectDriverData[e].email,
    r = settingsObjectDriverData[e].desc;
  settingsObjectDriverData[e].img;
  (text =
    '<div class="form-group"><div class="vertical-align"><div class="col-xs-4"><strong>' +
    la.NAME +
    ':</strong></div><div class="col-xs-8">' +
    t +
    "</div></div></div>"),
    "" != a &&
      (text +=
        '<div class="form-group"><div class="vertical-align"><div class="col-xs-4"><strong>' +
        la.ID_NUMBER +
        ':</strong></div><div class="col-xs-8">' +
        a +
        "</div></div></div>"),
    "" != o &&
      (text +=
        '<div class="form-group"><div class="vertical-align"><div class="col-xs-4"><strong>' +
        la.ADDRESS +
        ':</strong></div><div class="col-xs-8">' +
        o +
        "</div></div></div>"),
    "" != s &&
      (text +=
        '<div class="form-group"><div class="vertical-align"><div class="col-xs-4"><strong>' +
        la.PHONE +
        ':</strong></div><div class="col-xs-8">' +
        s +
        "</div></div></div>"),
    "" != n &&
      ((n = '<a href="mailto:' + n + '">' + n + "</a>"),
      (text +=
        '<div class="form-group"><div class="vertical-align"><div class="col-xs-4"><strong>' +
        la.EMAIL +
        ':</strong></div><div class="col-xs-8">' +
        n +
        "</div></div></div>")),
    "" != r &&
      (text +=
        '<div class="form-group"><div class="vertical-align"><div class="col-xs-4"><strong>' +
        la.DESCRIPTION +
        ':</strong></div><div class="col-xs-8">' +
        r +
        "</div></div></div>"),
    bootbox.alert({
      message: text,
      title: la.DRIVER_INFO,
    });
}
function utilsShowTrailerInfo(e) {
  var t = settingsObjectTrailerData[e].name,
    a = settingsObjectTrailerData[e].model,
    o = settingsObjectTrailerData[e].vin,
    s = settingsObjectTrailerData[e].plate_number,
    n = settingsObjectTrailerData[e].desc;
  (text =
    '<div class="form-group"><div class="vertical-align"><div class="col-xs-4"><strong>' +
    la.NAME +
    ':</strong></div><div class="col-xs-8">' +
    t +
    "</div></div></div>"),
    "" != a &&
      (text +=
        '<div class="form-group"><div class="vertical-align"><div class="col-xs-4"><strong>' +
        la.MODEL +
        ':</strong></div><div class="col-xs-8">' +
        a +
        "</div></div></div>"),
    "" != o &&
      (text +=
        '<div class="form-group"><div class="vertical-align"><div class="col-xs-4"><strong>' +
        la.VIN +
        ':</strong></div><div class="col-xs-8">' +
        o +
        "</div></div></div>"),
    "" != s &&
      (text +=
        '<div class="form-group"><div class="vertical-align"><div class="col-xs-4"><strong>' +
        la.PLATE_NUMBER +
        ':</strong></div><div class="col-xs-8">' +
        s +
        "</div></div></div>"),
    "" != n &&
      (text +=
        '<div class="form-group"><div class="vertical-align"><div class="col-xs-4"><strong>' +
        la.DESCRIPTION +
        ':</strong></div><div class="col-xs-8">' +
        n +
        "</div></div></div>"),
    bootbox.alert({
      message: text,
      title: la.TRAILER_INFO,
    });
}
(eventsData.last_id = -1), (eventsData.events_loaded = !1);
