<div id="dialog_object_add" title="<? echo $la['ADD_OBJECT'] ?>">

    <div class="scroll-y_">
        <div class="block width40">
            <div class="container">
                <div class="title-block"><? echo $la['OBJECT_DATA']; ?></div>
                <div class="row2 foucus-change">
                    <div class="width40">
                        <? echo $la['ACTIVE']; ?>
                    </div>
                    <div class="width60">
                        <input id="dialog_object_add_active" class="checkbox" type="checkbox" />
                    </div>
                </div>
                <div class="row2 foucus-change">
                    <div class="width40">
                        <? echo $la['PLAN']; ?>
                    </div>
                    <div class="width60">
                        <select id="dialog_object_add_plan" class="select width100" multiple="multiple" />
                        <option value="renta"><? echo $la['RENT']; ?></option>
                        <option value="venta"><? echo $la['SOLD']; ?></option>
                        <option value="demo"><? echo $la['DEMOS_']; ?></option>
                        </select>
                    </div>
                </div>
                <div class="row2 foucus-change">
                    <div class="width40">
                        <? echo $la['SELLER']; ?>
                    </div>
                    <div class="width60">
                        <select id="dialog_object_add_seller" class="select width100" multiple="multiple">
                            <option value="Daniel Perches">Daniel Perches</option>
                            <option value="R-Daniel Perches">R-Daniel Perches</option>
                            <option value="Gerardo">Gerardo</option>
                            <option value="R-Gerardo">R-Gerardo</option>
                            <option value="Yaneth">Yaneth</option>
                            <option value="R-Yaneth">R-Yaneth</option>
                            <option value="Brenda">Brenda</option>
                            <option value="R-Brenda">R-Brenda</option>
                            <option value="Adriana">Adriana</option>
                            <option value="R-Adriana">R-Adriana</option>
                            <option value="Nohemi">Nohemi</option>
                            <option value="R-Nohemi">R-Nohemi</option>
                            <option value="Julio">Julio</option>
                            <option value="R-Julio">R-Julio</option>
                            <option value="Esther">Esther</option>
                            <option value="R-Esther">R-Esther</option>
                            <option value="Eduardo">Eduardo</option>
                            <option value="R-Eduardo">R-Eduardo</option>
                            <option value="Roberto Ovalle">Roberto Ovalle</option>
                            <option value="R-Roberto Ovalle">R-Roberto Ovalle</option>
                            <option value="Omar">Omar</option>
                            <option value="R-Omar">R-Omar</option>
                            <option value="Roberto Leal">Roberto Leal</option>
                            <option value="R-Roberto Leal">R-Roberto Leal</option>
                            <option value="Christian">Christian</option>
                            <option value="R-Christian">R-Christian</option>
                            <option value="Leopoldo">Leopoldo</option>
                            <option value="R-Leopoldo">R-Leopoldo</option>
                            <option value="Blanca">Blanca</option>
                            <option value="R-Blanca">R-Blanca</option>
                            <option value="Jose Juan">Jose Juan</option>
                            <option value="R-Jose Juan">R-Jose Juan</option>
                            <option value="Alberto Baeza">Alberto Baeza</option>
                            <option value="R-Alberto Baeza">R-Alberto Baeza</option>
                            <option value="Alfredo">Alfredo</option>
                            <option value="R-Alfredo">R-Alfredo</option>
                            <option value="Marcos">Marcos</option>
                            <option value="R-Marcos">R-Marcos</option>
                            <option value="Lic Adriana">Lic Adriana</option>
                            <option value="R-Lic Adriana">R-Lic Adriana</option>
                            <option value="Vendedor 1">Vendedor 1</option>
                            <option value="R-Vendedor 1">R-Vendedor 1</option>
                            <option value="Vendedor 2">Vendedor 2</option>
                            <option value="R-Vendedor 2">R-Vendedor 2</option>
                            <option value="Vendedor 3">Vendedor 3</option>
                            <option value="R-Vendedor 3">R-Vendedor 3</option>
                        </select>
                    </div>
                </div>
                <div class="row2 foucus-change">
                    <div class="width40">
                        <? echo $la['NAME']; ?>
                    </div>
                    <div class="width60"><input id="dialog_object_add_name" class="inputbox" type="text" maxlength="25" />
                    </div>
                </div>
                <div class="row2 foucus-change">
                    <div class="width40">
                        <? echo $la['IMEI']; ?>
                    </div>
                    <div class="width60"><input id="dialog_object_add_imei" class="inputbox" type="text" maxlength="15" />
                    </div>
                </div>
                <div class="row2 foucus-change">
                    <div class="width40">
                        <? echo $la['TRANSPORT_MODEL']; ?>
                    </div>
                    <div class="width60"><input id="dialog_object_add_model" class="inputbox" type="text" value="" maxlength="30">
                    </div>
                </div>
                <!-- <div class="row2 foucus-change">
                <div class="width40">Vin</div>
                <div class="width60"><input id="dialog_object_add_vin" class="inputbox" type="text" maxlength="20"></div>
            </div> -->
                <div class="row2 foucus-change">
                    <div class="width40">Fecha Alta</div>
                    <div class="width60"><input id="dialog_object_add_fecha_alta" class="inputbox" type="date"></div>
                </div>
                <div class="row2 foucus-change">
                    <div class="width40">Renta</div>
                    <div class="width60">
                        <div style="display: flex">
                            <div style="margin-top: 6px; font-size=12px"><span style="font-weight: 600">$</span> </div>
                            <input id="dialog_object_add_renta" class="inputbox" type="number" value="0.00">
                        </div>
                    </div>
                </div>
                <!-- <div class="row2 foucus-change">
                <div class="width40">
                    <? echo $la['PLATE_NUMBER']; ?>
                </div>
                <div class="width60"><input id="dialog_object_add_plate_number" class="inputbox" type="text" maxlength="20">
                </div>
            </div> -->
                <div class="row2 foucus-change">
                    <div class="width40">
                        <? echo $la['GPS_DEVICE']; ?>
                    </div>
                    <!--PARA QUE SE ABRA LA TABLA DE SELECCION DE EQUIPO EN addAR -->
                    <!--<div class="width60"><input id="dialog_object_add_device" class="inputbox" type="text" maxlength="30"></select></div>-->
                    <div class="width60"><select class="width100" id="dialog_object_add_device"></select></div>
                </div>
                <div class="row2 foucus-change">
                    <div class="width40">
                        <? echo $la['SIM_CARD_NUMBER']; ?>
                    </div>
                    <div class="width60"><input id="dialog_object_add_sim_number" class="inputbox" type="text" value="" maxlength="30"></div>
                </div>
                <div class="row2 foucus-change">
                    <div class="width40">
                        <? echo $la['SIM_CARD_COMPANY']; ?>
                    </div>
                    <div class="width60">
                        <select id="dialog_object_add_sim_number_company" class="select width100" multiple="multiple" />
                        <option value="Telcel"><? echo $la['TELCEL']; ?></option>
                        <option value="M2M(Emprenet)"><? echo $la['M2M_E']; ?></option>
                        <option value="M2M(Telefonica)"><? echo $la['M2M_T']; ?></option>
                        <option value="M2M(Teltonika)"><? echo $la['M2M_TK']; ?></option>
                        <option value="AT&T"><? echo $la['AT&T']; ?></option>
                        </select>
                    </div>
                </div>
                <div class="row2 foucus-change">
                    <div class="width40">
                        <? echo $la['SIM_CARD_ACOUNT']; ?>
                    </div>
                    <div class="width60"><input id="dialog_object_add_sim_card_acount" class="inputbox" type="text" value="" maxlength="30"></div>
                </div>
                <div class="row2 foucus-change">
                    <div class="width40">
                        <? echo $la['SENSOR_TRADEMARK']; ?>
                    </div>
                    <div class="width60"><input id="dialog_object_add_sensor_trademark" class="inputbox" type="text" value="" maxlength="30"></div>
                </div>
                <div class="row2 foucus-change">
                    <div class="width40">
                        <? echo $la['NO_SENSOR1']; ?>
                    </div>
                    <div class="width60"><input id="dialog_object_add_no_sensor1" class="inputbox" type="text" value="" maxlength="30">
                    </div>
                </div>
                <div class="row2 foucus-change">
                    <div class="width40">
                        <? echo $la['NO_SENSOR2']; ?>
                    </div>
                    <div class="width60"><input id="dialog_object_add_no_sensor2" class="inputbox" type="text" value="" maxlength="30">
                    </div>
                </div>
                <div class="row2 foucus-change">
                    <div class="width40">
                        <? echo $la['NO_SENSOR3']; ?>
                    </div>
                    <div class="width60"><input id="dialog_object_add_no_sensor3" class="inputbox" type="text" value="" maxlength="30">
                    </div>
                </div>
                <div class="row2 foucus-change">
                    <div class="width40">
                        <? echo $la['ACC']; ?>
                    </div>
                    <div class="width60">
                        <select id="dialog_object_add_acc" class="select-multiple-search width100" multiple="multiple" />
                        <option value="Basico"><? echo $la['BASIC']; ?></option>
                        <option value="Boton de Panico"><? echo $la['SOS']; ?></option>
                        <option value="Mic y Bocina"><? echo $la['MIC_SPK']; ?></option>
                        <option value="Boton de Asistencia"><? echo $la['HELP_SOS']; ?></option>
                        <option value="Corte de Motor"><? echo $la['ENGINE_CUT']; ?></option>
                        <option value="Sensor de Temperatura"><? echo $la['SENSOR_TEMP']; ?></option>
                        <option value="Sensor de Enganche"><? echo $la['SENSOR_TOW']; ?></option>
                        <option value="Sensor de Puerta"><? echo $la['DOOR_SENSOR']; ?></option>
                        <option value="Sensor Temp 1"><? echo $la['SENSOR_T1']; ?></option>
                        <option value="Sensor Temp 2"><? echo $la['SENSOR_T2']; ?></option>
                        <option value="Sensor Temp 3"><? echo $la['SENSOR_T3']; ?></option>
                        <option value="Sensor Diesel 1"><? echo $la['SENSOR1']; ?></option>
                        <option value="Sensor Diesel 2"><? echo $la['SENSOR2']; ?></option>
                        <option value="Sensor Diesel 3"><? echo $la['SENSOR3']; ?></option>
                        </select>
                    </div>
                </div>
                <!-- <div class="row2 foucus-change">
                    <div class="width40">
                        <? echo $la['MAINTENANCE']; ?>
                    </div>
                    <div class="width60">
                        <select id="dialog_object_add_mtto" class="select width100">
                            <option value="Batería ER-100"><? echo $la['M_ER-100']; ?></option>
                            <option value="Mantenimiento Basico"><? echo $la['M_BASIC']; ?></option>
                            <option value="Mantenimiento Sensor"><? echo $la['M_SENSOR']; ?></option>
                            <option value="Cambio de Sensor 1"><? echo $la['SENSOR_1']; ?></option>
                            <option value="Cambio de Sensor 2"><? echo $la['SENSOR_2']; ?></option>
                            <option value="Cambio de Sensor 3"><? echo $la['SENSOR_3']; ?></option>
                            <option value="Cambio Kitt P/Motor"><? echo $la['M_MOTOR']; ?></option>
                            <option value="Cambio Kitt Panico"><? echo $la['M_PANIC']; ?></option>
                            <option value="Cambio Kitt Voz"><? echo $la['M_VOZ']; ?></option>
                            <option value="Cambio de Sensor Temp"><? echo $la['M_SENSOR_T']; ?></option>
                            <option value="Desinstalación por Remplazo"><? echo $la['R_EQUIPO']; ?></option>
                            <option value="Instalación por Remplazo"><? echo $la['I_EQUIPO']; ?></option>
                            <option value="Desinstalación por Remplazo (Garantia)"><? echo $la['RG_EQUIPO']; ?></option>
                            <option value="Instalación por Remplazo (Garantia)"><? echo $la['IG_EQUIPO']; ?></option>
                        </select>
                    </div>
                </div> -->

                <div class="row2 foucus-change">
                    <div class="width40">
                        <? echo $la['MANAGER']; ?>
                    </div>
                    <div class="width60"><select id="dialog_object_add_manager_id" class="select width100"></select></div>
                </div>
                <div class="row2 foucus-change">
                    <div class="width40">
                        <? echo $la['EXPIRE_ON']; ?>
                    </div>
                    <div class="width10"><input id="dialog_object_add_object_expire" class="checkbox" type="checkbox" onChange="objectaddCheck();" /></div>
                    <div class="width50"><input class="inputbox-calendar inputbox width100" id="dialog_object_add_object_expire_dt" /></div>
                </div>
                <div class="row2 foucus-change">
                    <div class="width40">Observaciones</div>
                    <div class="width60">
                        <textarea id="dialog_object_add_observaciones" class="textarea width100" placeholder="<? echo $la['COMMENT_ABOUT_USER']; ?>"></textarea>
                    </div>

                </div>
                <div class="row">
                    <div class="title-block"><? echo $la['USERS']; ?></div>
                    <div class="row2">
                        <?php if ($_SESSION["user_id"] == '772') : ?>
                            <div class="width100">
                                <select id="dialog_object_add_users" multiple="multiple" class="width100" disabled></select>
                            </div>
                        <?php else : ?>
                            <div class="width100">
                                <select id="dialog_object_add_users" multiple="multiple" class="width100"></select>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="block width60">
            <div class="container last">
                <div class="row">
                    <div class="title-block"><? echo $la['VEHICLE_DATA']; ?></div>
                    <div class="row2 foucus-change">
                        <div class="width40"><? echo $la['VEHICLE_YEAR']; ?></div>
                        <div class="width60">
                            <select id="dialog_object_add_vehicle_year" class="select-search width100">
                                <?php
                                $currentYear = date("Y");
                                $nextYear = date("Y") + 1;
                                for ($year = $nextYear; $year >= 1960; $year--) {
                                    echo "<option value='{$year}'>{$year}</option>";
                                }
                                ?>
                            </select>

                        </div>
                    </div>

                    <div class="row2 foucus-change">
                        <div class="width40"><? echo $la['VEHICLE_BRAND']; ?></div>
                        <div class="width60">
                            <select id="dialog_object_add_vehicle_brand" class="select-search width100" multiple="multiple">

                            <optgroup label="Autos y Camionetas">
                                    <option value="Audi">Audi</option>
                                    <option value="BMW">BMW</option>
                                    <option value="BYD">BYD</option>
                                    <option value="Chevrolet">Chevrolet</option>
                                    <option value="Dodge">Dodge</option>
                                    <option value="Ford">Ford</option>
                                    <option value="GMC">GMC</option>
                                    <option value="Honda">Honda</option>
                                    <option value="Hyundai">Hyundai</option>
                                    <option value="Jac">Jac</option>
                                    <option value="Jeep">Jeep</option>
                                    <option value="Kia">Kia</option>
                                    <option value="Mazda">Mazda</option>
                                    <option value="Mercedes-Benz">Mercedes-Benz</option>
                                    <option value="Mitsubishi">Mitsubishi</option>
                                    <option value="Nissan">Nissan</option>
                                    <option value="Omoda">Omoda</option>
                                    <option value="Peugeot">Peugeot</option>
                                    <option value="Renault">Renault</option>
                                    <option value="Subaru">Subaru</option>
                                    <option value="Suzuki">Suzuki</option>
                                    <option value="Toyota">Toyota</option>
                                    <option value="Volkswagen">Volkswagen</option>
                                    <option value="Volvo">Volvo</option>
                                </optgroup>

                                <optgroup label="Tractocamiones y Camiones">
                                    <option value="DAF">DAF</option>
                                    <option value="Dina">Dina</option>
                                    <option value="Foton">Foton</option>
                                    <option value="Freightliner">Freightliner</option>
                                    <option value="Hino">Hino</option>
                                    <option value="International">International</option>
                                    <option value="Isuzu">Isuzu</option>
                                    <option value="Kenworth">Kenworth</option>
                                    <option value="MAN">MAN</option>
                                    <option value="Mack">Mack</option>
                                    <option value="Mercedes-Benz">Mercedes-Benz</option>
                                    <option value="Peterbilt">Peterbilt</option>
                                    <option value="Scania">Scania</option>
                                    <option value="Sterling">Sterling</option>
                                    <option value="Volvo">Volvo</option>
                                </optgroup>

                                <optgroup label="Motocicletas">
                                    <option value="BMW Motorrad">BMW Motorrad</option>
                                    <option value="Ducati">Ducati</option>
                                    <option value="Harley-Davidson">Harley-Davidson</option>
                                    <option value="Honda">Honda</option>
                                    <option value="Italika">Italika</option>
                                    <option value="Kawasaki">Kawasaki</option>
                                    <option value="KTM">KTM</option>
                                    <option value="Suzuki">Suzuki</option>
                                    <option value="Yamaha">Yamaha</option>
                                </optgroup>

                                <optgroup label="Otros">
                                    <option value="Atro">Atro</option>
                                    <option value="Beall">Beall</option>
                                    <option value="Doepker">Doepker</option>
                                    <option value="East Manufacturing">East Manufacturing</option>
                                    <option value="Fontaine">Fontaine</option>
                                    <option value="Fruehauf">Fruehauf</option>
                                    <option value="Great Dane">Great Dane</option>
                                    <option value="Hyundai Translead">Hyundai Translead</option>
                                    <option value="Link Belt">Link Belt</option>
                                    <option value="Lozano">Lozano</option>
                                    <option value="MAC Trailer">MAC Trailer</option>
                                    <option value="Sany">Sany</option>
                                    <option value="Tanker">Tanker</option>
                                    <option value="Trail King">Trail King</option>
                                    <option value="Trail-Eze">Trail-Eze</option>
                                    <option value="Transcraft">Transcraft</option>
                                    <option value="Transtank">Transtank</option>
                                    <option value="Tremcar">Tremcar</option>
                                    <option value="Tytank">Tytank</option>
                                    <option value="Utility">Utility</option>
                                    <option value="Wabash">Wabash</option>
                                    <option value="Wilson Trailer">Wilson Trailer</option>
                                </optgroup>
                            </select>
                        </div>
                    </div>


                    <div class="row2 foucus-change">
                        <div class="width40"><? echo $la['VEHICLE_MODEL']; ?></div>
                        <div class="width60"><input id="dialog_object_add_vehicle_model" class="inputbox" type="text" value="" maxlength="50"></div>
                    </div>
                    <div class="row2 foucus-change">
                        <div class="width40"><? echo $la['VEHICLE_COLOR']; ?></div>
                        <div class="width60"><input id="dialog_object_add_vehicle_color" class="inputbox" type="text" value="" maxlength="30"></div>
                    </div>
                    <div class="row2 foucus-change">
                        <div class="width40"><? echo $la['LICENSE_PLATE']; ?></div>
                        <div class="width60"><input id="dialog_object_add_vehicle_plate" class="inputbox" type="text" maxlength="15" style="text-transform: uppercase;" oninput="this.value = this.value.toUpperCase();"></div>
                    </div>
                    <div class="row2 foucus-change">
                        <div class="width40"><? echo $la['VEHICLE_VIN']; ?></div>
                        <div class="width60"><input id="dialog_object_add_vehicle_vin" class="inputbox" type="text" value="" maxlength="25"></div>
                    </div>
                    <div class="row2 foucus-change">
                        <div class="width40"><? echo $la['CURRENT_ODOMETER']; ?></div>
                        <div class="width60"><input id="dialog_object_add_current_odometer" class="inputbox" type="text" value="" maxlength="30"></div>
                    </div>
                    <div class="row2 foucus-change">
                        <div class="width40"><? echo $la['INSURANCE_POLICY']; ?></div>
                        <div class="width60"><input id="dialog_object_add_vehicle_insurance" class="inputbox" type="text" value="" maxlength="40"></div>
                    </div>
                    <div class="row2 foucus-change">
                        <div class="width40"><? echo $la['INSURANCE_EXPIRATION']; ?></div>
                        <div class="width60"><input id="dialog_object_add_vehicle_insurance_exp" class="inputbox datetimepicker" type="text" value="" maxlength="20"></div>
                    </div>
                    <div class="row2 foucus-change">
                        <div class="width40"><? echo $la['FUEL_TYPE']; ?></div>
                        <div class="width60">
                            <select id="dialog_object_add_vehicle_fuel" class="select width100">
                                <option value="gasoline"><? echo $la['GASOLINE']; ?></option>
                                <option value="diesel"><? echo $la['DIESEL']; ?></option>
                                <option value="hybrid"><? echo $la['HYBRID']; ?></option>
                                <option value="electric"><? echo $la['ELECTRIC']; ?></option>
                            </select>
                        </div>
                    </div>


                    <script>
                        var textarea = document.getElementById('dialog_object_add_observaciones');

                        textarea.addEventListener('focus', function() {
                            this.value = '';
                        });
                    </script>

                </div>

            </div>
        </div>

        <div class="block width60">
            <div class="title-block"><? echo $la['OBJECTS_PRIVILEGES']; ?></div>
            <div class="block width50">
                <div class="row2">
                    <div class="width60">
                        Alimentación Principal
                    </div>
                    <div class="width30">
                        <select id="dialog_object_add_alimentacion_principal" class="select width100" />
                        <option value="true"><? echo $la['YES']; ?></option>
                        <option value="false"><? echo $la['NO']; ?></option>
                        </select>
                    </div>
                </div>
                <div class="row2">
                    <div class="width60">
                        Ignición
                    </div>
                    <div class="width30">
                        <select id="dialog_object_add_ignicion" class="select width100" />
                        <option value="true"><? echo $la['YES']; ?></option>
                        <option value="false"><? echo $la['NO']; ?></option>
                        </select>
                    </div>
                </div>
                <div class="row2">
                    <div class="width60">
                        Batería
                    </div>
                    <div class="width30">
                        <select id="dialog_object_add_bateria" class="select width100" />
                        <option value="true"><? echo $la['YES']; ?></option>
                        <option value="false"><? echo $la['NO']; ?></option>
                        </select>
                    </div>
                </div>
                <div class="row2">
                    <div class="width60">
                        Bloqueo
                    </div>
                    <div class="width30">
                        <select id="dialog_object_add_bloqueo" class="select width100" />
                        <option value="true"><? echo $la['YES']; ?></option>
                        <option value="false"><? echo $la['NO']; ?></option>
                        </select>
                    </div>
                </div>
                <!-- <div class="row2">
                    <div class="width60">
                        Boton de Pánico
                    </div>
                    <div class="width30">
                        <select id="dialog_object_add_panico" class="select width100" />
                        <option value="true"><? echo $la['YES']; ?></option>
                        <option value="false"><? echo $la['NO']; ?></option>
                        </select>
                    </div>
                </div> -->
                <div class="row2">
                    <div class="width60">
                        Temperatura del Motor
                    </div>
                    <div class="width30">
                        <select id="dialog_object_add_t_motor" class="select width100" />
                        <option value="true"><? echo $la['YES']; ?></option>
                        <option value="false"><? echo $la['NO']; ?></option>
                        </select>
                    </div>
                </div>
                <div class="row2">
                    <div class="width60">
                        Consumo
                    </div>
                    <div class="width30">
                        <select id="dialog_object_add_consumo" class="select width100" />
                        <option value="true"><? echo $la['YES']; ?></option>
                        <option value="false"><? echo $la['NO']; ?></option>
                        </select>
                    </div>
                </div>
                <div class="row2">
                    <div class="width60">
                        Cinturon de Seguridad
                    </div>
                    <div class="width30">
                        <select id="dialog_object_add_c_seguridad" class="select width100" />
                        <option value="true"><? echo $la['YES']; ?></option>
                        <option value="false"><? echo $la['NO']; ?></option>
                        </select>
                    </div>
                </div>
                <div class="row2">
                    <div class="width60">
                        Luces Frontales
                    </div>
                    <div class="width30">
                        <select id="dialog_object_add_l_frontales" class="select width100" />
                        <option value="true"><? echo $la['YES']; ?></option>
                        <option value="false"><? echo $la['NO']; ?></option>
                        </select>
                    </div>
                </div>
                <div class="row2">
                    <div class="width60">
                        Luces de Estacionamiento
                    </div>
                    <div class="width30">
                        <select id="dialog_object_add_l_estacionamiento" class="select width100" />
                        <option value="true"><? echo $la['YES']; ?></option>
                        <option value="false"><? echo $la['NO']; ?></option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="block width50">
                <div class="row2">
                    <div class="width60">Clutch</div>
                    <div class="width30">
                        <select id="dialog_object_add_clutch" class="select width100">
                            <option value="true"><? echo $la['YES']; ?></option>
                            <option value="false"><? echo $la['NO']; ?></option>
                        </select>
                    </div>
                </div>

                <div class="row2">
                    <div class="width60">Freno</div>
                    <div class="width30">
                        <select id="dialog_object_add_freno" class="select width100">
                            <option value="true"><? echo $la['YES']; ?></option>
                            <option value="false"><? echo $la['NO']; ?></option>
                        </select>
                    </div>
                </div>

                <div class="row2">
                    <div class="width60">Maletero</div>
                    <div class="width30">
                        <select id="dialog_object_add_maletero" class="select width100">
                            <option value="true"><? echo $la['YES']; ?></option>
                            <option value="false"><? echo $la['NO']; ?></option>
                        </select>
                    </div>
                </div>

                <div class="row2">
                    <div class="width60">Puerta Conductor</div>
                    <div class="width30">
                        <select id="dialog_object_add_p_conductor" class="select width100">
                            <option value="true"><? echo $la['YES']; ?></option>
                            <option value="false"><? echo $la['NO']; ?></option>
                        </select>
                    </div>
                </div>

                <div class="row2">
                    <div class="width60">Puerta Copiloto</div>
                    <div class="width30">
                        <select id="dialog_object_add_p_copiloto" class="select width100">
                            <option value="true"><? echo $la['YES']; ?></option>
                            <option value="false"><? echo $la['NO']; ?></option>
                        </select>
                    </div>
                </div>

                <div class="row2">
                    <div class="width60">Revoluciones por Minuto</div>
                    <div class="width30">
                        <select id="dialog_object_add_rpm" class="select width100">
                            <option value="true"><? echo $la['YES']; ?></option>
                            <option value="false"><? echo $la['NO']; ?></option>
                        </select>
                    </div>
                </div>

                <div class="row2">
                    <div class="width60">Velocidad</div>
                    <div class="width30">
                        <select id="dialog_object_add_velocidad" class="select width100">
                            <option value="true"><? echo $la['YES']; ?></option>
                            <option value="false"><? echo $la['NO']; ?></option>
                        </select>
                    </div>
                </div>

                <div class="row2">
                    <div class="width60">Nivel Combustible</div>
                    <div class="width30">
                        <select id="dialog_object_add_nivel_combustible" class="select width100">
                            <option value="true"><? echo $la['YES']; ?></option>
                            <option value="false"><? echo $la['NO']; ?></option>
                        </select>
                    </div>
                </div>

                <div class="row2">
                    <div class="width60">Freno de Mano</div>
                    <div class="width30">
                        <select id="dialog_object_add_f_mano" class="select width100">
                            <option value="true"><? echo $la['YES']; ?></option>
                            <option value="false"><? echo $la['NO']; ?></option>
                        </select>
                    </div>
                </div>

                <!-- <div class="row2">
                    <div class="width60">Chapa Magnetica</div>
                    <div class="width30">
                        <select id="dialog_object_add_ch_magnetica" class="select width100">
                            <option value="true"><? echo $la['YES']; ?></option>
                            <option value="false"><? echo $la['NO']; ?></option>
                        </select>
                    </div>
                </div> -->

            </div>
        </div>


        <!-- // SENSOR 1 -->
        <div class="block width60">
            <div class="title-block"><? echo $la['SENSORS_BT']; ?></div>
            <div class="block width25">
                <div class="row2">
                    <div class="width40">Sensor</div>
                    <div class="width40">
                        <select id="dialog_object_add_sensor_number_1" class="select width100">
                            <option value="1"><? echo '1'; ?></option>
                            <option value="2"><? echo '2'; ?></option>
                            <option value="3"><? echo '3'; ?></option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="block width25">
                <div class="row2">
                    <div class="width25">Tipo</div>
                    <div class="width70">
                        <select id="dialog_object_add_sensor_1" class="select width100" onchange="toggleMedidas(1)">
                            <option value="Diesel"><? echo $la['DIESEL']; ?></option>
                            <option value="DieselBT"><? echo $la['DIESELBT']; ?></option>
                            <option value="Temperatura"><? echo $la['TEMPERATURE_SENSOR']; ?></option>
                            <option value="TemperaturaBT"><? echo $la['TEMPERATUREBT']; ?></option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="block width25">
                <div class="row2">
                    <div class="width45">Largo(cm)</div>
                    <div class="width40">
                        <input id="dialog_object_add_largo_1" class="inputbox width100" type="inputbox" placeholder="0 - 500">
                    </div>
                </div>
            </div>
            <div class="block width25">
                <div class="row2">
                    <div class="width45">Alto(cm)</div>
                    <div class="width40">
                        <input id="dialog_object_add_alto_1" class="inputbox width100" type="inputbox" placeholder="0 - 500">
                    </div>
                </div>
            </div>

            <!-- // SENSOR 2 -->
            <div class="block width25">
                <div class="row2">
                    <div class="width40">Sensor</div>
                    <div class="width40">
                        <select id="dialog_object_add_sensor_number_2" class="select width100">
                            <option value="1"><? echo '1'; ?></option>
                            <option value="2"><? echo '2'; ?></option>
                            <option value="3"><? echo '3'; ?></option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="block width25">
                <div class="row2">
                    <div class="width25">Tipo</div>
                    <div class="width70">
                        <select id="dialog_object_add_sensor_2" class="select width100" onchange="toggleMedidas(2)">
                            <option value="Diesel"><? echo $la['DIESEL']; ?></option>
                            <option value="DieselBT"><? echo $la['DIESELBT']; ?></option>
                            <option value="Temperatura"><? echo $la['TEMPERATURE_SENSOR']; ?></option>
                            <option value="TemperaturaBT"><? echo $la['TEMPERATUREBT']; ?></option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="block width25">
                <div class="row2">
                    <div class="width45">Largo(cm)</div>
                    <div class="width40">
                        <input id="dialog_object_add_largo_2" class="inputbox width100" type="inputbox" placeholder="0 - 500">
                    </div>
                </div>
            </div>
            <div class="block width25">
                <div class="row2">
                    <div class="width45">Alto(cm)</div>
                    <div class="width40">
                        <input id="dialog_object_add_alto_2" class="inputbox width100" type="inputbox" placeholder="0 - 500">
                    </div>
                </div>
            </div>

            <!-- // SENSOR 3 -->
            <div class="block width25">
                <div class="row2">
                    <div class="width40">Sensor</div>
                    <div class="width40">
                        <select id="dialog_object_add_sensor_number_3" class="select width100">
                            <option value="1"><? echo '1'; ?></option>
                            <option value="2"><? echo '2'; ?></option>
                            <option value="3"><? echo '3'; ?></option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="block width25">
                <div class="row2">
                    <div class="width25">Tipo</div>
                    <div class="width70">
                        <select id="dialog_object_add_sensor_3" class="select width100" onchange="toggleMedidas(3)">
                            <option value="Diesel"><? echo $la['DIESEL']; ?></option>
                            <option value="DieselBT"><? echo $la['DIESELBT']; ?></option>
                            <option value="Temperatura"><? echo $la['TEMPERATURE_SENSOR']; ?></option>
                            <option value="TemperaturaBT"><? echo $la['TEMPERATUREBT']; ?></option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="block width25">
                <div class="row2">
                    <div class="width45">Largo(cm)</div>
                    <div class="width40">
                        <input id="dialog_object_add_largo_3" class="inputbox width100" type="inputbox" placeholder="0 - 500">
                    </div>
                </div>
            </div>
            <div class="block width25">
                <div class="row2">
                    <div class="width45">Alto(cm)</div>
                    <div class="width40">
                        <input id="dialog_object_add_alto_3" class="inputbox width100" type="inputbox" placeholder="0 - 500">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <center>
        <input class="button icon-new icon" type="button" onclick="objectAdd('add');" value="<? echo $la['ADD']; ?>" />&nbsp;
        <input class="button icon-close icon" type="button" onclick="objectAdd('cancel');" value="<? echo $la['CANCEL']; ?>" />
    </center>


</div>


<div id="dialog_object_edit" title="<? echo $la['EDIT_OBJECT']; ?>">

    <div class="scroll-y_">
        <div class="block width40">
            <div class="container">
                <div class="title-block"><? echo $la['OBJECT_DATA']; ?></div>
                <div class="row2 foucus-change">
                    <div class="width40">
                        <? echo $la['ACTIVE']; ?>
                    </div>
                    <div class="width60">
                        <input id="dialog_object_edit_active" class="checkbox" type="checkbox" />
                    </div>
                </div>
                <div class="row2 foucus-change">
                    <div class="width40">
                        <? echo $la['PLAN']; ?>
                    </div>
                    <div class="width60">
                        <select id="dialog_object_edit_plan" class="select width100" multiple="multiple" />
                        <option value="renta"><? echo $la['RENT']; ?></option>
                        <option value="venta"><? echo $la['SOLD']; ?></option>
                        <option value="demo"><? echo $la['DEMOS_']; ?></option>
                        </select>
                    </div>
                </div>
                <div class="row2 foucus-change">
                    <div class="width40">
                        <? echo $la['SELLER']; ?>
                    </div>
                    <div class="width60">
                        <select id="dialog_object_edit_seller" class="select width100" multiple="multiple">
                            <option value="Daniel Perches">Daniel Perches</option>
                            <option value="R-Daniel Perches">R-Daniel Perches</option>
                            <option value="Gerardo">Gerardo</option>
                            <option value="R-Gerardo">R-Gerardo</option>
                            <option value="Yaneth">Yaneth</option>
                            <option value="R-Yaneth">R-Yaneth</option>
                            <option value="Brenda">Brenda</option>
                            <option value="R-Brenda">R-Brenda</option>
                            <option value="Adriana">Adriana</option>
                            <option value="R-Adriana">R-Adriana</option>
                            <option value="Nohemi">Nohemi</option>
                            <option value="R-Nohemi">R-Nohemi</option>
                            <option value="Julio">Julio</option>
                            <option value="R-Julio">R-Julio</option>
                            <option value="Esther">Esther</option>
                            <option value="R-Esther">R-Esther</option>
                            <option value="Eduardo">Eduardo</option>
                            <option value="R-Eduardo">R-Eduardo</option>
                            <option value="Roberto Ovalle">Roberto Ovalle</option>
                            <option value="R-Roberto Ovalle">R-Roberto Ovalle</option>
                            <option value="Omar">Omar</option>
                            <option value="R-Omar">R-Omar</option>
                            <option value="Roberto Leal">Roberto Leal</option>
                            <option value="R-Roberto Leal">R-Roberto Leal</option>
                            <option value="Christian">Christian</option>
                            <option value="R-Christian">R-Christian</option>
                            <option value="Leopoldo">Leopoldo</option>
                            <option value="R-Leopoldo">R-Leopoldo</option>
                            <option value="Blanca">Blanca</option>
                            <option value="R-Blanca">R-Blanca</option>
                            <option value="Jose Juan">Jose Juan</option>
                            <option value="R-Jose Juan">R-Jose Juan</option>
                            <option value="Alberto Baeza">Alberto Baeza</option>
                            <option value="R-Alberto Baeza">R-Alberto Baeza</option>
                            <option value="Alfredo">Alfredo</option>
                            <option value="R-Alfredo">R-Alfredo</option>
                            <option value="Marcos">Marcos</option>
                            <option value="R-Marcos">R-Marcos</option>
                            <option value="Lic Adriana">Lic Adriana</option>
                            <option value="R-Lic Adriana">R-Lic Adriana</option>
                            <option value="Vendedor 1">Vendedor 1</option>
                            <option value="R-Vendedor 1">R-Vendedor 1</option>
                            <option value="Vendedor 2">Vendedor 2</option>
                            <option value="R-Vendedor 2">R-Vendedor 2</option>
                            <option value="Vendedor 3">Vendedor 3</option>
                            <option value="R-Vendedor 3">R-Vendedor 3</option>
                        </select>
                    </div>
                </div>
                <div class="row2 foucus-change">
                    <div class="width40">
                        <? echo $la['NAME']; ?>
                    </div>
                    <div class="width60"><input id="dialog_object_edit_name" class="inputbox" type="text" maxlength="25" />
                    </div>
                </div>
                <div class="row2 foucus-change">
                    <div class="width40">
                        <? echo $la['IMEI']; ?>
                    </div>
                    <div class="width60"><input id="dialog_object_edit_imei" class="inputbox" type="text" maxlength="15" />
                    </div>
                </div>
                <div class="row2 foucus-change">
                    <div class="width40">
                        <? echo $la['TRANSPORT_MODEL']; ?>
                    </div>
                    <div class="width60"><input id="dialog_object_edit_model" class="inputbox" type="text" value="" maxlength="30">
                    </div>
                </div>
                <!-- <div class="row2 foucus-change">
                <div class="width40">Vin</div>
                <div class="width60"><input id="dialog_object_edit_vin" class="inputbox" type="text" maxlength="20"></div>
            </div> -->
                <div class="row2 foucus-change">
                    <div class="width40">Fecha Alta</div>
                    <div class="width60"><input id="dialog_object_edit_fecha_alta" class="inputbox" type="date"></div>
                </div>
                <div class="row2 foucus-change">
                    <div class="width40">Renta</div>
                    <div class="width60">
                        <div style="display: flex">
                            <div style="margin-top: 6px; font-size=12px"><span style="font-weight: 600">$</span> </div>
                            <input id="dialog_object_edit_renta" class="inputbox" type="number" value="0.00">
                        </div>
                    </div>
                </div>
                <!-- <div class="row2 foucus-change">
                <div class="width40">
                    <? echo $la['PLATE_NUMBER']; ?>
                </div>
                <div class="width60"><input id="dialog_object_edit_plate_number" class="inputbox" type="text" maxlength="20">
                </div>
            </div> -->
                <div class="row2 foucus-change">
                    <div class="width40">
                        <? echo $la['GPS_DEVICE']; ?>
                    </div>
                    <!--PARA QUE SE ABRA LA TABLA DE SELECCION DE EQUIPO EN EDITAR -->
                    <!--<div class="width60"><input id="dialog_object_edit_device" class="inputbox" type="text" maxlength="30"></select></div>-->
                    <div class="width60"><select class="width100" id="dialog_object_edit_device"></select></div>
                </div>
                <div class="row2 foucus-change">
                    <div class="width40">
                        <? echo $la['SIM_CARD_NUMBER']; ?>
                    </div>
                    <div class="width60"><input id="dialog_object_edit_sim_number" class="inputbox" type="text" value="" maxlength="30"></div>
                </div>
                <div class="row2 foucus-change">
                    <div class="width40">
                        <? echo $la['SIM_CARD_COMPANY']; ?>
                    </div>
                    <div class="width60">
                        <select id="dialog_object_edit_sim_number_company" class="select width100" multiple="multiple" />
                        <option value="Telcel"><? echo $la['TELCEL']; ?></option>
                        <option value="M2M(Emprenet)"><? echo $la['M2M_E']; ?></option>
                        <option value="M2M(Telefonica)"><? echo $la['M2M_T']; ?></option>
                        <option value="M2M(Teltonika)"><? echo $la['M2M_TK']; ?></option>
                        <option value="AT&T"><? echo $la['AT&T']; ?></option>
                        </select>
                    </div>
                </div>
                <div class="row2 foucus-change">
                    <div class="width40">
                        <? echo $la['SIM_CARD_ACOUNT']; ?>
                    </div>
                    <div class="width60"><input id="dialog_object_edit_sim_number_acount" class="inputbox" type="text" value="" maxlength="30"></div>
                </div>
                <div class="row2 foucus-change">
                    <div class="width40">
                        <? echo $la['SENSOR_TRADEMARK']; ?>
                    </div>
                    <div class="width60"><input id="dialog_object_edit_sensor_trademark" class="inputbox" type="text" value="" maxlength="30"></div>
                </div>
                <div class="row2 foucus-change">
                    <div class="width40">
                        <? echo $la['NO_SENSOR1']; ?>
                    </div>
                    <div class="width60"><input id="dialog_object_edit_no_sensor1" class="inputbox" type="text" value="" maxlength="30">
                    </div>
                </div>
                <div class="row2 foucus-change">
                    <div class="width40">
                        <? echo $la['NO_SENSOR2']; ?>
                    </div>
                    <div class="width60"><input id="dialog_object_edit_no_sensor2" class="inputbox" type="text" value="" maxlength="30">
                    </div>
                </div>
                <div class="row2 foucus-change">
                    <div class="width40">
                        <? echo $la['NO_SENSOR3']; ?>
                    </div>
                    <div class="width60"><input id="dialog_object_edit_no_sensor3" class="inputbox" type="text" value="" maxlength="30">
                    </div>
                </div>
                <div class="row2 foucus-change">
                    <div class="width40">
                        <? echo $la['ACC']; ?>
                    </div>
                    <div class="width60">
                        <select id="dialog_object_edit_acc" class="select-multiple-search width100" multiple="multiple" />
                        <option value="Basico"><? echo $la['BASIC']; ?></option>
                        <option value="Boton de Panico"><? echo $la['SOS']; ?></option>
                        <option value="Mic y Bocina"><? echo $la['MIC_SPK']; ?></option>
                        <option value="Boton de Asistencia"><? echo $la['HELP_SOS']; ?></option>
                        <option value="Corte de Motor"><? echo $la['ENGINE_CUT']; ?></option>
                        <option value="Sensor de Temperatura"><? echo $la['SENSOR_TEMP']; ?></option>
                        <option value="Sensor de Enganche"><? echo $la['SENSOR_TOW']; ?></option>
                        <option value="Sensor de Puerta"><? echo $la['DOOR_SENSOR']; ?></option>
                        <option value="Sensor Temp 1"><? echo $la['SENSOR_T1']; ?></option>
                        <option value="Sensor Temp 2"><? echo $la['SENSOR_T2']; ?></option>
                        <option value="Sensor Temp 3"><? echo $la['SENSOR_T3']; ?></option>
                        <option value="Sensor Diesel 1"><? echo $la['SENSOR1']; ?></option>
                        <option value="Sensor Diesel 2"><? echo $la['SENSOR2']; ?></option>
                        <option value="Sensor Diesel 3"><? echo $la['SENSOR3']; ?></option>
                        </select>
                    </div>
                </div>
                <div class="row2 foucus-change">
                    <div class="width40">
                        <? echo $la['MAINTENANCE']; ?>
                    </div>
                    <div class="width60">
                        <select id="dialog_object_edit_mtto" class="select width100">
                            <option value="Batería ER-100"><? echo $la['M_ER-100']; ?></option>
                            <option value="Mantenimiento Basico"><? echo $la['M_BASIC']; ?></option>
                            <option value="Mantenimiento Sensor"><? echo $la['M_SENSOR']; ?></option>
                            <option value="Cambio de Sensor 1"><? echo $la['SENSOR_1']; ?></option>
                            <option value="Cambio de Sensor 2"><? echo $la['SENSOR_2']; ?></option>
                            <option value="Cambio de Sensor 3"><? echo $la['SENSOR_3']; ?></option>
                            <option value="Cambio Kitt P/Motor"><? echo $la['M_MOTOR']; ?></option>
                            <option value="Cambio Kitt Panico"><? echo $la['M_PANIC']; ?></option>
                            <option value="Cambio Kitt Voz"><? echo $la['M_VOZ']; ?></option>
                            <option value="Cambio de Sensor Temp"><? echo $la['M_SENSOR_T']; ?></option>
                            <option value="Desinstalación por Remplazo"><? echo $la['R_EQUIPO']; ?></option>
                            <option value="Instalación por Remplazo"><? echo $la['I_EQUIPO']; ?></option>
                            <option value="Desinstalación por Remplazo (Garantia)"><? echo $la['RG_EQUIPO']; ?></option>
                            <option value="Instalación por Remplazo (Garantia)"><? echo $la['IG_EQUIPO']; ?></option>
                        </select>
                    </div>
                </div>

                <div class="row2 foucus-change">
                    <div class="width40">
                        <? echo $la['MANAGER']; ?>
                    </div>
                    <div class="width60"><select id="dialog_object_edit_manager_id" class="select width100"></select></div>
                </div>
                <div class="row2 foucus-change">
                    <div class="width40">
                        <? echo $la['EXPIRE_ON']; ?>
                    </div>
                    <div class="width10"><input id="dialog_object_edit_object_expire" class="checkbox" type="checkbox" onChange="objectEditCheck();" /></div>
                    <div class="width50"><input class="inputbox-calendar inputbox width100" id="dialog_object_edit_object_expire_dt" /></div>
                </div>
                <div class="row2 foucus-change">
                    <div class="width40">Observaciones</div>
                    <div class="width60">
                        <textarea id="dialog_object_edit_observaciones" class="textarea width100" placeholder="<? echo $la['COMMENT_ABOUT_USER']; ?>"></textarea>
                    </div>

                </div>
                <div class="row">
                    <div class="title-block"><? echo $la['USERS']; ?></div>
                    <div class="row2">
                        <?php if ($_SESSION["user_id"] == '772') : ?>
                            <div class="width100">
                                <select id="dialog_object_edit_users" multiple="multiple" class="width100" disabled></select>
                            </div>
                        <?php else : ?>
                            <div class="width100">
                                <select id="dialog_object_edit_users" multiple="multiple" class="width100"></select>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="block width60">
            <div class="container last">
                <div class="row">
                    <div class="title-block"><? echo $la['VEHICLE_DATA']; ?></div>
                    <div class="row2 foucus-change">
                        <div class="width40"><? echo $la['VEHICLE_YEAR']; ?></div>
                        <div class="width60">
                            <select id="dialog_object_edit_add_vehicle_year" class="select-search width100">
                                <?php
                                $currentYear = date("Y");
                                $nextYear = date("Y") + 1;
                                for ($year = $nextYear; $year >= 1960; $year--) {
                                    echo "<option value='{$year}'>{$year}</option>";
                                }
                                ?>
                            </select>

                        </div>
                    </div>

                    <div class="row2 foucus-change">
                        <div class="width40"><? echo $la['VEHICLE_BRAND']; ?></div>
                        <div class="width60">
                            <select id="dialog_object_edit_add_vehicle_brand" class="select-search width100" multiple="multiple">

                            <optgroup label="Autos y Camionetas">
                                    <option value="Audi">Audi</option>
                                    <option value="BMW">BMW</option>
                                    <option value="Chevrolet">Chevrolet</option>
                                    <option value="Dodge">Dodge</option>
                                    <option value="Ford">Ford</option>
                                    <option value="GMC">GMC</option>
                                    <option value="Honda">Honda</option>
                                    <option value="Hyundai">Hyundai</option>
                                    <option value="Jac">Jac</option>
                                    <option value="Jeep">Jeep</option>
                                    <option value="Kia">Kia</option>
                                    <option value="Mazda">Mazda</option>
                                    <option value="Mercedes-Benz">Mercedes-Benz</option>
                                    <option value="Mitsubishi">Mitsubishi</option>
                                    <option value="Nissan">Nissan</option>
                                    <option value="Peugeot">Peugeot</option>
                                    <option value="Renault">Renault</option>
                                    <option value="Subaru">Subaru</option>
                                    <option value="Suzuki">Suzuki</option>
                                    <option value="Toyota">Toyota</option>
                                    <option value="Volkswagen">Volkswagen</option>
                                    <option value="Volvo">Volvo</option>
                                </optgroup>

                                <optgroup label="Tractocamiones y Camiones">
                                    <option value="DAF">DAF</option>
                                    <option value="Dina">Dina</option>
                                    <option value="Foton">Foton</option>
                                    <option value="Freightliner">Freightliner</option>
                                    <option value="Hino">Hino</option>
                                    <option value="International">International</option>
                                    <option value="Isuzu">Isuzu</option>
                                    <option value="Kenworth">Kenworth</option>
                                    <option value="MAN">MAN</option>
                                    <option value="Mack">Mack</option>
                                    <option value="Mercedes-Benz">Mercedes-Benz</option>
                                    <option value="Peterbilt">Peterbilt</option>
                                    <option value="Scania">Scania</option>
                                    <option value="Sterling">Sterling</option>
                                    <option value="Volvo">Volvo</option>
                                </optgroup>

                                <optgroup label="Motocicletas">
                                    <option value="BMW Motorrad">BMW Motorrad</option>
                                    <option value="Ducati">Ducati</option>
                                    <option value="Harley-Davidson">Harley-Davidson</option>
                                    <option value="Honda">Honda</option>
                                    <option value="Italika">Italika</option>
                                    <option value="Kawasaki">Kawasaki</option>
                                    <option value="KTM">KTM</option>
                                    <option value="Suzuki">Suzuki</option>
                                    <option value="Yamaha">Yamaha</option>
                                </optgroup>

                                <optgroup label="Otros">
                                    <option value="Atro">Atro</option>
                                    <option value="Beall">Beall</option>
                                    <option value="Doepker">Doepker</option>
                                    <option value="East Manufacturing">East Manufacturing</option>
                                    <option value="Fontaine">Fontaine</option>
                                    <option value="Fruehauf">Fruehauf</option>
                                    <option value="Great Dane">Great Dane</option>
                                    <option value="Hyundai Translead">Hyundai Translead</option>
                                    <option value="Lozano">Lozano</option>
                                    <option value="MAC Trailer">MAC Trailer</option>
                                    <option value="Sany">Sany</option>
                                    <option value="Tanker">Tanker</option>
                                    <option value="Trail King">Trail King</option>
                                    <option value="Trail-Eze">Trail-Eze</option>
                                    <option value="Transcraft">Transcraft</option>
                                    <option value="Transtank">Transtank</option>
                                    <option value="Tremcar">Tremcar</option>
                                    <option value="Tytank">Tytank</option>
                                    <option value="Utility">Utility</option>
                                    <option value="Wabash">Wabash</option>
                                    <option value="Wilson Trailer">Wilson Trailer</option>
                                </optgroup>

                            </select>
                        </div>
                    </div>


                    <div class="row2 foucus-change">
                        <div class="width40"><? echo $la['VEHICLE_MODEL']; ?></div>
                        <div class="width60"><input id="dialog_object_edit_add_vehicle_model" class="inputbox" type="text" value="" maxlength="50"></div>
                    </div>
                    <div class="row2 foucus-change">
                        <div class="width40"><? echo $la['VEHICLE_COLOR']; ?></div>
                        <div class="width60"><input id="dialog_object_edit_add_vehicle_color" class="inputbox" type="text" value="" maxlength="30"></div>
                    </div>
                    <div class="row2 foucus-change">
                        <div class="width40"><? echo $la['LICENSE_PLATE']; ?></div>
                        <div class="width60"><input id="dialog_object_edit_add_vehicle_plate" class="inputbox" type="text" maxlength="15" style="text-transform: uppercase;" oninput="this.value = this.value.toUpperCase();"></div>
                    </div>
                    <div class="row2 foucus-change">
                        <div class="width40"><? echo $la['VEHICLE_VIN']; ?></div>
                        <div class="width60"><input id="dialog_object_edit_add_vehicle_vin" class="inputbox" type="text" value="" maxlength="25"></div>
                    </div>
                    <div class="row2 foucus-change">
                        <div class="width40"><? echo $la['CURRENT_ODOMETER']; ?></div>
                        <div class="width60"><input id="dialog_object_edit_add_current_odometer" class="inputbox" type="text" value="" maxlength="30"></div>
                    </div>
                    <div class="row2 foucus-change">
                        <div class="width40"><? echo $la['INSURANCE_POLICY']; ?></div>
                        <div class="width60"><input id="dialog_object_edit_add_vehicle_insurance" class="inputbox" type="text" value="" maxlength="40"></div>
                    </div>
                    <div class="row2 foucus-change">
                        <div class="width40"><? echo $la['INSURANCE_EXPIRATION']; ?></div>
                        <div class="width60"><input id="dialog_object_edit_add_vehicle_insurance_exp" class="inputbox datetimepicker" type="text" value="" maxlength="20"></div>
                    </div>
                    <div class="row2 foucus-change">
                        <div class="width40"><? echo $la['FUEL_TYPE']; ?></div>
                        <div class="width60">
                            <select id="dialog_object_edit_add_vehicle_fuel" class="select width100">
                                <option value="gasoline"><? echo $la['GASOLINE']; ?></option>
                                <option value="diesel"><? echo $la['DIESEL']; ?></option>
                                <option value="hybrid"><? echo $la['HYBRID']; ?></option>
                                <option value="electric"><? echo $la['ELECTRIC']; ?></option>
                            </select>
                        </div>
                    </div>


                    <script>
                        var textarea = document.getElementById('dialog_object_edit_observaciones');

                        textarea.addEventListener('focus', function() {
                            this.value = '';
                        });
                    </script>

                </div>

            </div>
        </div>

        <div class="block width60">
            <div class="title-block"><? echo $la['OBJECTS_PRIVILEGES']; ?></div>
            <div class="block width50">
                <div class="row2">
                    <div class="width60">
                        Alimentación Principal
                    </div>
                    <div class="width30">
                        <select id="dialog_object_edit_alimentacion_principal" class="select width100" />
                        <option value="true"><? echo $la['YES']; ?></option>
                        <option value="false"><? echo $la['NO']; ?></option>
                        </select>
                    </div>
                </div>
                <div class="row2">
                    <div class="width60">
                        Ignición
                    </div>
                    <div class="width30">
                        <select id="dialog_object_edit_ignicion" class="select width100" />
                        <option value="true"><? echo $la['YES']; ?></option>
                        <option value="false"><? echo $la['NO']; ?></option>
                        </select>
                    </div>
                </div>
                <div class="row2">
                    <div class="width60">
                        Batería
                    </div>
                    <div class="width30">
                        <select id="dialog_object_edit_bateria" class="select width100" />
                        <option value="true"><? echo $la['YES']; ?></option>
                        <option value="false"><? echo $la['NO']; ?></option>
                        </select>
                    </div>
                </div>
                <div class="row2">
                    <div class="width60">
                        Bloqueo
                    </div>
                    <div class="width30">
                        <select id="dialog_object_edit_bloqueo" class="select width100" />
                        <option value="true"><? echo $la['YES']; ?></option>
                        <option value="false"><? echo $la['NO']; ?></option>
                        </select>
                    </div>
                </div>
                <!-- <div class="row2">
                    <div class="width60">
                        Boton de Pánico
                    </div>
                    <div class="width30">
                        <select id="dialog_object_edit_panico" class="select width100" />
                        <option value="true"><? echo $la['YES']; ?></option>
                        <option value="false"><? echo $la['NO']; ?></option>
                        </select>
                    </div>
                </div> -->
                <div class="row2">
                    <div class="width60">
                        Temperatura del Motor
                    </div>
                    <div class="width30">
                        <select id="dialog_object_edit_t_motor" class="select width100" />
                        <option value="true"><? echo $la['YES']; ?></option>
                        <option value="false"><? echo $la['NO']; ?></option>
                        </select>
                    </div>
                </div>
                <div class="row2">
                    <div class="width60">
                        Consumo
                    </div>
                    <div class="width30">
                        <select id="dialog_object_edit_consumo" class="select width100" />
                        <option value="true"><? echo $la['YES']; ?></option>
                        <option value="false"><? echo $la['NO']; ?></option>
                        </select>
                    </div>
                </div>
                <div class="row2">
                    <div class="width60">
                        Cinturon de Seguridad
                    </div>
                    <div class="width30">
                        <select id="dialog_object_edit_c_seguridad" class="select width100" />
                        <option value="true"><? echo $la['YES']; ?></option>
                        <option value="false"><? echo $la['NO']; ?></option>
                        </select>
                    </div>
                </div>
                <div class="row2">
                    <div class="width60">
                        Luces Frontales
                    </div>
                    <div class="width30">
                        <select id="dialog_object_edit_l_frontales" class="select width100" />
                        <option value="true"><? echo $la['YES']; ?></option>
                        <option value="false"><? echo $la['NO']; ?></option>
                        </select>
                    </div>
                </div>
                <div class="row2">
                    <div class="width60">
                        Luces de Estacionamiento
                    </div>
                    <div class="width30">
                        <select id="dialog_object_edit_l_estacionamiento" class="select width100" />
                        <option value="true"><? echo $la['YES']; ?></option>
                        <option value="false"><? echo $la['NO']; ?></option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="block width50">
                <div class="row2">
                    <div class="width60">Clutch</div>
                    <div class="width30">
                        <select id="dialog_object_edit_clutch" class="select width100">
                            <option value="true"><? echo $la['YES']; ?></option>
                            <option value="false"><? echo $la['NO']; ?></option>
                        </select>
                    </div>
                </div>

                <div class="row2">
                    <div class="width60">Freno</div>
                    <div class="width30">
                        <select id="dialog_object_edit_freno" class="select width100">
                            <option value="true"><? echo $la['YES']; ?></option>
                            <option value="false"><? echo $la['NO']; ?></option>
                        </select>
                    </div>
                </div>

                <div class="row2">
                    <div class="width60">Maletero</div>
                    <div class="width30">
                        <select id="dialog_object_edit_maletero" class="select width100">
                            <option value="true"><? echo $la['YES']; ?></option>
                            <option value="false"><? echo $la['NO']; ?></option>
                        </select>
                    </div>
                </div>

                <div class="row2">
                    <div class="width60">Puerta Conductor</div>
                    <div class="width30">
                        <select id="dialog_object_edit_p_conductor" class="select width100">
                            <option value="true"><? echo $la['YES']; ?></option>
                            <option value="false"><? echo $la['NO']; ?></option>
                        </select>
                    </div>
                </div>

                <div class="row2">
                    <div class="width60">Puerta Copiloto</div>
                    <div class="width30">
                        <select id="dialog_object_edit_p_copiloto" class="select width100">
                            <option value="true"><? echo $la['YES']; ?></option>
                            <option value="false"><? echo $la['NO']; ?></option>
                        </select>
                    </div>
                </div>

                <div class="row2">
                    <div class="width60">Revoluciones por Minuto</div>
                    <div class="width30">
                        <select id="dialog_object_edit_rpm" class="select width100">
                            <option value="true"><? echo $la['YES']; ?></option>
                            <option value="false"><? echo $la['NO']; ?></option>
                        </select>
                    </div>
                </div>

                <div class="row2">
                    <div class="width60">Velocidad</div>
                    <div class="width30">
                        <select id="dialog_object_edit_velocidad" class="select width100">
                            <option value="true"><? echo $la['YES']; ?></option>
                            <option value="false"><? echo $la['NO']; ?></option>
                        </select>
                    </div>
                </div>

                <div class="row2">
                    <div class="width60">Nivel Combustible</div>
                    <div class="width30">
                        <select id="dialog_object_edit_nivel_combustible" class="select width100">
                            <option value="true"><? echo $la['YES']; ?></option>
                            <option value="false"><? echo $la['NO']; ?></option>
                        </select>
                    </div>
                </div>

                <div class="row2">
                    <div class="width60">Freno de Mano</div>
                    <div class="width30">
                        <select id="dialog_object_edit_f_mano" class="select width100">
                            <option value="true"><? echo $la['YES']; ?></option>
                            <option value="false"><? echo $la['NO']; ?></option>
                        </select>
                    </div>
                </div>

                <!-- <div class="row2">
                    <div class="width60">Chapa Magnetica</div>
                    <div class="width30">
                        <select id="dialog_object_edit_ch_magnetica" class="select width100">
                            <option value="true"><? echo $la['YES']; ?></option>
                            <option value="false"><? echo $la['NO']; ?></option>
                        </select>
                    </div>
                </div> -->

            </div>
        </div>


        <!-- // SENSOR 1 -->
        <div class="block width60">
            <div class="title-block"><? echo $la['SENSORS_BT']; ?></div>
            <div class="block width25">
                <div class="row2">
                    <div class="width40">Sensor</div>
                    <div class="width40">
                        <select id="dialog_object_edit_sensor_number_1" class="select width100" onchange="toggleNumeroEdit(1)">
                            <option value="0"><? echo '0'; ?></option>
                            <option value="1"><? echo '1'; ?></option>
                            <option value="2"><? echo '2'; ?></option>
                            <option value="3"><? echo '3'; ?></option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="block width25">
                <div class="row2">
                    <div class="width25">Tipo</div>
                    <div class="width70">
                        <select id="dialog_object_edit_sensor_1" class="select width100" onchange="toggleMedidasEdit(1)">
                            <option value="Diesel"><? echo $la['DIESEL']; ?></option>
                            <option value="DieselBT"><? echo $la['DIESELBT']; ?></option>
                            <option value="Temperatura"><? echo $la['TEMPERATURE_SENSOR']; ?></option>
                            <option value="TemperaturaBT"><? echo $la['TEMPERATUREBT']; ?></option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="block width25">
                <div class="row2">
                    <div class="width45">Largo(cm)</div>
                    <div class="width40">
                        <input id="dialog_object_edit_largo_1" class="inputbox width100" type="inputbox" placeholder="0 - 500">
                    </div>
                </div>
            </div>
            <div class="block width25">
                <div class="row2">
                    <div class="width45">Alto(cm)</div>
                    <div class="width40">
                        <input id="dialog_object_edit_alto_1" class="inputbox width100" type="inputbox" placeholder="0 - 500">
                    </div>
                </div>
            </div>

            <!-- // SENSOR 2 -->
            <div class="block width25">
                <div class="row2">
                    <div class="width40">Sensor</div>
                    <div class="width40">
                        <select id="dialog_object_edit_sensor_number_2" class="select width100" onchange="toggleNumeroEdit(2)">
                            <option value="0"><? echo '0'; ?></option>
                            <option value="1"><? echo '1'; ?></option>
                            <option value="2"><? echo '2'; ?></option>
                            <option value="3"><? echo '3'; ?></option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="block width25">
                <div class="row2">
                    <div class="width25">Tipo</div>
                    <div class="width70">
                        <select id="dialog_object_edit_sensor_2" class="select width100" onchange="toggleMedidasEdit(2)">
                            <option value="Diesel"><? echo $la['DIESEL']; ?></option>
                            <option value="DieselBT"><? echo $la['DIESELBT']; ?></option>
                            <option value="Temperatura"><? echo $la['TEMPERATURE_SENSOR']; ?></option>
                            <option value="TemperaturaBT"><? echo $la['TEMPERATUREBT']; ?></option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="block width25">
                <div class="row2">
                    <div class="width45">Largo(cm)</div>
                    <div class="width40">
                        <input id="dialog_object_edit_largo_2" class="inputbox width100" type="inputbox" placeholder="0 - 500">
                    </div>
                </div>
            </div>
            <div class="block width25">
                <div class="row2">
                    <div class="width45">Alto(cm)</div>
                    <div class="width40">
                        <input id="dialog_object_edit_alto_2" class="inputbox width100" type="inputbox" placeholder="0 - 500">
                    </div>
                </div>
            </div>

            <!-- // SENSOR 3 -->
            <div class="block width25">
                <div class="row2">
                    <div class="width40">Sensor</div>
                    <div class="width40">
                        <select id="dialog_object_edit_sensor_number_3" class="select width100" onchange="toggleNumeroEdit(3)">
                            <option value="0"><? echo '0'; ?></option>
                            <option value="1"><? echo '1'; ?></option>
                            <option value="2"><? echo '2'; ?></option>
                            <option value="3"><? echo '3'; ?></option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="block width25">
                <div class="row2">
                    <div class="width25">Tipo</div>
                    <div class="width70">
                        <select id="dialog_object_edit_sensor_3" class="select width100" onchange="toggleMedidasEdit(3)">
                            <option value="Diesel"><? echo $la['DIESEL']; ?></option>
                            <option value="DieselBT"><? echo $la['DIESELBT']; ?></option>
                            <option value="Temperatura"><? echo $la['TEMPERATURE_SENSOR']; ?></option>
                            <option value="TemperaturaBT"><? echo $la['TEMPERATUREBT']; ?></option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="block width25">
                <div class="row2">
                    <div class="width45">Largo(cm)</div>
                    <div class="width40">
                        <input id="dialog_object_edit_largo_3" class="inputbox width100" type="inputbox" placeholder="0 - 500">
                    </div>
                </div>
            </div>
            <div class="block width25">
                <div class="row2">
                    <div class="width45">Alto(cm)</div>
                    <div class="width40">
                        <input id="dialog_object_edit_alto_3" class="inputbox width100" type="inputbox" placeholder="0 - 500">
                    </div>
                </div>
            </div>
        </div>
    </div>


    <center>
        <input class="button icon-save icon" type="button" onclick="objectEdit('save');" value="<? echo $la['SAVE']; ?>" />&nbsp;
        <input class="button icon-close icon" type="button" onclick="objectEdit('cancel');" value="<? echo $la['CANCEL']; ?>" />
    </center>


</div>


<div id="dialog_object_edit_crm" title="<? echo $la['EDIT_OBJECT']; ?>">
    <div class="row">
        <input type="hidden" id="dialog_object_imei">
        <div class="row2">
            <div class="width40"><? echo $la['NAME_GPS']; ?></div>
            <div class="width60"><input id="dialog_object_edit_name_crm" class="inputbox width100" type="text" value="" maxlength="250" readonly></div>
        </div>
        <div class="row2">
            <div class="width40"><? echo $la['EVENT_GPS']; ?></div>
            <div class="width60"><input id="dialog_object_edit_imei_crm" class="inputbox width100" type="text" value="" maxlength="250" readonly></div>
        </div>
        <div class="row2">
            <div class="width40"><? echo $la['EVENT_DETAILS']; ?></div>
            <div id="dialog_object_edit_mtto_crm" class="width60" style='height: 300px; overflow-y: scroll'></div>
        </div>
        <div class="row2">
            <div class="width40"><? echo $la['STATUS']; ?></div>
            <div class="width60"><select id="dialog_attended_details_estatus_list" onChange="objectEditDate();" class="select width100">
                    <? include("inc/inc_status_attended.php"); ?>
                </select></div>
        </div>
        <div class="row2">
            <div class="width40"><?php echo $la['DATE_SERVICES']; ?></div>
            <div class="width60"><input id="dialog_object_edit_fecha_alta_crm" class="inputbox" type="datetime-local"></div>
        </div>
        <div class="row2">
            <div class="width40"><? echo $la['ADD_DETAILS']; ?></div>
            <div class="width60"><input id="dialog_attended_detail_event_crm" class="inputbox width100" type="text" value="" maxlength="250"></div>
        </div>
    </div>
    <center>
        <input class="button icon-save icon" type="button" onclick="objectEditCrm('save');" value="<? echo $la['SAVE']; ?>" />&nbsp;
        <input class="button icon-close icon" type="button" onclick="objectEditCrm('cancel');" value="<? echo $la['CANCEL']; ?>" />
    </center>
</div>

</div>

<div id="dialog_imei_edit" title="<? echo $la['EDIT_IMEI']; ?>">
    <div class="row">
        <div class="row2 foucus-change">
            <div class="width40">
                <? echo $la['IMEI']; ?>
            </div>
            <div class="width60"><input id="dialog_imei_edit_imei" class="inputbox" type="text" value="" maxlength="30" readonly></div>
        </div>
        <div class="row2 foucus-change">
            <div class="width40">
                <? echo $la['SIM_CARD_ICCID']; ?>
            </div>
            <div class="width60"><input id="dialog_imei_edit_iccid" class="inputbox" type="text" value="" maxlength="30"></div>
        </div>
        <div class="row2 foucus-change">
            <div class="width40">
                <? echo $la['SIM_CARD_NUMBER']; ?>
            </div>
            <div class="width60"><input id="dialog_imei_edit_sim_numero" class="inputbox" type="text" value="" maxlength="30"></div>
        </div>
        <div class="row2">
            <div class="width40">
                <? echo $la['PLAN']; ?>
            </div>
            <div class="width60">
                <select id="dialog_imei_edit_plan" class="select width100" multiple="multiple" />
                <option value="renta"><? echo $la['RENT']; ?></option>
                <option value="venta"><? echo $la['SOLD']; ?></option>
                <option value="demo"><? echo $la['DEMOS_']; ?></option>
                </select>
            </div>
        </div>
        <div class="row2 foucus-change">
            <div class="width40">
                <? echo $la['RENT_COST']; ?>
            </div>
            <div class="width60"><input id="dialog_imei_edit_renta_costo" class="inputbox" type="text" value="" maxlength="30"></div>
        </div>
        <div class="row2 foucus-change">
            <div class="width40">
                <? echo $la['SIM_CARD_COMPANY']; ?>
            </div>
            <div class="width60"><input id="dialog_imei_edit_sim_compania" class="inputbox" type="text" value="" maxlength="30"></div>
        </div>
        <div class="row2 foucus-change">
            <div class="width40">
                <? echo $la['SUPPLIER']; ?>
            </div>
            <div class="width60"><input id="dialog_imei_edit_sim_proveedor" class="inputbox" type="text" value="" maxlength="30"></div>
        </div>
        <div class="row2 foucus-change">
            <div class="width40"><? echo $la['DATE_CLOSE']; ?></div>
            <div class="width60"><input id="dialog_imei_edit_fecha_corte" class="inputbox" type="date"></div>
        </div>
        <div class="row2 foucus-change">
            <div class="width40"><? echo $la['PURCHASE_DATE']; ?></div>
            <div class="width60"><input id="dialog_imei_edit_fecha_compra" class="inputbox" type="date"></div>
        </div>
        <div class="row2 foucus-change">
            <div class="width40"><? echo $la['ALTA_DATE']; ?></div>
            <div class="width60"><input id="dialog_imei_edit_fecha_alta" class="inputbox" type="date"></div>
        </div>


    </div>
    <center>
        <input class="button icon-save icon" type="button" onclick="imeiEdit('save');" value="<? echo $la['SAVE']; ?>" />&nbsp;
        <input class="button icon-close icon" type="button" onclick="imeiEdit('cancel');" value="<? echo $la['CANCEL']; ?>" />
    </center>

</div>

<div id="dialog_device_edit" title="<? echo $la['EDIT_DEVICE']; ?>">
    <div class="row">
        <div class="row2 foucus-change">
            <div class="width40">
                <? echo $la['IMEI']; ?>
            </div>
            <div class="width60"><input id="dialog_device_edit_imei" class="inputbox" type="text" value="" maxlength="30" readonly></div>
        </div>
        <div class="row2 foucus-change">
            <div class="width40">
                <? echo $la['BRANDING']; ?>
            </div>
            <div class="width60"><input id="dialog_device_edit_marca" class="inputbox" type="text" value="" maxlength="30"></div>
        </div>
        <div class="row2 foucus-change">
            <div class="width40">
                <? echo $la['MODEL']; ?>
            </div>
            <div class="width60"><input id="dialog_device_edit_modelo" class="inputbox" type="text" value="" maxlength="30"></div>
        </div>
        <div class="row2 foucus-change">
            <div class="width40">
                <? echo $la['SUPPLIER']; ?>
            </div>
            <div class="width60"><input id="dialog_device_edit_proveedor" class="inputbox" type="text" value="" maxlength="30"></div>
        </div>
        <div class="row2 foucus-change">
            <div class="width40">
                <? echo $la['RENT_COST']; ?>
            </div>
            <div class="width60"><input id="dialog_device_edit_renta_costo" class="inputbox" type="text" value="" maxlength="30"></div>
        </div>
        <div class="row2 foucus-change">
            <div class="width40"><? echo $la['PURCHASE_DATE']; ?></div>
            <div class="width60"><input id="dialog_device_edit_fecha_compra" class="inputbox" type="date"></div>
        </div>
        <div class="row2 foucus-change">
            <div class="width40"><? echo $la['ALTA_DATE']; ?></div>
            <div class="width60"><input id="dialog_device_edit_fecha_alta" class="inputbox" type="date"></div>
        </div>


    </div>
    <center>
        <input class="button icon-save icon" type="button" onclick="deviceEdit('save');" value="<? echo $la['SAVE']; ?>" />&nbsp;
        <input class="button icon-close icon" type="button" onclick="deviceEdit('cancel');" value="<? echo $la['CANCEL']; ?>" />
    </center>

</div>