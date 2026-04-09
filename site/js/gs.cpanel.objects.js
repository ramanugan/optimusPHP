function objectAdd(cmd) {
  switch (cmd) {
    case "open":
      if (cpValues["privileges"] == "manager") {
        document.getElementById("dialog_object_add_manager_id").disabled = true;
      }

      document.getElementById("dialog_object_add_active").checked = true;
      document.getElementById(
        "dialog_object_add_object_expire"
      ).checked = false;

      if (cpValues["privileges"] == "manager") {
        if (cpValues["obj_days"] == "true") {
          document.getElementById("dialog_object_add_object_expire_dt").value =
            cpValues["obj_days_dt"];
        } else {
          document.getElementById("dialog_object_add_object_expire_dt").value =
            moment().add("years", 1).format("YYYY-MM-DD");
        }
      } else {
        document.getElementById("dialog_object_add_object_expire_dt").value =
          moment().add("years", 1).format("YYYY-MM-DD");
      }

      document.getElementById("dialog_object_add_name").value = "";
      document.getElementById("dialog_object_add_imei").value = "";
      document.getElementById("dialog_object_add_model").value = "";
      document.getElementById("dialog_object_add_device").value = "";
      document.getElementById("dialog_object_add_sim_number").value = "";
      document.getElementById("dialog_object_add_sim_number_company").value ="";
      document.getElementById("dialog_object_add_sim_card_acount").value = "";
      document.getElementById("dialog_object_add_sensor_trademark").value = "";
      document.getElementById("dialog_object_add_no_sensor1").value = "";
      document.getElementById("dialog_object_add_no_sensor2").value = "";
      document.getElementById("dialog_object_add_no_sensor3").value = "";
      document.getElementById("dialog_object_add_manager_id").value = 0;
      document.getElementById("dialog_object_add_acc").value = "";
      document.getElementById("dialog_object_add_renta").value = 0.0;
      document.getElementById("dialog_object_add_observaciones").value = "";
      document.getElementById("dialog_object_add_seller").value = "";
      document.getElementById("dialog_object_add_plan").value = "";
      let fecha = fecha_actual();
      document.getElementById("dialog_object_add_fecha_alta").value = fecha;

      $("#dialog_object_add_manager_id").multipleSelect("refresh");
      $("#dialog_object_add_acc").multipleSelect("refresh");

      // Datos del vehiculo //

      document.getElementById("dialog_object_add_vehicle_year").value = "";
      document.getElementById("dialog_object_add_vehicle_brand").value = "";
      document.getElementById("dialog_object_add_vehicle_model").value = "";
      document.getElementById("dialog_object_add_vehicle_color").value = "";
      document.getElementById("dialog_object_add_vehicle_plate").value = "";
      document.getElementById("dialog_object_add_vehicle_vin").value = "";
      document.getElementById("dialog_object_add_current_odometer").value = "";
      document.getElementById("dialog_object_add_vehicle_insurance").value = "";
      document.getElementById("dialog_object_add_vehicle_insurance_exp").value = "";
      document.getElementById("dialog_object_add_vehicle_fuel").value = "";


      $("#dialog_object_add_vehicle_year").multipleSelect("refresh");
      $("#dialog_object_add_vehicle_fuel").multipleSelect("refresh");
      $("#dialog_object_add_vehicle_brand").multipleSelect("refresh");

      // Datos Sensores //
      [
        "clutch", "freno", "maletero", "p_conductor", "p_copiloto",
        "rpm", "velocidad", "nivel_combustible", "ignicion", "bateria",
        "alimentacion_principal", "bloqueo", "consumo", "c_seguridad",
        "l_frontales", "l_estacionamiento", "t_motor", "f_mano", "panico",
        "ch_magnetica"
      ].forEach(id => {
        const $el = $("#dialog_object_add_" + id);
      
        if ($el.data("multipleSelect")) {
          $el.multipleSelect("setSelects", ["false"]);
          $el.multipleSelect("refresh");
        } else {
          $el.val("false");
        }
      });

      // SensoresTemp_diesel //
      document.getElementById("dialog_object_add_sensor_number_1").value = "";
      document.getElementById("dialog_object_add_sensor_1").value = "";
      document.getElementById("dialog_object_add_largo_1").value = "";
      document.getElementById("dialog_object_add_alto_1").value = "";
      document.getElementById("dialog_object_add_largo_1").disabled = true;
      document.getElementById("dialog_object_add_alto_1").disabled = true;

      document.getElementById("dialog_object_add_sensor_number_2").value = "";
      document.getElementById("dialog_object_add_sensor_2").value = "";
      document.getElementById("dialog_object_add_largo_2").value = "";
      document.getElementById("dialog_object_add_alto_2").value = "";
      document.getElementById("dialog_object_add_largo_2").disabled = true;
      document.getElementById("dialog_object_add_alto_2").disabled = true;

      document.getElementById("dialog_object_add_sensor_number_3").value = "";
      document.getElementById("dialog_object_add_sensor_3").value = "";
      document.getElementById("dialog_object_add_largo_3").value = "";
      document.getElementById("dialog_object_add_alto_3").value = "";
      document.getElementById("dialog_object_add_largo_3").disabled = true;
      document.getElementById("dialog_object_add_alto_3").disabled = true;


      $("#dialog_object_add_sensor_1").multipleSelect("refresh");
      $("#dialog_object_add_sensor_2").multipleSelect("refresh");
      $("#dialog_object_add_sensor_3").multipleSelect("refresh");
      $("#dialog_object_add_sensor_number_1").multipleSelect("refresh");
      $("#dialog_object_add_sensor_number_2").multipleSelect("refresh");
      $("#dialog_object_add_sensor_number_3").multipleSelect("refresh");
      
      
      objectAddCheck();

      $("#dialog_object_add_users").tokenize().clear();
      $("#dialog_object_add").dialog("open");
    break;

    case "add":
      var name = document.getElementById("dialog_object_add_name").value;
      var imei = document.getElementById("dialog_object_add_imei").value;
      var model = document.getElementById("dialog_object_add_model").value;
      var device = document.getElementById("dialog_object_add_device").value;
      var sim_number = document.getElementById("dialog_object_add_sim_number").value;
      var sim_number_company = document.getElementById("dialog_object_add_sim_number_company").value;
      var cuenta_padre = document.getElementById("dialog_object_add_sim_card_acount").value;
      var sensor_trademark = document.getElementById("dialog_object_add_sensor_trademark").value;
      var no_sensor1 = document.getElementById("dialog_object_add_no_sensor1").value;
      var no_sensor2 = document.getElementById("dialog_object_add_no_sensor2").value;
      var no_sensor3 = document.getElementById("dialog_object_add_no_sensor3").value;
      var manager_id = document.getElementById("dialog_object_add_manager_id").value;
      var acc = multiselectGetValues(document.getElementById("dialog_object_add_acc"));
      var active = document.getElementById("dialog_object_add_active").checked;
      var object_expire = document.getElementById("dialog_object_add_object_expire").checked;
      var object_expire_dt = document.getElementById("dialog_object_add_object_expire_dt").value;
      var renta = document.getElementById("dialog_object_add_renta").value;
      var observaciones = document.getElementById("dialog_object_add_observaciones").value;
      var seller = document.getElementById("dialog_object_add_seller").value;
      var plan = document.getElementById("dialog_object_add_plan").value;

      // Datos del vehiculo //

      var v_year = document.getElementById("dialog_object_add_vehicle_year").value;
      var v_brand = document.getElementById("dialog_object_add_vehicle_brand").value;
      var v_model = document.getElementById("dialog_object_add_vehicle_model").value;
      var v_color = document.getElementById("dialog_object_add_vehicle_color").value;
      var v_vin = document.getElementById("dialog_object_add_vehicle_vin").value;
      var v_plate = document.getElementById("dialog_object_add_vehicle_plate").value;
      var v_odo = document.getElementById("dialog_object_add_current_odometer").value;
      var v_ins = document.getElementById("dialog_object_add_vehicle_insurance").value;
      var v_insx = document.getElementById("dialog_object_add_vehicle_insurance_exp").value;
      var v_fuel = document.getElementById("dialog_object_add_vehicle_fuel").value;

      // Datos Sensores //
      var v_alimentacion_principal = document.getElementById("dialog_object_add_alimentacion_principal").value;
      var v_bateria                = document.getElementById("dialog_object_add_bateria").value;
      var v_ignicion               = document.getElementById("dialog_object_add_ignicion").value;
      var v_bloqueo                = document.getElementById("dialog_object_add_bloqueo").value;
      // var v_panico                 = document.getElementById("dialog_object_add_panico").value;
      var v_vel                    = document.getElementById("dialog_object_add_velocidad").value;
      var v_t_motor                = document.getElementById("dialog_object_add_t_motor").value;
      var v_consumo                = document.getElementById("dialog_object_add_consumo").value;
      var v_c_seguridad            = document.getElementById("dialog_object_add_c_seguridad").value;
      var v_l_frontales            = document.getElementById("dialog_object_add_l_frontales").value;
      var v_l_estacionamiento      = document.getElementById("dialog_object_add_l_estacionamiento").value;
      
      var v_clutch                 = document.getElementById("dialog_object_add_clutch").value;
      var v_freno                  = document.getElementById("dialog_object_add_freno").value;
      var v_maletero               = document.getElementById("dialog_object_add_maletero").value;
      var v_p_conductor            = document.getElementById("dialog_object_add_p_conductor").value;
      var v_p_copiloto             = document.getElementById("dialog_object_add_p_copiloto").value;
      
      var v_rpm                    = document.getElementById("dialog_object_add_rpm").value;
      var v_nivel_combustible      = document.getElementById("dialog_object_add_nivel_combustible").value;
      var v_f_mano                 = document.getElementById("dialog_object_add_f_mano").value;
      // var v_ch_mag                 = document.getElementById("dialog_object_add_ch_magnetica").value;

      // SensoresTemp_diesel //
      var sn_1  = document.getElementById("dialog_object_add_sensor_number_1").value || 'false';
      var s_1  = document.getElementById("dialog_object_add_sensor_1").value || 'false';
      var sl_1 = document.getElementById("dialog_object_add_largo_1").value || 'false';
      var sa_1 = document.getElementById("dialog_object_add_alto_1").value  || 'false';
      
      var sn_2  = document.getElementById("dialog_object_add_sensor_number_2").value || 'false';
      var s_2  = document.getElementById("dialog_object_add_sensor_2").value || 'false';
      var sl_2 = document.getElementById("dialog_object_add_largo_2").value || 'false';
      var sa_2 = document.getElementById("dialog_object_add_alto_2").value  || 'false';
      
      var sn_3  = document.getElementById("dialog_object_add_sensor_number_3").value || 'false';
      var s_3  = document.getElementById("dialog_object_add_sensor_3").value || 'false';
      var sl_3 = document.getElementById("dialog_object_add_largo_3").value || 'false';
      var sa_3 = document.getElementById("dialog_object_add_alto_3").value  || 'false';
      

      var user_ids = $("#dialog_object_add_users").tokenize().toArray();

      user_ids = JSON.stringify(user_ids);

      const validations = [
        { value: name, msg: la["NAME_CANT_BE_EMPTY"] },
        { value: seller, msg: la["SELLER_CANT_BE_EMPTY"] },
        { value: sim_number_company, msg: la["SIM_NUMBER_COMPANY_CANT_BE_EMPTY"] },
        { value: cuenta_padre, msg: la["SIM_NUMBER_ACOUNT_CANT_BE_EMPTY"] },
        { value: plan, msg: la["PLAN_CANT_BE_EMPTY"] },
        { value: acc, msg: la["ACC_CANT_BE_EMPTY"] },
        { value: sensor_trademark, msg: la["SENSOR_TRADEMARK_CANT_BE_EMPTY"] },
        { value: renta, msg: la["RENT_CANT_BE_EMPTY"], extraCheck: (val) => val === "" || val == 0 }
      ];

      for (const { value, msg, extraCheck } of validations) {
        if (extraCheck ? extraCheck(value) : value === "") {
          notifyDialog(msg);
          return;
        }
      }

      if (!isIMEIValid(imei)) {
        notifyDialog(la["IMEI_IS_NOT_VALID"]);
        return;
      }

      if (
        acc == "Sensor 1,Sensor 2,Sensor 3" &&
        (!no_sensor1 || !no_sensor2 || !no_sensor3)
      ) {
        notifyDialog(la["ACC_SENSOR_CANT_BE_EMPTY"]);
        return;
      }
      if (acc == "Sensor 1" && !no_sensor1) {
        notifyDialog(la["ACC_SENSOR_CANT_BE_EMPTY"]);
        return;
      }
      if (acc == "Sensor 2" && !no_sensor2) {
        notifyDialog(la["ACC_SENSOR_CANT_BE_EMPTY"]);
        return;
      }
      if (acc == "Sensor 3" && !no_sensor3) {
        notifyDialog(la["ACC_SENSOR_CANT_BE_EMPTY"]);
        return;
      }

      // expire object
      if (object_expire == true) {
        if (object_expire_dt == "") {
          notifyDialog(la["DATE_CANT_BE_EMPTY"]);
          break;
        }
      } else {
        object_expire_dt = "";
      }

      var vehicleFields = [v_year, v_brand, v_model, v_color, v_plate, v_odo, v_ins, v_insx, v_fuel];

      for (var i = 0; i < vehicleFields.length; i++) {
        if (vehicleFields[i] === "" || vehicleFields[i] == null) {
          notifyDialog(la["ALL_VALUES_OF_VEHICLES_CAN_BE_FULL"]);
          return;
        }
      }

      var data = {
        cmd: "add_object",
        name: name,
        imei: imei,
        model: model,
        plan: plan,
        device: device,
        sim_number: sim_number,
        sim_number_company: sim_number_company,
        cuenta_padre: cuenta_padre,
        sensor_trademark: sensor_trademark,
        no_sensor1: no_sensor1,
        no_sensor2: no_sensor2,
        no_sensor3: no_sensor3,
        manager_id: manager_id,
        acc: acc,
        active: active,
        object_expire: object_expire,
        object_expire_dt: object_expire_dt,
        user_ids: user_ids,
        renta: renta,
        observaciones: observaciones,
        seller: seller,
      
        // Datos del vehículo
        vehicle_year: v_year,
        vehicle_brand: v_brand,
        vehicle_model: v_model,
        vehicle_color: v_color,
        vehicle_plate: v_plate,
        vehicle_vin: v_vin,
        vehicle_odometer: v_odo,
        vehicle_insurance: v_ins,
        vehicle_insurance_exp: v_insx,
        vehicle_fuel: v_fuel,
      
        // Datos de sensores / señales del vehículo
        alimentacion_principal: v_alimentacion_principal,
        bateria: v_bateria,
        ignicion: v_ignicion,
        bloqueo: v_bloqueo,
        // panico: v_panico,
        velocidad: v_vel,
        t_motor: v_t_motor,
        consumo: v_consumo,
        c_seguridad: v_c_seguridad,
        l_frontales: v_l_frontales,
        l_estacionamiento: v_l_estacionamiento,
        clutch: v_clutch,
        freno: v_freno,
        maletero: v_maletero,
        p_conductor: v_p_conductor,
        p_copiloto: v_p_copiloto,
        rpm: v_rpm,
        nivel_combustible: v_nivel_combustible,
        f_mano: v_f_mano,
        // ch_mag: v_ch_mag,

        //Sensores Temp_Diesel
        sn_1: sn_1,
        s_1: s_1,
        sl_1: sl_1,
        sa_1: sa_1,
        sn_2: sn_2,
        s_2: s_2,
        sl_2: sl_2,
        sa_2: sa_2,
        sn_3: sn_3,
        s_3: s_3,
        sl_3: sl_3,
        sa_3: sa_3
      };      

      $.ajax({
        type: "POST",
        url: "func/fn_cpanel.objects.php",
        data: data,
        success: function (result) {
          if (result == "OK") {
            initSelectList("manager_list");
            initStats();

            $("#cpanel_user_list_grid").trigger("reloadGrid");
            $("#cpanel_object_list_grid").trigger("reloadGrid");
            $("#cpanel_unused_object_list_grid").trigger("reloadGrid");
            $("#dialog_object_add").dialog("close");
          } else if (result == "ERROR_SYSTEM_OBJECT_LIMIT") {
            notifyDialog(la["SYSTEM_OBJECT_LIMIT_IS_REACHED"]);
          } else if (result == "ERROR_OBJECT_LIMIT") {
            notifyDialog(la["OBJECT_LIMIT_IS_REACHED"]);
          } else if (result == "ERROR_EXPIRATION_DATE_NOT_SET") {
            notifyDialog(la["OBJECT_EXPIRATION_DATE_MUST_BE_SET"]);
          } else if (result == "ERROR_EXPIRATION_DATE_TOO_LATE") {
            notifyDialog(la["OBJECT_EXPIRATION_DATE_IS_TOO_LATE"]);
          } else if (result == "ERROR_NO_PRIVILEGES") {
            notifyDialog(la["THIS_ACCOUNT_HAS_NO_PRIVILEGES_TO_DO_THAT"]);
          } else if (result == "ERROR_IMEI_EXISTS") {
            notifyDialog(la["THIS_IMEI_ALREADY_EXISTS"]);
          } else if (result == "ERROR_SIM_NUMBER_EXISTS") {
            notifyDialog(la["THIS_SIM_NUMBER_ALREADY_EXISTS"]);
          } else if (result == "ERROR_SIM_NUMBER_COMPANY_CANT_BE_EMPTY") {
            notifyDialog(la["SIM_NUMBER_COMPANY_CANT_BE_EMPTY"]);
          } else if (result == "ERROR_SENSOR1_NUMBER_EXISTS") {
            notifyDialog(la["THIS_SENSOR1_NUMBER_ALREADY_EXISTS"]);
          } else if (result == "ERROR_SENSOR2_NUMBER_EXISTS") {
            notifyDialog(la["THIS_SENSOR2_NUMBER_ALREADY_EXISTS"]);
          } else if (result == "ERROR_SENSOR3_NUMBER_EXISTS") {
            notifyDialog(la["THIS_SENSOR3_NUMBER_ALREADY_EXISTS"]);
          } else if (result == "DEVICE_CANT_BE_EMPTY") {
            notifyDialog(la["DEVICE_CANT_BE_EMPTY"]);
          }
        },
      });
      break;
    case "cancel":
      $("#dialog_object_add").dialog("close");
      break;
  }
}
function toggleMedidas(i) {
  var sensor = document.getElementById("dialog_object_add_sensor_" + i);
  var largo  = document.getElementById("dialog_object_add_largo_" + i);
  var alto   = document.getElementById("dialog_object_add_alto_" + i);

  if (sensor.value === "Diesel" || sensor.value === "DieselBT") {
    largo.disabled = false;
    alto.disabled = false;
  } else {
    largo.value = "";
    alto.value = "";
    largo.disabled = true;
    alto.disabled = true;
  }
}

function toggleMedidasEdit(i) {
  var sensor = document.getElementById("dialog_object_edit_sensor_" + i);
  var largo  = document.getElementById("dialog_object_edit_largo_" + i);
  var alto   = document.getElementById("dialog_object_edit_alto_" + i);

  if (sensor.value === "Diesel" || sensor.value === "DieselBT") {
    largo.disabled = false;
    alto.disabled = false;
  } else {
    largo.value = "";
    alto.value = "";
    largo.disabled = true;
    alto.disabled = true;
  }
}
function toggleNumeroEdit(i) {

  var numero = document.getElementById("dialog_object_edit_sensor_number_" + i);
  var sensor = document.getElementById("dialog_object_edit_sensor_" + i);
  var largo  = document.getElementById("dialog_object_edit_largo_" + i);
  var alto   = document.getElementById("dialog_object_edit_alto_" + i);

  if (numero.value === "0" || numero.value === 0) {
    sensor.value = "";
    largo.value = "";
    alto.value = "";

    sensor.disabled = true;
    largo.disabled = true;
    alto.disabled = true;

    return;
  }
  sensor.disabled = false;

  if (sensor.value === "Diesel" || sensor.value === "DieselBT") {
    largo.disabled = false;
    alto.disabled = false;
  } else {
    largo.value = "";
    alto.value = "";
    largo.disabled = true;
    alto.disabled = true;
  }
}


function objectAddCheck() {
  var object_expire = document.getElementById(
    "dialog_object_add_object_expire"
  ).checked;
  if (object_expire == true) {
    document.getElementById(
      "dialog_object_add_object_expire_dt"
    ).disabled = false;
  } else {
    document.getElementById(
      "dialog_object_add_object_expire_dt"
    ).disabled = true;
  }
}

function objectEditCrm(cmd) {
  if (cmd === "cancel") {
    $("#dialog_object_edit_crm").dialog("close");
    return;
  }

  if (cmd === "save") {
    const att_status = multiselectGetValues(document.getElementById("dialog_attended_details_estatus_list"));
    const fecha_service = document.getElementById("dialog_object_edit_fecha_alta_crm").value;
    const newDetail = $("#dialog_attended_detail_event_crm").val();

    if (att_status.includes("Agendar") && fecha_service === "") {
      notifyDialog(la["DATE_CANT_BE_EMPTY"]);
    } else if (att_status.includes("Postfechar") && fecha_service === cpValues["edit_fecha_alta_crm"]) {
      notifyDialog(la["DATE_CANT_BE_EMPTY"]);
    } else if (newDetail == "") {
      notifyDialog(la["COMMENT_CANT_BE_EMPTY"]);
    } else {
      saveEventData();
    }
    return;
  }

  $.ajax({
    type: "POST",
    url: "func/fn_cpanel.objects.php",
    data: {
      cmd: "load_object_data_crm",
      imei: cmd,
    },
    dataType: "json",
    cache: false,
    success: function (e) {
      multiselectSetValues(
        document.getElementById("dialog_attended_details_estatus_list"),
        [e.attended_status]
      );
  
      cpValues["edit_fecha_alta_crm"] = e["fecha_service"] || "";
      cpValues["new_dialog_object_edit_fecha_alta_crm"] = "";
  
      $("#dialog_attended_details_estatus_list").multipleSelect("refresh");
      $("#dialog_attended_detail_event_crm").val("");
      objectEditDate();
  
      $("#dialog_object_imei").val(e.imei);
      $("#dialog_object_edit_name_crm").val(e.name);
      $("#dialog_object_edit_imei_crm").val(e.event_desc);
  
      let fecha = fecha_hora_crm();
      $("#dialog_object_edit_fecha_alta_crm").val(fecha);
  
      if (e.attended_status !== "Postfechar") {
        $("#dialog_object_edit_fecha_alta_crm").prop("disabled", true);
      } else {
        $("#dialog_object_edit_fecha_alta_crm").prop("disabled", false);
      }
  
      $("#dialog_object_edit_mtto_crm").html(e.details || "");
      $("#dialog_object_edit_crm").dialog("open");
    }
  });  

  function saveEventData() {
    const attStatus = multiselectGetValues(document.getElementById("dialog_attended_details_estatus_list"));
    const newDetail = $("#dialog_attended_detail_event_crm").val();
    const fecha_service = document.getElementById("dialog_object_edit_fecha_alta_crm").value;

    $.ajax({
      type: "POST",
      url: "func/fn_cpanel.objects.php",
      data: {
        cmd: "save_event_data_crm",
        imei: $("#dialog_object_imei").val(),
        attended_status: attStatus,
        fecha_service: fecha_service,
        detail: newDetail,
      },
      dataType: "json",
      cache: false,
      success: function (e) {
        if (e === 'CHANGE_STATE') {
          notifyDialog(la["CHANGE_STATE"]);
          return;
        } else if (e === 'SELECT_DIFERENT_DATE') {
          notifyDialog(la["SELECT_DIFERENT_DATE"]);
          return;
        } else if (e === 'DATE_INVALID') {
          notifyDialog(la["DATE_INVALID"]);
          return;
        }else if (typeof e === 'object' && e.status === 'OK') {
          sendEmail('open_crm', {
            event: e.event,
            attStatus: e.attended_status,
            newDetail: e.detail,
            fecha_edit: e.fecha_edit,
            fecha_servicio: e.fecha_servicio,
            email_client: e.email_client,
            user_email: e.user_email,
            status_label: e.status_label
          });
        }
    
        $("#dialog_object_edit_crm").dialog("close");
        $("#cpanel_unused_object_list_grid").trigger("reloadGrid");
      }
    });
    
  }
}

function objectEdit(cmd) {
  switch (cmd) {
    default:
      var data = {
        cmd: "load_object_data",
        imei: cmd,
      };

      $.ajax({
        type: "POST",
        url: "func/fn_cpanel.objects.php",
        data: data,
        dataType: "json",
        cache: false,
        success: function (result) {
          if (cpValues["privileges"] == "manager") {
            document.getElementById(
              "dialog_object_edit_manager_id"
            ).disabled = true;
          }

          cpValues["edit_object_imei"] = result["imei"];
          cpValues["edit_object_new_imei"] = "";

          cpValues["edit_object_sim_number"] = result["sim_number"];
          cpValues["edit_object_new_sim_number"] = "";

          cpValues["edit_object_mtto"] = result["mtto"];
          cpValues["edit_object_new_mtto"] = "";

          cpValues["edit_object_sim_number_acount"] = result["cuenta_padre"];
          cpValues["edit_object_new_sim_number_acount"] = "";

          cpValues["edit_object_no_sensor1"] = result["no_sensor1"];
          cpValues["edit_object_new_no_sensor1"] = "";

          cpValues["edit_object_no_sensor2"] = result["no_sensor2"];
          cpValues["edit_object_new_no_sensor2"] = "";

          cpValues["edit_object_no_sensor3"] = result["no_sensor3"];
          cpValues["edit_object_new_no_sensor3"] = "";

          document.getElementById("dialog_object_edit_active").checked =
            strToBoolean(result["active"]);

          var object_expire = strToBoolean(result["object_expire"]);
          document.getElementById("dialog_object_edit_object_expire").checked =
            object_expire;
          if (object_expire == true) {
            document.getElementById(
              "dialog_object_edit_object_expire_dt"
            ).value = result["object_expire_dt"];
          } else {
            document.getElementById(
              "dialog_object_edit_object_expire_dt"
            ).value = "";
          }

          $("#dialog_object_edit_observaciones").val("");
          $("#dialog_object_edit_renta").val(0.0);
          document.getElementById("dialog_object_edit_plan").value =
            result["plan"];
          document.getElementById("dialog_object_edit_name").value =
            result["name"];
          document.getElementById("dialog_object_edit_imei").value =
            result["imei"];
          document.getElementById("dialog_object_edit_model").value =
            result["model"];
          document.getElementById("dialog_object_edit_device").value =
            result["device"];
          document.getElementById("dialog_object_edit_sim_number").value =
            result["sim_number"];
          document.getElementById("dialog_object_edit_sim_number_company").value =
            result["sim_number_company"];
          document.getElementById("dialog_object_edit_sim_number_acount").value = 
            result["cuenta_padre"];
          document.getElementById("dialog_object_edit_sensor_trademark").value =
            result["sensor_trademark"];
          document.getElementById("dialog_object_edit_no_sensor1").value =
            result["no_sensor1"];
          document.getElementById("dialog_object_edit_no_sensor2").value =
            result["no_sensor2"];
          document.getElementById("dialog_object_edit_no_sensor3").value =
            result["no_sensor3"];
          document.getElementById("dialog_object_edit_manager_id").value =
            result["manager_id"];
          document.getElementById("dialog_object_edit_acc").value =
            result["acc"];
          document.getElementById("dialog_object_edit_mtto").value =
            result["mtto"];
          document.getElementById("dialog_object_edit_observaciones").value =
            result["observacion"];
          document.getElementById("dialog_object_edit_renta").value =
            result["renta"];
          document.getElementById("dialog_object_edit_seller").value =
            result["seller"];
          document.getElementById("dialog_object_edit_fecha_alta").value =
            result["fecha"] ? devolver_fecha(result["fecha"]) : "";
            

            // Datos de vehiculo

          document.getElementById("dialog_object_edit_add_vehicle_year").value =
            result["vehicle_year"];
          document.getElementById("dialog_object_edit_add_vehicle_brand").value =
            result["vehicle_brand"];
          document.getElementById("dialog_object_edit_add_vehicle_model").value =
            result["vehicle_model"];
          document.getElementById("dialog_object_edit_add_vehicle_color").value =
            result["vehicle_color"];
          document.getElementById("dialog_object_edit_add_vehicle_plate").value =
            result["vehicle_plate"];
          document.getElementById("dialog_object_edit_add_vehicle_vin").value =
            result["vehicle_vin"];
          document.getElementById("dialog_object_edit_add_current_odometer").value =
            result["vehicle_odometer"];
          document.getElementById("dialog_object_edit_add_vehicle_insurance").value =
            result["vehicle_insurance"];
          document.getElementById("dialog_object_edit_add_vehicle_insurance_exp").value =
            result["vehicle_insurance_exp"];
          document.getElementById("dialog_object_edit_add_vehicle_fuel").value =
            result["vehicle_fuel"];


          $("#dialog_object_edit_add_vehicle_year").multipleSelect("refresh");
          $("#dialog_object_edit_add_vehicle_fuel").multipleSelect("refresh");
          $("#dialog_object_edit_add_vehicle_brand").multipleSelect("refresh");

          $("#dialog_object_edit_manager_id").multipleSelect("refresh");

          $("#dialog_object_edit_acc").multipleSelect("refresh");
          $("#dialog_object_edit_seller").multipleSelect("refresh");
          $("#dialog_object_edit_mtto").multipleSelect("refresh");
          $("#dialog_object_edit_plan").multipleSelect("refresh");
          $("#dialog_object_edit_sim_number_company").multipleSelect("refresh");
          $("#dialog_object_edit_acc").multipleSelect(
            "setSelects",
            result["acc"].split(",")
          );
          if (result["mtto"] !== null) {
            $("#dialog_object_edit_mtto").multipleSelect(
              "setSelects",
              result["mtto"].split(",")
            );
          }
    
         // Datos Sensores //
          [
            "clutch", "freno", "maletero", "p_conductor", "p_copiloto",
            "rpm", "velocidad", "nivel_combustible", "ignicion", "bateria",
            "alimentacion_principal", "bloqueo", "consumo", "c_seguridad",
            "l_frontales", "l_estacionamiento", "t_motor", "f_mano", "panico",
            "ch_magnetica"
          ].forEach(id => {
            const $el = $("#dialog_object_edit_" + id);
          
            const valor = result[id] === 'true' ? 'true' : 'false';
          
            if ($el.data("multipleSelect")) {
              $el.multipleSelect("setSelects", [valor]);
              $el.multipleSelect("refresh");
            } else {
              $el.val(valor);
            }
          });


          [1, 2, 3].forEach(i => {
            const numero = document.getElementById("dialog_object_edit_sensor_number_" + i);
            const sensor = document.getElementById("dialog_object_edit_sensor_" + i);
            const largo  = document.getElementById("dialog_object_edit_largo_"  + i);
            const alto   = document.getElementById("dialog_object_edit_alto_"   + i);
          
            const sn = result["sn_" + i] || "";
            const tipo   = result["s_" + i] || "";
            const sl     = result["s_" + i + "_sl"] || "";
            const sa     = result["s_" + i + "_sa"] || "";

          
            sensor.value = tipo;
            largo.value  = sl || "";
            alto.value   = sa || "";
            numero.value = sn || "";
          
            if (tipo === "Diesel" || tipo === "DieselBT") {
              largo.disabled = false;
              alto.disabled  = false;
            } else {
              largo.disabled = true;
              alto.disabled  = true;
            }
          
            sensor.addEventListener("change", function () {
              const val = sensor.value;
              if (val === "Diesel" || val === "DieselBT") {
                largo.disabled = false;
                alto.disabled  = false;
              } else {
                largo.value = "";
                alto.value  = "";
                largo.disabled = true;
                alto.disabled  = true;
              }
            });
          });
          $("#dialog_object_edit_sensor_1").multipleSelect("refresh");
          $("#dialog_object_edit_sensor_2").multipleSelect("refresh");
          $("#dialog_object_edit_sensor_3").multipleSelect("refresh");
          $("#dialog_object_edit_sensor_number_1").multipleSelect("refresh");
          $("#dialog_object_edit_sensor_number_2").multipleSelect("refresh");
          $("#dialog_object_edit_sensor_number_3").multipleSelect("refresh");

          var users = result["users"];

          objectEditCheck();

          $("#dialog_object_edit_users").tokenize().clear();

          $("#dialog_object_edit_users").tokenize().options.newElements = true;
          var users = result["users"];
          for (var i = 0; i < users.length; i++) {
            var value = users[i].value;
            var text = users[i].text;
            $("#dialog_object_edit_users").tokenize().tokenAdd(value, text);
          }
          $("#dialog_object_edit_users").tokenize().options.newElements = false;
        },
      });

      $("#dialog_object_edit").dialog("open");
      break;

    case "save":
      var active = document.getElementById("dialog_object_edit_active").checked;
      var object_expire = document.getElementById(
        "dialog_object_edit_object_expire"
      ).checked;
      var object_expire_dt = document.getElementById(
        "dialog_object_edit_object_expire_dt"
      ).value;
      var name = document.getElementById("dialog_object_edit_name").value;
      var plan = document.getElementById("dialog_object_edit_plan").value;
      var imei = document.getElementById("dialog_object_edit_imei").value;
      var model = document.getElementById("dialog_object_edit_model").value;
      var device = document.getElementById("dialog_object_edit_device").value;

      var sim_number = document.getElementById("dialog_object_edit_sim_number").value;
      var sim_number_company = document.getElementById("dialog_object_edit_sim_number_company").value;
      var cuenta_padre = document.getElementById("dialog_object_edit_sim_number_acount").value;
      var sensor_trademark = document.getElementById("dialog_object_edit_sensor_trademark").value;
      var no_sensor1 = document.getElementById("dialog_object_edit_no_sensor1").value;
      var no_sensor2 = document.getElementById("dialog_object_edit_no_sensor2").value;
      var no_sensor3 = document.getElementById("dialog_object_edit_no_sensor3").value;
      var manager_id = document.getElementById("dialog_object_edit_manager_id").value;
      var acc = multiselectGetValues(document.getElementById("dialog_object_edit_acc"));
      var mtto = multiselectGetValues(document.getElementById("dialog_object_edit_mtto"));

      // Agregando las nuevas variables asignadas 09/sep/2021
      var fecha_alta = document.getElementById("dialog_object_edit_fecha_alta").value;
      var renta = document.getElementById("dialog_object_edit_renta").value;
      var observaciones = document.getElementById("dialog_object_edit_observaciones").value;
      var seller = document.getElementById("dialog_object_edit_seller").value;
      var user_ids = $("#dialog_object_edit_users").tokenize().toArray();

      // Datos de vehiculo
      var v_year = document.getElementById("dialog_object_edit_add_vehicle_year").value;
      var v_brand = document.getElementById("dialog_object_edit_add_vehicle_brand").value;
      var v_model = document.getElementById("dialog_object_edit_add_vehicle_model").value;
      var v_color = document.getElementById("dialog_object_edit_add_vehicle_color").value;
      var v_plate = document.getElementById("dialog_object_edit_add_vehicle_plate").value;
      var v_vin = document.getElementById("dialog_object_edit_add_vehicle_vin").value;
      var v_odo = document.getElementById("dialog_object_edit_add_current_odometer").value;
      var v_ins = document.getElementById("dialog_object_edit_add_vehicle_insurance").value;
      var v_insx = document.getElementById("dialog_object_edit_add_vehicle_insurance_exp").value;
      var v_fuel = document.getElementById("dialog_object_edit_add_vehicle_fuel").value;
      
      // Datos Sensores //
      var v_alimentacion_principal = document.getElementById("dialog_object_edit_alimentacion_principal").value;
      var v_bateria                = document.getElementById("dialog_object_edit_bateria").value;
      var v_ignicion               = document.getElementById("dialog_object_edit_ignicion").value;
      var v_bloqueo                = document.getElementById("dialog_object_edit_bloqueo").value;
      // var v_panico                 = document.getElementById("dialog_object_edit_panico").value;
      var v_vel                    = document.getElementById("dialog_object_edit_velocidad").value;
      var v_t_motor                = document.getElementById("dialog_object_edit_t_motor").value;
      var v_consumo                = document.getElementById("dialog_object_edit_consumo").value;
      var v_c_seguridad            = document.getElementById("dialog_object_edit_c_seguridad").value;
      var v_l_frontales            = document.getElementById("dialog_object_edit_l_frontales").value;
      var v_l_estacionamiento      = document.getElementById("dialog_object_edit_l_estacionamiento").value;
      
      var v_clutch                 = document.getElementById("dialog_object_edit_clutch").value;
      var v_freno                  = document.getElementById("dialog_object_edit_freno").value;
      var v_maletero               = document.getElementById("dialog_object_edit_maletero").value;
      var v_p_conductor            = document.getElementById("dialog_object_edit_p_conductor").value;
      var v_p_copiloto             = document.getElementById("dialog_object_edit_p_copiloto").value;
      
      var v_rpm                    = document.getElementById("dialog_object_edit_rpm").value;
      var v_nivel_combustible      = document.getElementById("dialog_object_edit_nivel_combustible").value;
      var v_f_mano                 = document.getElementById("dialog_object_edit_f_mano").value;
      // var v_ch_mag                 = document.getElementById("dialog_object_edit_ch_magnetica").value;
      
      // SensoresTemp_diesel //
      var sn_1  = document.getElementById("dialog_object_edit_sensor_number_1").value || 'false';
      var s_1  = document.getElementById("dialog_object_edit_sensor_1").value || 'false';
      var sl_1 = document.getElementById("dialog_object_edit_largo_1").value || 'false';
      var sa_1 = document.getElementById("dialog_object_edit_alto_1").value  || 'false';
          
      var sn_2  = document.getElementById("dialog_object_edit_sensor_number_2").value || 'false';
      var s_2  = document.getElementById("dialog_object_edit_sensor_2").value || 'false';
      var sl_2 = document.getElementById("dialog_object_edit_largo_2").value || 'false';
      var sa_2 = document.getElementById("dialog_object_edit_alto_2").value  || 'false';
          
      var sn_3  = document.getElementById("dialog_object_edit_sensor_number_3").value || 'false';
      var s_3  = document.getElementById("dialog_object_edit_sensor_3").value || 'false';
      var sl_3 = document.getElementById("dialog_object_edit_largo_3").value || 'false';
      var sa_3 = document.getElementById("dialog_object_edit_alto_3").value  || 'false';
          

      user_ids = JSON.stringify(user_ids);

      if (name == "") {
        notifyDialog(la["NAME_CANT_BE_EMPTY"]);
        return;
      }

      if (renta == "" || renta == 0) {
        notifyDialog(la["RENT_CANT_BE_EMPTY"]);
        return;
      }

      if (cuenta_padre == "") {
        notifyDialog(la["SIM_NUMBER_ACOUNT_CANT_BE_EMPTY"]);
        return;
      }

      if (sim_number_company == "") {
        notifyDialog(la["SIM_NUMBER_COMPANY_CANT_BE_EMPTY"]);
        return;
      }

      if (plan == "") {
        notifyDialog(la["PLAN_CANT_BE_EMPTY"]);
        return;
      }

      if ("Er-100(2G)" == device && "Batería ER-100" == mtto) {
        notifyDialog(la["GPS_DEVICE_NO_COMPATIBLE"]);
        return;
      }

      if (acc == "" && imei != cpValues["edit_object_imei"]) {
        notifyDialog(la["ACC_CANT_BE_EMPTY"]);
        return;
      }

      if (
        acc == "Sensor 1,Sensor 2,Sensor 3" &&
        (!no_sensor1 || !no_sensor2 || !no_sensor3)
      ) {
        notifyDialog(la["ACC_SENSOR_CANT_BE_EMPTY"]);
        return;
      }
      if (acc == "Sensor 1" && !no_sensor1) {
        notifyDialog(la["ACC_SENSOR_CANT_BE_EMPTY"]);
        return;
      }
      if (acc == "Sensor 2" && !no_sensor2) {
        notifyDialog(la["ACC_SENSOR_CANT_BE_EMPTY"]);
        return;
      }
      if (acc == "Sensor 3" && !no_sensor3) {
        notifyDialog(la["ACC_SENSOR_CANT_BE_EMPTY"]);
        return;
      }

      if (!isIMEIValid(imei)) {
        notifyDialog(la["IMEI_IS_NOT_VALID"]);
        return;
      }

      // expire object
      if (object_expire == true) {
        if (object_expire_dt == "") {
          notifyDialog(la["DATE_CANT_BE_EMPTY"]);
          break;
        }
      } else {
        object_expire_dt = "";
      }

      if (imei != cpValues["edit_object_imei"]) {
        cpValues["edit_object_new_imei"] = imei;
        cpValues["edit_object_new_sim_number"] = sim_number;
        cpValues["edit_object_new_mtto"] = mtto;
        cpValues["edit_object_new_no_sensor2"] = no_sensor2;
        cpValues["edit_object_new_no_sensor1"] = no_sensor1;
        cpValues["edit_object_new_no_sensor3"] = no_sensor3;
        cpValues["edit_object_new_sim_number_acount"] = cuenta_padre;
        confirmDialog(
          la["ARE_YOU_SURE_YOU_WANT_TO_CHANGE_OBJECT"],
          function (response) {
            if (response) {
              confirmDialog(
                la["IS_THIS_REPLACEMENT_UNDER_WARRANTY"],
                function (response) {
                  if (response) {
                    cpValues["edit_object_mtto"] =
                      "Remplazo de Equipo Garantia";
                    responseSave();
                  } else {
                    confirmDialog(
                      la["IS_THIS_REPLACEMENT_WITH_CLIENT"],
                      function (response) {
                        if (response) {
                          cpValues["edit_object_mtto"] =
                            "Remplazo de Equipo Cliente";
                        } else {
                          cpValues["edit_object_mtto"] = "Remplazo de Equipo";
                        }
                        responseSave();
                      }
                    );
                  }
                }
              );
            }
          }
        );
      } else if (sim_number != cpValues["edit_object_sim_number"]) {
        cpValues["edit_object_new_sim_number"] = sim_number;
        cpValues["edit_object_new_mtto"] = mtto;
        cpValues["edit_object_new_no_sensor2"] = no_sensor2;
        cpValues["edit_object_new_no_sensor1"] = no_sensor1;
        cpValues["edit_object_new_no_sensor3"] = no_sensor3;
        cpValues["edit_object_new_sim_number_acount"] = cuenta_padre;

        confirmDialog(
          la["ARE_YOU_SURE_YOU_WANT_TO_CHANGE_OBJECT_SIM_NUMBER"],
          function (response) {
            if (response) {
              responseSave();
            }
          }
        );
      } else if (mtto != cpValues["edit_object_mtto"]) {
        var Values = cpValues["edit_object_mtto"];

        if (Values) {
            var valuesArray = Values.split(", ");
            var ultimoValor = valuesArray[valuesArray.length - 1];
            console.log("Último valor:", ultimoValor);
        }

        if (ultimoValor !== mtto) {
          cpValues["edit_object_new_mtto"] = mtto;
          cpValues["edit_object_new_no_sensor2"] = no_sensor2;
          cpValues["edit_object_new_no_sensor1"] = no_sensor1;
          cpValues["edit_object_new_no_sensor3"] = no_sensor3;
          cpValues["edit_object_new_sim_number_acount"] = cuenta_padre;

          confirmDialog(
            la["ARE_YOU_SURE_YOU_WANT_TO_CHANGE_OBJECT_MTTO"],
            function (response) {
              if (response) {
                responseSave();
              }
            }
          );
        }
      } else if (cuenta_padre != cpValues["edit_object_sim_number_acount"]) {
        cpValues["edit_object_new_sim_number_acount"] = cuenta_padre;
        cpValues["edit_object_new_mtto"] = mtto;
        cpValues["edit_object_new_no_sensor2"] = no_sensor2;
        cpValues["edit_object_new_no_sensor1"] = no_sensor1;
        cpValues["edit_object_new_no_sensor3"] = no_sensor3;

        confirmDialog(
          la["ARE_YOU_SURE_YOU_WANT_TO_CHANGE_OBJECT_SIM_NUMBER_ACOUNT"],
          function (response) {
            if (response) {
              responseSave();
            }
          }
        );
      } else if (
        no_sensor1 &&
        no_sensor2 != cpValues["edit_object_no_sensor2"]
      ) {
        cpValues["edit_object_new_no_sensor2"] = no_sensor2;
        cpValues["edit_object_new_mtto"] = mtto;
        cpValues["edit_object_new_no_sensor1"] = no_sensor1;
        cpValues["edit_object_new_no_sensor3"] = no_sensor3;
        cpValues["edit_object_new_sim_number_acount"] = cuenta_padre;

        confirmDialog(
          la["ARE_YOU_SURE_YOU_WANT_TO_CHANGE_OBJECT_NO_SENSORS"],
          function (response) {
            if (response) {
              responseSave();
            }
          }
        );
      } else if (no_sensor1 != cpValues["edit_object_no_sensor1"]) {
        cpValues["edit_object_new_no_sensor1"] = no_sensor1;
        cpValues["edit_object_new_mtto"] = mtto;
        cpValues["edit_object_new_sim_number_acount"] = cuenta_padre;

        confirmDialog(
          la["ARE_YOU_SURE_YOU_WANT_TO_CHANGE_OBJECT_NO_SENSOR1"],
          function (response) {
            if (response) {
              responseSave();
            }
          }
        );
      } else if (no_sensor2 != cpValues["edit_object_no_sensor2"]) {
        cpValues["edit_object_new_no_sensor2"] = no_sensor2;
        cpValues["edit_object_new_no_sensor3"] = no_sensor3;
        cpValues["edit_object_new_mtto"] = mtto;
        cpValues["edit_object_new_sim_number_acount"] = cuenta_padre;

        confirmDialog(
          la["ARE_YOU_SURE_YOU_WANT_TO_CHANGE_OBJECT_NO_SENSOR2"],
          function (response) {
            if (response) {
              responseSave();
            }
          }
        );
      } else if (no_sensor3 != cpValues["edit_object_no_sensor3"]) {
        cpValues["edit_object_new_no_sensor1"] = no_sensor1;
        cpValues["edit_object_new_no_sensor2"] = no_sensor2;
        cpValues["edit_object_new_no_sensor3"] = no_sensor3;
        cpValues["edit_object_new_mtto"] = mtto;
        cpValues["edit_object_new_sim_number_acount"] = cuenta_padre;

        confirmDialog(
          la["ARE_YOU_SURE_YOU_WANT_TO_CHANGE_OBJECT_NO_SENSOR3"],
          function (response) {
            if (response) {
              responseSave();
            }
          }
        );
      } else {
        cpValues["edit_object_new_sim_number_acount"] = cuenta_padre;
        responseSave();
      }

      break;
    case "cancel":
      $("#dialog_object_edit").dialog("close");
      break;
  }

  function responseSave() {
    var data = {
      cmd: "edit_object",
      active: active,
      object_expire: object_expire,
      object_expire_dt: object_expire_dt,
      name: name,
      imei: cpValues["edit_object_imei"],
      new_imei: cpValues["edit_object_new_imei"],
      model: model,
      plan: plan,
      device: device,
      sim_number: cpValues["edit_object_sim_number"],
      sim_number_company: sim_number_company,
      new_sim_number: cpValues["edit_object_new_sim_number"],
      cuenta_padre: cpValues["edit_object_sim_number_acount"],
      sensor_trademark: sensor_trademark,
      new_cuenta_padre: cpValues["edit_object_new_sim_number_acount"],
      no_sensor1: cpValues["edit_object_no_sensor1"],
      new_no_sensor1: cpValues["edit_object_new_no_sensor1"],
      no_sensor2: cpValues["edit_object_no_sensor2"],
      new_no_sensor2: cpValues["edit_object_new_no_sensor2"],
      no_sensor3: cpValues["edit_object_no_sensor3"],
      new_no_sensor3: cpValues["edit_object_new_no_sensor3"],
      manager_id: manager_id,
      acc: acc,
      mtto: cpValues["edit_object_mtto"],
      new_mtto: cpValues["edit_object_new_mtto"],
      user_ids: user_ids,
      fecha_alta: fecha_alta,
      renta: renta,
      observaciones: observaciones,
      seller: seller,

      // Datos del vehículo
      v_year: v_year,
      v_brand: v_brand,
      v_model: v_model,
      v_color: v_color,
      v_vin: v_vin,
      v_plate: v_plate,
      v_odo: v_odo,
      v_ins: v_ins,
      v_insx: v_insx,
      v_fuel: v_fuel,

      // Datos de sensores / señales del vehículo
      alimentacion_principal: v_alimentacion_principal,
      bateria: v_bateria,
      ignicion: v_ignicion,
      bloqueo: v_bloqueo,
      // panico: v_panico,
      velocidad: v_vel,
      t_motor: v_t_motor,
      consumo: v_consumo,
      c_seguridad: v_c_seguridad,
      l_frontales: v_l_frontales,
      l_estacionamiento: v_l_estacionamiento,
      clutch: v_clutch,
      freno: v_freno,
      maletero: v_maletero,
      p_conductor: v_p_conductor,
      p_copiloto: v_p_copiloto,
      rpm: v_rpm,
      nivel_combustible: v_nivel_combustible,
      f_mano: v_f_mano,
      // ch_mag: v_ch_mag,

      //Sensores Temp_Diesel
      sn_1: sn_1,
      s_1: s_1,
      sl_1: sl_1,
      sa_1: sa_1,
      sn_2: sn_2,
      s_2: s_2,
      sl_2: sl_2,
      sa_2: sa_2,
      sn_3: sn_3,
      s_3: s_3,
      sl_3: sl_3,
      sa_3: sa_3
    };

    $.ajax({
      type: "POST",
      url: "func/fn_cpanel.objects.php",
      data: data,
      success: function (result) {
        if (result == "OK") {
          initSelectList("manager_list");

          $("#dialog_object_edit").dialog("close");
        } else if (result == "ERROR_EXPIRATION_DATE_NOT_SET") {
          notifyDialog(la["OBJECT_EXPIRATION_DATE_MUST_BE_SET"]);
        } else if (result == "ERROR_EXPIRATION_DATE_TOO_LATE") {
          notifyDialog(la["OBJECT_EXPIRATION_DATE_IS_TOO_LATE"]);
        } else if (result == "ERROR_IMEI_EXISTS") {
          notifyDialog(la["THIS_IMEI_ALREADY_EXISTS"]);
        } else if (result == "ERROR_SIM_NUMBER_EXISTS") {
          notifyDialog(la["THIS_SIM_NUMBER_ALREADY_EXISTS"]);
        } else if (result == "ERROR_SENSOR1_NUMBER_EXISTS") {
          notifyDialog(la["THIS_SENSOR1_NUMBER_ALREADY_EXISTS"]);
        } else if (result == "ERROR_SENSOR2_NUMBER_EXISTS") {
          notifyDialog(la["THIS_SENSOR2_NUMBER_ALREADY_EXISTS"]);
        } else if (result == "ERROR_SENSOR3_NUMBER_EXISTS") {
          notifyDialog(la["THIS_SENSOR3_NUMBER_ALREADY_EXISTS"]);
        } else if (result == "FALSE") {
          notifyDialog(la["THIS_ACCOUNT_HAS_NO_PRIVILEGES_TO_DO_THAT"]);
          $("#dialog_object_edit").dialog("close");
        }
      },
    });
  }
}

function objectEditCheck() {
  var object_expire = document.getElementById(
    "dialog_object_edit_object_expire"
  ).checked;
  if (object_expire == true) {
    document.getElementById(
      "dialog_object_edit_object_expire_dt"
    ).disabled = false;
  } else {
    document.getElementById(
      "dialog_object_edit_object_expire_dt"
    ).disabled = true;
  }
}

function objectEditDate() {
  var selectedStatus = document.getElementById(
    "dialog_attended_details_estatus_list"
  ).value;

document.getElementById("dialog_object_edit_fecha_alta_crm").disabled =
  !(selectedStatus === "Postfechar" || selectedStatus === "Agendar");

}

function objectClearHistory(imei) {
  confirmDialog(
    la["ARE_YOU_SURE_YOU_WANT_TO_CLEAR_HISTORY_EVENTS"],
    function (response) {
      if (response) {
        var data = {
          cmd: "clear_history_object",
          imei: imei,
        };

        $.ajax({
          type: "POST",
          url: "func/fn_cpanel.objects.php",
          data: data,
          success: function (result) {
            if (result == "OK") {
              $("#cpanel_object_list_grid").trigger("reloadGrid");
            } else {
              notifyDialog(la["THIS_ACCOUNT_HAS_NO_PRIVILEGES_TO_DO_THAT"]);
              $("#cpanel_user_list_grid").trigger("reloadGrid");
              $("#cpanel_object_list_grid").trigger("reloadGrid");
              return;
            }
          },
        });
      }
    }
  );
}

function objectDelete(imei) {
  confirmDialog(
    la["ARE_YOU_SURE_YOU_WANT_TO_DELETE_THIS_OBJECT_FROM_SYSTEM"],
    function (response) {
      if (response) {
        var data = {
          cmd: "delete_object",
          imei: imei,
        };

        $.ajax({
          type: "POST",
          url: "func/fn_cpanel.objects.php",
          data: data,
          success: function (result) {
            if (result == "OK") {
              initStats();
              initSelectList("manager_list");

              $("#cpanel_user_list_grid").trigger("reloadGrid");
              $("#cpanel_object_list_grid").trigger("reloadGrid");
            } else {
              notifyDialog(la["THIS_ACCOUNT_HAS_NO_PRIVILEGES_TO_DO_THAT"]);
              $("#cpanel_user_list_grid").trigger("reloadGrid");
              $("#cpanel_object_list_grid").trigger("reloadGrid");
              return;
            }
          },
        });
      }
    }
  );
}

function objectActivate(imei) {
  var data = {
    cmd: "activate_object",
    imei: imei,
  };

  $.ajax({
    type: "POST",
    url: "func/fn_cpanel.objects.php",
    data: data,
    success: function (result) {
      if (result == "OK") {
        $("#cpanel_object_list_grid").trigger("reloadGrid");
        if ($("#dialog_user_edit").dialog("isOpen") == true) {
          $("#dialog_user_edit_object_list_grid").trigger("reloadGrid");
        }
      } else {
        notifyDialog(la["THIS_ACCOUNT_HAS_NO_PRIVILEGES_TO_DO_THAT"]);
        $("#cpanel_user_list_grid").trigger("reloadGrid");
        $("#cpanel_object_list_grid").trigger("reloadGrid");
        return;
      }
    },
  });
}

function objectExceedActivate(imei) {
  var data = {
    cmd: "Activate_object_exceed",
    imei: imei,
  };

  $.ajax({
    type: "POST",
    url: "func/fn_cpanel.objects.php",
    data: data,
    success: function (result) {
      if (result == "OK") {
        $("#cpanel_unused_object_list_grid").trigger("reloadGrid");
      }
    },
  });
}

function objectExceedDesactivate(imei) {
  var data = {
    cmd: "Desactivate_object_exceed",
    imei: imei,
  };

  $.ajax({
    type: "POST",
    url: "func/fn_cpanel.objects.php",
    data: data,
    success: function (result) {
      if (result == "OK") {
        $("#cpanel_unused_object_list_grid").trigger("reloadGrid");
      }
    },
  });
}

function objectDeactivate(imei) {
  var data = {
    cmd: "deactivate_object",
    imei: imei,
  };

  $.ajax({
    type: "POST",
    url: "func/fn_cpanel.objects.php",
    data: data,
    success: function (result) {
      if (result == "OK") {
        $("#cpanel_object_list_grid").trigger("reloadGrid");
        if ($("#dialog_user_edit").dialog("isOpen") == true) {
          $("#dialog_user_edit_object_list_grid").trigger("reloadGrid");
        }
      } else {
        notifyDialog(la["THIS_ACCOUNT_HAS_NO_PRIVILEGES_TO_DO_THAT"]);
        $("#cpanel_user_list_grid").trigger("reloadGrid");
        $("#cpanel_object_list_grid").trigger("reloadGrid");
        return;
      }
    },
  });
}

function objectActivateSelected() {
  var objects = $("#cpanel_object_list_grid").jqGrid(
    "getGridParam",
    "selarrrow"
  );

  if (objects == "") {
    notifyDialog(la["NO_ITEMS_SELECTED"]);
    return;
  }

  confirmDialog(
    la["ARE_YOU_SURE_YOU_WANT_TO_ACTIVATE_SELECTED_ITEMS"],
    function (response) {
      if (response) {
        var data = {
          cmd: "activate_selected_objects",
          imeis: objects,
        };

        $.ajax({
          type: "POST",
          url: "func/fn_cpanel.objects.php",
          data: data,
          success: function (result) {
            if (result == "OK") {
              $("#cpanel_user_list_grid").trigger("reloadGrid");
              $("#cpanel_object_list_grid").trigger("reloadGrid");
            } else {
              notifyDialog(la["THIS_ACCOUNT_HAS_NO_PRIVILEGES_TO_DO_THAT"]);
              $("#cpanel_user_list_grid").trigger("reloadGrid");
              $("#cpanel_object_list_grid").trigger("reloadGrid");
              return;
            }
          },
        });
      }
    }
  );
}

function objectDeactivateSelected() {
  var objects = $("#cpanel_object_list_grid").jqGrid(
    "getGridParam",
    "selarrrow"
  );

  if (objects == "") {
    notifyDialog(la["NO_ITEMS_SELECTED"]);
    return;
  }

  confirmDialog(
    la["ARE_YOU_SURE_YOU_WANT_TO_DEACTIVATE_SELECTED_ITEMS"],
    function (response) {
      if (response) {
        var data = {
          cmd: "deactivate_selected_objects",
          imeis: objects,
        };

        $.ajax({
          type: "POST",
          url: "func/fn_cpanel.objects.php",
          data: data,
          success: function (result) {
            if (result == "OK") {
              $("#cpanel_user_list_grid").trigger("reloadGrid");
              $("#cpanel_object_list_grid").trigger("reloadGrid");
            } else {
              notifyDialog(la["THIS_ACCOUNT_HAS_NO_PRIVILEGES_TO_DO_THAT"]);
              $("#cpanel_user_list_grid").trigger("reloadGrid");
              $("#cpanel_object_list_grid").trigger("reloadGrid");
              return;
            }
          },
        });
      }
    }
  );
}

function objectClearHistorySelected() {
  var objects = $("#cpanel_object_list_grid").jqGrid(
    "getGridParam",
    "selarrrow"
  );

  if (objects == "") {
    notifyDialog(la["NO_ITEMS_SELECTED"]);
    return;
  }

  confirmDialog(
    la["ARE_YOU_SURE_YOU_WANT_TO_CLEAR_SELECTED_ITEMS_HISTORY_EVENTS"],
    function (response) {
      if (response) {
        var data = {
          cmd: "clear_history_selected_objects",
          user_id: cpValues["user_edit_id"],
          imeis: objects,
        };

        $.ajax({
          type: "POST",
          url: "func/fn_cpanel.objects.php",
          data: data,
          success: function (result) {
            if (result == "OK") {
              initStats();
              initSelectList("manager_list");

              $("#cpanel_object_list_grid").trigger("reloadGrid");
            } else {
              notifyDialog(la["THIS_ACCOUNT_HAS_NO_PRIVILEGES_TO_DO_THAT"]);
              $("#cpanel_user_list_grid").trigger("reloadGrid");
              $("#cpanel_object_list_grid").trigger("reloadGrid");
              return;
            }
          },
        });
      }
    }
  );
}

function objectDeleteSelected() {
  var objects = $("#cpanel_object_list_grid").jqGrid(
    "getGridParam",
    "selarrrow"
  );

  if (objects == "") {
    notifyDialog(la["NO_ITEMS_SELECTED"]);
    return;
  }

  confirmDialog(
    la["ARE_YOU_SURE_YOU_WANT_TO_DELETE_SELECTED_ITEMS"],
    function (response) {
      if (response) {
        var data = {
          cmd: "delete_selected_objects",
          imeis: objects,
        };

        $.ajax({
          type: "POST",
          url: "func/fn_cpanel.objects.php",
          data: data,
          success: function (result) {
            if (result == "OK") {
              initStats();
              initSelectList("manager_list");

              $("#cpanel_user_list_grid").trigger("reloadGrid");
              $("#cpanel_object_list_grid").trigger("reloadGrid");
            } else {
              notifyDialog(la["THIS_ACCOUNT_HAS_NO_PRIVILEGES_TO_DO_THAT"]);
              $("#cpanel_user_list_grid").trigger("reloadGrid");
              $("#cpanel_object_list_grid").trigger("reloadGrid");
              return;
            }
          },
        });
      }
    }
  );
}

function unusedObjectDelete(imei) {
  confirmDialog(
    la["ARE_YOU_SURE_YOU_WANT_TO_DELETE_THIS_UNUSED_OBJECT"],
    function (response) {
      if (response) {
        var data = {
          cmd: "delete_unused_object",
          imei: imei,
        };

        $.ajax({
          type: "POST",
          url: "func/fn_cpanel.objects.php",
          data: data,
          success: function (result) {
            if (result == "OK") {
              initStats();
              $("#cpanel_unused_object_list_grid").trigger("reloadGrid");
            }
          },
        });
      }
    }
  );
}

function unusedObjectDeleteSelected() {
  var objects = $("#cpanel_unused_object_list_grid").jqGrid(
    "getGridParam",
    "selarrrow"
  );

  if (objects == "") {
    notifyDialog(la["NO_ITEMS_SELECTED"]);
    return;
  }

  confirmDialog(
    la["ARE_YOU_SURE_YOU_WANT_TO_DELETE_SELECTED_ITEMS"],
    function (response) {
      if (response) {
        var data = {
          cmd: "delete_selected_unused_objects",
          imeis: objects,
        };

        $.ajax({
          type: "POST",
          url: "func/fn_cpanel.objects.php",
          data: data,
          success: function (result) {
            if (result == "OK") {
              initStats();
              $("#cpanel_unused_object_list_grid").trigger("reloadGrid");
            }
          },
        });
      }
    }
  );
}

function objectImport() {
  // a bit dirty sollution, maybe will make better in the feature :)
  document
    .getElementById("load_file")
    .addEventListener("change", objectImportCSVFile, false);
  document.getElementById("load_file").click();
}

function exportar_user_csv() {
  var data = {
    cmd: "user_export_csv",
  };
  $.ajax({
    type: "POST",
    url: "custom/csv.php",
    data: data,
    cache: false,
    success: function (result) {
      var workbook = XLS.read(result, { type: "binary" });
      var wbout = XLS.write(workbook, { bookType: "xls", type: "binary" });
      var blobObject = new Blob([s2ab(wbout)], {
        type: "application/octet-stream",
      });
      var downloadLink = document.createElement("a");
      var url = URL.createObjectURL(blobObject);
      downloadLink.href = url;
      downloadLink.download = "usuarios.xls";
      document.body.appendChild(downloadLink);
      downloadLink.click();
      document.body.removeChild(downloadLink);
      loadingData(false);
    },
    error: function (statusCode, errorThrown) {
      loadingData(false);
    },
  });
}
function s2ab(s) {
  var buf = new ArrayBuffer(s.length);
  var view = new Uint8Array(buf);
  for (var i = 0; i != s.length; ++i) view[i] = s.charCodeAt(i) & 0xff;
  return buf;
}

function exportar_csv() {
  var data = {
    cmd: "export_csv",
  };
  $.ajax({
    type: "POST",
    url: "custom/csv.php",
    data: data,
    cache: false,
    success: function (result) {
      /*
       * Make CSV downloadable
       */
      var downloadLink = document.createElement("a");
      var fileData = [result];

      var blobObject = new Blob(fileData, {
        type: "text/csv;charset=utf-8;",
      });

      var url = URL.createObjectURL(blobObject);
      downloadLink.href = url;
      downloadLink.download = "dispositivos.csv";

      /*
       * Actually download CSV
       */
      document.body.appendChild(downloadLink);
      downloadLink.click();
      document.body.removeChild(downloadLink);

      loadingData(false);
    },
    error: function (statusCode, errorThrown) {
      loadingData(false);
    },
  });
}

function exportar_users_csv() {
  var data = {
    cmd: "export_users_csv",
  };
  $.ajax({
    type: "POST",
    url: "custom/csv.php",
    data: data,
    cache: false,
    success: function (result) {
      /*
       * Make CSV downloadable
       */
      var downloadLink = document.createElement("a");
      var fileData = [result];

      var blobObject = new Blob(fileData, {
        type: "text/csv;charset=utf-8;",
      });

      var url = URL.createObjectURL(blobObject);
      downloadLink.href = url;
      downloadLink.download = "usuarios.csv";

      /*
       * Actually download CSV
       */
      document.body.appendChild(downloadLink);
      downloadLink.click();
      document.body.removeChild(downloadLink);

      loadingData(false);
    },
    error: function (statusCode, errorThrown) {
      loadingData(false);
    },
  });
}

function exportar_fac_csv() {
  var data = {
    cmd: "export_fac_csv",
  };
  $.ajax({
    type: "POST",
    url: "custom/csv.php",
    data: data,
    cache: false,
    success: function (result) {
      /*
       * Make CSV downloadable
       */
      var downloadLink = document.createElement("a");
      var fileData = [result];

      var blobObject = new Blob(fileData, {
        type: "text/csv;charset=utf-8;",
      });

      var url = URL.createObjectURL(blobObject);
      downloadLink.href = url;
      downloadLink.download = "dispositivos_fac.csv";

      /*
       * Actually download CSV
       */
      document.body.appendChild(downloadLink);
      downloadLink.click();
      document.body.removeChild(downloadLink);

      loadingData(false);
    },
    error: function (statusCode, errorThrown) {
      loadingData(false);
    },
  });
}

function exportarcsv() {
  var data = {
    cmd: "export_excedd_csv",
  };
  $.ajax({
    type: "POST",
    url: "custom/csv.php",
    data: data,
    cache: false,
    success: function (result) {
      /*
       * Make CSV downloadable
       */
      var downloadLink = document.createElement("a");
      var fileData = [result];

      var blobObject = new Blob(fileData, {
        type: "text/csv;charset=utf-8;",
      });

      var url = URL.createObjectURL(blobObject);
      downloadLink.href = url;
      downloadLink.download = "dispositivos_fac.csv";

      /*
       * Actually download CSV
       */
      document.body.appendChild(downloadLink);
      downloadLink.click();
      document.body.removeChild(downloadLink);

      loadingData(false);
    },
    error: function (statusCode, errorThrown) {
      loadingData(false);
    },
  });
}

function objectImportCSVFile(evt) {
  var files = evt.target.files;
  var reader = new FileReader();
  reader.onload = function (event) {
    try {
      if (files[0].name.split(".").pop().toLowerCase() == "csv") {
        var data_json = csv2json(event.target.result);

        console.log(data_json);

        for (i = 0; i < data_json.length; i += 1) {
          if (
            data_json[i].name != undefined &&
            data_json[i].imei != undefined
          ) {
            if (data_json[i].name == "" || data_json[i].imei == "") {
              notifyDialog(la["INVALID_FILE_FORMAT"]);
              return;
            }
          } else {
            notifyDialog(la["INVALID_FILE_FORMAT"]);
            return;
          }
        }

        var objects = JSON.stringify(data_json);
        var objects_count = data_json.length;

        if (objects_count == 0) {
          notifyDialog(la["NOTHING_HAS_BEEN_FOUND_TO_IMPORT"]);
          return;
        }

        var text =
          sprintf(la["OBJECTS_FOUND"], objects_count) +
          " " +
          la["ARE_YOU_SURE_YOU_WANT_TO_IMPORT"];

        confirmDialog(text, function (response) {
          if (response) {
            loadingData(true);

            var data = {
              format: "object_csv",
              data: objects,
            };

            $.ajax({
              type: "POST",
              url: "func/fn_cpanel.import.php",
              data: data,
              cache: false,
              success: function (result) {
                loadingData(false);

                if (result == "OK") {
                  initStats();
                  $("#cpanel_object_list_grid").trigger("reloadGrid");
                } else if (result == "ERROR_SYSTEM_OBJECT_LIMIT") {
                  notifyDialog(la["SYSTEM_OBJECT_LIMIT_IS_REACHED"]);
                }
              },
              error: function (statusCode, errorThrown) {
                loadingData(false);
              },
            });
          }
        });
      } else {
        notifyDialog(la["INVALID_FILE_FORMAT"]);
      }
    } catch (ex) {
      notifyDialog(la["INVALID_FILE_FORMAT"]);
    }

    document.getElementById("load_file").value = "";
  };
  reader.readAsText(files[0], "UTF-8");

  this.removeEventListener("change", objectImportCSVFile, false);
}

function settingsUsersDocumentsMiContrato() {
  document
    .getElementById("load_file")
    .addEventListener("change", settingsUsersDocumentsMiContratoUploadFile, !1),
    document.getElementById("load_file").click();
}

function settingsUsersDocumentsMiContratoUploadFile(e) {
  var t = e.target.files,
    a = new FileReader();
  (a.onloadend = function (e) {
    var a = e.target.result;
    if (t[0].type.match("application/pdf")) {
      user_id = cpValues["user_edit_id"] ? cpValues["user_edit_id"] : 0;
      $.ajax({
        url: "func/fn_upload.php?file=MiContrato&user_id=" + user_id,
        type: "POST",
        data: a,
        processData: !1,
        contentType: !1,
        success: function (e) {
          document.getElementById("UsersDocumentsMiContrato").value = e;
          document.getElementById(
            "dialog_EditDownloadUsersDocumentsMiContrato"
          ).style.backgroundColor = "#68A062";
          $("#dialog_EditDownloadUsersDocumentsMiContrato").prop(
            "disabled",
            false
          );
        },
      });

      document.getElementById("load_file").value = "";
    } else notifyBox("error", la.ERROR, la.FILE_TYPE_MUST_BE_PDF);
  }),
    a.readAsDataURL(t[0]),
    this.removeEventListener(
      "change",
      settingsUsersDocumentsMiContratoUploadFile,
      !1
    );
}

function settingsUsersDocumentsActaConstitutiva() {
  document
    .getElementById("load_file")
    .addEventListener(
      "change",
      settingsUsersDocumentsActaConstitutivaUploadFile,
      !1
    ),
    document.getElementById("load_file").click();
}

function settingsUsersDocumentsActaConstitutivaUploadFile(e) {
  var t = e.target.files,
    a = new FileReader();
  (a.onloadend = function (e) {
    var a = e.target.result;
    if (t[0].type.match("application/pdf")) {
      user_id = cpValues["user_edit_id"] ? cpValues["user_edit_id"] : 0;
      $.ajax({
        url: "func/fn_upload.php?file=ActaConstitutiva&user_id=" + user_id,
        type: "POST",
        data: a,
        processData: !1,
        contentType: !1,
        success: function (e) {
          document.getElementById("UsersDocumentsActaConstitutiva").value = e;
          document.getElementById(
            "dialog_EditDownloadUsersDocumentsActaConstitutiva"
          ).style.backgroundColor = "#68A062";
          $("#dialog_EditDownloadUsersDocumentsActaConstitutiva").prop(
            "disabled",
            false
          );
        },
      });

      document.getElementById("load_file").value = "";
    } else notifyBox("error", la.ERROR, la.FILE_TYPE_MUST_BE_PDF);
  }),
    a.readAsDataURL(t[0]),
    this.removeEventListener(
      "change",
      settingsUsersDocumentsActaConstitutivaUploadFile,
      !1
    );
}

function settingsUsersDocumentsRepLegal() {
  document
    .getElementById("load_file")
    .addEventListener("change", settingsUsersDocumentsRepLegalUploadFile, !1),
    document.getElementById("load_file").click();
}

function settingsUsersDocumentsRepLegalUploadFile(e) {
  var t = e.target.files,
    a = new FileReader();
  (a.onloadend = function (e) {
    var a = e.target.result;
    if (t[0].type.match("application/pdf")) {
      user_id = cpValues["user_edit_id"] ? cpValues["user_edit_id"] : 0;
      $.ajax({
        url: "func/fn_upload.php?file=RepLegal&user_id=" + user_id,
        type: "POST",
        data: a,
        processData: !1,
        contentType: !1,
        success: function (e) {
          document.getElementById("UsersDocumentsRepLegal").value = e;
          document.getElementById(
            "dialog_EditDownloadUsersDocumentsRepLegal"
          ).style.backgroundColor = "#68A062";
          $("#dialog_EditDownloadUsersDocumentsRepLegal").prop(
            "disabled",
            false
          );
        },
      });

      document.getElementById("load_file").value = "";
    } else notifyBox("error", la.ERROR, la.FILE_TYPE_MUST_BE_PDF);
  }),
    a.readAsDataURL(t[0]),
    this.removeEventListener(
      "change",
      settingsUsersDocumentsRepLegalUploadFile,
      !1
    );
}

function settingsUsersDocumentsIdentificacionOficial() {
  document
    .getElementById("load_file")
    .addEventListener(
      "change",
      settingsUsersDocumentsIdentificacionOficialUploadFile,
      !1
    ),
    document.getElementById("load_file").click();
}

function settingsUsersDocumentsIdentificacionOficialUploadFile(e) {
  var t = e.target.files,
    a = new FileReader();
  (a.onloadend = function (e) {
    var a = e.target.result;
    if (t[0].type.match("application/pdf")) {
      user_id = cpValues["user_edit_id"] ? cpValues["user_edit_id"] : 0;
      $.ajax({
        url: "func/fn_upload.php?file=IdentificacionOficial&user_id=" + user_id,
        type: "POST",
        data: a,
        processData: !1,
        contentType: !1,
        success: function (e) {
          document.getElementById("UsersDocumentsIdentificacionOficial").value =
            e;
          document.getElementById(
            "dialog_EditDownloadUsersDocumentsIdentificacionOficial"
          ).style.backgroundColor = "#68A062";
          $("#dialog_EditDownloadUsersDocumentsIdentificacionOficial").prop(
            "disabled",
            false
          );
        },
      });

      document.getElementById("load_file").value = "";
    } else notifyBox("error", la.ERROR, la.FILE_TYPE_MUST_BE_PDF);
  }),
    a.readAsDataURL(t[0]),
    this.removeEventListener(
      "change",
      settingsUsersDocumentsIdentificacionOficialUploadFile,
      !1
    );
}

function settingsUsersDocumentsOpinionPositiva() {
  document
    .getElementById("load_file")
    .addEventListener(
      "change",
      settingsUsersDocumentsOpinionPositivaUploadFile,
      !1
    ),
    document.getElementById("load_file").click();
}

function settingsUsersDocumentsOpinionPositivaUploadFile(e) {
  var t = e.target.files,
    a = new FileReader();
  (a.onloadend = function (e) {
    var a = e.target.result;
    if (t[0].type.match("application/pdf")) {
      user_id = cpValues["user_edit_id"] ? cpValues["user_edit_id"] : 0;
      $.ajax({
        url: "func/fn_upload.php?file=OpinionPositiva&user_id=" + user_id,
        type: "POST",
        data: a,
        processData: !1,
        contentType: !1,
        success: function (e) {
          document.getElementById("UsersDocumentsOpinionPositiva").value = e;
          document.getElementById(
            "dialog_EditDownloadUsersDocumentsOpinionPositiva"
          ).style.backgroundColor = "#68A062";
          $("#dialog_EditDownloadUsersDocumentsOpinionPositiva").prop(
            "disabled",
            false
          );
        },
      });

      document.getElementById("load_file").value = "";
    } else notifyBox("error", la.ERROR, la.FILE_TYPE_MUST_BE_PDF);
  }),
    a.readAsDataURL(t[0]),
    this.removeEventListener(
      "change",
      settingsUsersDocumentsOpinionPositivaUploadFile,
      !1
    );
}

function settingsUsersDocumentsConstanciaFiscal() {
  document
    .getElementById("load_file")
    .addEventListener(
      "change",
      settingsUsersDocumentsConstanciaFiscalUploadFile,
      !1
    ),
    document.getElementById("load_file").click();
}

function settingsUsersDocumentsConstanciaFiscalUploadFile(e) {
  var t = e.target.files,
    a = new FileReader();
  (a.onloadend = function (e) {
    var a = e.target.result;
    if (t[0].type.match("application/pdf")) {
      user_id = cpValues["user_edit_id"] ? cpValues["user_edit_id"] : 0;
      $.ajax({
        url: "func/fn_upload.php?file=ConstanciaFiscal&user_id=" + user_id,
        type: "POST",
        data: a,
        processData: !1,
        contentType: !1,
        success: function (e) {
          document.getElementById("UsersDocumentsConstanciaFiscal").value = e;
          document.getElementById(
            "dialog_EditDownloadUsersDocumentsConstanciaFiscal"
          ).style.backgroundColor = "#68A062";
          $("#dialog_EditDownloadUsersDocumentsConstanciaFiscal").prop(
            "disabled",
            false
          );
        },
      });

      document.getElementById("load_file").value = "";
    } else notifyBox("error", la.ERROR, la.FILE_TYPE_MUST_BE_PDF);
  }),
    a.readAsDataURL(t[0]),
    this.removeEventListener(
      "change",
      settingsUsersDocumentsConstanciaFiscalUploadFile,
      !1
    );
}

function settingsUsersDocumentsDomicilioFiscal() {
  document
    .getElementById("load_file")
    .addEventListener(
      "change",
      settingsUsersDocumentsDomicilioFiscalUploadFile,
      !1
    ),
    document.getElementById("load_file").click();
}

function settingsUsersDocumentsDomicilioFiscalUploadFile(e) {
  var t = e.target.files,
    a = new FileReader();
  (a.onloadend = function (e) {
    var a = e.target.result;
    if (t[0].type.match("application/pdf")) {
      user_id = cpValues["user_edit_id"] ? cpValues["user_edit_id"] : 0;
      $.ajax({
        url: "func/fn_upload.php?file=DomicilioFiscal&user_id=" + user_id,
        type: "POST",
        data: a,
        processData: !1,
        contentType: !1,
        success: function (e) {
          document.getElementById("UsersDocumentsDomicilioFiscal").value = e;
          document.getElementById(
            "dialog_EditDownloadUsersDocumentsDomicilioFiscal"
          ).style.backgroundColor = "#68A062";
          $("#dialog_EditDownloadUsersDocumentsDomicilioFiscal").prop(
            "disabled",
            false
          );
        },
      });

      document.getElementById("load_file").value = "";
    } else notifyBox("error", la.ERROR, la.FILE_TYPE_MUST_BE_PDF);
  }),
    a.readAsDataURL(t[0]),
    this.removeEventListener(
      "change",
      settingsUsersDocumentsDomicilioFiscalUploadFile,
      !1
    );
}

function settingsUsersDocumentsOtros() {
  document
    .getElementById("load_file")
    .addEventListener("change", settingsUsersDocumentsOtrosUploadFile, !1),
    document.getElementById("load_file").click();
}

function settingsUsersDocumentsOtrosUploadFile(e) {
  var t = e.target.files,
    a = new FileReader();
  (a.onloadend = function (e) {
    var a = e.target.result;
    if (t[0].type.match("application/pdf")) {
      user_id = cpValues["user_edit_id"] ? cpValues["user_edit_id"] : 0;
      $.ajax({
        url: "func/fn_upload.php?file=Otros&user_id=" + user_id,
        type: "POST",
        data: a,
        processData: !1,
        contentType: !1,
        success: function (e) {
          document.getElementById("UsersDocumentsOtros").value = e;
          document.getElementById(
            "dialog_EditDownloadUsersDocumentsOtros"
          ).style.backgroundColor = "#68A062";
          $("#dialog_EditDownloadUsersDocumentsOtros").prop("disabled", false);
        },
      });

      document.getElementById("load_file").value = "";
    } else notifyBox("error", la.ERROR, la.FILE_TYPE_MUST_BE_PDF);
  }),
    a.readAsDataURL(t[0]),
    this.removeEventListener(
      "change",
      settingsUsersDocumentsOtrosUploadFile,
      !1
    );
}

function imeiEdit(cmd) {
  switch (cmd) {
    default:
      var data = {
        cmd: "load_imei_data",
        imei: cmd,
      };

      $.ajax({
        type: "POST",
        url: "func/fn_cpanel.objects.php",
        data: data,
        dataType: "json",
        cache: false,
        success: function (result) {
          // Limpiamos todos los campos del formulario de edición 
          document.getElementById("dialog_imei_edit_imei").value = "";
          document.getElementById("dialog_imei_edit_iccid").value = "";
          document.getElementById("dialog_imei_edit_sim_numero").value = "";
          document.getElementById("dialog_imei_edit_plan").value = "";
          $("#dialog_imei_edit_plan").multipleSelect("refresh");
          document.getElementById("dialog_imei_edit_renta_costo").value = "";
          document.getElementById("dialog_imei_edit_sim_compania").value = "";
          document.getElementById("dialog_imei_edit_sim_proveedor").value = "";
          document.getElementById("dialog_imei_edit_fecha_corte").value = "";
          document.getElementById("dialog_imei_edit_fecha_compra").value = "";
          document.getElementById("dialog_imei_edit_fecha_alta").value = "";

          if (result == "ERROR") {
            notifyDialog(la["THIS_ACCOUNT_HAS_NO_PRIVILEGES_TO_DO_THAT"]);
            return;
          }
          
          //Actualizamos los campos con los datos del ajax
          document.getElementById("dialog_imei_edit_imei").value = result.imei;
          document.getElementById("dialog_imei_edit_iccid").value = result.iccid;
          document.getElementById("dialog_imei_edit_sim_numero").value = result.numero_linea;
          document.getElementById("dialog_imei_edit_plan").value = result.plan;
          $("#dialog_imei_edit_plan").multipleSelect("refresh");
          document.getElementById("dialog_imei_edit_renta_costo").value = result.renta_costo;                   
          document.getElementById("dialog_imei_edit_sim_compania").value = result.sim_number_company;
          document.getElementById("dialog_imei_edit_sim_proveedor").value = result.proveedor;
          document.getElementById("dialog_imei_edit_fecha_corte").value = result.fecha_corte.split(" ")[0];
          document.getElementById("dialog_imei_edit_fecha_compra").value = result.fecha_compra.split(" ")[0];
          document.getElementById("dialog_imei_edit_fecha_alta").value = result.fecha_alta.split(" ")[0];           
        },
      });

      $("#dialog_imei_edit").dialog("open");
      break;

    case "save":
      var data = {
        cmd: "edit_imei",
        imei: document.getElementById("dialog_imei_edit_imei").value,
        iccid: document.getElementById("dialog_imei_edit_iccid").value,
        numero_linea: document.getElementById("dialog_imei_edit_sim_numero").value,
        plan: document.getElementById("dialog_imei_edit_plan").value,
        renta_costo: document.getElementById("dialog_imei_edit_renta_costo").value,
        sim_number_company: document.getElementById("dialog_imei_edit_sim_compania").value,
        proveedor: document.getElementById("dialog_imei_edit_sim_proveedor").value,
        fecha_corte: document.getElementById("dialog_imei_edit_fecha_corte").value,
        fecha_compra: document.getElementById("dialog_imei_edit_fecha_compra").value,
        fecha_alta: document.getElementById("dialog_imei_edit_fecha_alta").value        
      };

      $.ajax({
        type: "POST",
        url: "func/fn_cpanel.objects.php",
        data: data,
        success: function (result) {
          if (result == "OK") {
            $("#dialog_imei_edit").dialog("close");
            $('#cpanel_imei_list_grid').trigger("reloadGrid");
          }
        },
      });
      break;
    case "cancel":
      $("#dialog_imei_edit").dialog("close");
      break;
  }


}

function deviceEdit(cmd) {
  switch (cmd) {
    default:
      var data = {
        cmd: "load_device_data",
        imei: cmd,
      };

      $.ajax({
        type: "POST",
        url: "func/fn_cpanel.objects.php",
        data: data,
        dataType: "json",
        cache: false,
        success: function (result) {
          // Limpiamos todos los campos del formulario de edición de dispositivo
          document.getElementById("dialog_device_edit_imei").value = "";
          document.getElementById("dialog_device_edit_marca").value = "";
          document.getElementById("dialog_device_edit_modelo").value = "";
          document.getElementById("dialog_device_edit_proveedor").value = "";
          document.getElementById("dialog_device_edit_renta_costo").value = "";
          document.getElementById("dialog_device_edit_fecha_compra").value = "";
          document.getElementById("dialog_device_edit_fecha_alta").value = "";


          if (result == "ERROR") {
            notifyDialog(la["THIS_ACCOUNT_HAS_NO_PRIVILEGES_TO_DO_THAT"]);
            return;
          }
          document.getElementById("dialog_device_edit_imei").value = result.imei;
          document.getElementById("dialog_device_edit_marca").value = result.marca;
          document.getElementById("dialog_device_edit_modelo").value = result.modelo;
          document.getElementById("dialog_device_edit_proveedor").value = result.proveedor;
          document.getElementById("dialog_device_edit_renta_costo").value = result.renta_costo;                   
          document.getElementById("dialog_device_edit_fecha_compra").value = result.fecha_compra.split(" ")[0];
          document.getElementById("dialog_device_edit_fecha_alta").value = result.fecha_alta.split(" ")[0];           
        },
      });

      $("#dialog_device_edit").dialog("open");
      break;

    case "save":
      var data = {
        cmd: "edit_device",
        imei: document.getElementById("dialog_device_edit_imei").value,
        marca: document.getElementById("dialog_device_edit_marca").value,
        modelo: document.getElementById("dialog_device_edit_modelo").value,
        proveedor: document.getElementById("dialog_device_edit_proveedor").value,
        renta_costo: document.getElementById("dialog_device_edit_renta_costo").value,
        fecha_compra: document.getElementById("dialog_device_edit_fecha_compra").value,
        fecha_alta: document.getElementById("dialog_device_edit_fecha_alta").value        
      };

      $.ajax({
        type: "POST",
        url: "func/fn_cpanel.objects.php",
        data: data,
        success: function (result) {
          if (result == "OK") {
            $("#dialog_device_edit").dialog("close");
            $('#cpanel_imei_device_list_grid').trigger("reloadGrid");
          }
        },
      });
      break;
    case "cancel":
      $("#dialog_device_edit").dialog("close");
      break;
  }


}