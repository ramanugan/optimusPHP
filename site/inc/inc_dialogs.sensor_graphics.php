<div id="dialog_sensors_graphics" style="overflow-x: hidden;overflow-y: auto;" title="<? echo $la['CUSTOM_GRAPHICS']; ?>">
	<div id="control_dialog_sensors_graphics">
		<div class="controls-block width100">
			<input style="width: 100px; background-color: #83de6f; " class="button panel float-right" type="button" value="<? echo $la['SHOW']; ?>" onclick="sensorsGraphicsShow();" />
			<input style="width: 100px; background-color: #83de6f; " class="button panel float-right" type="button" value="<? echo $la['PRINT_REPORT']; ?>" onclick="printReport('print-area');" />
			<div id="check_show_loads_unloads" style="display: none;">
				<label class="button panel float-right"><strong> Cargas/Descargas</strong><input id="show_loads_unloads" class="checkbox" type="checkbox" onChange="toggleShowLoadsUnLoads();" checked="checked" /></label>
			</div>
			<!--
                <input style="width: 100px; margin-right: 3px;" class="button panel float-right" type="button" value="<? echo $la['EXPORT_CSV']; ?>" onclick="graphicsExportCSV();" />
                -->
		</div>

		<div class="row">
			<div class="block width33">
				<div class="container">
					<div class="row2">
						<div class="width30"><? echo $la['OBJECT']; ?></div>
						<div class="width70"><select id="dialog_sensors_graphics_object_list" class="select-search width100"></select></div>
					</div>
					<div class="row2">
						<div class="width30"><? echo $la['FILTER']; ?></div>
						<div class="width70">
							<select id="dialog_sensors_graphics_filter" class="select width100" onchange="switchDateFilter('sensors_graphics');">
								<option value="0" selected><? echo $la['WHOLE_PERIOD']; ?></option>
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
				</div>
			</div>
			<div class="block width33">
				<div class="container">
					<div class="row2">
						<div class="width35"><? echo $la['TIME_FROM']; ?></div>
						<div class="width31">
							<input readonly class="inputbox-calendar inputbox width100" id="dialog_sensors_graphics_date_from" type="text" value="" />
						</div>
						<div class="width2"></div>
						<div class="width15">
							<select id="dialog_sensors_graphics_hour_from" class="select width100">
								<? include("inc/inc_dt.hours.php"); ?>
							</select>
						</div>
						<div class="width2"></div>
						<div class="width15">
							<select id="dialog_sensors_graphics_minute_from" class="select width100">
								<? include("inc/inc_dt.minutes.php"); ?>
							</select>
						</div>
					</div>
					<div class="row2">
						<div class="width35"><? echo $la['TIME_TO']; ?></div>
						<div class="width31">
							<input readonly class="inputbox-calendar inputbox width100" id="dialog_sensors_graphics_date_to" type="text" value="" />
						</div>
						<div class="width2"></div>
						<div class="width15">
							<select id="dialog_sensors_graphics_hour_to" class="select width100">
								<? include("inc/inc_dt.hours.php"); ?>
							</select>
						</div>
						<div class="width2"></div>
						<div class="width15">
							<select id="dialog_sensors_graphics_minute_to" class="select width100">
								<? include("inc/inc_dt.minutes.php"); ?>
							</select>
						</div>
					</div>
				</div>
			</div>
			<div class="width2"></div>
			<div class="block width33">
				<div class="container">
					<div class="row2">
						<div style="background-color: #FAFCA6;" class="" id="tooltipContainerGraphics"></div>
					</div>
				</div>
			</div>
		</div>
		<div id="loading_containerGraphics" style="display: none;">
			<div class="table">
				<div class="table-cell center-middle">
					<div class="loader">
						<span></span><span></span><span></span><span></span><span></span><span></span><span></span>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div id='print-area' style="display: none;">
		<div class="row">
			<div class="block width100">
				<div class="container">
					<div class="dashboard-container">
						<div class="block width100" id="containerGraphics"></div>
					</div>
				</div>

			</div>

		</div>
		<div class="row">
			<div class="block width17">&nbsp;</div>
			<div class="block width300">
				<div class="container">
					<div class="block width100" id="map_fuel_loading_unloading"></div>
				</div>
			</div>
		</div>

		<div class="row">
			<div class="block width100">
				<div class="container">
					<h1>Unidad: <span id='gpsTableGraphicsDatos'></span></h1>
					<h2>Tabla de Datos</h2>
					<table class="styled-table">
						<thead>
							<tr>
								<th>Fecha</th>
								<th>Distancia (Km)</th>
								<th>Consumo (ltr)</th>
								<th>Km/lt</th>
								<th>Carga(ltr)</th>
								<th>Descarga(ltr)</th>
							</tr>
						</thead>
						<tbody id='trTableGraphicsDatos'>
						</tbody>
						<tfoot id='trTableGraphicsDatosResumen'>
						</tfoot>
					</table>
				</div>

			</div>

		</div>
	</div>
	<!-- 	<div id='tableGraphicsTanques' class="row" style="display: none;">
		<div class="block width100">
			<div class="container">
				<h2>Tanques</h2>
				<table class="styled-table">
					<thead>
						<tr>
							<th>Fecha</th>
							<th>Distancia (Km)</th>
							<th>Tanque 1 (ltr)</th>
							<th>Tanque 2 (ltr)</th>
							<th>Tanque 3 (ltr)</th>
							<th>Consumo (ltr)</th>
							<th>Km/lt</th>
							<th>Carga(ltr)</th>
							<th>Descarga(ltr)</th>
						</tr>
					</thead>
					<tbody id='trTableGraphicsDatosTanques'>
					</tbody>
					<tfoot id='trTableGraphicsDatosTanquesResumen'>
					</tfoot>
				</table>
			</div>

		</div>
	-->
</div>