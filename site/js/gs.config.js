// ############################################################
// All listed setting can be changed only by editing this file
// Other settings can be changed from CPanel/Manage server
// ############################################################

var gsValues = new Array(); // do not remove

// map min zoom
gsValues["map_min_zoom"] = 3;

// map max zoom
gsValues["map_max_zoom"] = 18;

// realtime object data refresh, default 10 seconds
gsValues["map_refresh"] = 10;

// events refresh, default 30 seconds
gsValues["event_refresh"] = 30;

// dashboard refresh, default 60 seconds
gsValues["dashboard_refresh"] = 60;

// cmd status refresh, default 60 seconds
gsValues["cmd_status_refresh"] = 60;

// img gallery refresh, default 60 seconds
gsValues["img_refresh"] = 60;

// chat refresh, default 10 seconds
gsValues["chat_refresh"] = 10;

// billing refresh, default 60 seconds
gsValues["billing_refresh"] = 60;

// check if user session is still active if not block and ask for login, default 30 seconds, false - do not check;
gsValues["session_check"] = 30;

// default object control commands
gsValues["cmd_default"] = new Array();
gsValues["cmd_default"] = [
  { name: "alarm_arm" },
  { name: "alarm_disarm" },
  { name: "command_interval", params: "120" },
  { name: "engine_resume" },
  { name: "engine_stop" },
  { name: "output_off", params: "1" },
  { name: "output_on", params: "1" },
  { name: "photo_request" },
  { name: "position_interval", params: "60" },
  { name: "tracking_start" },
  { name: "tracking_stop" },
];

// supported GPS device protocols
gsValues["protocol_list"] = new Array();
gsValues["protocol_list"] = [
  { name: "android" },
  { name: "Er-100(2G)" },
  { name: "Er-100(3G)" },
  { name: "Er-100(4G)" },
  { name: "LUXPro" },
  { name: "cellocatorCR300B" },
  { name: "CondorKeny" },
  { name: "queclinkgv300" },
  { name: "DUX" },
  { name: "DUXPro" },
  { name: "EYESPro" },
  { name: "450/EYESPro" },
  { name: "TEMPUS" },
  { name: "TEMPUSPro" },
  { name: "queclinkgv75w" },
  { name: "suntechst300" },
  { name: "suntechst3940" },
  { name: "suntechst500" },
  { name: "suntechst910" },
  { name: "suntechST310U" },
  { name: "suntechST4305" },
  { name: "suntechST4310" },
  { name: "ILMA" },
  { name: "suntechST4345" },
  { name: "suntechST4955" },
  { name: "suntechST3310U" },
  { name: "teltonikafm130" },
  { name: "teltonikafm920" },
  { name: "teltonikafmOBD" },
  { name: "teltonikafm150" },
  { name: "MV720" },
  { name: "LUX" },
];
