//#################################################
// VARS
//#################################################

// language array/vars
var la = [];

var cpValues = new Array();
cpValues["set_expiration"] = false;
cpValues["edit_object_imei"] = "";
cpValues["edit_object_new_imei"] = "";
cpValues["edit_theme_id"] = false;
cpValues["edit_custom_map_id"] = false;
cpValues["edit_billing_plan_id"] = false;
cpValues["edit_user_billing_plan_id"] = false;

// timers
var timer_loadStats;
var timer_sessionCheck;

//#################################################
// END VARS
//#################################################

function load() {
  loadLanguage(function (response) {
    loadSettings("cpanel", function (response) {
      loadSettings("server", function (response) {
        load2();
      });
    });
  });
}

function load2() {
  initGui();
  initGrids();
  initStats();
  showNotification();

  loadGridList("themes");
  loadGridList("custom_maps");
  loadGridList("billing");
  loadGridList("languages");
  loadGridList("templates");
  loadGridList("logs");

  document.getElementById("loading_panel").style.display = "none";
  document.getElementById("content").style.visibility = "visible";

  notifyCheck("session_check");
}

function switchCPManager(manager_id) {
  cpValues["manager_id"] = manager_id;

  $("#dialog_user_object_add_objects").tokenize().options.datas =
    "func/fn_cpanel.objects.php?cmd=load_object_search_list&manager_id=" +
    cpValues["manager_id"];
  $("#dialog_object_add_users").tokenize().options.datas =
    "func/fn_cpanel.users.php?cmd=load_user_search_list&manager_id=" +
    cpValues["manager_id"];
  $("#dialog_object_edit_users").tokenize().options.datas =
    "func/fn_cpanel.users.php?cmd=load_user_search_list&manager_id=" +
    cpValues["manager_id"];

  $("#cpanel_user_list_grid").setGridParam({
    url:
      "func/fn_cpanel.users.php?cmd=load_user_list&manager_id=" +
      cpValues["manager_id"],
  });
  $("#cpanel_user_list_grid").trigger("reloadGrid");

  $("#cpanel_object_list_grid").setGridParam({
    url:
      "func/fn_cpanel.objects.php?cmd=load_object_list&manager_id=" +
      cpValues["manager_id"],
  });
  $("#cpanel_object_list_grid").trigger("reloadGrid");

  $("#cpanel_billing_plan_list_grid").setGridParam({
    url:
      "func/fn_cpanel.billing.php?cmd=load_billing_plan_list&manager_id=" +
      cpValues["manager_id"],
  });
  $("#cpanel_billing_plan_list_grid").trigger("reloadGrid");

  initStats();
}

function initStats() {
  clearTimeout(timer_loadStats);

  var data = {
    cmd: "stats",
    manager_id: cpValues["manager_id"],
  };

  $.ajax({
    type: "POST",
    url: "func/fn_cpanel.php",
    data: data,
    dataType: "json",
    cache: false,
    error: function (statusCode, errorThrown) {
      // shedule next stats reload
      timer_loadStats = setTimeout("initStats();", 30000);
    },

    success: function (result) {
      document.getElementById("user_list_stats").innerHTML =
        "(" + result.result2["total_users"] + ")";
      document.getElementById("object_list_stats").innerHTML =
        "(" +
        result.result2["total_objects"] +
        "/" +
        result.result2["total_objects_online"] +
        ")";

      if (document.getElementById("unused_object_list_stats") != undefined) {
        document.getElementById("unused_object_list_stats").innerHTML =
          "(" +
          result.result2["total_excedd_objects"] +
          "/" +
          result.result2["total_excedd_rest"] +
          "/" +
          result.result2["total_excedd_objects_follow"] +
          ")";
      }

      if (document.getElementById("billing_plan_list_stats") != undefined) {
        document.getElementById("billing_plan_list_stats").innerHTML =
          "(" + result.result2["total_billing_plans"] + ")";
      }

      document.getElementById(
        "cpanel_manage_server_sms_gateway_total_in_queue"
      ).innerHTML = result.result2["sms_gateway_total_in_queue"];
      timer_loadStats = setTimeout(initStats, 30000);
    },
  });
}
function showNotification() {
  var data = {
    cmd: "mtto",
    manager_id: cpValues["manager_id"],
  };

  $.ajax({
    type: "POST",
    url: "func/fn_cpanel.php",
    data: data,
    dataType: "json",
    cache: false,
    error: function (statusCode, errorThrown) {
      // shedule next stats reload
      timer_loadStats = setTimeout("initStats();", 30000);
    },
    success: function (result) {
      if (result.result1 !== undefined && result.result1.length > 0) {
        let notificationMessage =
          '<div style="text-align: center; font-weight: bold;">Total Objects Maintenance</div><br><br>';

        for (const element of result.result1) {
          let device = element;
          let planInfo = device["plan"];
          let nameInfo = device["unidad"];
          let dias_rest = device["dias_vencidos"];
          let days_diff = device["dias_restantes"];
          let imeiInfo = device["imei"];
          let protocolInfo = device["cliente"];

          notificationMessage +=
            "<div>Unidad: " +
            nameInfo +
            ", Imei: " +
            imeiInfo +
            ", Plan: " +
            planInfo +
            ", Días rest: " +
            dias_rest +
            ", Días diff: " +
            days_diff +
            ", Cliente: " +
            protocolInfo +
            "</div><br>";
        }
        showNotificationPopup(notificationMessage);
      }
    },
  });
}

function showNotificationPopup(message) {
  let notificationPopup = document.getElementById("notificationPopup");
  let notificationText = document.getElementById("notificationText");
  let closeButton = document.createElement("button");

  notificationText.innerHTML = message;

  closeButton.innerHTML = "Cerrar";
  notificationText.appendChild(closeButton);

  let timeoutId;
  notificationPopup.style.display = "block";

  notificationPopup.scrollTop = 0;

  function hideNotification() {
    notificationPopup.style.display = "none";
  }

  function closeNotification() {
    hideNotification();
    clearTimeout(timeoutId);
  }

  closeButton.addEventListener("click", closeNotification);

  timeoutId = setTimeout(hideNotification, 15000);

  notificationPopup.addEventListener("mouseenter", function () {
    clearTimeout(timeoutId);
  });

  notificationPopup.addEventListener("mouseleave", function () {
    timeoutId = setTimeout(hideNotification, 5000);
  });

  document.addEventListener("scroll", function () {
    clearTimeout(timeoutId);
  });
}

function previewFile() {
  var fileInput = document.getElementById("send_email_attachment");
  var filePreview = document.getElementById("file_preview");
  var fileName = document.getElementById("file_name");
  var fileIcon = document.getElementById("file_icon");

  var file = fileInput.files[0];

  if (file) {
    var fileType = file.type.split("/")[0];
    switch (fileType) {
      case "image":
        fileIcon.src = URL.createObjectURL(file);
        fileIcon.style.display = "block";
        break;
      case "application":
        var extension = file.name.split(".").pop().toLowerCase();
        switch (extension) {
          case "xls":
          case "xlsx":
          case "csv":
            fileIcon.src = "../img/Excel.png"; // Icono para Excel
            break;
          case "doc":
          case "docx":
            fileIcon.src = "../img/Word.png"; // Icono para Word
            break;
          case "pdf":
            fileIcon.src = "../img/PDF.png"; // Icono para PDF
            break;
          case "rar":
          case "zip":
            fileIcon.src = "../img/Rar.png"; // Icono para RAR
            break;
          default:
            fileIcon.src = "../img/hs.png"; // Icono para otros tipos de archivo en aplicación
            break;
        }
        fileIcon.style.display = "block";
        break;
      case "text":
        var extension = file.name.split(".").pop().toLowerCase();
        switch (extension) {
          case "html":
            fileIcon.src = "../img/chrome.png"; // Icono para HTML
            break;
          case "txt":
            fileIcon.src = "../img/Txt.png"; // Icono para TXT
            break;
          default:
            fileIcon.src = "../img/hs.png"; // Icono para otros tipos de archivo de texto
            break;
        }
        fileIcon.style.display = "block";
        break;
      default:
        fileIcon.style.display = "none"; // Ocultar el icono para tipos de archivo desconocidos
        break;
    }
    filePreview.style.display = "block";
  } else {
    filePreview.style.display = "none";
  }
}

function sendEmail(cmd, data = {}) {
  switch (cmd) {
    case "open":
      document.getElementById("send_email_send_to").value = "all";
      $("#send_email_send_to").multipleSelect("refresh");
      document.getElementById("send_email_subject").value = "";
      document.getElementById("send_email_message").value = "";
      document.getElementById("send_email_status").innerHTML = "";
      document.getElementById("send_email_attachment").value = "";
      var previewContainer = document.getElementById("file_preview");
      if (previewContainer) {
        previewContainer.style.display = "none";
      }

      sendEmailSendToSwitch();

      $("#dialog_send_email").dialog("open");

      break;
    case "cancel":
      $("#dialog_send_email").dialog("close");
      break;
    case "send":
      var send_to = document.getElementById("send_email_send_to").value;
      var user_ids = $("#send_email_username").tokenize().toArray().join(",");
      var subject = document.getElementById("send_email_subject").value;
      var message = document.getElementById("send_email_message").value;
      var attachment = document.getElementById("send_email_attachment")
        .files[0];

      if (
        (send_to == "selected" && user_ids.length == 0) ||
        subject == "" ||
        message == ""
      ) {
        notifyDialog(la["ALL_AVAILABLE_FIELDS_SHOULD_BE_FILLED_OUT"]);
        break;
      }
      confirmDialog(
        la["ARE_YOU_SURE_YOU_WANT_TO_SEND_THIS_MESSAGE"],
        function (response) {
          if (response) {
            document.getElementById("send_email_status").innerHTML =
              la["SENDING_PLEASE_WAIT"];
            var formData = new FormData();
            formData.append("cmd", "send_email");
            formData.append("manager_id", cpValues["manager_id"]);
            formData.append("send_to", send_to);
            formData.append("user_ids", user_ids);
            formData.append("subject", subject);
            formData.append("message", message);
            formData.append("attachment", attachment);

            $.ajax({
              type: "POST",
              url: "func/fn_cpanel.php",
              data: formData,
              processData: false,
              contentType: false,
              success: function (result) {
                if (result == "OK") {
                  $("#dialog_send_email").dialog("close");
                }
                notifyDialog(la["EMAIL_SEND"]);
              },
            });
          }
        }
      );

      break;

      case "open_crm":

      document.getElementById("send_email_send_to").value = "custom";
      $("#send_email_send_to").multipleSelect("refresh");
      sendEmailSendToSwitch();
    
      $("#send_email_username").tokenize().clear();
      $("#send_email_username").tokenize().options.newElements = true;
    
      const destinatarios = [data.user_email, data.email_client]
        .filter(Boolean)
        .join(', ')
        .split(',')
        .map(s => s.trim());
    
      for (const email of destinatarios) {
        $("#send_email_username").tokenize().tokenAdd(email, email);
      }
    
      document.getElementById("send_email_subject").value = "Seguimiento preventivo de su unidad GPS";
    
      const resumen =
      `Hola,\n\n` +
      `Este es un mensaje generado automáticamente por el sistema OptimusGPS. Por favor, no responda a este correo.\n\n` +
      `Se ha detectado una situación que requiere seguimiento técnico. A continuación se detallan los datos observados:\n\n` +
        `Evento: ${data.event}\n\n` +
        `Estatus: ${data.status_label}\n` +
        `Comentario: ${data.newDetail}\n` +
        `Fecha: ${data.fecha_edit}\n` +
        `Fecha de Servicio: ${data.fecha_servicio}`;
    
      document.getElementById("send_email_message").value = resumen;
    
      document.getElementById("send_email_status").innerHTML = "";
      document.getElementById("send_email_attachment").value = "";
    
      $("#dialog_send_email").dialog("open");
    
      break;
    

    case "cancel":
      $("#dialog_send_email").dialog("close");
    break;

    case "test":
      var subject = document.getElementById("send_email_subject").value;
      var message = document.getElementById("send_email_message").value;

      if (subject == "" || message == "") {
        notifyDialog(la["ALL_AVAILABLE_FIELDS_SHOULD_BE_FILLED_OUT"]);
        break;
      }

      confirmDialog(
        la["ARE_YOU_SURE_YOU_WANT_TO_SEND_TEST_MESSAGE_TO_YOUR_EMAIL"],
        function (response) {
          if (response) {
            document.getElementById("send_email_status").innerHTML =
              la["SENDING_PLEASE_WAIT"];

            var data = {
              cmd: "send_email_test",
              subject: subject,
              message: message,
            };

            $.ajax({
              type: "POST",
              url: "func/fn_cpanel.php",
              data: data,
              success: function (result) {
                if (result == "OK") {
                  document.getElementById("send_email_status").innerHTML =
                    la["SENDING_FINISHED"];
                } else {
                  document.getElementById("send_email_status").innerHTML =
                    la["CANT_SEND_EMAIL"] +
                    " " +
                    la["PLEASE_CHECK_SERVER_EMAIL_SMTP_SETTINGS"];
                }
              },
            });
          }
        }
      );

      break;
  }
}

function sendEmailSendToSwitch() {
  var send_to = document.getElementById("send_email_send_to").value;

  switch (send_to) {
    case "all":
      $("#send_email_username").tokenize().clear();
      document.getElementById("send_email_username_row").style.display = "none";
      break;

    case "selected":
      $("#send_email_username").tokenize().clear();
      document.getElementById("send_email_username_row").style.display = "";

      var users = $("#cpanel_user_list_grid").jqGrid(
        "getGridParam",
        "selarrrow"
      );

      $("#send_email_username").tokenize().options.newElements = true;
      for (var i = 0; i < users.length; i++) {
        var value = users[i];
        var text = $("#cpanel_user_list_grid").jqGrid(
          "getCell",
          value,
          "username"
        );
        $("#send_email_username").tokenize().tokenAdd(value, text);
      }
      $("#send_email_username").tokenize().options.newElements = false;

      break;

      case "custom":
        $("#send_email_username").tokenize().clear();
        document.getElementById("send_email_username_row").style.display = "";
      
        $("#send_email_username").tokenize().options.newElements = true;
      
        break;
      
  }
}

function notifyCheck(what) {
  switch (what) {
    case "session_check":
      if (gsValues["session_check"] == false) {
        break;
      }

      clearTimeout(timer_sessionCheck);

      var data = {
        cmd: "session_check",
      };

      $.ajax({
        type: "POST",
        url: "func/fn_connect.php",
        data: data,
        cache: false,
        error: function (statusCode, errorThrown) {
          timer_sessionCheck = setTimeout(
            "notifyCheck('session_check');",
            gsValues["session_check"] * 1000
          );
        },
        success: function (result) {
          if (result == "false") {
            $("#blocking_panel").show();
          } else {
            timer_sessionCheck = setTimeout(
              "notifyCheck('session_check');",
              gsValues["session_check"] * 1000
            );
          }
        },
      });
      break;
  }
}

function setExpirationSelected(cmd) {
  switch (cmd) {
    case "open_users":
      var users = $("#cpanel_user_list_grid").jqGrid(
        "getGridParam",
        "selarrrow"
      );

      if (users == "") {
        notifyDialog(la["NO_ITEMS_SELECTED"]);
        return;
      }

      var data = {
        cmd: "get_user_expire_avg",
        ids: users,
      };

      $.ajax({
        type: "POST",
        url: "func/fn_cpanel.php",
        data: data,
        success: function (result) {
          cpValues["set_expiration"] = "users";

          document.getElementById(
            "dialog_set_expiration_expire"
          ).checked = true;
          document.getElementById("dialog_set_expiration_expire_dt").value =
            result;

          setExpirationCheck();

          $("#dialog_set_expiration").dialog("open");
        },
      });
      break;

    case "open_objects":
      var objects = $("#cpanel_object_list_grid").jqGrid(
        "getGridParam",
        "selarrrow"
      );

      if (objects == "") {
        notifyDialog(la["NO_ITEMS_SELECTED"]);
        return;
      }

      var data = {
        cmd: "get_object_expire_avg",
        imeis: objects,
      };

      $.ajax({
        type: "POST",
        url: "func/fn_cpanel.php",
        data: data,
        success: function (result) {
          cpValues["set_expiration"] = "objects";

          document.getElementById(
            "dialog_set_expiration_expire"
          ).checked = true;
          document.getElementById("dialog_set_expiration_expire_dt").value =
            result;

          setExpirationCheck();

          $("#dialog_set_expiration").dialog("open");
        },
      });
      break;

    case "open_user_objects":
      var objects = $("#dialog_user_edit_object_list_grid").jqGrid(
        "getGridParam",
        "selarrrow"
      );

      if (objects == "") {
        notifyDialog(la["NO_ITEMS_SELECTED"]);
        return;
      }

      var data = {
        cmd: "get_object_expire_avg",
        imeis: objects,
      };

      $.ajax({
        type: "POST",
        url: "func/fn_cpanel.php",
        data: data,
        success: function (result) {
          cpValues["set_expiration"] = "user_objects";

          document.getElementById(
            "dialog_set_expiration_expire"
          ).checked = true;
          document.getElementById("dialog_set_expiration_expire_dt").value =
            result;

          setExpirationCheck();

          $("#dialog_set_expiration").dialog("open");
        },
      });
      break;

    case "save":
      var expire = document.getElementById(
        "dialog_set_expiration_expire"
      ).checked;
      var expire_dt = document.getElementById(
        "dialog_set_expiration_expire_dt"
      ).value;

      // expire object
      if (expire == true) {
        if (expire_dt == "") {
          notifyDialog(la["DATE_CANT_BE_EMPTY"]);
          break;
        }
      } else {
        expire_dt = "";
      }

      if (cpValues["set_expiration"] == "users") {
        var users = $("#cpanel_user_list_grid").jqGrid(
          "getGridParam",
          "selarrrow"
        );

        confirmDialog(
          la["ARE_YOU_SURE_YOU_WANT_TO_SET_EXPIRATION_FOR_SELECTED_ITEMS"],
          function (response) {
            if (response) {
              var data = {
                cmd: "set_user_expire_selected",
                ids: users,
                expire: expire,
                expire_dt: expire_dt,
              };

              $.ajax({
                type: "POST",
                url: "func/fn_cpanel.php",
                data: data,
                success: function (result) {
                  if (result == "OK") {
                    $("#cpanel_user_list_grid").trigger("reloadGrid");
                    $("#dialog_set_expiration").dialog("close");
                  } else {
                    notifyDialog(
                      la["THIS_ACCOUNT_HAS_NO_PRIVILEGES_TO_DO_THAT"]
                    );
                    $("#dialog_set_expiration").dialog("close");
                    $("#cpanel_user_list_grid").trigger("reloadGrid");
                    $("#cpanel_object_list_grid").trigger("reloadGrid");
                    return;
                  }
                },
              });
            }
          }
        );
      } else if (cpValues["set_expiration"] == "objects") {
        var objects = $("#cpanel_object_list_grid").jqGrid(
          "getGridParam",
          "selarrrow"
        );

        confirmDialog(
          la["ARE_YOU_SURE_YOU_WANT_TO_SET_EXPIRATION_FOR_SELECTED_ITEMS"],
          function (response) {
            if (response) {
              var data = {
                cmd: "set_object_expire_selected",
                imeis: objects,
                expire: expire,
                expire_dt: expire_dt,
              };

              $.ajax({
                type: "POST",
                url: "func/fn_cpanel.php",
                data: data,
                success: function (result) {
                  if (result == "OK") {
                    $("#cpanel_object_list_grid").trigger("reloadGrid");

                    $("#dialog_set_expiration").dialog("close");
                  } else {
                    notifyDialog(
                      la["THIS_ACCOUNT_HAS_NO_PRIVILEGES_TO_DO_THAT"]
                    );
                    $("#dialog_set_expiration").dialog("close");
                    $("#cpanel_user_list_grid").trigger("reloadGrid");
                    $("#cpanel_object_list_grid").trigger("reloadGrid");
                    return;
                  }
                },
              });
            }
          }
        );
      } else if (cpValues["set_expiration"] == "user_objects") {
        var objects = $("#dialog_user_edit_object_list_grid").jqGrid(
          "getGridParam",
          "selarrrow"
        );

        confirmDialog(
          la["ARE_YOU_SURE_YOU_WANT_TO_SET_EXPIRATION_FOR_SELECTED_ITEMS"],
          function (response) {
            if (response) {
              var data = {
                cmd: "set_object_expire_selected",
                imeis: objects,
                expire: expire,
                expire_dt: expire_dt,
              };

              $.ajax({
                type: "POST",
                url: "func/fn_cpanel.php",
                data: data,
                success: function (result) {
                  if (result == "OK") {
                    $("#dialog_user_edit_object_list_grid").trigger(
                      "reloadGrid"
                    );

                    $("#dialog_set_expiration").dialog("close");
                  } else {
                    notifyDialog(
                      la["THIS_ACCOUNT_HAS_NO_PRIVILEGES_TO_DO_THAT"]
                    );
                    $("#dialog_set_expiration").dialog("close");
                    $("#cpanel_user_list_grid").trigger("reloadGrid");
                    $("#cpanel_object_list_grid").trigger("reloadGrid");
                    return;
                  }
                },
              });
            }
          }
        );
      } else {
        $("#dialog_set_expiration").dialog("close");
      }

      break;

    case "cancel":
      $("#dialog_set_expiration").dialog("close");
      break;
  }
}

function setExpirationCheck() {
  var object_expire = document.getElementById(
    "dialog_set_expiration_expire"
  ).checked;
  if (object_expire == true) {
    document.getElementById("dialog_set_expiration_expire_dt").disabled = false;
  } else {
    document.getElementById("dialog_set_expiration_expire_dt").disabled = true;
  }
}

function downloadUsersDocuments(typeDoc) {
  let docName = "";
  let dt = Date.parse(new Date());
  if (typeDoc == "MiContrato") docName = "MiContrato_user_id_";
  if (typeDoc == "ActaConstitutiva") docName = "ActaConstitutiva_user_id_";
  if (typeDoc == "RepLegal") docName = "RepLegal_user_id_";
  if (typeDoc == "IdentificacionOficial")
    docName = "IdentificacionOficial_user_id_";
  if (typeDoc == "OpinionPositiva") docName = "OpinionPositiva_user_id_";
  if (typeDoc == "ConstanciaFiscal") docName = "ConstanciaFiscal_user_id_";
  if (typeDoc == "DomicilioFiscal") docName = "DomicilioFiscal_user_id_";
  if (typeDoc == "Otros") docName = "Otros_user_id_";
  let url = `/data/user/settingsUsersDocument/${docName}${cpValues["user_edit_id"]}.pdf?dt=${dt}`;
  window.open(url, "clearcache=yes");
}
