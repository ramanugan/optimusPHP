// #################################################
// VALIDATION FUNCTIONS
// #################################################

function isMobileVersion() {
  if (document.URL.indexOf("mobile") != -1) {
    return true;
  } else {
    return false;
  }
}

function isObjectFollow() {
  if (document.URL.indexOf("func/fn_object.follow.php") != -1) {
    return true;
  } else {
    return false;
  }
}

function isSharePosition() {
  if (document.URL.indexOf("mod/share/index.php") != -1) {
    return true;
  } else {
    return false;
  }
}

function isNumber(num) {
  return Number(parseFloat(num)) == num;
}

function isEven(num) {
  return num % 2 == 0;
}

function isIntValid(value) {
  var er = /^[0-9-]{1,100}$/;

  return er.test(value) ? true : false;
}

function isHexValid(hex) {
  if (hex.match(/^[0-9A-F]{1,1024}$/) && isEven(hex.length)) {
    return true;
  } else {
    return false;
  }
}

function isEmailValid(email) {
  var RegExp = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,15}$/;

  if (email.match(RegExp)) {
    return true;
  }
  return false;
}

function isIMEIValid(imei) {
  var RegExp = /^[a-zA-Z0-9]{1,15}$/;

  if (imei.match(RegExp)) {
    return true;
  }

  return false;
}

function isNumberKey(evt) {
  var charCode = evt.which ? evt.which : event.keyCode;
  if (
    charCode > 31 &&
    (charCode < 48 || charCode > 57) &&
    charCode != 45 &&
    charCode != 46
  ) {
    return false;
  }

  return true;
}

function isPointInPolygon(poly, pt) {
  for (var c = false, i = -1, l = poly.length, j = l - 1; ++i < l; j = i)
    ((poly[i].y <= pt.y && pt.y < poly[j].y) ||
      (poly[j].y <= pt.y && pt.y < poly[i].y)) &&
      pt.x <
      ((poly[j].x - poly[i].x) * (pt.y - poly[i].y)) /
      (poly[j].y - poly[i].y) +
      poly[i].x &&
      (c = !c);
  return c;
}

function strMatches(str, search) {
  str = str.toLowerCase();
  search = search.toLowerCase();

  if (str.indexOf(search) != -1) {
    return true;
  } else {
    return false;
  }
}

function strUcFirst(str) {
  return str.charAt(0).toUpperCase() + str.slice(1);
}

// #################################################
// END VALIDATION FUNCTIONS
// #################################################

// #################################################
// CONVERSION/MATH FUNCTIONS
// #################################################

function calcString(str) {
  var result = 0;
  try {
    str = str.trim();
    // str = str.replace(/[^-()\d/*+.]/g, '');
    str = str.split("pow").join("Math.pow");
    str = str.split("asin").join("Math.asin");
    str = str.split("acos").join("Math.acos");
    str = str.split("sqrt").join("Math.sqrt");
    str = str.split("round").join("Math.round");

    return result + eval(str);
  } catch (err) {
    return result;
  }
}

function convSpeedUnits(val, from, to) {
  return Math.floor(convDistanceUnits(val, from, to));
}

function convDistanceUnits(val, from, to) {
  if (from == "km") {
    if (to == "mi") {
      val = val * 0.621371;
    } else if (to == "nm") {
      val = val * 0.539957;
    }
  } else if (from == "mi") {
    if (to == "km") {
      val = val * 1.60934;
    } else if (to == "nm") {
      val = val * 0.868976;
    }
  } else if (from == "nm") {
    if (to == "km") {
      val = val * 1.852;
    } else if (to == "nm") {
      val = val * 1.15078;
    }
  }

  return val;
}

function textToLinks(text) {
  var exp =
    /(\b(https?|ftp|file):\/\/[-A-Z0-9+&@#\/%?=~_|!:,.;]*[-A-Z0-9+&@#\/%=~_|])/gi;
  return text.replace(exp, "<a href='$1' target='_blank'>$1</a>");
}

function hexToAscii(hex) {
  var str = "";
  if (isHexValid(hex)) {
    for (i = 0; i < hex.length; i += 2) {
      str += String.fromCharCode(parseInt(hex.substr(i, 2), 16));
    }
  }
  return str;
}

function strToBoolean(str) {
  if (str == "true") {
    return true;
  } else {
    return false;
  }
}

function booleanToStr(bool) {
  if (bool == true) {
    return "true";
  } else {
    return "false";
  }
}

function uniqueArray(a) {
  a.sort();
  for (var i = 1; i < a.length;) {
    if (a[i - 1] == a[i]) {
      a.splice(i, 1);
    } else {
      i++;
    }
  }
  return a;
}

function toRad(Value) {
  return (Value * Math.PI) / 180;
}

function toDeg(Value) {
  return (Value * 180) / Math.PI;
}

function getAngle(lat1, lon1, lat2, lon2) {
  var dLat = toRad(lat2 - lat1);
  var dLon = toRad(lon2 - lon1);

  lat1 = toRad(lat1);
  lat2 = toRad(lat2);

  var y = Math.sin(dLon) * Math.cos(lat2);
  var x =
    Math.cos(lat1) * Math.sin(lat2) -
    Math.sin(lat1) * Math.cos(lat2) * Math.cos(dLon);
  var angle = toDeg(Math.atan2(y, x));

  if (angle < 0) {
    angle = 360 + angle;
  }

  return Math.abs(angle).toFixed(0);
}

function getAreaFromLatLngs(latlngs) {
  var pointsCount = latlngs.length,
    area = 0.0,
    d2r = 0.017453292519943295,
    p1,
    p2;

  if (pointsCount > 2) {
    for (var i = 0; i < pointsCount; i++) {
      p1 = latlngs[i];
      p2 = latlngs[(i + 1) % pointsCount];
      area +=
        (p2.lng - p1.lng) *
        d2r *
        (2 + Math.sin(p1.lat * d2r) + Math.sin(p2.lat * d2r));
    }

    area = (area * 6378137.0 * 6378137.0) / 2.0;
  }

  return Math.abs(area); // sq meters
}

function getLengthFromLatLngs(latlngs) {
  var dist = 0;

  for (var i = 0; i < latlngs.length - 1; i++) {
    var latlng1 = latlngs[i];
    var latlng2 = latlngs[i + 1];

    dist += getLengthBetweenCoordinates(
      latlng1.lat,
      latlng1.lng,
      latlng2.lat,
      latlng2.lng
    );
  }

  return dist;
}

function getLengthBetweenCoordinates(lat1, lng1, lat2, lng2) {
  var R = 6371; // Radius of the earth in km
  var dLat = toRad(lat2 - lat1); // deg2rad below
  var dLon = toRad(lng2 - lng1);
  var a =
    Math.sin(dLat / 2) * Math.sin(dLat / 2) +
    Math.cos(toRad(lat1)) *
    Math.cos(toRad(lat2)) *
    Math.sin(dLon / 2) *
    Math.sin(dLon / 2);
  var c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
  var d = R * c; // Distance in km

  return d;
}

function recapDataForDateSegments(
  formula_fuel,
  velocidad_minima,
  carga_minima,
  descarga_minima,
  plots
) {
  const NIVEL_SUAVIZADO = 3;
  const DIFERENCIAS_ACUMULADAS = 5;

  function parseLocalDate(str) {
    return new Date(str.replace(" ", "T"));
  }

  function dayKey(ms) {
    const d = new Date(ms);
    const y = d.getFullYear();
    const m = String(d.getMonth() + 1).padStart(2, "0");
    const da = String(d.getDate()).padStart(2, "0");
    return `${y}-${m}-${da}`;
  }

  function dayStartMs(key) {
    const [y, m, d] = key.split("-").map(Number);
    return new Date(y, m - 1, d, 0, 0, 0, 0).getTime();
  }

  function dayEndMs(key) {
    return dayStartMs(key) + 24 * 60 * 60 * 1000;
  }

  function suavizarSerie(serie, nuevosLitros, suavizado = NIVEL_SUAVIZADO) {
    if (serie.length >= suavizado) {
      let suma = 0;
      for (let i = 1; i < suavizado; i++) suma += serie[serie.length - i];
      suma += nuevosLitros;
      return Math.round((suma / suavizado + Number.EPSILON) * 100) / 100;
    }
    return nuevosLitros;
  }

  function procesarTanqueValue(adcKey, formulaKey, item, ultimoLitros, serieVals) {
    if (!formula_fuel || !formula_fuel[formulaKey]) {
      return ultimoLitros ?? 0;
    }

    let voltaje = parseInt(item[6]?.[adcKey] || 0);
    if (voltaje > 5000) voltaje = 5000;

    if (voltaje > 0) {
      let litros = calcString(formula_fuel[formulaKey].split("x").join(voltaje));
      litros = suavizarSerie(serieVals, litros, NIVEL_SUAVIZADO);
      return litros;
    }

    return ultimoLitros ?? 0;
  }

  const pts = plots
    .map((p) => ({
      raw: p,
      dt: parseLocalDate(p[0]).getTime(),
      lat: p[1],
      lng: p[2],
      speed: parseInt(p[5] ?? 0),
    }))
    .sort((a, b) => a.dt - b.dt);

  function ensureMidnightBridges(arr) {
    if (arr.length < 2) return arr;

    const out = [arr[0]];
    for (let i = 1; i < arr.length; i++) {
      const prev = out[out.length - 1];
      const cur = arr[i];

      const prevDay = dayKey(prev.dt);
      const curDay = dayKey(cur.dt);

      if (prevDay !== curDay) {
        const ds = dayStartMs(curDay);

        if (cur.dt > ds) {
          const bridge = {
            raw: cur.raw,
            dt: ds,
            lat: prev.lat,
            lng: prev.lng,
            speed: 0,
            __bridgeFromPrev: true,
          };

          if (prev.dt !== ds) out.push(bridge);
        }
      }

      out.push(cur);
    }
    return out;
  }

  const ptsWithBridges = ensureMidnightBridges(pts);

  let ultimo1 = null,
    ultimo2 = null,
    ultimo3 = null;
  const serie1 = [],
    serie2 = [],
    serie3 = [];

  for (let i = 0; i < ptsWithBridges.length; i++) {
    const item = ptsWithBridges[i].raw;

    const l1 = procesarTanqueValue("adc1", "adc1", item, ultimo1, serie1);
    const l2 = procesarTanqueValue("adc2", "adc2", item, ultimo2, serie2);
    const l3 = procesarTanqueValue("adc3", "adc3", item, ultimo3, serie3);

    if (l1 !== 0) ultimo1 = l1;
    if (l2 !== 0) ultimo2 = l2;
    if (l3 !== 0) ultimo3 = l3;

    serie1.push(l1);
    serie2.push(l2);
    serie3.push(l3);

    let total = l1 + l2 + l3;
    total = Math.round((total + Number.EPSILON) * 100) / 100;

    ptsWithBridges[i].total = total;

    if (ptsWithBridges[i].__bridgeFromPrev && i > 0) {
      ptsWithBridges[i].total = ptsWithBridges[i - 1].total;
    }
  }

  const events = [];

  let total_por_index = 0;
  let total_anterior = 0;
  let litros_inicial_global = ptsWithBridges.length ? ptsWithBridges[0].total : 0;

  let diferencias_recientes_carga = [];
  let diferencias_recientes_descarga = [];

  let tramo_cargando = null;
  let tramo_descargando = null;
  let contador_no_carga = 0;
  let contador_no_descarga = 0;

  for (let i = 0; i < ptsWithBridges.length; i++) {
    if (i === 0) {
      total_por_index = ptsWithBridges[i].total;
      continue;
    }

    const total = ptsWithBridges[i].total;
    const velocidad = ptsWithBridges[i].speed;

    let diferencia_litros = total - total_por_index;
    diferencia_litros = Math.round((diferencia_litros + Number.EPSILON) * 100) / 100;

    total_anterior = total_por_index;
    total_por_index = total;

    if (total_anterior === 0) total_anterior = litros_inicial_global;
    if (diferencia_litros === total) diferencia_litros = 0;

    diferencias_recientes_carga.push(diferencia_litros);
    if (diferencias_recientes_carga.length > DIFERENCIAS_ACUMULADAS) {
      diferencias_recientes_carga.shift();
    }

    if (diferencia_litros < 0) {
      diferencias_recientes_descarga.push(diferencia_litros);
      if (diferencias_recientes_descarga.length > DIFERENCIAS_ACUMULADAS) {
        diferencias_recientes_descarga.shift();
      }
    }

    const suma_diferencias_cargas = diferencias_recientes_carga.reduce((acc, v) => acc + v, 0);

    // CARGA
    if (suma_diferencias_cargas >= carga_minima && velocidad < velocidad_minima) {
      contador_no_carga = 0;
      if (!tramo_cargando) {
        const startIdx = Math.max(0, i - 2);
        tramo_cargando = {
          inicioMs: ptsWithBridges[startIdx].dt,
          litros_inicio: ptsWithBridges[startIdx].total,
        };
      }
    } else if (tramo_cargando) {
      contador_no_carga++;
      if (contador_no_carga >= DIFERENCIAS_ACUMULADAS) {
        const suma_dif = diferencias_recientes_carga.reduce((acc, v) => acc + v, 0);

        if (suma_dif <= carga_minima) {
          const litrosCargados = total - tramo_cargando.litros_inicio;
          if (litrosCargados > carga_minima) {
            events.push({
              type: "carga",
              startMs: tramo_cargando.inicioMs,
              endMs: ptsWithBridges[i].dt,
              litros: litrosCargados,
            });
          }
          tramo_cargando = null;
          contador_no_carga = 0;
        }
      }
    }

    // DESCARGA
    if (diferencia_litros < -descarga_minima && velocidad < velocidad_minima) {
      contador_no_descarga = 0;
      if (!tramo_descargando) {
        tramo_descargando = {
          inicioMs: ptsWithBridges[Math.max(0, i - 1)].dt,
          litros_inicio: total_anterior,
        };
      }
    } else if (tramo_descargando) {
      contador_no_descarga++;
      if (contador_no_descarga >= DIFERENCIAS_ACUMULADAS) {
        const suma_dif_desc = diferencias_recientes_descarga
          .map(Math.abs)
          .reduce((acc, v) => acc + v, 0);

        if (suma_dif_desc <= descarga_minima) {
          const litrosDesc = tramo_descargando.litros_inicio - total;
          if (litrosDesc > descarga_minima) {
            events.push({
              type: "descarga",
              startMs: tramo_descargando.inicioMs,
              endMs: ptsWithBridges[i].dt,
              litros: litrosDesc,
            });
          }
          tramo_descargando = null;
          contador_no_descarga = 0;
        }
      }
    }
  }

  const dayMap = {};

  for (const p of ptsWithBridges) {
    const k = dayKey(p.dt);

    if (!dayMap[k]) {
      dayMap[k] = {
        key: k,
        distPts: [],
        firstTotal: p.total,
        lastTotal: p.total,
        carga: 0,
        descarga: 0,
      };
    }

    if (!p.__bridgeFromPrev) {
      dayMap[k].distPts.push({ lat: p.lat, lng: p.lng });
    }

    dayMap[k].lastTotal = p.total;
  }

  function addEventToDays(ev) {
    const start = ev.startMs;
    const end = ev.endMs;
    if (end <= start) return;

    let k = dayKey(start);

    while (true) {
      const ds = dayStartMs(k);
      const de = dayEndMs(k);

      const overlapStart = Math.max(start, ds);
      const overlapEnd = Math.min(end, de);
      const overlap = overlapEnd - overlapStart;

      if (overlap > 0) {
        const ratio = overlap / (end - start);
        const litrosParte = ev.litros * ratio;

        if (!dayMap[k]) {
          dayMap[k] = {
            key: k,
            distPts: [],
            firstTotal: 0,
            lastTotal: 0,
            carga: 0,
            descarga: 0,
          };
        }

        if (ev.type === "carga") dayMap[k].carga += litrosParte;
        else dayMap[k].descarga += litrosParte;
      }

      if (end <= de) break;

      const nextDay = new Date(de);
      k = dayKey(nextDay.getTime());
    }
  }

  for (const ev of events) addEventToDays(ev);

  const keys = Object.keys(dayMap).sort();

  let totalKms = 0,
    cargaTotal = 0,
    descargaTotal = 0,
    consumoTotal = 0;

  const rows = [];

  for (const k of keys) {
    const d = dayMap[k];

    let distanciaDiaKms = 0;
    if (d.distPts.length >= 2) {
      distanciaDiaKms = parseFloat(
        convDistanceUnits(
          getLengthFromLatLngs(d.distPts),
          "km",
          settingsUserData.unit_distance
        ).toFixed(2)
      );
    }

    const consumo = (d.firstTotal + d.carga) - d.lastTotal;

    rows.push({
      fecha: k,
      distancia: distanciaDiaKms.toFixed(2),
      carga: d.carga.toFixed(2),
      descarga: d.descarga.toFixed(2),
      consumo: consumo.toFixed(2),
    });

    totalKms += distanciaDiaKms;
    cargaTotal += d.carga;
    descargaTotal += d.descarga;
    consumoTotal += consumo;
  }

  rows.push({
    distanciaTotal: totalKms.toFixed(2),
    cargaTotal: cargaTotal.toFixed(2),
    descargaTotal: descargaTotal.toFixed(2),
    consumoTotal: consumoTotal.toFixed(2),
  });

  return rows;
}


function transformsToSettingsObjectData(data) {
  var settings = new Array();
  if (data != undefined) {
    for (var key in data) {
      settings[key] = {
        protocol: data[key][0],
        group_id: data[key][1],
        driver_id: data[key][2],
        trailer_id: data[key][3],
        name: data[key][4],
        icon: data[key][5],
        map_arrows: data[key][6],
        map_icon: data[key][7],
        tail_color: data[key][8],
        tail_points: data[key][9],
        device: data[key][10],
        sim_number: data[key][11],
        model: data[key][12],
        vin: data[key][13],
        plate_number: data[key][14],
        odometer_type: data[key][15],
        engine_hours_type: data[key][16],
        odometer: data[key][17],
        engine_hours: data[key][18],
        fcr: data[key][19],
        time_adj: data[key][20],
        accuracy: data[key][21],
        accvirt: data[key][22],
        accvirt_cn: data[key][23],
        sensors: data[key][24],
        service: data[key][25],
        custom_fields: data[key][26],
        params: data[key][27],
        active: data[key][28],
        object_expire: data[key][29],
        object_expire_dt: data[key][30],
        mtto: data[key][31],
        iccid: data[key][32],
        activefordward: data[key][33],
        sim_number_company: data[key][34],
        fota: data[key][35],
        activestream: data[key][36],
        url_stream: data[key][37],
        port_stream: data[key][38],
        time_stream: data[key][39],
        left_time_stream: data[key][40],
      };
    }
  }

  return settings;
}

function transformToObjectData(data) {
  var result = [];
  result["data"] = [];

  result["visible"] = data["v"];
  result["follow"] = data["f"];
  result["selected"] = data["s"];
  result["event"] = data["evt"];
  result["event_arrow_color"] = data["evtac"];
  result["event_ohc_color"] = data["evtohc"];
  result["address"] = data["a"];
  result["layers"] = data["l"];

  result["status"] = data["st"];
  result["status_string"] = data["ststr"];
  result["protocol"] = data["p"];
  result["connection"] = data["cn"];
  result["odometer"] = data["o"];
  result["engine_hours"] = data["eh"];
  result["service"] = data["sr"];
  result["last_img_file"] = data["lif"];
  result["dt_last_idle"] = data["dt_last_idle"];

  if (data["d"] != "") {
    result["data"].push({
      dt_server: data["d"][0][0],
      dt_tracker: data["d"][0][1],
      lat: data["d"][0][2],
      lng: data["d"][0][3],
      altitude: data["d"][0][4],
      angle: data["d"][0][5],
      speed: data["d"][0][6],
      params: data["d"][0][7],
    });
  }

  return result;
}

function transformToHistoryRoute(data) {
  var temp_angle = 0;

  var sroute = data.route;
  var route = [];
  for (var i = 0; i < sroute.length; i++) {
    // calculate angle
    if (i > 0) {
      var lat1 = sroute[i - 1][1];
      var lon1 = sroute[i - 1][2];
      var lat2 = sroute[i][1];
      var lon2 = sroute[i][2];
      sroute[i][4] = getAngle(lat1, lon1, lat2, lon2);
    }

    // fix 0 angle during speed 0
    if (sroute[i][4] > 0) {
      var temp_angle = sroute[i][4];
    }

    if (sroute[i][5] == 0) {
      sroute[i][4] = temp_angle;
    }

    route.push({
      dt_tracker: sroute[i][0],
      lat: sroute[i][1],
      lng: sroute[i][2],
      altitude: sroute[i][3],
      angle: sroute[i][4],
      speed: sroute[i][5],
      params: sroute[i][6],
    });
  }

  var sstops = data.stops;
  var stops = [];
  if (sstops != undefined) {
    for (var i = 0; i < sstops.length; i++) {
      stops.push({
        id_start: sstops[i][0],
        id_end: sstops[i][1],
        lat: sstops[i][2],
        lng: sstops[i][3],
        altitude: sstops[i][4],
        angle: sstops[i][5],
        speed: 0,
        dt_start: sstops[i][6],
        dt_end: sstops[i][7],
        duration: sstops[i][8],
        fuel_consumption: sstops[i][9],
        fuel_cost: sstops[i][10],
        engine_idle: sstops[i][11],
        params: sstops[i][12],
      });
    }
  }

  var sdrives = data.drives;
  var drives = [];
  if (sdrives != undefined) {
    for (var i = 0; i < sdrives.length; i++) {
      drives.push({
        id_start_s: sdrives[i][0],
        id_start: sdrives[i][1],
        id_end: sdrives[i][2],
        dt_start_s: sdrives[i][3],
        dt_start: sdrives[i][4],
        dt_end: sdrives[i][5],
        duration: sdrives[i][6],
        route_length: sdrives[i][7],
        top_speed: sdrives[i][8],
        avg_speed: sdrives[i][9],
        fuel_consumption: sdrives[i][10],
        fuel_cost: sdrives[i][11],
        engine_work: sdrives[i][12],
        fuel_consumption_per_100km: sdrives[i][13],
        fuel_consumption_mpg: sdrives[i][14],
      });
    }
  }

  var sevents = data.events;
  var events = [];
  if (sevents != undefined) {
    for (var i = 0; i < sevents.length; i++) {
      events.push({
        event_desc: sevents[i][0],
        dt_tracker: sevents[i][1],
        lat: sevents[i][2],
        lng: sevents[i][3],
        altitude: sevents[i][4],
        angle: sevents[i][5],
        speed: sevents[i][6],
        params: sevents[i][7],
      });
    }
  }

  data["route"] = route;
  data["stops"] = stops;
  data["drives"] = drives;
  data["events"] = events;

  return data;
}

function getTimeDetails(sec, show_days) {
  var seconds = 0;
  var hours = 0;
  var minutes = 0;

  if (sec % 86400 <= 0) {
    days = sec / 86400;
  }
  if (sec % 86400 > 0) {
    var rest = sec % 86400;
    var days = (sec - rest) / 86400;

    if (rest % 3600 > 0) {
      var rest1 = rest % 3600;
      var hours = (rest - rest1) / 3600;

      if (rest1 % 60 > 0) {
        var rest2 = rest1 % 60;
        minutes = (rest1 - rest2) / 60;
        seconds = rest2;
      } else {
        minutes = rest1 / 60;
      }
    } else {
      hours = rest / 3600;
    }
  }

  days = parseFloat(days);
  hours = parseFloat(hours);
  minutes = parseFloat(minutes);
  seconds = parseFloat(seconds);

  if (show_days == false) {
    hours += days * 24;
    days = 0;
  }

  if (days > 0) {
    days = days + " " + la["UNIT_D"] + " ";
  } else {
    days = "";
  }
  if (hours > 0) {
    hours = hours + " " + la["UNIT_H"] + " ";
  } else {
    hours = "";
  }
  if (minutes > 0) {
    minutes = minutes + " " + la["UNIT_MIN"] + " ";
  } else {
    minutes = "";
  }
  seconds = seconds + " " + la["UNIT_S"];

  return days + hours + minutes + seconds;
}

function getTimestampFromDate(date) {
  date = Date.parse(date);
  return date;
}

function getDatetimeFromTimestamp(ts) {
  var dt = new Date(ts);

  var year = dt.getUTCFullYear();

  var month = dt.getUTCMonth();
  month = month + 1; // because js months start from 0
  if (month.toString().length == 1) month = "0" + month;

  var date = dt.getUTCDate();
  if (date.toString().length == 1) date = "0" + date;

  var h = dt.getUTCHours();
  if (h.toString().length == 1) h = "0" + h;

  var min = dt.getUTCMinutes();
  if (min.toString().length == 1) min = "0" + min;

  var s = dt.getUTCSeconds();
  if (s.toString().length == 1) s = "0" + s;

  return year + "-" + month + "-" + date + " " + h + ":" + min + ":" + s;
}

function getTimeDifference(laterdate, earlierdate) {
  laterdate = new Date(laterdate.replace(" ", "T"));
  earlierdate = new Date(earlierdate.replace(" ", "T"));

  var difference = laterdate.getTime() - earlierdate.getTime();

  var daysDifference = Math.floor(difference / 1000 / 60 / 60 / 24);
  difference -= daysDifference * 1000 * 60 * 60 * 24;

  var hoursDifference = Math.floor(difference / 1000 / 60 / 60);
  difference -= hoursDifference * 1000 * 60 * 60;

  var minutesDifference = Math.floor(difference / 1000 / 60);
  difference -= minutesDifference * 1000 * 60;

  var secondsDifference = Math.floor(difference / 1000);

  var d, h, m, s;
  if (daysDifference > 0) {
    d = daysDifference + " " + la["UNIT_D"] + " ";
  } else {
    d = "";
  }
  if (hoursDifference > 0) {
    h = hoursDifference + " " + la["UNIT_H"] + " ";
  } else {
    h = "";
  }
  if (minutesDifference > 0) {
    m = minutesDifference + " " + la["UNIT_MIN"] + " ";
  } else {
    m = "";
  }
  if (secondsDifference > 0) {
    s = secondsDifference + " " + la["UNIT_S"] + "";
  } else {
    s = "";
  }
  return d + h + m + s;
}

function getDateDifference(date1, date2) {
  // The number of milliseconds in one day
  var ONE_DAY = 1000 * 60 * 60 * 24;

  // Convert both dates to milliseconds
  var date1_ms = date1.getTime();
  var date2_ms = date2.getTime();

  // Calculate the difference in milliseconds
  var difference_ms = Math.abs(date1_ms - date2_ms);

  // Convert back to days and return
  return Math.round(difference_ms / ONE_DAY);
}

function sortSelectList(select) {
  var tmpAry = new Array();
  for (var i = 0; i < select.options.length; i++) {
    tmpAry[i] = new Array();
    tmpAry[i] = {
      value: select.options[i].value,
      text: select.options[i].text,
    };
  }

  tmpAry = sortArrayByElement(tmpAry, "text");

  while (select.options.length > 0) {
    select.options[0] = null;
  }

  for (var i = 0; i < tmpAry.length; i++) {
    var op = new Option(tmpAry[i].text, tmpAry[i].value);
    select.options[i] = op;
  }
  return;
}

function sortNumber(a, b) {
  return a - b;
}

function sortString(a, b) {
  var x = a.toLowerCase();
  var y = b.toLowerCase();
  return x < y ? -1 : x > y ? 1 : 0;
}

function sortArrayByElement(arr, el) {
  arr = arr.slice(0);

  arr.sort(function (a, b) {
    var x = a[el].toLowerCase();
    var y = b[el].toLowerCase();
    return x < y ? -1 : x > y ? 1 : 0;
  });

  return arr;
}

function arrayMove(arr, fromIndex, toIndex) {
  var element = arr[fromIndex];
  arr.splice(fromIndex, 1);
  arr.splice(toIndex, 0, element);

  return;
}

function strLink(str) {
  //URLs starting with http://, https://, or ftp://
  var replacePattern1 =
    /(\b(https?|ftp):\/\/[-A-Z0-9+&@#\/%?=~_|!:,.;]*[-A-Z0-9+&@#\/%=~_|])/gim;
  var replacedText = str.replace(
    replacePattern1,
    '<a href="$1" target="_blank">$1</a>'
  );

  //URLs starting with www. (without // before it, or it'd re-link the ones done above)
  var replacePattern2 = /(^|[^\/])(www\.[\S]+(\b|$))/gim;
  var replacedText = replacedText.replace(
    replacePattern2,
    '$1<a href="http://$2" target="_blank">$2</a>'
  );

  //Change email addresses to mailto:: links
  var replacePattern3 = /(\w+@[a-zA-Z_]+?\.[a-zA-Z]{2,6})/gim;
  var replacedText = replacedText.replace(
    replacePattern3,
    '<a href="mailto:$1">$1</a>'
  );

  return replacedText;
}

function stripHTML(str) {
  str = str.replace(
    /(<\?[a-z]*(\s[^>]*)?\?(>|$)|<!\[[a-z]*\[|\]\]>|<!DOCTYPE[^>]*?(>|$)|<!--[\s\S]*?(-->|$)|<[a-z?!\/]([a-z0-9_:.])*(\s[^>]*)?(>|$))/gi,
    ""
  );
  return str;
}

// #################################################
// END CONVERSION/MATH FUNCTIONS
// #################################################

// #################################################
// MULTISELECT FUNCTIONS
// #################################################

function multiselectClear(select) {
  var optgroups = select.getElementsByTagName("optgroup");
  for (var i = optgroups.length - 1; i >= 0; i--) {
    select.removeChild(optgroups[i]);
  }

  select.options.length = 0;
}

function multiselectSetGroups(select, groups_arr) {
  for (var key in groups_arr) {
    var group = groups_arr[key];

    if (group.items.length > 0) {
      var optgroup = $('<optgroup label="' + group.name + '" />');

      for (var key in group.items) {
        var item = group.items[key];
        $('<option value="' + item.value + '"/>')
          .html(item.name)
          .appendTo(optgroup);
      }

      optgroup.appendTo(select);
    }
  }
}

function multiselectSet(select, arr) {
  if (arr.length > 0) {
    for (var key in arr) {
      var item = arr[key];
      $('<option value="' + item.value + '"/>')
        .html(item.name)
        .appendTo(select);
    }
  }
}

function multiselectIsSelected(select) {
  var valid = false;
  for (var i = 0; i < select.options.length; i++) {
    if (select.options[i].selected) {
      valid = true;
      break;
    }
  }

  return valid;
}

function multiselectGetValues(select) {
  var selected = "";
  for (var i = 0; i < select.options.length; i++) {
    if (select.options[i].selected) {
      selected += select.options[i].value + ",";
    }
  }

  return selected.slice(0, -1);
}

function multiselectSetValues(select, values) {
  for (var i = 0; i < select.options.length; i++) {
    if ($.inArray(select.options[i].value, values) > -1) {
      select.options[i].selected = true;
    } else {
      select.options[i].selected = false;
    }
  }
}

// #################################################
// END MULTISELECT FUNCTIONS
// #################################################

// #################################################
// VARIOUS GUI FUNCTIONS
// #################################################

function scrollToBottom(id) {
  var obj = document.getElementById(id);
  obj.scrollTop = obj.scrollHeight;
}

// #################################################
// END VARIOUS GUI FUNCTIONS
// #################################################

// #################################################
// COOKIES FUNCTIONS
// #################################################

function getCookie(c_name) {
  var i,
    x,
    y,
    ARRcookies = document.cookie.split(";");
  for (i = 0; i < ARRcookies.length; i++) {
    x = ARRcookies[i].substr(0, ARRcookies[i].indexOf("="));
    y = ARRcookies[i].substr(ARRcookies[i].indexOf("=") + 1);
    x = x.replace(/^\s+|\s+$/g, "");
    if (x == c_name) {
      return unescape(y);
    }
  }
}

function setCookie(c_name, value, exdays) {
  var domain = document.domain;

  var exdate = new Date();
  exdate.setDate(exdate.getDate() + exdays);

  var c_value =
    escape(value) + (exdays == null ? "" : "; expires=" + exdate.toUTCString());
  c_value += "; domain=." + domain + "; path=/";

  document.cookie = c_name + "=" + c_value;
}

// #################################################
// END COOKIES FUNCTIONS
// #################################################

// #################################################
// SENSOR FUNCTIONS
// #################################################

function getObjectOdometer(imei, params) {
  var result = -1;

  if (settingsObjectData[imei]["odometer_type"] == "off") {
    return result;
  } else if (settingsObjectData[imei]["odometer_type"] == "gps") {
    if (params == false) {
      result = objectsData[imei]["odometer"];
    }
  } else if (settingsObjectData[imei]["odometer_type"] == "sen") {
    if (params == false) {
      result = objectsData[imei]["odometer"];
    } else {
      var sensor = getSensorFromType(imei, "odo");

      if (sensor != false) {
        var sensor_ = sensor[0];

        if (sensor_.result_type == "abs") {
          var sensor_data = getSensorValue(params, sensor_);
          result = sensor_data.value;
        }
      }
    }
  }

  return result;
}

function getObjectEngineHours(imei, params) {
  var result = -1;

  if (settingsObjectData[imei]["engine_hours_type"] == "off") {
    return result;
  } else if (settingsObjectData[imei]["engine_hours_type"] == "acc") {
    if (params == false) {
      result = objectsData[imei]["engine_hours"];
    }
  } else if (settingsObjectData[imei]["engine_hours_type"] == "sen") {
    if (params == false) {
      result = objectsData[imei]["engine_hours"];
    } else {
      var sensor = getSensorFromType(imei, "engh");

      if (sensor != false) {
        var sensor_ = sensor[0];

        if (sensor_.result_type == "abs") {
          var sensor_data = getSensorValue(params, sensor_);
          result = sensor_data.value;
        }
      }
    }
  }

  if (result > -1) {
    result = getTimeDetails(result, false);
  }

  return result;
}

function getObjectParamsArray(imei) {
  var arr_params = new Array();

  var params = settingsObjectData[imei]["params"];

  for (var i = 0; i < params.length; i++) {
    if (params[i] != "") {
      arr_params.push(params[i]);
    }
  }

  return uniqueArray(arr_params).sort();
}

function getAllParamsArray() {
  var arr_params = new Array();

  for (var imei in settingsObjectData) {
    var params = settingsObjectData[imei]["params"];

    for (var i = 0; i < params.length; i++) {
      if (params[i] != "") {
        arr_params.push(params[i]);
      }
    }
  }

  return uniqueArray(arr_params).sort();
}

function getAllSensorsArray() {
  var arr_sensors = new Array();

  for (var imei in settingsObjectData) {
    var sensors = settingsObjectData[imei]["sensors"];
    for (var key in sensors) {
      var sensor = sensors[key];

      if (sensor.name != "") {
        arr_sensors.push(sensor.name);
      }
    }
  }

  return uniqueArray(arr_sensors);
}

function getParamValue(params, param) {
  var param_value = 0;

  if (params != null) {
    if (params[param] != undefined) {
      param_value = params[param];
    }
  }

  return param_value;
}

function getSensorValueFuelLevelSumUp(imei, params, sensor) {
  var result = [];
  result["value"] = 0;
  result["value_full"] = "";

  var fuel_sensors = getSensorFromType(imei, "fuel");

  for (var i = 0; i < fuel_sensors.length; i++) {
    if (fuel_sensors[i].result_type == "value") {
      var sensor_data = getSensorValue(params, fuel_sensors[i]);
      result["value"] += parseFloat(sensor_data.value);
    }
  }

  result["value"] = Math.round(result["value"] * 100) / 100;

  result["value_full"] = result["value"];
  result["value_full"] += " " + sensor.units;

  return result;
}

function getSensorValue(params, sensor) {
  var result = [];
  result["value"] = 0;
  result["value_full"] = "";

  var param_value = getParamValue(params, sensor.param);

  if (
    (sensor.param == "adc1" ||
      sensor.param == "adc2" ||
      sensor.param == "adc3") &&
    param_value >= 5000
  ) {
    param_value = 5000;
  }

  // formula
  if (
    sensor.result_type == "abs" ||
    sensor.result_type == "rel" ||
    sensor.result_type == "value"
  ) {
    param_value = parseFloat(param_value);

    if (!isNumber(param_value)) {
      param_value = 0;
    }

    if (sensor.formula != "") {
      var formula = sensor.formula.toLowerCase();
      // formula = formula.replace('x', param_value);
      formula = formula.split("x").join(param_value); //Nueva Linea Agregada por GRivera
      param_value = calcString(formula);
    }
  }

  if (sensor.result_type == "abs" || sensor.result_type == "rel") {
    param_value = Math.round(param_value * 1000) / 1000;

    result["value"] = param_value;
    result["value_full"] = param_value;
  } else if (sensor.result_type == "logic") {
    if (param_value == 1) {
      result["value"] = param_value;
      result["value_full"] = sensor.text_1;
    } else {
      result["value"] = param_value;
      result["value_full"] = sensor.text_0;
    }
  } else if (sensor.result_type == "value") {
    // calibration
    var out_of_cal = true;
    var calibration = sensor.calibration;

    // function to get calibration Y value
    var calGetY = function (x) {
      var result = 0;
      for (var j = 0; j < calibration.length; j++) {
        if (calibration[j]["x"] == x) {
          result = parseFloat(calibration[j]["y"]);
        }
      }
      return result;
    };

    if (calibration.length >= 2) {
      // put all X values to separate array
      var x_arr = new Array();
      for (var i = 0; i < calibration.length; i++) {
        x_arr.push(parseFloat(calibration[i]["x"]));
      }

      x_arr.sort(sortNumber);

      // loop and check if in cal
      for (var i = 0; i < x_arr.length; i++) {
        var x_low = x_arr[i];
        var x_high = x_arr[i + 1];

        if (param_value >= x_low && param_value <= x_high) {
          // get Y low and high
          var y_low = calGetY(x_low);
          var y_high = calGetY(x_high);

          // get coeficient
          var a = param_value - x_low;
          var b = x_high - x_low;

          var coef = a / b;

          var c = y_high - y_low;
          coef = c * coef;

          param_value = y_low + coef;

          out_of_cal = false;

          break;
        }
      }

      if (out_of_cal) {
        // check if lower than cal
        var x_low = x_arr[0];

        if (param_value < x_low) {
          param_value = calGetY(x_low);
        }

        // check if higher than cal
        var x_high = x_arr.slice(-1)[0];

        if (param_value > x_high) {
          param_value = calGetY(x_high);
        }
      }
    }

    param_value = Math.round(param_value * 100) / 100;

    // dictionary
    var dictionary = sensor.dictionary;
    if (dictionary.length >= 1) {
      var param_text = param_value;

      for (var j = 0; j < dictionary.length; j++) {
        if (dictionary[j]["value"] == param_value) {
          param_text = dictionary[j]["text"];
        }
      }

      result["value"] = param_value;
      result["value_full"] = param_text + " " + sensor.units;
    } else {
      result["value"] = param_value;
      result["value_full"] = param_value + " " + sensor.units;
    }
  } else if (sensor.result_type == "string") {
    result["value"] = param_value;
    result["value_full"] = param_value;
  } else if (sensor.result_type == "percentage") {
    param_value = parseFloat(param_value);
    sensor.lv = parseFloat(sensor.lv);
    sensor.hv = parseFloat(sensor.hv);

    if (param_value > sensor.lv && param_value < sensor.hv) {
      var a = param_value - sensor.lv;
      var b = sensor.hv - sensor.lv;

      result["value"] = Math.round((a / b) * 100);
    } else if (param_value <= sensor.lv) {
      result["value"] = 0;
    } else if (param_value >= sensor.hv) {
      result["value"] = 100;
    }

    result["value_full"] = result["value"] + " " + sensor.units;
  }

  return result;
}

function getSensorFromType(imei, type) {
  var result = new Array();

  var sensors = settingsObjectData[imei]["sensors"];

  for (var key in sensors) {
    var sensor = sensors[key];
    if (sensor.type == type) {
      result.push(sensor);
    }
  }

  if (result.length > 0) {
    return result;
  } else {
    return false;
  }
}

// #################################################
// END SENSOR FUNCTIONS
// #################################################

// #################################################
// GEOCODER FUNCTIONS
// #################################################

function geocoderGetLocation(search, response) {
  var path = "tools/gc_post.php";

  if (isMobileVersion() || isObjectFollow()) {
    var path = "../" + path;
  } else if (isSharePosition()) {
    var path = "../../" + path;
  }

  var data = {
    cmd: "address",
    search: search,
  };

  $.ajax({
    type: "POST",
    url: path,
    data: data,
    dataType: "json",
    cache: false,
    success: function (result) {
      response(result);
    },
  });
}

function geocoderGetAddress(lat, lng, response) {
  var path = "tools/gc_post.php";

  if (isMobileVersion() || isObjectFollow()) {
    var path = "../" + path;
  } else if (isSharePosition()) {
    var path = "../../" + path;
  }

  var data = {
    cmd: "latlng",
    lat: lat,
    lng: lng,
  };

  $.ajax({
    type: "POST",
    url: path,
    data: data,
    dataType: "json",
    cache: false,
    success: function (result) {
      if (result === null) {
        response("");
      } else {
        response(result);
      }
    },
  });
}

// #################################################
// END GEOCODER FUNCTIONS
// #################################################

// #################################################
// MAP FUNCTIONS
// #################################################

function defineMapLayers() {
  if (gsValues["map_osm"]) {
    mapLayers["osm"] = new L.TileLayer(
      "https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png",
      {
        attribution:
          '&copy; <a href="http://osm.org/copyright">OpenStreetMap</a> contributors',
      }
    );
  }

  if (gsValues["map_bing"]) {
    mapLayers["broad"] = new L.BingLayer(gsValues["map_bing_key"], {
      type: "Road",
    });
    mapLayers["baer"] = new L.BingLayer(gsValues["map_bing_key"], {
      type: "Aerial",
    });
    mapLayers["bhyb"] = new L.BingLayer(gsValues["map_bing_key"], {
      type: "AerialWithLabels",
    });
  }

  if (gsValues["map_google"]) {
    mapLayers["gmap"] = new L.Google("ROADMAP");
    mapLayers["gsat"] = new L.Google("SATELLITE");
    mapLayers["ghyb"] = new L.Google("HYBRID");
    mapLayers["gter"] = new L.Google("TERRAIN");
  }

  if (gsValues["map_mapbox"]) {
    mapLayers["mbmap"] = L.tileLayer(
      "https://api.mapbox.com/v4/mapbox.streets/{z}/{x}/{y}.png?access_token=" +
      gsValues["map_mapbox_key"],
      {
        attribution:
          '© <a href="https://www.mapbox.com/map-feedback/">Mapbox</a> © <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>',
      }
    );

    mapLayers["mbsat"] = L.tileLayer(
      "https://api.mapbox.com/v4/mapbox.satellite/{z}/{x}/{y}.png?access_token=" +
      gsValues["map_mapbox_key"],
      {
        attribution:
          '© <a href="https://www.mapbox.com/map-feedback/">Mapbox</a> © <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>',
      }
    );
  }

  if (gsValues["map_yandex"]) {
    mapLayers["yandex"] = new L.Yandex();
  }

  for (var i = 0; i < gsValues["map_custom"].length; i++) {
    var layer_id = gsValues["map_custom"][i].layer_id;
    var name = gsValues["map_custom"][i].name;
    var type = gsValues["map_custom"][i].type;
    var url = gsValues["map_custom"][i].url;
    var layers = gsValues["map_custom"][i].layers;

    if (type == "tms") {
      mapLayers[layer_id] = L.tileLayer(url, { tms: true });
    } else {
      mapLayers[layer_id] = L.tileLayer.wms(url, { layers: layers });
    }
  }
}

function defineMapKMLLayers(map) {
  var path = "data/user/kml/";

  if (isMobileVersion() || isObjectFollow()) {
    var path = "../" + path;
  } else if (isSharePosition()) {
    var path = "../../" + path;
  }

  mapLayers["kml"].clearLayers();

  for (var key in settingsKMLData) {
    if (settingsKMLData[key].active == "true") {
      fetch(path + settingsKMLData[key].kml_file)
        .then((res) => res.text())
        .then((kmltext) => {
          var parser = new DOMParser();
          var kml = parser.parseFromString(kmltext, "text/xml");
          var layer = new L.KML(kml);
          mapLayers["kml"].addLayer(layer);
        });
    }
  }
}

function switchMapLayer(id) {
  gsValues["map_layer"] = id;

  var backup_layer = false;

  if (gsValues["map_osm"]) {
    if (map.hasLayer(mapLayers["osm"])) {
      map.removeLayer(mapLayers["osm"]);
    }

    if (backup_layer == false) {
      backup_layer = "osm";
    }
  }

  if (gsValues["map_bing"]) {
    if (map.hasLayer(mapLayers["broad"])) {
      map.removeLayer(mapLayers["broad"]);
    }

    if (map.hasLayer(mapLayers["baer"])) {
      map.removeLayer(mapLayers["baer"]);
    }

    if (map.hasLayer(mapLayers["bhyb"])) {
      map.removeLayer(mapLayers["bhyb"]);
    }

    if (backup_layer == false) {
      backup_layer = "broad";
    }
  }

  if (gsValues["map_google"]) {
    if (map.hasLayer(mapLayers["gmap"])) {
      map.removeLayer(mapLayers["gmap"]);
    }

    if (map.hasLayer(mapLayers["gsat"])) {
      map.removeLayer(mapLayers["gsat"]);
    }

    if (map.hasLayer(mapLayers["ghyb"])) {
      map.removeLayer(mapLayers["ghyb"]);
    }

    if (map.hasLayer(mapLayers["gter"])) {
      map.removeLayer(mapLayers["gter"]);
    }

    if (backup_layer == false) {
      backup_layer = "gmap";
    }
  }

  if (gsValues["map_mapbox"]) {
    if (map.hasLayer(mapLayers["mbmap"])) {
      map.removeLayer(mapLayers["mbmap"]);
    }

    if (map.hasLayer(mapLayers["mbsat"])) {
      map.removeLayer(mapLayers["mbsat"]);
    }

    if (backup_layer == false) {
      backup_layer = "mbmap";
    }
  }

  if (gsValues["map_yandex"]) {
    if (map.hasLayer(mapLayers["yandex"])) {
      map.removeLayer(mapLayers["yandex"]);
    }

    if (backup_layer == false) {
      backup_layer = "yandex";
    }
  }

  for (var i = 0; i < gsValues["map_custom"].length; i++) {
    var layer_id = gsValues["map_custom"][i].layer_id;

    if (map.hasLayer(mapLayers[layer_id])) {
      map.removeLayer(mapLayers[layer_id]);
    }
  }

  try {
    map.addLayer(mapLayers[gsValues["map_layer"]]);
  } catch (e) {
    gsValues["map_layer"] = backup_layer;

    if (backup_layer != false) {
      map.addLayer(mapLayers[gsValues["map_layer"]]);
    }
  }

  if (document.getElementById("map_layer").value != gsValues["map_layer"]) {
    document.getElementById("map_layer").value = gsValues["map_layer"];
  }
}

function switchMapLayerFuel() {
  gsValues["map_layer"] = "gmap";

  var backup_layer = "gmap";

  if (gsValues["map_google"]) {
    if (map_fuel.hasLayer(mapLayers["gmap"])) {
      map_fuel.removeLayer(mapLayers["gmap"]);
    }
  }

  try {
    map_fuel.addLayer(mapLayers[gsValues["map_layer"]]);
  } catch (e) {
    gsValues["map_layer"] = backup_layer;

    map_fuel.addLayer(mapLayers[gsValues["map_layer"]]);
  }
}

function fitObjectsOnMap() {
  var count = 0;
  var bounds = new Array();

  for (var imei in objectsData) {
    if (
      objectsData[imei]["data"] != "" &&
      objectsData[imei]["visible"] == true
    ) {
      var lat = objectsData[imei]["data"][0]["lat"];
      var lng = objectsData[imei]["data"][0]["lng"];

      bounds.push([lat, lng]);

      count += 1;
    }
  }

  if (count > 0) {
    map.fitBounds(bounds);
  }
}

// #################################################
// END MAP FUNCTIONS
// #################################################

// #################################################
// GROUP LIST FUNCTIONS
// #################################################

function getGroupsObjectsArray(protocol) {
  var groups_arr = new Array();

  for (var group_id in settingsObjectGroupData) {
    var items_arr = new Array();

    for (var imei in settingsObjectData) {
      var object = settingsObjectData[imei];

      if (object.group_id == group_id) {
        if (object.active == "true") {
          if (protocol == undefined || protocol == "") {
            items_arr.push({ value: imei, name: object.name });
          } else {
            if (protocol == object.protocol) {
              items_arr.push({ value: imei, name: object.name });
            }
          }
        }
      }
    }

    var group_name = settingsObjectGroupData[group_id].name;

    items_arr = sortArrayByElement(items_arr, "name");

    groups_arr.push({ name: group_name, id: group_id, items: items_arr });
  }

  groups_arr = sortArrayByElement(groups_arr, "name");

  // ungrouped goes to the top
  for (var key in groups_arr) {
    var group = groups_arr[key];

    if (group.id == 0) {
      arrayMove(groups_arr, key, 0);
    }
  }

  return groups_arr;
}

function getGroupsObjectsArraySubaccounts(protocol) {
  var groups_arr = new Array();

  for (var group_id in settingsObjectGroupData) {
    var items_arr = new Array();

    for (var imei in settingsObjectData) {
      var object = settingsObjectData[imei];

      if (object.group_id == group_id) {
        if (object.active == "true") {
          if (protocol == undefined || protocol == "") {
            items_arr.push({ value: imei, name: object.name });
          } else {
            if (protocol == object.protocol) {
              items_arr.push({ value: imei, name: object.name });
            }
          }
        }
      }
    }

    var group_name = settingsObjectGroupData[group_id].name;

    items_arr = sortArrayByElement(items_arr, "name");

    groups_arr.push({ name: group_name, id: group_id, items: items_arr });
  }

  groups_arr = sortArrayByElement(groups_arr, "name");

  // ungrouped goes to the top
  for (var key in groups_arr) {
    var group = groups_arr[key];

    if (group.id == 0) {
      arrayMove(groups_arr, key, 0);
    }
  }

  var grp_zero = groups_arr[0];
  var grp_others = groups_arr.slice(1);

  // se quitan los imeis del grupo cero, solo si ya estan el cualquier otro grupo
  for (var key_grp_zero in grp_zero.items) {
    //console.log('Zero: ',grp_zero.items[key_grp_zero].value);
    for (var key_grp_others in grp_others) {
      for (var key_grp_items in grp_others[key_grp_others].items) {
        r_grp_others =
          grp_others[key_grp_others].items[key_grp_items].value.split("_");
        if (
          !typeof grp_zero.items[key_grp_zero].value === "undefined" &&
          r_grp_others[0] == grp_zero.items[key_grp_zero].value
        ) {
          groups_arr[0].items.splice(key_grp_items, 1);
          break;
          console.log("Others: ", r_grp_others[0]);
        }
      }
    }
  }

  return groups_arr;
}

function getGroupsPlacesArray(places) {
  if (places == "markers") {
    var placesData = placesMarkerData["markers"];
  } else if (places == "routes") {
    var placesData = placesRouteData["routes"];
  } else if (places == "zones") {
    var placesData = placesZoneData["zones"];
  } else {
    return;
  }

  var groups_arr = new Array();

  for (var group_id in placesGroupData["groups"]) {
    var items_arr = new Array();

    for (var id in placesData) {
      var place = placesData[id];

      if (place.data.group_id == group_id) {
        items_arr.push({ value: id, name: place.data.name });
      }
    }

    var group_name = placesGroupData["groups"][group_id].name;

    items_arr = sortArrayByElement(items_arr, "name");

    groups_arr.push({ name: group_name, id: group_id, items: items_arr });
  }

  groups_arr = sortArrayByElement(groups_arr, "name");

  // ungrouped goes to the top
  for (var key in groups_arr) {
    var group = groups_arr[key];

    if (group.id == 0) {
      arrayMove(groups_arr, key, 0);
    }
  }

  return groups_arr;
}

function getSubAccountsArray() {
  var subAccounts = new Array();
  for (var id in settingsSubaccountData) {
    subAccounts.push({ value: id, name: settingsSubaccountData[id].username });
  }
  return subAccounts;
}

// #################################################
// END GROUP LIST FUNCTIONS
// #################################################

// #################################################
// OTHER FUNCTIONS
// #################################################

function getAllProtocolsArray() {
  var arr_protocols = new Array();

  for (var imei in settingsObjectData) {
    var protocol = settingsObjectData[imei]["protocol"];
    arr_protocols.push(protocol);
  }

  return uniqueArray(arr_protocols);
}

function getEngineIcon(status) {
  var path = "";

  if (isMobileVersion()) {
    path = "../";
  }

  switch (status) {
    case 0:
      return (
        '<img src="' +
        path +
        'theme/images/engine-off.svg" style="width: 16px;" title="' +
        la["ENGINE_OFF"] +
        '"/>'
      );
      break;
    case 1:
      return (
        '<img src="' +
        path +
        'theme/images/engine-on.svg" style="width: 16px;" title="' +
        la["ENGINE_ON"] +
        '"/>'
      );
      break;
  }
}

function getConnectionIcon(status) {
  var path = "";

  if (isMobileVersion()) {
    path = "../";
  }

  switch (status) {
    case 0:
      return (
        '<img src="' +
        path +
        'theme/images/connection-no.svg" style="width: 16px;" title="' +
        la["CONNECTION_NO_GPS_NO"] +
        '"/>'
      );
      break;
    case 1:
      return (
        '<img src="' +
        path +
        'theme/images/connection-gsm.svg" style="width: 16px;" title="' +
        la["CONNECTION_YES_GPS_NO"] +
        '"/>'
      );
      break;
    case 2:
      return (
        '<img src="' +
        path +
        'theme/images/connection-gsm-gps.svg" style="width: 16px;" title="' +
        la["CONNECTION_YES_GPS_YES"] +
        '"/>'
      );
      break;
  }
}

function getObjectListColor(imei, status, ohc_color) {
  var color = "";

  if (status == "B") {
    if (settingsUserData["ohc"]["stop_engine"] == true) {
      color = settingsUserData["ohc"]["stop_engine_color"];
    }
  } else if (status == "off") {
    if (settingsUserData["ohc"]["no_connection"] == true) {
      color = settingsUserData["ohc"]["no_connection_color"];
    }
  } else if (status == "s") {
    if (settingsUserData["ohc"]["stopped"] == true) {
      color = settingsUserData["ohc"]["stopped_color"];
    }
  } else if (status == "m") {
    if (settingsUserData["ohc"]["moving"] == true) {
      color = settingsUserData["ohc"]["moving_color"];
    }
  } else if (status == "i") {
    if (
      settingsUserData["ohc"]["engine_idle"] == true &&
      settingsUserData["ohc"]["time_idle"]
    ) {
      let dt_last_idle = objectsData[imei].dt_last_idle;
  
      if (new Date(dt_last_idle).getTime() >= 1) {
        let dt_now = new Date().getTime();
        let dt_last_idle_ms = new Date(dt_last_idle).getTime();
        dt_last_idle_ms -= 6 * 60 * 60 * 1000;

        let dt_difference = Math.abs((dt_last_idle_ms - dt_now) / 1000);
        let time = settingsUserData["ohc"]["time_idle"] * 60;

        if (dt_difference > time) {
          color = settingsUserData["ohc"]["engine_idle_color"];
        } else if (settingsUserData["ohc"]["engine_idle"] == true && !time) {
          color = settingsUserData["ohc"]["engine_idle_color"];
        }
      } else {
        color = settingsUserData["ohc"]["moving_color"];
      }
    }
  }

  if (ohc_color != false) {
    color = ohc_color;
  }

  return color;
}


function getMarkerIconNew(imei, angle) {

  let mapIcon = settingsObjectData[imei]["map_icon"];
  let image = mapIcon.split('/').pop().replace('.svg', '');
  
  if (image == 'new_tornado-van') {
    if (angle < 180) {
      mapIcon = "img/markers/objects/new_tornado-van_norte.svg";
    } else {
      mapIcon = "img/markers/objects/new_tornado_izq_sur.svg";
    }
  } else if (image == 'new_peugeot-expert') {
    if (angle < 180) {
      mapIcon = "img/markers/objects/new_peugeot-expert-norte.svg";
    } else {
      mapIcon = "img/markers/objects/new_peugeot-expert-norte-espejo.svg";
    }
  } else if (image == 'new_kangoo_front') {
    if (angle < 180) {
      mapIcon = "img/markers/objects/kangoo.svg";
    } else {
      mapIcon = "img/markers/objects/kangoo-espejo.svg";
    }
  }  

  if (mapIcon && (mapIcon.endsWith(".svg") || mapIcon.endsWith(".png"))) {
    return L.icon({
      iconUrl: mapIcon,
      iconSize: [40, 50],
      iconAnchor: [14, 14],
      popupAnchor: [0, 0]
    });
  }

  var icon = settingsObjectData[imei]["icon"];
  return mapMarkerIcons[icon];
}

function getMarkerIcon(imei, speed, status, arrow_color) {
  const mapIcon = settingsObjectData[imei]["map_icon"];

  if (mapIcon === "arrow") {
    var arrow = settingsObjectData[imei]["map_arrows"]["arrow_stopped"];

    if (status === false) {
      speed = convSpeedUnits(speed, "km", settingsUserData["unit_distance"]);

      var min_idle_speed =
        settingsObjectData[imei]["accuracy"]["min_idle_speed"];

      min_idle_speed = convSpeedUnits(
        min_idle_speed,
        "km",
        settingsUserData["unit_distance"]
      );

      if (speed > min_idle_speed) {
        arrow = settingsObjectData[imei]["map_arrows"]["arrow_moving"];
      }
    } else {
      if (status === "off") {
        arrow = settingsObjectData[imei]["map_arrows"]["arrow_no_connection"];
      } else if (status === "m") {
        arrow = settingsObjectData[imei]["map_arrows"]["arrow_moving"];
      } else if (status === "i") {
        if (
          settingsObjectData[imei]["map_arrows"]["arrow_engine_idle"] !== "off"
        ) {
          arrow = settingsObjectData[imei]["map_arrows"]["arrow_engine_idle"];
        }
      }

      if (arrow_color !== false) {
        arrow = arrow_color;
      }
    }

    return mapMarkerIcons[arrow];
  }
  var icon = settingsObjectData[imei]["icon"];
  return mapMarkerIcons[icon];
}


function urlPosition(lat, lng) {
  var position =
    '<a href="https://maps.google.com/maps?q=' +
    lat +
    "," +
    lng +
    '&t=m" target="_blank">' +
    parseFloat(lat).toFixed(6) +
    " &deg;, " +
    parseFloat(lng).toFixed(6) +
    " &deg;</a>";

  return position;
}

function fileExist(url) {
  var xhr = new XMLHttpRequest();
  xhr.open("HEAD", url, false);
  xhr.send();

  if (xhr.status == "301" || xhr.status == "404") {
    return false;
  } else {
    return true;
  }
}

function imageExists(url, response) {
  var image = new Image();

  image.onload = function () {
    response(true);
  };
  image.onerror = function () {
    response(false);
  };

  image.src = url;
}

function loadLanguage(response) {
  var path = "func/fn_lng.php";

  if (isMobileVersion() || isObjectFollow()) {
    var path = "../" + path;
  } else if (isSharePosition()) {
    var path = "../../" + path;
  }

  var data = {
    cmd: "load_language",
  };

  $.ajax({
    type: "POST",
    url: path,
    data: data,
    dataType: "json",
    cache: false,
    success: function (result) {
      la = result;

      if ($.jgrid != undefined) {
        $.jgrid.nav.addtitle = la["ADD"];
        $.jgrid.nav.refreshtitle = la["RELOAD"];
        $.jgrid.defaults.recordtext = la["GRID_VIEW_TEXT"];
        $.jgrid.defaults.emptyrecords = la["NO_RECORDS_TO_VIEW"];
        $.jgrid.defaults.loadtext = la["LOADING"];
        $.jgrid.defaults.pgtext = la["GRID_PAGE_TEXT"];
      }

      response(true);
    },
  });
}

function switchLanguageCPanel() {
  var language = document.getElementById("system_language").value;

  var data = {
    cmd: "save_user_language",
    language: language,
  };

  $.ajax({
    type: "POST",
    url: "func/fn_settings.php",
    data: data,
    cache: false,
    success: function (result) {
      if (result === "OK") {
        window.open("cpanel.php", "_self", false);
      }
    },
  });
}

function openNewSystem() {
  /**
   * Open new system to clients
   */
  var data = {
    cmd: "openNuevasFunciones",
  };
  var data_default = {
    error: true,
    msg: "No se registraron datos en el payload",
  };
  $.ajax({
    type: "POST",
    url: "custom/service_new.php",
    data: data,
    cache: false,
    success: function (result) {
      let payload = result ? JSON.parse(result) : data_default;
      if (payload.error === true) {
        alert(payload.msg);
      } else {
        window.open(payload.url, "_blank");
      }
    },
  });
}

function switchLanguageTracking() {
  var language = document.getElementById("system_language").value;

  var data = {
    cmd: "save_user_language",
    language: language,
  };

  $.ajax({
    type: "POST",
    url: "func/fn_settings.php",
    data: data,
    cache: false,
    success: function (result) {
      if (result == "OK") {
        window.open("tracking.php", "_self", false);
      }
    },
  });
}

function switchLanguageLogin() {
  var language = document.getElementById("system_language").value;
  window.open("index.php?lng=" + language, "_self", false);
}

function getNearestMarker(imei, lat, lng) {
  var name = "";
  var distance = 0;

  for (var marker_id in placesMarkerData["markers"]) {
    var marker = placesMarkerData["markers"][marker_id];

    var marker_lat = marker.data.lat;
    var marker_lng = marker.data.lng;

    var temp = getLengthBetweenCoordinates(lat, lng, marker_lat, marker_lng);

    if (distance > temp || distance == 0) {
      distance = temp;
      name = marker.data.name;
    }
  }

  distance = convDistanceUnits(
    distance,
    "km",
    settingsUserData["unit_distance"]
  );

  distance = distance.toFixed(2);

  distance += " " + la["UNIT_DISTANCE"];

  var result = [];
  result["name"] = name;
  result["distance"] = distance;

  return result;
}

function getNearestZone(imei, lat, lng) {
  var in_zone_vertices = [];
  var name = "";
  var distance = 0;

  for (var zone_id in placesZoneData["zones"]) {
    var zone = placesZoneData["zones"][zone_id];

    var zone_vertices = zone.data.vertices.split(",");

    for (j = 0; j < zone_vertices.length; j += 2) {
      var zone_lat = zone_vertices[j];
      var zone_lng = zone_vertices[j + 1];

      var temp = getLengthBetweenCoordinates(lat, lng, zone_lat, zone_lng);

      if (distance > temp || distance == 0) {
        distance = temp;
        name = zone.data.name;
        in_zone_vertices = zone_vertices;
      }
    }
  }

  // prepare polygon array to check if it is in zone
  var polygon = [];
  for (j = 0; j < in_zone_vertices.length; j += 2) {
    var zone_lat = parseFloat(in_zone_vertices[j]);
    var zone_lng = parseFloat(in_zone_vertices[j + 1]);
    polygon.push({ x: zone_lat, y: zone_lng });
  }

  // if in zone, reset distance
  if (isPointInPolygon(polygon, { x: lat, y: lng }) == true) {
    distance = 0;
  }

  distance = convDistanceUnits(
    distance,
    "km",
    settingsUserData["unit_distance"]
  );

  distance = distance.toFixed(2);

  distance += " " + la["UNIT_DISTANCE"];

  var result = [];
  result["name"] = name;
  result["distance"] = distance;

  return result;
}

function verifyGPSNearestZone(zone, lat, lng) {
  var in_zone_vertices = new Array();
  in_zone_vertices = zone.split(",");
  var distance = 0;

  for (j = 0; j < in_zone_vertices.length; j += 2) {
    var zone_lat = in_zone_vertices[j];
    var zone_lng = in_zone_vertices[j + 1];

    var temp = getLengthBetweenCoordinates(lat, lng, zone_lat, zone_lng);

    if (distance > temp || distance == 0) {
      distance = temp;
    }
  }

  // prepare polygon array to check if it is in zone
  var polygon = [];
  for (j = 0; j < in_zone_vertices.length; j += 2) {
    var zone_lat = parseFloat(in_zone_vertices[j]);
    var zone_lng = parseFloat(in_zone_vertices[j + 1]);
    polygon.push({ x: zone_lat, y: zone_lng });
  }

  // if in zone, reset distance
  if (isPointInPolygon(polygon, { x: lat, y: lng }) == true) {
    distance = 0;
  }

  distance = convDistanceUnits(
    distance,
    "km",
    settingsUserData["unit_distance"]
  );

  distance = distance.toFixed(2);

  distance += " " + la["UNIT_DISTANCE"];

  var result = [];
  result["distance"] = distance;

  return result;
}

function getDriverFromSensor(imei, params) {
  var driver = false;

  var driver_assign_id = false;

  var sensor = getSensorFromType(imei, "da");

  if (sensor != false) {
    var sensor_ = sensor[0];

    var sensor_data = getSensorValue(params, sensor_);
    driver_assign_id = sensor_data.value;
  } else {
    return driver;
  }

  driver_assign_id = driver_assign_id.toString().toUpperCase();

  var drivers = settingsObjectDriverData;
  for (var driver_id in drivers) {
    if (driver_assign_id == drivers[driver_id].assign_id.toUpperCase()) {
      driver = drivers[driver_id];
      // add id, because we will need it later
      driver.driver_id = driver_id;

      return driver;
    }
  }

  return driver;
}

function getTrailerFromSensor(imei, params) {
  var trailer = false;

  var trailer_assign_id = false;

  var sensor = getSensorFromType(imei, "ta");

  if (sensor != false) {
    var sensor_ = sensor[0];

    var sensor_data = getSensorValue(params, sensor_);
    trailer_assign_id = sensor_data.value;
  } else {
    return trailer;
  }

  trailer_assign_id = trailer_assign_id.toString().toUpperCase();

  var trailers = settingsObjectTrailerData;
  for (var trailer_id in trailers) {
    if (trailer_assign_id == trailers[trailer_id].assign_id.toUpperCase()) {
      trailer = trailers[trailer_id];
      // add id, because we will need it later
      trailer.trailer_id = trailer_id;

      return trailer;
    }
  }

  return trailer;
}

function getDriver(imei, params) {
  var driver = false;

  var driver_id = settingsObjectData[imei]["driver_id"];

  if (driver_id == "-1") {
    return driver;
  }

  if (driver_id == "0") {
    return getDriverFromSensor(imei, params);
  }

  if (settingsObjectDriverData[driver_id]) {
    driver = settingsObjectDriverData[driver_id];
    // add id, because we will need it later
    driver.driver_id = driver_id;
  }

  return driver;
}

function getTrailer(imei, params) {
  var trailer = false;

  var trailer_id = settingsObjectData[imei]["trailer_id"];

  if (trailer_id == "-1") {
    return trailer;
  }

  if (trailer_id == "0") {
    return getTrailerFromSensor(imei, params);
  }

  if (settingsObjectTrailerData[trailer_id]) {
    trailer = settingsObjectTrailerData[trailer_id];
    // add id, because we will need it later
    trailer.trailer_id = trailer_id;
  }

  return trailer;
}

// #################################################
// END OTHER FUNCTIONS
// #################################################
