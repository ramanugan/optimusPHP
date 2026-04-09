<div id="map"></div>

<div class="map-layer-control">
	<div class="row4">
		<select id="map_layer" class="select" style="min-width: 100px;" onChange="switchMapLayer($(this).val());"></select>
	</div>
</div>

<div id="history_view_control" class="history-view-control">
	<a href="#" onclick="historyRouteRouteToggle();" title="<? echo $la['ENABLE_DISABLE_ROUTE']; ?>">
		<span class="icon-route-route" id="history_view_control_route"></span>
	</a>
	<a href="#" onclick="historyRouteSnapToggle();" title="<? echo $la['ENABLE_DISABLE_SNAP']; ?>">
		<span class="icon-route-snap disabled" id="history_view_control_snap"></span>
	</a>
	<a href="#" onclick="historyRouteArrowsToggle();" title="<?php echo $la['ENABLE_DISABLE_ARROWS']; ?>" style="display: none;">
		<span class="icon-route-arrow disabled" id="history_view_control_arrows"></span>
	</a>
	<a href="#" onclick="historyRouteDataPointsToggle();" title="<? echo $la['ENABLE_DISABLE_DATA_POINTS']; ?>">
		<span class="icon-route-arrow" id="history_view_control_data_points"></span>
	</a>
	<a href="#" onclick="historyRouteStopsToggle();" title="<? echo $la['ENABLE_DISABLE_STOPS']; ?>">
		<span class="icon-route-stop" id="history_view_control_stops"></span>
	</a>
	<a href="#" onclick="historyRouteEventsToggle();" title="<? echo $la['ENABLE_DISABLE_EVENTS']; ?>">
		<span class="icon-route-event" id="history_view_control_events"></span>
	</a>
	<a href="#" onclick="historyHideRoute();" title="<? echo $la['HIDE']; ?>">
		<span class="icon-close"></span>
	</a>
</div>

<div id="camera_control" class="camera-control">
	<? echo $la['CAMERA']; ?>
</div>

<div id="street_view_control" class="street-view-control">
	<? echo $la['STREET_VIEW']; ?>
</div>

<div id="top_panel">
	<div class="tp-menu left-menu">
		<? if ($gsValues["ABOUT"] == 'true') { ?>
			<div class="about-btn">
				<a href="#" onclick="if(utilsCheckPrivileges('perm_acerca_de')) { $('#dialog_about').dialog('open'); } return false;" title="<? echo $la['ABOUT']; ?>">
					<img src="<? echo $gsValues['URL_ROOT'] . '/img/' . $gsValues['LOGO_SMALL']; ?>" border="0" />
				</a>
			</div>
		<? } ?>
		<? if ($gsValues["HELP_PAGE"] == 'true') { ?>
			<div class="help-btn" id="help_btn_container">
				<a href="<? echo $gsValues['URL_HELP']; ?>" target="_blank" onclick="if(!utilsCheckPrivileges('perm_info')) return false;" title="<? echo $la['HELP']; ?>">
					<img src="theme/images/info3.svg" border="0" />
				</a>
			</div>
		<? } ?>
		<div class="settings-btn" id="settings_btn_container">
			<a href="#" onclick="if(utilsCheckPrivileges('perm_configuracion')) { settingsOpen(); } return false;" title="<? echo $la['SETTINGS']; ?>">
				<img src="theme/images/settings.svg" border="0" />
			</a>
		</div>
		<? if ($_SESSION["privileges_dashboard"] == true) { ?>
			<div class="dashboard-btn">
				<a href="#" onclick="dashboardOpen();" title="<? echo $la['DASHBOARD']; ?>">
					<img src="theme/images/dashboard.svg" border="0" />
				</a>
			</div>
		<? } ?>
		<div class="point-btn" id="point_btn_container">
			<a href="#" onclick="if(utilsCheckPrivileges('perm_coordenadas')) { $('#dialog_show_point').dialog('open'); } return false;" title="<? echo $la['SHOW_POINT']; ?>">
				<img src="theme/images/marker.svg" border="0" />
			</a>
		</div>
		<div class="search-btn" id="search_btn_container">
			<a href="#" onclick="if(utilsCheckPrivileges('perm_buscar')) { $('#dialog_address_search').dialog('open'); } return false;" title="<? echo $la['ADDRESS_SEARCH']; ?>">
				<img src="theme/images/search.svg" border="0" />
			</a>
		</div>
		<? if ($_SESSION["privileges_reports"] == true) { ?>
			<div class="report-btn">
				<a href="#" onclick="reportsOpen();" title="<? echo $la['REPORTS']; ?>">
					<img src="theme/images/report.svg" border="0" />
				</a>
			</div>
		<? } ?>
		<? if (($_SESSION["privileges_tachograph"] == true) && (1 == 0)) { // disabled till next version
		?>
			<div class="report-btn">
				<a href="#" onclick="tachographOpen();" title="<? echo $la['TACHOGRAPH']; ?>">
					<img src="theme/images/tachograph.svg" border="0" />
				</a>
			</div>
		<? } ?>
		<? if ($_SESSION["privileges_tasks"] == true) { ?>
			<div class="tasks-btn">
				<a href="#" onclick="tasksOpen();" title="<? echo $la['TASKS']; ?>">
					<img src="theme/images/tasks.svg" border="0" />
				</a>
			</div>
		<? } ?>
		<? if ($_SESSION["privileges_rilogbook"] == true) { ?>
			<div class="rilogbook-btn">
				<a href="#" onclick="rilogbookOpen();" title="<? echo $la['RFID_AND_IBUTTON_LOGBOOK']; ?>">
					<img src="theme/images/logbook.svg" border="0" />
				</a>
			</div>
		<? } ?>
		<? if ($_SESSION["privileges_dtc"] == true) { ?>
			<div class="dtc-btn">
				<a href="#" onclick="dtcOpen();" title="<? echo $la['DIAGNOSTIC_TROUBLE_CODES']; ?>">
					<img src="theme/images/dtc.svg" border="0" />
				</a>
			</div>
		<? } ?>
		<? if ($_SESSION["privileges_maintenance"] == true) { ?>
			<div class="maintenance-btn" id="maintenance_btn_container">
				<a href="#" onclick="maintenanceOpen();" title="<? echo $la['MAINTENANCE']; ?>">
					<img src="theme/images/maintenance.svg" border="0" />
				</a>
			</div>
		<? } ?>
		<? if ($_SESSION["privileges_expenses"] == true) { ?>
			<div class="expenses-btn">
				<a href="#" onclick="expensesOpen();" title="<? echo $la['EXPENSES']; ?>">
					<img src="theme/images/expenses.svg" border="0" />
				</a>
			</div>
		<? } ?>
		<? if ($_SESSION["privileges_object_control"] == true) { ?>
			<div class="cmd-btn">
				<a href="#" onclick="cmdOpen();" title="<? echo $la['OBJECT_CONTROL']; ?>">
					<img src="theme/images/cmd.svg" border="0" />
				</a>
			</div>
		<? } ?>
		<div class="cmd-btn">
			<a href="#" onclick="openGrafica()" title="<? echo $la['CUSTOM_GRAPHICS']; ?>">
				<img src="theme/images/angle.svg" border="0" />
			</a>
		</div>

		<div class="cmd-btn">
			<a href="#" onclick="openGraphsBluetooth()" title="<? echo $la['GRAPHICS_BLUETOOTH']; ?>">
				<img src="theme/images/temperature.svg" border="0" />
			</a>
		</div>



		<? if ($_SESSION["privileges_image_gallery"] == true) { ?>
			<div class="gallery-btn">
				<a href="#" onclick="imgOpen();" title="<? echo $la['IMAGE_GALLERY']; ?>">
					<img src="theme/images/gallery.svg" border="0" />
				</a>
			</div>
		<? } ?>
		<? if ($_SESSION["privileges_chat"] == true) { ?>
			<div class="chat-btn">
				<a href="#" onclick="chatOpen();" title="<? echo $la['CHAT']; ?>">
					<img class="float-left" src="theme/images/chat.svg" border="0" />
					<span id="chat_msg_count">0</span>
				</a>
			</div>
		<? } ?>


		<!-- Custom icono gps_server
        <div class="dtc-btn">
            <a href="#" onclick="openNewSystem();" title="Nueva Opcion">
                <img src="theme/images/dtc.svg" border="0"/>
            </a>
        </div>
		-->

	</div>

	<div class="tp-menu right-menu">

			<?php if ($_SESSION['privileges'] == 'super_admin' || $_SESSION['privileges'] == 'admin') { ?>
				<!-- Telegram Vinculación (solo super_admin) -->
				<div class="telegram-link-btn" id="telegram_link_container">
					<a href="#" onclick="tgManualOpen(); return false;" title="<? echo $la['TELEGRAM_LINK_TITLE'] ?? 'Vincular Telegram'; ?>" id="tg_panel_btn">
						<img src="theme/images/telegram.svg" border="0" style="width:20px;height:20px;vertical-align:middle;" />
					</a>
				</div>

				<!-- Modal bloqueante del manual de Telegram -->
				<div id="tg_manual_overlay" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.7); z-index:999998; justify-content:center; align-items:center;">
					<div style="background:#fff; border-radius:8px; box-shadow:0 8px 32px rgba(0,0,0,0.4); width:90%; max-width:860px; height:88vh; display:flex; flex-direction:column; overflow:hidden;">
						<div style="background:#2CA5E0; color:#fff; padding:12px 18px; display:flex; justify-content:space-between; align-items:center; flex-shrink:0;">
							<span style="font-weight:bold; font-size:15px;">&#9992; Manual de vinculación con Telegram</span>
							<a href="#" onclick="tgManualClose(); return false;" style="color:#fff; text-decoration:none; font-size:22px; line-height:1; font-weight:bold;">&times;</a>
						</div>
						<iframe src="manual/telegram.html" style="flex:1; border:none; width:100%;" id="tg_manual_iframe"></iframe>
						<div style="padding:10px 18px; background:#f5f5f5; border-top:1px solid #ddd; display:flex; justify-content:flex-end; flex-shrink:0;">
							<button onclick="tgManualClose();" style="background:#2CA5E0; color:#fff; border:none; border-radius:4px; padding:8px 24px; font-size:14px; cursor:pointer; font-weight:bold;">Entendido, continuar</button>
						</div>
					</div>
				</div>
				<script>
				function tgManualOpen() {
					var overlay = document.getElementById('tg_manual_overlay');
					overlay.style.display = 'flex';
					document.body.style.overflow = 'hidden';
				}
				function tgManualClose() {
					var overlay = document.getElementById('tg_manual_overlay');
					overlay.style.display = 'none';
					document.body.style.overflow = '';
					telegramPanelToggle();
				}
				</script>

			<!-- Panel flotante de Telegram (no modal) -->
			<div id="tg_float_panel" style="display:none; position:fixed; width:340px; background:#fff; border:1px solid #ccc; border-radius:4px; box-shadow:0 4px 16px rgba(0,0,0,0.2); z-index:99999; overflow:hidden;">
				<div style="background:#2CA5E0; color:#fff; font-weight:bold; font-size:15px; padding:12px 16px; display:flex; justify-content:space-between; align-items:center; width:100%; box-sizing:border-box;">
					<span><? echo $la['NOTIFICATIONS'] ?? 'Notificaciones'; ?></span>
					<a href="#" onclick="document.getElementById('tg_float_panel').style.display='none'; return false;" style="color:#fff; text-decoration:none; font-size:18px; line-height:1;">&times;</a>
				</div>
				<div id="tg_float_body" style="padding:14px 16px; background:#fff; display:flex; flex-direction:column; align-items:center; box-sizing:border-box;">
					<!-- Contenido cargado dinámicamente -->
				</div>
			</div>
			<?php } ?>

		<div class="select-language <? if ($_SESSION["cpanel_privileges"]) { ?>cp<? } ?>" id="language_container">
			<select id="system_language" onChange="if(utilsCheckPrivileges('perm_lenguaje')) { switchLanguageTracking(); } return false;" class="select">
				<? echo getLanguageList(); ?>
			</select>
		</div>
		<? if ($_SESSION["cpanel_privileges"]) { ?>
			<div class="cpanel-btn">
				<a href="cpanel.php" title="<? echo $la['CONTROL_PANEL']; ?>">
					<img src="theme/images/cogs-white.svg" border="0" />
				</a>
			</div>
		<? } ?>
		<? if ($_SESSION["billing"] == true) { ?>
			<div class="billing-btn">
				<a href="#" onclick="billingOpen();" title="<? echo $la['BILLING']; ?>">
					<img class="float-left" src="theme/images/cart-white.svg" border="0" />
					<span id="billing_plan_count">0</span>
				</a>
			</div>
		<? } ?>
		<div class="user-btn" id="my_account_btn_container">
			<a href="#" onclick="if(utilsCheckPrivileges('perm_mi_cuenta')) { settingsOpenUser(); } return false;" title="<? echo $la['MY_ACCOUNT']; ?>">
				<img src="theme/images/user.svg" border="0" />
				<span><? echo $_SESSION["username"]; ?></span>
			</a>
		</div>
		<div class="mobile-btn" id="mobile_version_btn_container">
			<a href="#" onclick="if(utilsCheckPrivileges('perm_version_celular')) { window.location.href='mobile/tracking.php'; } return false;" title="<? echo $la['MOBILE_VERSION']; ?>">
				<img src="theme/images/mobile.svg" border="0" />
			</a>
		</div>

		<div class="logout-btn">
			<a href="#" onclick="connectLogout();" title="<? echo $la['LOGOUT']; ?>">
				<img src="theme/images/logout.svg" border="0" />
			</a>
		</div>
	</div>
</div>

<div id="side_panel">
	<ul>
		<li><a href="#side_panel_objects" onclick="datalistBottomSwitch('object');"><? echo $la['OBJECTS']; ?></a></li>
		<li>
			<a href="#side_panel_events" onclick="if(!utilsCheckPrivileges('perm_eventos')){ event.preventDefault(); event.stopImmediatePropagation(); return false; } datalistBottomSwitch('event');">
				<? echo $la['EVENTS']; ?>
			</a>
		</li>
		<li>
			<a href="#side_panel_managment_events" onclick="if(!utilsCheckPrivileges('perm_alertas')){ event.preventDefault(); event.stopImmediatePropagation(); return false; } datalistBottomSwitch('managment_event');">
				<? echo $la['EVENTS']; ?>
			</a>
		</li>
		<li>
			<a href="#side_panel_places" id="side_panel_places_tab" onclick="if(!utilsCheckPrivileges('perm_geocercas')){ event.preventDefault(); event.stopImmediatePropagation(); return false; }">
				<? echo $la['PLACES']; ?>
			</a>
		</li>
		<li><a href="#side_panel_history" onclick="datalistBottomSwitch('route');"><? echo $la['HISTORY']; ?></a></li>
	</ul>

	<div id="side_panel_objects">
		<div id="side_panel_objects_object_list">
			<table id="side_panel_objects_object_list_grid"></table>
		</div>
		<div id="side_panel_objects_dragbar"></div>
		<div id="side_panel_objects_object_data_list">
			<table id="side_panel_objects_object_datalist_grid"></table>
		</div>
	</div>

	<div id="side_panel_events">
		<div id="side_panel_events_event_list">
			<table id="side_panel_events_event_list_grid"></table>
			<div id="side_panel_events_event_list_grid_pager"></div>
		</div>
		<div id="side_panel_events_dragbar"></div>
		<div id="side_panel_events_event_data_list">
			<table id="side_panel_events_event_datalist_grid"></table>
		</div>
	</div>

	<?php
	$permGeocercas = (getUserPrivileges($user_id, 'perm_geocercas') === "false");

	if ($permGeocercas) {
		$canMarkers = false;
		$canRoutes  = false;
		$canZones   = false;
	} else {
		$canMarkers = (getUserPrivileges($user_id, 'perm_lista_marcadores') === "true");
		$canRoutes  = (getUserPrivileges($user_id, 'perm_lista_rutas') === "true");
		$canZones   = (getUserPrivileges($user_id, 'perm_lista_zonas') === "true");
	}

	$permMarkers = $permGeocercas ? 'perm_geocercas' : 'perm_lista_marcadores';
	$permRoutes  = $permGeocercas ? 'perm_geocercas' : 'perm_lista_rutas';
	$permZones   = $permGeocercas ? 'perm_geocercas' : 'perm_lista_zonas';
	?>

	<div id="side_panel_places">
		<ul>
			<?php if (!$canMarkers && !$canRoutes && !$canZones) { ?>
				<li style="display:none;">
					<a href="#side_panel_places_blank" id="side_panel_places_blank_tab"> </a>
				</li>
			<?php } ?>

			<li <?php if (!$canMarkers) { ?>style="display:none;" <?php } ?>>
				<a href="#side_panel_places_markers" id="side_panel_places_markers_tab" onclick="return utilsCheckPrivileges('<?php echo $permMarkers; ?>');">
					<span><? echo $la['MARKERS']; ?></span>
					<span id="side_panel_places_markers_num"></span>
				</a>
			</li>

			<li <?php if (!$canRoutes) { ?>style="display:none;" <?php } ?>>
				<a href="#side_panel_places_routes" id="side_panel_places_routes_tab" onclick="return utilsCheckPrivileges('<?php echo $permRoutes; ?>');">
					<span><? echo $la['ROUTES']; ?></span>
					<span id="side_panel_places_routes_num"></span>
				</a>
			</li>

			<li <?php if (!$canZones) { ?>style="display:none;" <?php } ?>>
				<a href="#side_panel_places_zones" id="side_panel_places_zones_tab" onclick="return utilsCheckPrivileges('<?php echo $permZones; ?>');">
					<span><? echo $la['ZONES']; ?></span>
					<span id="side_panel_places_zones_num"></span>
				</a>
			</li>
		</ul>

		<?php if (!$canMarkers && !$canRoutes && !$canZones) { ?>
			<div id="side_panel_places_blank">&nbsp;</div>
		<?php } ?>

		<div id="side_panel_places_markers">
			<div id="side_panel_places_marker_list">
				<table id="side_panel_places_marker_list_grid"></table>
				<div id="side_panel_places_marker_list_grid_pager"></div>
			</div>
		</div>

		<div id="side_panel_places_routes">
			<div id="side_panel_places_route_list">
				<table id="side_panel_places_route_list_grid"></table>
				<div id="side_panel_places_route_list_grid_pager"></div>
			</div>
		</div>

		<div id="side_panel_places_zones">
			<div id="side_panel_places_zone_list">
				<table id="side_panel_places_zone_list_grid"></table>
				<div id="side_panel_places_zone_list_grid_pager"></div>
			</div>
		</div>
	</div>

	<div id="side_panel_history">
		<div id="side_panel_history_parameters">
			<div class="row2">
				<div class="width35"><? echo $la['OBJECT']; ?></div>
				<div class="width65"><select id="side_panel_history_object_list" class="select-search width100"></select></div>
			</div>
			<div class="row2">
				<div class="width35"><? echo $la['FILTER']; ?></div>
				<div class="width65">
					<select id="side_panel_history_filter" class="select width100" onchange="switchDateFilter('history');">
						<option value="0" selected></option>
						<option value="1"><? echo $la['LAST_HOUR']; ?></option>
						<option value="2"><? echo $la['TODAY']; ?></option>
						<option value="3"><? echo $la['YESTERDAY']; ?></option>
						<option value="4"><? echo $la['BEFORE_2_DAYS']; ?></option>
						<option value="5"><? echo $la['BEFORE_3_DAYS']; ?></option>
						<option value="6"><? echo $la['THIS_WEEK']; ?></option>
						<option value="7"><? echo $la['LAST_WEEK']; ?></option>
						<option value="8"><? echo $la['THIS_MONTH']; ?></option>
						<option value="9"><? echo $la['LAST_MONTH']; ?></option>
					</select>
				</div>
			</div>
			<div class="row2">
				<div class="width35"><? echo $la['TIME_FROM']; ?></div>
				<div class="width31">
					<input readonly class="inputbox-calendar inputbox width100" id="side_panel_history_date_from" type="text" value="" />
				</div>
				<div class="width2"></div>
				<div class="width15">
					<select id="side_panel_history_hour_from" class="select width100">
						<? include("inc/inc_dt.hours.php"); ?>
					</select>
				</div>
				<div class="width2"></div>
				<div class="width15">
					<select id="side_panel_history_minute_from" class="select width100">
						<? include("inc/inc_dt.minutes.php"); ?>
					</select>
				</div>
			</div>
			<div class="row2">
				<div class="width35"><? echo $la['TIME_TO']; ?></div>
				<div class="width31">
					<input readonly class="inputbox-calendar inputbox width100" id="side_panel_history_date_to" type="text" value="" />
				</div>
				<div class="width2"></div>
				<div class="width15">
					<select id="side_panel_history_hour_to" class="select width100">
						<? include("inc/inc_dt.hours.php"); ?>
					</select>
				</div>
				<div class="width2"></div>
				<div class="width15">
					<select id="side_panel_history_minute_to" class="select width100">
						<? include("inc/inc_dt.minutes.php"); ?>
					</select>
				</div>
			</div>

			<div class="row3">
				<div class="width35"><? echo $la['STOPS']; ?></div>
				<div class="width31">
					<select id="side_panel_history_stop_duration" class="select width100">
						<option value="1">> 1 <? echo $la['UNIT_MIN']; ?></option>
						<option value="2">> 2 <? echo $la['UNIT_MIN']; ?></option>
						<option value="5">> 5 <? echo $la['UNIT_MIN']; ?></option>
						<option value="10">> 10 <? echo $la['UNIT_MIN']; ?></option>
						<option value="20">> 20 <? echo $la['UNIT_MIN']; ?></option>
						<option value="30">> 30 <? echo $la['UNIT_MIN']; ?></option>
						<option value="60">> 1 <? echo $la['UNIT_H']; ?></option>
						<option value="120">> 2 <? echo $la['UNIT_H']; ?></option>
						<option value="300">> 5 <? echo $la['UNIT_H']; ?></option>
					</select>
				</div>
			</div>

			<div class="row3">
				<input style="width: 100px; margin-right: 3px;" class="button" type="button" value="<? echo $la['SHOW']; ?>" onclick="historyLoadRoute({showIntoGPS:false, device:0});" />
				<input style="width: 100px; margin-right: 3px;" class="button" type="button" value="<? echo $la['HIDE']; ?>" onclick="historyHideRoute();" />
				<input style="width: 134px;" id="side_panel_history_import_export_action_menu_button" class="button" type="button" value="<? echo $la['IMPORT_EXPORT']; ?>" />
			</div>

		</div>

		<div id="side_panel_history_route">
			<table id="side_panel_history_route_detail_list_grid"></table>
		</div>

		<div id="side_panel_history_dragbar">
		</div>

		<div id="side_panel_history_route_data_list">
			<table id="side_panel_history_route_datalist_grid"></table>
		</div>
	</div>

	<div id="side_panel_managment_events">
		<div id="side_panel_managment_events_event_list">
			<table id="side_panel_managment_events_event_list_grid"></table>
			<div id="side_panel_managment_events_event_list_grid_pager"></div>
		</div>
		<div id="side_panel_managment_events_dragbar">
		</div>
		<div id="side_panel_managment_events_event_data_list">
			<table id="side_panel_managment_events_event_datalist_grid"></table>
		</div>
	</div>
</div>

<div id="bottom_panel">
	<div class="controls">
		<a href="#" onclick="hideBottomPanel();" title="<? echo $la['HIDE']; ?>">
			<span class="icon-close"></span>
		</a>
	</div>

	<div id="bottom_panel_tabs" style="height: 100%;">
		<ul>
			<li id="bottom_panel_datalist_tab"><a href="#bottom_panel_datalist"><? echo $la['DATA']; ?></a></li>
			<li><a href="#bottom_panel_graph"><? echo $la['GRAPH']; ?></a></li>
			<li><a href="#bottom_panel_msg"><? echo $la['MESSAGES']; ?></a></li>
		</ul>

		<div id="bottom_panel_datalist" class="datalist">
			<div id="bottom_panel_datalist_object_data_list" class="datalist-item-list">
				<div class="data-item-text"><? echo $la['NO_OBJECT_SELECTED']; ?></div>
			</div>
			<div id="bottom_panel_datalist_event_data_list" class="datalist-item-list" style="display: none;">
				<div class="data-item-text"><? echo $la['NO_EVENT_SELECTED']; ?></div>
			</div>
			<div id="bottom_panel_datalist_route_data_list" class="datalist-item-list" style="display: none;">
				<div class="data-item-text"><? echo $la['NO_HISTORY_LOADED']; ?></div>
			</div>
		</div>

		<div id="bottom_panel_graph">
			<div class="graph-controls">
				<div class="graph-controls-left">
					<select id="bottom_panel_graph_data_source" class="select" style="width:120px;" onchange="historyRouteChangeGraphSource();"></select>
					<a href="#" onclick="historyRoutePlay();">
						<div class="panel-button" title="<? echo $la['PLAY']; ?>">
							<img src="theme/images/play.svg" width="12px" border="0" />
						</div>
					</a>
					<a href="#" onclick="historyRoutePause();">
						<div class="panel-button" title="<? echo $la['PAUSE']; ?>">
							<img src="theme/images/pause.svg" width="12px" border="0" />
						</div>
					</a>
					<a href="#" onclick="historyRouteStop();">
						<div class="panel-button" title="<? echo $la['STOP']; ?>">
							<img src="theme/images/stop.svg" width="12px" border="0" />
						</div>
					</a>
					<select id="bottom_panel_graph_play_speed" class="select" style="width:50px;">
						<option value=1>x1</option>
						<option value=2>x2</option>
						<option value=3>x3</option>
						<option value=4>x4</option>
						<option value=5>x5</option>
						<option value=6>x6</option>
					</select>
				</div>
				<div class="graph-controls-right">
					<div id="bottom_panel_graph_label" class="graph-label"></div>

					<a href="#" onclick="graphPanLeft();">
						<div class="panel-button" title="<? echo $la['PAN_LEFT']; ?>">
							<img src="theme/images/arrow-left.svg" width="12px" border="0" />
						</div>
					</a>

					<a href="#" onclick="graphPanRight();">
						<div class="panel-button" title="<? echo $la['PAN_RIGHT']; ?>">
							<img src="theme/images/arrow-right.svg" width="12px" border="0" />
						</div>
					</a>

					<a href="#" onclick="graphZoomIn();">
						<div class="panel-button" title="<? echo $la['ZOOM_IN']; ?>">
							<img src="theme/images/plus.svg" width="12px" border="0" />
						</div>
					</a>

					<a href="#" onclick="graphZoomOut();">
						<div class="panel-button" title="<? echo $la['ZOOM_OUT']; ?>">
							<img src="theme/images/minus.svg" width="12px" border="0" />
						</div>
					</a>
				</div>
			</div>

			<div id="bottom_panel_graph_plot"></div>
		</div>

		<div id="bottom_panel_msg">
			<table id="bottom_panel_msg_list_grid"></table>
			<div id="bottom_panel_msg_list_grid_pager"></div>
		</div>
	</div>
</div>

<a href="#" onclick="showHideLeftPanel();">
	<div id="side_panel_dragbar">
	</div>
</a>

<a href="#" onclick="showBottomPanel();">
	<div id="bottom_panel_dragbar">
	</div>
</a>