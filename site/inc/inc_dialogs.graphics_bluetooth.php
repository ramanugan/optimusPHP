<div id="dialog_graphics_bluetooth" style="overflow-x: hidden;overflow-y: auto;" title="<? echo $la['GRAPHICS_BLUETOOTH']; ?>">
    <div id="control_dialog_graphics_bluetooth" class="no-print">
        <div class="controls-block width100" id="control_print_show_graphics_bluetooth">
            <input style="width: 100px; background-color: #83de6f; " class="button panel float-right" type="button" value="<? echo $la['SHOW']; ?>" onclick="sensorsBluetoothShow();" />
            <input style="width: 100px; background-color: #83de6f; " class="button panel float-right" type="button" value="<? echo $la['PRINT_REPORT']; ?>" onclick="printReportBluetooth('print-area');" />
        </div>

        <div class="row">
            <div class="block width33">
                <div class="container">
                    <div class="row2">
                        <div class="width30"><? echo $la['OBJECT']; ?></div>
                        <div class="width70"><select id="dialog_graphics_bluetooth_object_list" class="select-search width100" onchange="gpsBluetoothSelected(this);"></select></div>
                    </div>
                    <div class="row2">
                        <div class="width30"><? echo $la['FILTER']; ?></div>
                        <div class="width70">
                            <select id="dialog_graphics_bluetooth_filter" class="select width100" onchange="switchDateFilter('sensors_bluetooth');">
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
                            <input readonly class="inputbox-calendar inputbox width100" id="dialog_graphics_bluetooth_date_from" type="text" value="" />
                        </div>
                        <div class="width2"></div>
                        <div class="width15">
                            <select id="dialog_graphics_bluetooth_hour_from" class="select width100">
                                <? include("inc/inc_dt.hours.php"); ?>
                            </select>
                        </div>
                        <div class="width2"></div>
                        <div class="width15">
                            <select id="dialog_graphics_bluetooth_minute_from" class="select width100">
                                <? include("inc/inc_dt.minutes.php"); ?>
                            </select>
                        </div>
                    </div>
                    <div class="row2">
                        <div class="width35"><? echo $la['TIME_TO']; ?></div>
                        <div class="width31">
                            <input readonly class="inputbox-calendar inputbox width100" id="dialog_graphics_bluetooth_date_to" type="text" value="" />
                        </div>
                        <div class="width2"></div>
                        <div class="width15">
                            <select id="dialog_graphics_bluetooth_hour_to" class="select width100">
                                <? include("inc/inc_dt.hours.php"); ?>
                            </select>
                        </div>
                        <div class="width2"></div>
                        <div class="width15">
                            <select id="dialog_graphics_bluetooth_minute_to" class="select width100">
                                <? include("inc/inc_dt.minutes.php"); ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="block width33">
                <div class="container">
                    <div class="row2">
                        <div class="width35"><? echo $la['CHOICE_GRAPHS']; ?></div>
                        <div class="width65">
                            <select id="dialog_graphics_bluetooth_graphs" class="select width100"></select>
                        </div>
                    </div>
                </div>
            </div>
            <div id="loading_containerGraphicsBluetooth" style="display: none;">
                <div class="table">
                    <div class="table-cell center-middle">
                        <div class="loader">
                            <span></span><span></span><span></span><span></span><span></span><span></span><span></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="print-area-bluetoothGraphs" class="print-container">
    <div class="row">
        <div class="block width95">
            <div class="container">
                <div class="dashboard-container">
                    <div class="block width95" style="display: none;" id="print-area-bluetoothGraphs-logo">
                        <img class="logo" src="<? echo $gsValues['URL_ROOT'] . '/img/' . $gsValues['LOGO']; ?>" />
                    </div>
                </div>
            </div>
        </div>

    
    <!-- Tus gráficas -->
    <div class="row">
        <div class="block width100">
            <div class="container">
                <h1><span id='showNameBluetoothLegend'></span></h1>
                <div class="dashboard-container">
                    <div class="block width100" id="containerBluetoothGraphs_1"></div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="block width100">
            <div class="container">
                <div class="dashboard-container">
                    <div class="block width100" id="containerBluetoothGraphs_2"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="block width100">
            <div class="container">
                <div class="dashboard-container">
                    <div class="block width100" id="containerBluetoothGraphs_3"></div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
            <div class="block width100">
                <div class="container">
                    <div class="dashboard-container">
                        <div class="block width100" id="containerBluetoothGraphs_Wired"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

</div>