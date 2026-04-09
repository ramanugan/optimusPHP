<div id="dialog_places_groups" title="<? echo $la['GROUPS']; ?>">
	<table id="places_group_list_grid"></table>
	<div id="places_group_list_grid_pager"></div>
</div>

<div id="dialog_places_group_properties" title="<? echo $la['PLACES_GROUP_PROPERTIES']; ?>">
	<div class="row">
		<div class="row2">
			<div class="width40"><? echo $la['NAME']; ?></div>
			<div class="width60"><input id="dialog_places_group_name" class="inputbox" type="text" value="" maxlength="250"></div>
		</div>
		<div class="row2">
			<div class="width40"><? echo $la['DESCRIPTION']; ?></div>
			<div class="width60"><textarea id="dialog_places_group_desc" class="inputbox" style="height:50px;" maxlength="250"></textarea></div>
		</div>
	</div>

	<center>
		<input class="button icon-save icon" type="button" onclick="placesGroupProperties('save');" value="<? echo $la['SAVE']; ?>" />&nbsp;
		<input class="button icon-close icon" type="button" onclick="placesGroupProperties('cancel');" value="<? echo $la['CANCEL']; ?>" />
	</center>
</div>

<div id="dialog_places_marker_properties" title="<? echo $la['MARKER_PROPERTIES']; ?>">
	<div class="row">
		<div class="row2">
			<div class="width40"><? echo $la['NAME']; ?></div>
			<div class="width60"><input id="dialog_places_marker_name" class="inputbox" type="text" value="" maxlength="250"></div>
		</div>
		<div class="row2">
			<div class="width40"><? echo $la['DESCRIPTION']; ?></div>
			<div class="width60"><textarea id="dialog_places_marker_desc" class="inputbox" style="height: 60px;" type='text' maxlength="2050"></textarea></div>
		</div>
		<div class="row2">
			<div class="width40"><? echo $la['GROUP']; ?></div>
			<div class="width60"><select id="dialog_places_marker_group" class="select-multiple-search width100" multiple="multiple"></select></div>
		</div>
		<div class="row2">
			<div class="width40"><? echo $la['MARKER_VISIBLE']; ?></div>
			<div class="width60"><input id="dialog_places_marker_visible" type="checkbox" class="checkbox" checked="yes" /></div>
		</div>
		<div class="row2">
			<div id="places_marker_icon_tabs">
				<ul>
					<li><a href="#places_marker_icon_default_tab"><? echo $la['DEFAULT']; ?></a></li>
					<li><a href="#places_marker_icon_custom_tab"><? echo $la['CUSTOM']; ?></a></li>
				</ul>
				<div id="places_marker_icon_default_tab">
					<div class="row2">
						<div class="icon_selector width100" id="places_marker_icon_default_list">
						</div>
					</div>
				</div>
				<div id="places_marker_icon_custom_tab">
					<div class="row">
						<div class="row2">
							<div class="icon_selector width100" id="places_marker_icon_custom_list">
							</div>
						</div>
					</div>
					<center>
						<input class="button" type="button" value="<? echo $la['UPLOAD']; ?>" onclick="placesMarkerUploadCustomIcon();" />&nbsp;
						<input class="button" type="button" value="<? echo $la['DELETE_ALL']; ?>" onclick="placesMarkerDeleteAllCustomIcon();" />
					</center>
				</div>
			</div>
		</div>
	</div>
	<center>
		<input class="button icon-save icon" type="button" onclick="placesMarkerProperties('save');" value="<? echo $la['SAVE']; ?>" />&nbsp;
		<input class="button icon-close icon" type="button" onclick="placesMarkerProperties('cancel');" value="<? echo $la['CANCEL']; ?>" />
	</center>
</div>

<div id="dialog_places_zone_properties" title="<? echo $la['ZONE_PROPERTIES']; ?>">
	<div class="row">
		<div class="row2">
			<div class="width40"><? echo $la['NAME']; ?></div>
			<div class="width60"><input id="dialog_places_zone_name" class="inputbox width100" type="text" value="" maxlength="250"></div>
		</div>
		<!--		<div class="row2">
			<div class="width40"><? //echo $la['TYPE']; 
									?></div>
			<div class="width60">
				<select id="dialog_places_zone_type" class="select width100" onchange="placesZoneSwitchType();">
					<option value="polygon"><? //echo $la['POLYGON'];
											?></option>
					<option value="circle"><? //echo $la['CIRCLE'];
											?></option>					
				</select>
			</div>
		</div>-->
		<div class="row2">
			<div class="width40"><? echo $la['DESCRIPTION']; ?></div>
			<div class="width60"><textarea id="dialog_places_zone_desc" class="inputbox" style="height: 60px;" type='text' maxlength="250"></textarea></div>
		</div>
		<div class="row2">
			<div class="width40"><? echo $la['GROUP']; ?></div>
			<div class="width60"><select id="dialog_places_zone_group" class="select-multiple-search width100" multiple="multiple"></select></div>
		</div>
		<div class="row2">
			<div class="width40"><? echo $la['COLOR']; ?></div>
			<div class="width60"><input class="color inputbox" style="width:55px" type='text' id='dialog_places_zone_color' /></div>
		</div>
		<div class="row2">
			<div class="width40"><? echo $la['ZONE_VISIBLE']; ?></div>
			<div class="width60"><input id="dialog_places_zone_visible" type="checkbox" class="checkbox" checked="yes" /></div>
		</div>
		<div class="row2">
			<div class="width40"><? echo $la['NAME_VISIBLE']; ?></div>
			<div class="width60"><input id="dialog_places_zone_name_visible" type="checkbox" class="checkbox" checked="yes" /></div>
		</div>
		<div class="row2">
			<div class="width40"><? echo $la['MEASURE_AREA']; ?></div>
			<div class="width60">
				<select id="dialog_places_zone_area" class="select width100">
					<option value="0"><? echo $la['OFF']; ?></option>
					<option value="1"><? echo $la['ACRES']; ?></option>
					<option value="2"><? echo $la['HECTARES']; ?></option>
					<option value="3"><? echo $la['SQ_M']; ?></option>
					<option value="4"><? echo $la['SQ_KM']; ?></option>
					<option value="5"><? echo $la['SQ_FT']; ?></option>
					<option value="6"><? echo $la['SQ_MI']; ?></option>
				</select>
			</div>
		</div>
	</div>
	<center>
		<input class="button icon-save icon" type="button" onclick="placesZoneProperties('save');" value="<? echo $la['SAVE']; ?>" />&nbsp;
		<input class="button icon-close icon" type="button" onclick="placesZoneProperties('cancel');" value="<? echo $la['CANCEL']; ?>" />
	</center>
</div>

<div id="dialog_places_route_properties" title="<? echo $la['ROUTE_PROPERTIES']; ?>">
	<div class="row">
		<div class="row2">
			<div class="width40"><? echo $la['NAME']; ?></div>
			<div class="width60"><input id="dialog_places_route_name" class="inputbox width100" type="text" value="" maxlength="250"></div>
		</div>
		<div class="row2">
			<div class="width40"><? echo $la['DESCRIPTION']; ?></div>
			<div class="width60"><textarea id="dialog_places_route_desc" class="inputbox" style="height: 60px;" type='text' maxlength="250"></textarea></div>
		</div>
		<div class="row2">
			<div class="width40"><? echo $la['GROUP']; ?></div>
			<div class="width60"><select id="dialog_places_route_group" class="select-multiple-search width100" multiple="multiple"></select></div>
		</div>
		<div class="row2">
			<div class="width40"><? echo $la['COLOR']; ?></div>
			<div class="width60"><input class="color inputbox" style="width:55px" type='text' id='dialog_places_route_color' /></div>
		</div>
		<div class="row2">
			<div class="width40"><? echo $la['ROUTE_VISIBLE']; ?></div>
			<div class="width60"><input id="dialog_places_route_visible" type="checkbox" class="checkbox" checked="yes" /></div>
		</div>
		<div class="row2">
			<div class="width40"><? echo $la['NAME_VISIBLE']; ?></div>
			<div class="width60"><input id="dialog_places_route_name_visible" type="checkbox" class="checkbox" checked="yes" /></div>
		</div>
		<div class="row2">
			<div class="width40"><? echo $la['DEVIATION'] . ' (' . $la["UNIT_DISTANCE"] . ')'; ?></div>
			<div class="width60"><input id="dialog_places_route_deviation" class="inputbox width100" type="text" value="" maxlength="10"></div>
		</div>
	</div>
	<center>
		<input class="button icon-save icon" type="button" onclick="placesRouteProperties('save');" value="<? echo $la['SAVE']; ?>" />&nbsp;
		<input class="button icon-close icon" type="button" onclick="placesRouteProperties('cancel');" value="<? echo $la['CANCEL']; ?>" />
	</center>
</div>

<div id="dialog_catered_events_properties" title="<? echo $la['EVENTS_PROPERTIES']; ?>">
	<div class="row">
		<input type="hidden" id="dialog_attended_id">
		<div class="row2">
			<div class="width40"><? echo $la['NAME_GPS']; ?></div>
			<div class="width60"><input id="dialog_attended_name_event" class="inputbox width100" type="text" value="" maxlength="250" readonly></div>
		</div>
		<div class="row2">
			<div class="width40"><? echo $la['EVENT_GPS']; ?></div>
			<div class="width60"><input id="dialog_attended_type_event" class="inputbox width100" type="text" value="" maxlength="250" readonly></div>
		</div>
		<div class="row2">
			<div class="width40"><? echo $la['EVENT_DETAILS']; ?></div>
			<div id="dialog_attended_details_event" class="width60" style='height: 300px; overflow-y: scroll'></div>
		</div>
		<div class="row2">
			<div class="width40"><? echo $la['STATUS']; ?></div>
			<div class="width60"><select id="dialog_attended_details_estatus_list" class="select width100">
					<? include("inc/inc_status_attended.php"); ?>
				</select></div>
		</div>
		<div class="row2">
			<div class="width40"><? echo $la['ADD_DETAILS']; ?></div>
			<div class="width60"><input id="dialog_attended_detail_event" class="inputbox width100" type="text" value="" maxlength="250"></div>
		</div>
	</div>
	<center>
		<input class="button icon-save icon" type="button" onclick="eventsAttendedProperties('save');" value="<? echo $la['SAVE']; ?>" />&nbsp;
		<input class="button icon-close icon" type="button" onclick="eventsAttendedProperties('cancel');" value="<? echo $la['CANCEL']; ?>" />
	</center>
</div>