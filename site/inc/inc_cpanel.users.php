<input type="hidden" id="UsersDocumentsMiContrato" name="UsersDocumentsMiContrato" value="" />
<input type="hidden" id="UsersDocumentsActaConstitutiva" name="UsersDocumentsActaConstitutiva" value="" />
<input type="hidden" id="UsersDocumentsRepLegal" name="UsersDocumentsRepLegal" value="" />
<input type="hidden" id="UsersDocumentsIdentificacionOficial" name="UsersDocumentsIdentificacionOficial" value="" />
<input type="hidden" id="UsersDocumentsOpinionPositiva" name="UsersDocumentsOpinionPositiva" value="" />
<input type="hidden" id="UsersDocumentsConstanciaFiscal" name="UsersDocumentsConstanciaFiscal" value="" />
<input type="hidden" id="UsersDocumentsDomicilioFiscal" name="UsersDocumentsDomicilioFiscal" value="" />
<input type="hidden" id="UsersDocumentsOtros" name="UsersDocumentsOtros" value="" />
<div id="dialog_send_email" title="<? echo $la['SEND_EMAIL']; ?>">
        <div class="row">
                <div class="row2">
                        <div class="width20"><? echo $la['SEND_TO']; ?></div>
                        <div class="width80">
                                <select id="send_email_send_to" class="select width100" onchange="sendEmailSendToSwitch('test');">
                                        <option value="all"><? echo $la['ALL_USER_ACCOUNTS']; ?></option>
                                        <option value="selected"><? echo $la['SELECTED_USER_ACCOUNTS']; ?></option>
                                        <option value="custom"><? echo $la['CUSTOM']; ?></option>
                                </select>
                        </div>
                </div>
                <div class="row2" id="send_email_username_row">
                        <div class="width20"><? echo $la['USERNAME']; ?></div>
                        <div class="width80"><select id="send_email_username" multiple="multiple" class="width100"></select></div>
                </div>
                <div class="row2">
                        <div class="width20"><? echo $la['SUBJECT']; ?></div>
                        <div class="width80"><input id="send_email_subject" class="inputbox" type="text" value="" maxlength="50"></div>
                </div>
                <div class="row3">
                        <div class="width20"><? echo $la['MESSAGE']; ?></div>
                        <div class="width80"><textarea id="send_email_message" class="inputbox" style="height: 250px;" type='text'></textarea></div>
                </div>
                <div class="row3">
                        <div class="width20"><?php echo $la['ATTACH_FILE']; ?></div>
                        <div class="width80">
                                <input id="send_email_attachment" class="inputbox" type="file" onchange="previewFile()" />
                                <div id="file_preview" style="display: none;">
                                        <img id="file_icon" src="#" alt="Icono del archivo" style="max-width: 50px; max-height: 50px;" />
                                </div>
                        </div>
                </div>



                <div class="row3">
                        <div class="width20"><? echo $la['STATUS']; ?></div>
                        <div class="width80">
                                <div id="send_email_status" style="text-align:center;"></div>
                        </div>
                </div>

        </div>

        <center>
                <!-- <input class="button icon-time icon" type="button" onclick="sendEmail('test');" value="<? echo $la['TEST']; ?>" />&nbsp; -->
                <input class="button icon-create icon" type="button" onclick="sendEmail('send');" value="<? echo $la['SEND']; ?>" />&nbsp;
                <input class="button icon-close icon" type="button" onclick="sendEmail('cancel');" value="<? echo $la['CANCEL']; ?>" />
        </center>
</div>

<div id="dialog_user_add" title="<? echo $la['ADD_USER']; ?>">

        <div class="scroll-y_">
                <div class="row">
                        <div class="row2">
                                <div class="width40"><? echo $la['EMAIL']; ?></div>
                                <div class="width40"><input id="dialog_user_add_email" class="inputbox" type="text" maxlength="50"></div>
                        </div>
                        <div class="row2">
                                <div class="width40"><? echo $la['CLIENT_NUMBER']; ?></div>
                                <div class="width15"><input class="inputbox" id="settings_main_number"></div>
                        </div>
                        <div class="row2">
                                <div class="width40"><? echo 'Persona Moral'; ?></div>
                                <div class="width60"><input id="dialog_user_person" type="checkbox" class="checkbox" onChange="userAddCheck();" checked /></div>
                        </div>
                        <div class="row2">
                                <div class="width40"><? echo $la['SEND_CREDENTIALS']; ?></div>
                                <div class="width60"><input id="dialog_user_add_send" type="checkbox" class="checkbox" checked /></div>
                        </div>
                        <? if (($_SESSION["cpanel_privileges"] == 'super_admin') || ($_SESSION["cpanel_privileges"] == 'admin')) { ?>
                                <div class="row">
                                        <div class="title-block"><? echo $la['USER_PLAN']; ?></div>
                                        <div class="row2">
                                                <div class="width40"><? echo $la['PLAN_TYPE']; ?></div>
                                                <div class="width30">
                                                        <select id="dialog_user_add_account_plan" class="select width100">
                                                                <option value="Oro">Oro</option>
                                                                <option value="Bronce">Bronce</option>
                                                                <option value="Plata">Plata</option>
                                                        </select>
                                                </div>
                                        </div>
                                </div>
                        <? } ?>
                </div>
                <div id="settings_main_my_account">

                        <div class="row">
                                <div class="title-block"><? echo $la['COMPANY_INFO']; ?></div>
                                <div class="row2">
                                        <div class="width40"><? echo $la['COMPANY_NAME']; ?></div>
                                        <div class="width40"><input class="inputbox" id="settings_main_company"></div>
                                </div>
                                <div class="row2">
                                        <div class="width40"><? echo $la['BUSINESS_NAME']; ?></div>
                                        <div class="width40"><input class="inputbox" id="settings_main_business_name"></div>
                                </div>
                                <div class="row2">
                                        <div class="width40"><? echo $la['BUSINESS_RFC']; ?></div>
                                        <div class="width40"><input class="inputbox" id="settings_main_business_rfc"></div>
                                </div>
                                <div class="row2">
                                        <div class="width40"><? echo $la['ADDRESS']; ?></div>
                                        <div class="width40"><input class="inputbox" id="settings_main_address"></div>
                                </div>
                                <div class="row2">
                                        <div class="width40"><? echo $la['POST_CODE']; ?></div>
                                        <div class="width40"><input class="inputbox" id="settings_main_post_code"></div>
                                </div>
                                <div class="row2">
                                        <div class="width40"><? echo $la['CITY']; ?></div>
                                        <div class="width40"><input class="inputbox" id="settings_main_city"></div>
                                </div>
                                <div class="row2">
                                        <div class="width40"><? echo $la['COUNTRY_STATE']; ?></div>
                                        <div class="width40"><input class="inputbox" id="settings_main_country"></div>
                                </div>
                                <div class="row2">
                                        <div class="width40"><? echo $la['PHONE']; ?></div>
                                        <div class="width40"><input class="inputbox" id="settings_main_phone1"></div>
                                </div>
                                <div class="row2">
                                        <div class="width40"><? echo $la['EMAIL']; ?></div>
                                        <div class="width40"><input class="inputbox" id="settings_main_email1"></div>
                                </div>
                                <div class="row2">
                                        <div class="width40"><? echo $la['CONTACT_PAGE_URL'] ?></div>
                                        <div class="width40"><input class="inputbox" id="settings_main_web"></div>
                                </div>


                                <div class="title-block"><? echo $la['CONTACT_INFO']; ?></div>
                                <div class="row2">
                                        <div class="width40"><? echo $la['NAME_SURNAME']; ?></div>
                                        <div class="width40"><input class="inputbox" id="settings_main_name_surname"></div>
                                </div>
                                <div class="row2">
                                        <div class="width40"><? echo 'Telefono de Contacto'; ?></div>
                                        <div class="width40"><input class="inputbox" id="settings_main_phone2"></div>
                                </div>
                                <div class="row2">
                                        <div class="width40"><? echo 'E-mail Ofertas y Promociones' ?></div>
                                        <div class="width40"><input class="inputbox" id="settings_main_email2"></div>
                                </div>
                                <div class="row2">
                                        <div class="width40"><? echo $la['COMMENT']; ?></div>
                                        <div class="width40">
                                                <textarea id="dialog_add_user_account_comment" class="inputbox" style="height:70px;" maxlength="500" placeholder="<? echo $la['COMMENT_ABOUT_USER']; ?>"></textarea>
                                        </div>
                                </div>
                                <div class="title-block"><? echo $la['JOURNEY_DOCUMENTS']; ?></div>
                                <div class="row2">
                                        <div class="report-block block width50">
                                                <div class="row2">
                                                        <input class="button icon-save icon" type="button" value="<? echo 'Mi Contrato' ?>" onclick="settingsUsersDocumentsMiContrato();" />
                                                        <input id='dialog_DownloadUsersDocumentsMiContrato' class="button  icon-export icon" type="button" value="Descargar" onclick="downloadUsersDocuments('MiContrato');" />
                                                </div>
                                                <div class="row2">
                                                        <input class="button icon-save icon" type="button" value="<? echo 'Acta Constitutiva' ?>" onclick="settingsUsersDocumentsActaConstitutiva();" />
                                                        <input id='dialog_DownloadUsersDocumentsActaConstitutiva' class="button  icon-export icon" type="button" value="Descargar" onclick="downloadUsersDocuments('ActaConstitutiva');" />
                                                </div>
                                                <div class="row2">
                                                        <input class="button icon-save icon" type="button" value="<? echo 'Rep Legal' ?>" onclick="settingsUsersDocumentsRepLegal();" />
                                                        <input id='dialog_DownloadUsersDocumentsRepLegal' class="button  icon-export icon" type="button" value="Descargar" onclick="downloadUsersDocuments('RepLegal');" />
                                                </div>
                                                <div class="row2">
                                                        <input class="button icon-save icon" type="button" value="<? echo 'Identificación Oficial' ?>" onclick="settingsUsersDocumentsIdentificacionOficial();" />
                                                        <input id='dialog_DownloadUsersDocumentsIdentificacionOficial' class="button  icon-export icon" type="button" value="Descargar" onclick="downloadUsersDocuments('IdentificacionOficial');" />
                                                </div>
                                        </div>
                                        <div class="report-block block width50">
                                                <div class="row2">
                                                        <input class="button icon-save icon" type="button" value="<? echo 'Opinión Positiva' ?>" onclick="settingsUsersDocumentsOpinionPositiva();" />
                                                        <input id='dialog_DownloadUsersDocumentsOpinionPositiva' class="button  icon-export icon" type="button" value="Descargar" onclick="downloadUsersDocuments('OpinionPositiva');" />
                                                </div>
                                                <div class="row2">
                                                        <input class="button icon-save icon" type="button" value="<? echo 'Constancia Fiscal' ?>" onclick="settingsUsersDocumentsConstanciaFiscal();" />
                                                        <input id='dialog_DownloadUsersDocumentsConstanciaFiscal' class="button  icon-export icon" type="button" value="Descargar" onclick="downloadUsersDocuments('ConstanciaFiscal');" />
                                                </div>
                                                <div class="row2">
                                                        <input class="button icon-save icon" type="button" value="<? echo 'Domicilio Fiscal' ?>" onclick="settingsUsersDocumentsDomicilioFiscal();" />
                                                        <input id='dialog_DownloadUsersDocumentsDomicilioFiscal' class="button  icon-export icon" type="button" value="Descargar" onclick="downloadUsersDocuments('DomicilioFiscal');" />
                                                </div>
                                                <div class="row2">
                                                        <input class="button icon-save icon" type="button" value="<? echo 'Otros' ?>" onclick="settingsUsersDocumentsOtros();" />
                                                        <input id='dialog_DownloadUsersDocumentsOtros' class="button  icon-export icon" type="button" value="Descargar" onclick="downloadUsersDocuments('DomicilioFiscal');" />
                                                </div>

                                        </div>
                                </div>
                        </div>
                </div>
        </div>

        <center>
                <input class="button icon-new icon" type="button" onclick="userAdd('register');" value="<? echo $la['REGISTER']; ?>" />&nbsp;
                <input class="button icon-close icon" type="button" onclick="userAdd('cancel');" value="<? echo $la['CANCEL']; ?>" />
        </center>
</div>

<div id="dialog_user_edit" title="<? echo $la['EDIT_USER']; ?>">
        <div id="dialog_user_edit_tabs">
                <ul>
                        <li><a href="#dialog_user_edit_account"><? echo $la['ACCOUNT']; ?></a></li>
                        <li><a href="#dialog_user_edit_contact_info"><? echo $la['CONTACT_INFO']; ?></a></li>
                        <li><a href="#dialog_user_edit_subaccounts"><? echo $la['SUB_ACCOUNTS']; ?></a></li>
                        <li><a href="#dialog_user_edit_objects"><? echo $la['OBJECTS']; ?></a></li>
                        <? if ($_SESSION["billing"] == true) { ?>
                                <li><a href="#dialog_user_edit_billing_plans"><? echo $la['BILLING_PLANS']; ?></a></li>
                        <? } ?>
                        <li><a href="#dialog_user_edit_usage"><? echo $la['USAGE']; ?></a></li>
                </ul>

                <div id="dialog_user_edit_account">
                        <div class="controls">
                                <input class="button panel icon-save icon" type="button" onclick="userEdit('save');" value="<? echo $la['SAVE']; ?>">
                                <input class="button panel icon-key icon" type="button" onclick="userEditLogin();" value="<? echo $la['LOGIN_AS_USER']; ?>">
                        </div>
                        <div class="block width40">
                                <div class="container">
                                        <div class="row">
                                                <div class="title-block"><? echo $la['USER']; ?></div>
                                                <div class="row2">
                                                        <div class="width40"><? echo $la['ACTIVE']; ?></div>
                                                        <div class="width60"><input id="dialog_user_edit_account_active" class="checkbox" type="checkbox" /></div>
                                                </div>
                                                <div class="row2">
                                                        <div class="width40"><? echo $la['USERNAME']; ?></div>
                                                        <div class="width60"><input id="dialog_user_edit_account_username" class="inputbox" maxlength="50" /></div>
                                                </div>
                                                <div class="row2">
                                                        <div class="width40"><? echo $la['EMAIL']; ?></div>
                                                        <div class="width60"><input id="dialog_user_edit_account_email" class="inputbox" maxlength="50" /></div>
                                                </div>
                                                <div class="row2">
                                                        <div class="width40"><? echo $la['PASSWORD']; ?></div>
                                                        <div class="width60"><input id="dialog_user_edit_account_password" class="inputbox" maxlength="20" placeholder="<? echo $la['ENTER_NEW_PASSWORD']; ?>" /></div>
                                                </div>
                                                <div class="row2">
                                                        <div class="width40"><? echo $la['PRIVILEGES']; ?></div>
                                                        <div class="width60"><select id="dialog_user_edit_account_privileges" class="select width100" onChange="userEditCheck();"></select></div>
                                                </div>
                                                <? if (($_SESSION["cpanel_privileges"] == 'super_admin') || ($_SESSION["cpanel_privileges"] == 'admin')) { ?>
                                                        <div class="row2">
                                                                <div class="width40"><? echo $la['MANAGER']; ?></div>
                                                                <div class="width60"><select id="dialog_user_edit_account_manager_id" class="select width100" onChange="userEditCheck();"></select></div>
                                                        </div>
                                                <? } ?>
                                                <div class="row2">
                                                        <div class="width40">
                                                                <? echo $la['EXPIRE_ON']; ?>
                                                        </div>
                                                        <div class="width10">
                                                                <input id="dialog_user_edit_account_expire" type="checkbox" class="checkbox" onChange="userEditCheck();" />
                                                        </div>
                                                        <div class="width50">
                                                                <input class="inputbox-calendar inputbox width100" id="dialog_user_edit_account_expire_dt" />
                                                        </div>
                                                </div>
                                        </div>
                                        <? if (($_SESSION["cpanel_privileges"] == 'super_admin') || ($_SESSION["cpanel_privileges"] == 'admin')) { ?>
                                                <div class="row">
                                                        <div class="title-block"><? echo $la['MANAGER_PRIVILEGES']; ?></div>
                                                        <div class="row2">
                                                                <div class="width40"><? echo $la['BILLING']; ?></div>
                                                                <div class="width30">
                                                                        <select id="dialog_user_edit_account_manager_billing" class="select width100">
                                                                                <option value="true"><? echo $la['YES']; ?></option>
                                                                                <option value="false"><? echo $la['NO']; ?></option>
                                                                        </select>
                                                                </div>
                                                        </div>
                                                </div>
                                        <? } ?>
                                        <? if (($_SESSION["cpanel_privileges"] == 'super_admin') || ($_SESSION["cpanel_privileges"] == 'admin')) { ?>
                                                <div class="row">
                                                        <div class="title-block"><? echo $la['USER_PLAN']; ?></div>
                                                        <div class="row2">
                                                                <div class="width40"><? echo $la['PLAN_TYPE']; ?></div>
                                                                <div class="width30">
                                                                        <select id="dialog_user_edit_account_plan" class="select width100" onchange="applyUserPlanPermissions(this.value)">
                                                                                <option value="Personalizado">Personalizado</option>
                                                                                <option value="Bronce">Bronce</option>
                                                                                <option value="Plata">Plata</option>
                                                                                <option value="Oro">Oro</option>
                                                                        </select>
                                                                </div>
                                                        </div>
                                                </div>
                                        <? } ?>
                                </div>
                        </div>

                        <div class="block width60">
                                <div class="container last">
                                        <div class="row">
                                                <div class="title-block"><? echo $la['ACCOUNT_PRIVILEGES']; ?></div>
                                                <div style="height: 460px; overflow-y: scroll;">
                                                        <div class="row2">
                                                                <div class="width50">
                                                                        <? echo $la['USER_SHOW_UPDATE_DIALOG']; ?>
                                                                </div>
                                                                <div class="width20">
                                                                        <select id="dialog_user_edit_account_show_update_dialog" class="select width100" />
                                                                        <option value="true"><? echo $la['YES']; ?></option>
                                                                        <option value="false"><? echo $la['NO']; ?></option>
                                                                        </select>
                                                                </div>
                                                        </div>
                                                        <div class="row2">
                                                                <div class="width50">
                                                                        OSM Map
                                                                </div>
                                                                <div class="width20">
                                                                        <select id="dialog_user_edit_account_map_osm" class="select width100" />
                                                                        <option value="true"><? echo $la['YES']; ?></option>
                                                                        <option value="false"><? echo $la['NO']; ?></option>
                                                                        </select>
                                                                </div>
                                                        </div>
                                                        <div class="row2">
                                                                <div class="width50">
                                                                        Bing Maps
                                                                </div>
                                                                <div class="width20">
                                                                        <select id="dialog_user_edit_account_map_bing" class="select width100" />
                                                                        <option value="true"><? echo $la['YES']; ?></option>
                                                                        <option value="false"><? echo $la['NO']; ?></option>
                                                                        </select>
                                                                </div>
                                                        </div>
                                                        <div class="row2">
                                                                <div class="width50">
                                                                        Google Maps
                                                                </div>
                                                                <div class="width20">
                                                                        <select id="dialog_user_edit_account_map_google" class="select width100" />
                                                                        <option value="true"><? echo $la['YES']; ?></option>
                                                                        <option value="false"><? echo $la['NO']; ?></option>
                                                                        </select>
                                                                </div>
                                                        </div>
                                                        <div class="row2">
                                                                <div class="width50">
                                                                        Google Maps Street View
                                                                </div>
                                                                <div class="width20">
                                                                        <select id="dialog_user_edit_account_map_google_street_view" class="select width100" />
                                                                        <option value="true"><? echo $la['YES']; ?></option>
                                                                        <option value="false"><? echo $la['NO']; ?></option>
                                                                        </select>
                                                                </div>
                                                        </div>
                                                        <div class="row2">
                                                                <div class="width50">
                                                                        Google Maps Traffic
                                                                </div>
                                                                <div class="width20">
                                                                        <select id="dialog_user_edit_account_map_google_traffic" class="select width100" />
                                                                        <option value="true"><? echo $la['YES']; ?></option>
                                                                        <option value="false"><? echo $la['NO']; ?></option>
                                                                        </select>
                                                                </div>
                                                        </div>
                                                        <div class="row2">
                                                                <div class="width50">
                                                                        Mapbox Maps
                                                                </div>
                                                                <div class="width20">
                                                                        <select id="dialog_user_edit_account_map_mapbox" class="select width100" />
                                                                        <option value="true"><? echo $la['YES']; ?></option>
                                                                        <option value="false"><? echo $la['NO']; ?></option>
                                                                        </select>
                                                                </div>
                                                        </div>
                                                        <div class="row2">
                                                                <div class="width50">
                                                                        Yandex Map
                                                                </div>
                                                                <div class="width20">
                                                                        <select id="dialog_user_edit_account_map_yandex" class="select width100" />
                                                                        <option value="true"><? echo $la['YES']; ?></option>
                                                                        <option value="false"><? echo $la['NO']; ?></option>
                                                                        </select>
                                                                </div>
                                                        </div>
                                                        <? if (($_SESSION["cpanel_privileges"] == 'super_admin') || ($_SESSION["cpanel_privileges"] == 'admin')) { ?>
                                                                <div class="row2">
                                                                        <div class="width50"><? echo $la['ADD_OBJECTS']; ?></div>
                                                                        <div class="width20">
                                                                                <select id="dialog_user_edit_account_obj_add" class="select width100" onChange="userEditCheck();">
                                                                                        <option value="true"><? echo $la['YES']; ?></option>
                                                                                        <option value="false"><? echo $la['NO']; ?></option>
                                                                                        <option value="trial"><? echo $la['TRIAL']; ?></option>
                                                                                </select>
                                                                        </div>
                                                                </div>
                                                                <div class="row2">
                                                                        <div class="width50"><? echo $la['OBJECT_LIMIT']; ?></div>
                                                                        <div class="width20">
                                                                                <select id="dialog_user_edit_account_obj_limit" class="select width100" onChange="userEditCheck();">
                                                                                        <option value="true"><? echo $la['YES']; ?></option>
                                                                                        <option value="false"><? echo $la['NO']; ?></option>
                                                                                </select>
                                                                        </div>
                                                                        <div class="width2"></div>
                                                                        <div class="width20">
                                                                                <input id="dialog_user_edit_account_obj_limit_num" class="inputbox width100" onkeypress="return isNumberKey(event);" maxlength="4" />
                                                                        </div>
                                                                </div>
                                                                <div class="row2">
                                                                        <div class="width50"><? echo $la['OBJECT_DATE_LIMIT']; ?></div>
                                                                        <div class="width20">
                                                                                <select id="dialog_user_edit_account_obj_days" class="select width100" onChange="userEditCheck();">
                                                                                        <option value="true"><? echo $la['YES']; ?></option>
                                                                                        <option value="false"><? echo $la['NO']; ?></option>
                                                                                </select>
                                                                        </div>
                                                                        <div class="width2"></div>
                                                                        <div class="width20">
                                                                                <input class="inputbox-calendar inputbox width100" id="dialog_user_edit_account_obj_days_dt" />
                                                                        </div>
                                                                </div>
                                                        <? } ?>
                                                        <div class="row2">
                                                                <div class="width50"><? echo $la['EDIT_OBJECTS']; ?></div>
                                                                <div class="width20">
                                                                        <select id="dialog_user_edit_account_obj_edit" class="select width100">
                                                                                <option value="true"><? echo $la['YES']; ?></option>
                                                                                <option value="false"><? echo $la['NO']; ?></option>
                                                                        </select>
                                                                </div>
                                                        </div>
                                                        <div class="row2">
                                                                <div class="width50"><? echo $la['DELETE_OBJECTS']; ?></div>
                                                                <div class="width20">
                                                                        <select id="dialog_user_edit_account_obj_delete" class="select width100">
                                                                                <option value="true"><? echo $la['YES']; ?></option>
                                                                                <option value="false"><? echo $la['NO']; ?></option>
                                                                        </select>
                                                                </div>
                                                        </div>
                                                        <div class="row2">
                                                                <div class="width50"><? echo $la['CLEAR_OBJECTS_HISTORY']; ?></div>
                                                                <div class="width20">
                                                                        <select id="dialog_user_edit_account_obj_history_clear" class="select width100">
                                                                                <option value="true"><? echo $la['YES']; ?></option>
                                                                                <option value="false"><? echo $la['NO']; ?></option>
                                                                        </select>
                                                                </div>
                                                        </div>
                                                        <div class="row2">
                                                                <div class="width50"><? echo $la['DASHBOARD']; ?></div>
                                                                <div class="width20">
                                                                        <select id="dialog_user_edit_account_dashboard" class="select width100">
                                                                                <option value="true"><? echo $la['YES']; ?></option>
                                                                                <option value="false"><? echo $la['NO']; ?></option>
                                                                        </select>
                                                                </div>
                                                        </div>
                                                        <div class="row2">
                                                                <div class="width50"><? echo $la['HISTORY']; ?></div>
                                                                <div class="width20">
                                                                        <select id="dialog_user_edit_account_history" class="select width100">
                                                                                <option value="true"><? echo $la['YES']; ?></option>
                                                                                <option value="false"><? echo $la['NO']; ?></option>
                                                                        </select>
                                                                </div>
                                                        </div>
                                                        <div class="row2">
                                                                <div class="width50"><? echo $la['REPORTS']; ?></div>
                                                                <div class="width20">
                                                                        <select id="dialog_user_edit_account_reports" class="select width100">
                                                                                <option value="true"><? echo $la['YES']; ?></option>
                                                                                <option value="false"><? echo $la['NO']; ?></option>
                                                                        </select>
                                                                </div>
                                                        </div>
                                                        <div class="row2" style="display: none;">
                                                                <div class="width50"><? echo $la['TACHOGRAPH']; ?></div>
                                                                <div class="width20">
                                                                        <select id="dialog_user_edit_account_tachograph" class="select width100">
                                                                                <option value="true"><? echo $la['YES']; ?></option>
                                                                                <option value="false"><? echo $la['NO']; ?></option>
                                                                        </select>
                                                                </div>
                                                        </div>
                                                        <div class="row2">
                                                                <div class="width50"><? echo $la['TASKS']; ?></div>
                                                                <div class="width20">
                                                                        <select id="dialog_user_edit_account_tasks" class="select width100">
                                                                                <option value="true"><? echo $la['YES']; ?></option>
                                                                                <option value="false"><? echo $la['NO']; ?></option>
                                                                        </select>
                                                                </div>
                                                        </div>
                                                        <div class="row2">
                                                                <div class="width50"><? echo $la['RFID_AND_IBUTTON_LOGBOOK']; ?></div>
                                                                <div class="width20">
                                                                        <select id="dialog_user_edit_account_rilogbook" class="select width100">
                                                                                <option value="true"><? echo $la['YES']; ?></option>
                                                                                <option value="false"><? echo $la['NO']; ?></option>
                                                                        </select>
                                                                </div>
                                                        </div>
                                                        <div class="row2">
                                                                <div class="width50"><? echo $la['DIAGNOSTIC_TROUBLE_CODES']; ?></div>
                                                                <div class="width20">
                                                                        <select id="dialog_user_edit_account_dtc" class="select width100">
                                                                                <option value="true"><? echo $la['YES']; ?></option>
                                                                                <option value="false"><? echo $la['NO']; ?></option>
                                                                        </select>
                                                                </div>
                                                        </div>
                                                        <div class="row2">
                                                                <div class="width50"><? echo $la['MAINTENANCES']; ?></div>
                                                                <div class="width20">
                                                                        <select id="dialog_user_edit_account_maintenance" class="select width100">
                                                                                <option value="true"><? echo $la['YES']; ?></option>
                                                                                <option value="false"><? echo $la['NO']; ?></option>
                                                                        </select>
                                                                </div>
                                                        </div>
                                                        <div class="row2">
                                                                <div class="width50"><? echo $la['EXPENSES']; ?></div>
                                                                <div class="width20">
                                                                        <select id="dialog_user_edit_account_expenses" class="select width100">
                                                                                <option value="true"><? echo $la['YES']; ?></option>
                                                                                <option value="false"><? echo $la['NO']; ?></option>
                                                                        </select>
                                                                </div>
                                                        </div>
                                                        <div class="row2">
                                                                <div class="width50"><? echo $la['OBJECT_CONTROL']; ?></div>
                                                                <div class="width20">
                                                                        <select id="dialog_user_edit_account_object_control" class="select width100">
                                                                                <option value="true"><? echo $la['YES']; ?></option>
                                                                                <option value="false"><? echo $la['NO']; ?></option>
                                                                        </select>
                                                                </div>
                                                        </div>
                                                        <div class="row2">
                                                                <div class="width50"><? echo $la['IMAGE_GALLERY']; ?></div>
                                                                <div class="width20">
                                                                        <select id="dialog_user_edit_account_image_gallery" class="select width100">
                                                                                <option value="true"><? echo $la['YES']; ?></option>
                                                                                <option value="false"><? echo $la['NO']; ?></option>
                                                                        </select>
                                                                </div>
                                                        </div>
                                                        <div class="row2">
                                                                <div class="width50"><? echo $la['CHAT']; ?></div>
                                                                <div class="width20">
                                                                        <select id="dialog_user_edit_account_chat" class="select width100">
                                                                                <option value="true"><? echo $la['YES']; ?></option>
                                                                                <option value="false"><? echo $la['NO']; ?></option>
                                                                        </select>
                                                                </div>
                                                        </div>
                                                        <div class="row2">
                                                                <div class="width50"><? echo $la['SUB_ACCOUNTS']; ?></div>
                                                                <div class="width20">
                                                                        <select id="dialog_user_edit_account_subaccounts" class="select width100">
                                                                                <option value="true"><? echo $la['YES']; ?></option>
                                                                                <option value="false"><? echo $la['NO']; ?></option>
                                                                        </select>
                                                                </div>
                                                        </div>
                                                        <div class="row2">
                                                                <div class="width50"><? echo $la['SERVER_SMS_GATEWAY']; ?></div>
                                                                <div class="width20">
                                                                        <select id="dialog_user_edit_account_sms_gateway_server" class="select width100">
                                                                                <option value="true"><? echo $la['YES']; ?></option>
                                                                                <option value="false"><? echo $la['NO']; ?></option>
                                                                        </select>
                                                                </div>
                                                        </div>
                                                        <div class="row2">
                                                                <div class="width50"><? echo $la['API']; ?></div>
                                                                <div class="width20">
                                                                        <select id="dialog_user_edit_api_active" class="select width100">
                                                                                <option value="true"><? echo $la['YES']; ?></option>
                                                                                <option value="false"><? echo $la['NO']; ?></option>
                                                                        </select>
                                                                </div>
                                                        </div>
                                                        <div class="row2">
                                                                <div class="width50"><? echo $la['API_KEY']; ?></div>
                                                                <div class="width50">
                                                                        <input id="dialog_user_edit_api_key" class="inputbox width100" readOnly="true" />
                                                                </div>
                                                        </div>
                                                        <div class="row2">
                                                                <div class="width50">
                                                                        <? echo $la['MAX_MARKERS']; ?>
                                                                </div>
                                                                <div class="width20">
                                                                        <input id="dialog_user_edit_places_markers" class="inputbox width100" onkeypress="return isNumberKey(event);" maxlength="5" />
                                                                </div>
                                                        </div>
                                                        <div class="row2">
                                                                <div class="width50">
                                                                        <? echo $la['MAX_ROUTES']; ?>
                                                                </div>
                                                                <div class="width20">
                                                                        <input id="dialog_user_edit_places_routes" class="inputbox width100" onkeypress="return isNumberKey(event);" maxlength="5" />
                                                                </div>
                                                        </div>
                                                        <div class="row2">
                                                                <div class="width50">
                                                                        <? echo $la['MAX_ZONES']; ?>
                                                                </div>
                                                                <div class="width20">
                                                                        <input id="dialog_user_edit_places_zones" class="inputbox width100" onkeypress="return isNumberKey(event);" maxlength="5" />
                                                                </div>
                                                        </div>
                                                        <div class="row2">
                                                                <div class="width50">
                                                                        <? echo $la['MAX_EMAILS_DAILY']; ?>
                                                                </div>
                                                                <div class="width20">
                                                                        <input id="dialog_user_edit_usage_email_daily" class="inputbox width100" onkeypress="return isNumberKey(event);" maxlength="8" />
                                                                </div>
                                                        </div>
                                                        <div class="row2">
                                                                <div class="width50">
                                                                        <? echo $la['MAX_SMS_DAILY']; ?>
                                                                </div>
                                                                <div class="width20">
                                                                        <input id="dialog_user_edit_usage_sms_daily" class="inputbox width100" onkeypress="return isNumberKey(event);" maxlength="8" />
                                                                </div>
                                                        </div>
                                                        <div class="row2">
                                                                <div class="width50">
                                                                        <? echo $la['MAX_WEBHOOK_DAILY']; ?>
                                                                </div>
                                                                <div class="width20">
                                                                        <input id="dialog_user_edit_usage_webhook_daily" class="inputbox width100" onkeypress="return isNumberKey(event);" maxlength="8" />
                                                                </div>
                                                        </div>
                                                        <div class="row2">
                                                                <div class="width50">
                                                                        <? echo $la['MAX_API_DAILY']; ?>
                                                                </div>
                                                                <div class="width20">
                                                                        <input id="dialog_user_edit_usage_api_daily" class="inputbox width100" onkeypress="return isNumberKey(event);" maxlength="8" />
                                                                </div>
                                                        </div>

                                                        <!-- ===================== Permisos - Bronce ===================== -->
                                                        <div class="title-block">Permisos - Bronce</div>

                                                        <div class="row2">
                                                                <div class="width50">Eventos</div>
                                                                <div class="width20">
                                                                        <select id="dialog_user_edit_account_perm_eventos" class="select width100">
                                                                                <option value="true"><? echo $la['YES']; ?></option>
                                                                                <option value="false"><? echo $la['NO']; ?></option>
                                                                        </select>
                                                                </div>
                                                        </div>

                                                        <div class="row2">
                                                                <div class="width50">Alertas</div>
                                                                <div class="width20">
                                                                        <select id="dialog_user_edit_account_perm_alertas" class="select width100">
                                                                                <option value="true"><? echo $la['YES']; ?></option>
                                                                                <option value="false"><? echo $la['NO']; ?></option>
                                                                        </select>
                                                                </div>
                                                        </div>

                                                        <div class="row2">
                                                                <div class="width50">Geocercas</div>
                                                                <div class="width20">
                                                                        <select id="dialog_user_edit_account_perm_geocercas" class="select width100">
                                                                                <option value="true"><? echo $la['YES']; ?></option>
                                                                                <option value="false"><? echo $la['NO']; ?></option>
                                                                        </select>
                                                                </div>
                                                        </div>

                                                        <div class="row2">
                                                                <div class="width50">Historial</div>
                                                                <div class="width20">
                                                                        <select id="dialog_user_edit_account_perm_historial" class="select width100">
                                                                                <option value="true"><? echo $la['YES']; ?></option>
                                                                                <option value="false"><? echo $la['NO']; ?></option>
                                                                        </select>
                                                                </div>
                                                        </div>

                                                        <!-- ===================== Permisos - Lista de GPS ===================== -->
                                                        <div class="title-block">Permisos - Lista de GPS</div>

                                                        <div class="row2">
                                                                <div class="width50">Buscar GPS</div>
                                                                <div class="width20">
                                                                        <select id="dialog_user_edit_account_perm_buscar_gps" class="select width100">
                                                                                <option value="true"><? echo $la['YES']; ?></option>
                                                                                <option value="false"><? echo $la['NO']; ?></option>
                                                                        </select>
                                                                </div>
                                                        </div>

                                                        <div class="row2">
                                                                <div class="width50">Refrescar</div>
                                                                <div class="width20">
                                                                        <select id="dialog_user_edit_account_perm_refrescar" class="select width100">
                                                                                <option value="true"><? echo $la['YES']; ?></option>
                                                                                <option value="false"><? echo $la['NO']; ?></option>
                                                                        </select>
                                                                </div>
                                                        </div>

                                                        <div class="row2">
                                                                <div class="width50">Compartir unidad</div>
                                                                <div class="width20">
                                                                        <select id="dialog_user_edit_account_perm_compartir_unidad" class="select width100">
                                                                                <option value="true"><? echo $la['YES']; ?></option>
                                                                                <option value="false"><? echo $la['NO']; ?></option>
                                                                        </select>
                                                                </div>
                                                        </div>

                                                        <div class="row2">
                                                                <div class="width50">Agregar unidad</div>
                                                                <div class="width20">
                                                                        <select id="dialog_user_edit_account_perm_agregar_unidad" class="select width100">
                                                                                <option value="true"><? echo $la['YES']; ?></option>
                                                                                <option value="false"><? echo $la['NO']; ?></option>
                                                                        </select>
                                                                </div>
                                                        </div>

                                                        <div class="row2">
                                                                <div class="width50">Mostrar / Ocultar</div>
                                                                <div class="width20">
                                                                        <select id="dialog_user_edit_account_perm_mostrar_ocultar" class="select width100">
                                                                                <option value="true"><? echo $la['YES']; ?></option>
                                                                                <option value="false"><? echo $la['NO']; ?></option>
                                                                        </select>
                                                                </div>
                                                        </div>

                                                        <div class="row2">
                                                                <div class="width50">Seguimiento</div>
                                                                <div class="width20">
                                                                        <select id="dialog_user_edit_account_perm_seguimiento" class="select width100">
                                                                                <option value="true"><? echo $la['YES']; ?></option>
                                                                                <option value="false"><? echo $la['NO']; ?></option>
                                                                        </select>
                                                                </div>
                                                        </div>

                                                        <!-- ===================== Permisos - Editar Lista de GPS ===================== -->
                                                        <div class="title-block">Permisos - Editar Lista de GPS</div>

                                                        <div class="row2">
                                                                <div class="width50">Historial (Editar)</div>
                                                                <div class="width20">
                                                                        <select id="dialog_user_edit_account_perm_historial_edit" class="select width100">
                                                                                <option value="true"><? echo $la['YES']; ?></option>
                                                                                <option value="false"><? echo $la['NO']; ?></option>
                                                                        </select>
                                                                </div>
                                                        </div>

                                                        <div class="row2">
                                                                <div class="width50">Vista calle</div>
                                                                <div class="width20">
                                                                        <select id="dialog_user_edit_account_perm_vista_calle" class="select width100">
                                                                                <option value="true"><? echo $la['YES']; ?></option>
                                                                                <option value="false"><? echo $la['NO']; ?></option>
                                                                        </select>
                                                                </div>
                                                        </div>

                                                        <div class="row2">
                                                                <div class="width50">Enviar comando</div>
                                                                <div class="width20">
                                                                        <select id="dialog_user_edit_account_perm_enviar_comando" class="select width100">
                                                                                <option value="true"><? echo $la['YES']; ?></option>
                                                                                <option value="false"><? echo $la['NO']; ?></option>
                                                                        </select>
                                                                </div>
                                                        </div>

                                                        <div class="row2">
                                                                <div class="width50">Editar</div>
                                                                <div class="width20">
                                                                        <select id="dialog_user_edit_account_perm_editar" class="select width100">
                                                                                <option value="true"><? echo $la['YES']; ?></option>
                                                                                <option value="false"><? echo $la['NO']; ?></option>
                                                                        </select>
                                                                </div>
                                                        </div>

                                                        <div class="row2">
                                                                <div class="width50">Ver en vivo</div>
                                                                <div class="width20">
                                                                        <select id="dialog_user_edit_account_perm_ver_en_vivo" class="select width100">
                                                                                <option value="true"><? echo $la['YES']; ?></option>
                                                                                <option value="false"><? echo $la['NO']; ?></option>
                                                                        </select>
                                                                </div>
                                                        </div>

                                                        <div class="row2">
                                                                <div class="width50">Ver eventos cámara</div>
                                                                <div class="width20">
                                                                        <select id="dialog_user_edit_account_perm_ver_eventos_camara" class="select width100">
                                                                                <option value="true"><? echo $la['YES']; ?></option>
                                                                                <option value="false"><? echo $la['NO']; ?></option>
                                                                        </select>
                                                                </div>
                                                        </div>

                                                        <div class="row2">
                                                                <div class="width50">Lista datos</div>
                                                                <div class="width20">
                                                                        <select id="dialog_user_edit_account_perm_lista_datos" class="select width100">
                                                                                <option value="true"><? echo $la['YES']; ?></option>
                                                                                <option value="false"><? echo $la['NO']; ?></option>
                                                                        </select>
                                                                </div>
                                                        </div>

                                                        <div class="row2">
                                                                <div class="width50">Lista marcadores</div>
                                                                <div class="width20">
                                                                        <select id="dialog_user_edit_account_perm_lista_marcadores" class="select width100">
                                                                                <option value="true"><? echo $la['YES']; ?></option>
                                                                                <option value="false"><? echo $la['NO']; ?></option>
                                                                        </select>
                                                                </div>
                                                        </div>

                                                        <div class="row2">
                                                                <div class="width50">Lista zonas</div>
                                                                <div class="width20">
                                                                        <select id="dialog_user_edit_account_perm_lista_zonas" class="select width100">
                                                                                <option value="true"><? echo $la['YES']; ?></option>
                                                                                <option value="false"><? echo $la['NO']; ?></option>
                                                                        </select>
                                                                </div>
                                                        </div>

                                                        <div class="row2">
                                                                <div class="width50">Lista rutas</div>
                                                                <div class="width20">
                                                                        <select id="dialog_user_edit_account_perm_lista_rutas" class="select width100">
                                                                                <option value="true"><? echo $la['YES']; ?></option>
                                                                                <option value="false"><? echo $la['NO']; ?></option>
                                                                        </select>
                                                                </div>
                                                        </div>

                                                        <!-- ===================== Permisos - Top Panel ===================== -->
                                                        <div class="title-block">Permisos - Top Panel</div>

                                                        <div class="row2">
                                                                <div class="width50">Acerca de</div>
                                                                <div class="width20">
                                                                        <select id="dialog_user_edit_account_perm_acerca_de" class="select width100">
                                                                                <option value="true"><? echo $la['YES']; ?></option>
                                                                                <option value="false"><? echo $la['NO']; ?></option>
                                                                        </select>
                                                                </div>
                                                        </div>

                                                        <div class="row2">
                                                                <div class="width50">Info</div>
                                                                <div class="width20">
                                                                        <select id="dialog_user_edit_account_perm_info" class="select width100">
                                                                                <option value="true"><? echo $la['YES']; ?></option>
                                                                                <option value="false"><? echo $la['NO']; ?></option>
                                                                        </select>
                                                                </div>
                                                        </div>

                                                        <div class="row2">
                                                                <div class="width50">Configuración</div>
                                                                <div class="width20">
                                                                        <select id="dialog_user_edit_account_perm_configuracion" class="select width100">
                                                                                <option value="true"><? echo $la['YES']; ?></option>
                                                                                <option value="false"><? echo $la['NO']; ?></option>
                                                                        </select>
                                                                </div>
                                                        </div>

                                                        <div class="row2">
                                                                <div class="width50">Coordenadas</div>
                                                                <div class="width20">
                                                                        <select id="dialog_user_edit_account_perm_coordenadas" class="select width100">
                                                                                <option value="true"><? echo $la['YES']; ?></option>
                                                                                <option value="false"><? echo $la['NO']; ?></option>
                                                                        </select>
                                                                </div>
                                                        </div>

                                                        <div class="row2">
                                                                <div class="width50">Buscar</div>
                                                                <div class="width20">
                                                                        <select id="dialog_user_edit_account_perm_buscar" class="select width100">
                                                                                <option value="true"><? echo $la['YES']; ?></option>
                                                                                <option value="false"><? echo $la['NO']; ?></option>
                                                                        </select>
                                                                </div>
                                                        </div>

                                                        <div class="row2">
                                                                <div class="width50">Reportes</div>
                                                                <div class="width20">
                                                                        <select id="dialog_user_edit_account_perm_reportes" class="select width100">
                                                                                <option value="true"><? echo $la['YES']; ?></option>
                                                                                <option value="false"><? echo $la['NO']; ?></option>
                                                                        </select>
                                                                </div>
                                                        </div>

                                                        <div class="row2">
                                                                <div class="width50">Tareas</div>
                                                                <div class="width20">
                                                                        <select id="dialog_user_edit_account_perm_tareas" class="select width100">
                                                                                <option value="true"><? echo $la['YES']; ?></option>
                                                                                <option value="false"><? echo $la['NO']; ?></option>
                                                                        </select>
                                                                </div>
                                                        </div>

                                                        <div class="row2">
                                                                <div class="width50">Mantenimientos</div>
                                                                <div class="width20">
                                                                        <select id="dialog_user_edit_account_perm_mantenimientos" class="select width100">
                                                                                <option value="true"><? echo $la['YES']; ?></option>
                                                                                <option value="false"><? echo $la['NO']; ?></option>
                                                                        </select>
                                                                </div>
                                                        </div>

                                                        <div class="row2">
                                                                <div class="width50">Comandos</div>
                                                                <div class="width20">
                                                                        <select id="dialog_user_edit_account_perm_comandos" class="select width100">
                                                                                <option value="true"><? echo $la['YES']; ?></option>
                                                                                <option value="false"><? echo $la['NO']; ?></option>
                                                                        </select>
                                                                </div>
                                                        </div>

                                                        <div class="row2">
                                                                <div class="width50">Gráfica combustible</div>
                                                                <div class="width20">
                                                                        <select id="dialog_user_edit_account_perm_grafica_combustible" class="select width100">
                                                                                <option value="true"><? echo $la['YES']; ?></option>
                                                                                <option value="false"><? echo $la['NO']; ?></option>
                                                                        </select>
                                                                </div>
                                                        </div>

                                                        <div class="row2">
                                                                <div class="width50">Gráfica temperatura</div>
                                                                <div class="width20">
                                                                        <select id="dialog_user_edit_account_perm_grafica_temperatura" class="select width100">
                                                                                <option value="true"><? echo $la['YES']; ?></option>
                                                                                <option value="false"><? echo $la['NO']; ?></option>
                                                                        </select>
                                                                </div>
                                                        </div>

                                                        <div class="row2">
                                                                <div class="width50">Imágenes</div>
                                                                <div class="width20">
                                                                        <select id="dialog_user_edit_account_perm_imagenes" class="select width100">
                                                                                <option value="true"><? echo $la['YES']; ?></option>
                                                                                <option value="false"><? echo $la['NO']; ?></option>
                                                                        </select>
                                                                </div>
                                                        </div>

                                                        <div class="row2">
                                                                <div class="width50">Chat</div>
                                                                <div class="width20">
                                                                        <select id="dialog_user_edit_account_perm_chat" class="select width100">
                                                                                <option value="true"><? echo $la['YES']; ?></option>
                                                                                <option value="false"><? echo $la['NO']; ?></option>
                                                                        </select>
                                                                </div>
                                                        </div>

                                                        <div class="row2">
                                                                <div class="width50">Lenguaje</div>
                                                                <div class="width20">
                                                                        <select id="dialog_user_edit_account_perm_lenguaje" class="select width100">
                                                                                <option value="true"><? echo $la['YES']; ?></option>
                                                                                <option value="false"><? echo $la['NO']; ?></option>
                                                                        </select>
                                                                </div>
                                                        </div>

                                                        <div class="row2">
                                                                <div class="width50">Mi cuenta</div>
                                                                <div class="width20">
                                                                        <select id="dialog_user_edit_account_perm_mi_cuenta" class="select width100">
                                                                                <option value="true"><? echo $la['YES']; ?></option>
                                                                                <option value="false"><? echo $la['NO']; ?></option>
                                                                        </select>
                                                                </div>
                                                        </div>

                                                        <div class="row2">
                                                                <div class="width50">Versión celular</div>
                                                                <div class="width20">
                                                                        <select id="dialog_user_edit_account_perm_version_celular" class="select width100">
                                                                                <option value="true"><? echo $la['YES']; ?></option>
                                                                                <option value="false"><? echo $la['NO']; ?></option>
                                                                        </select>
                                                                </div>
                                                        </div>

                                                        <!-- ===================== Permisos - Editar Top Panel ===================== -->
                                                        <div class="title-block">Permisos - Editar Top Panel</div>

                                                        <div class="row2">
                                                                <div class="width50">GPS</div>
                                                                <div class="width20">
                                                                        <select id="dialog_user_edit_account_perm_gps" class="select width100">
                                                                                <option value="true"><? echo $la['YES']; ?></option>
                                                                                <option value="false"><? echo $la['NO']; ?></option>
                                                                        </select>
                                                                </div>
                                                        </div>

                                                        <div class="row2">
                                                                <div class="width50">Plantillas</div>
                                                                <div class="width20">
                                                                        <select id="dialog_user_edit_account_perm_plantillas" class="select width100">
                                                                                <option value="true"><? echo $la['YES']; ?></option>
                                                                                <option value="false"><? echo $la['NO']; ?></option>
                                                                        </select>
                                                                </div>
                                                        </div>

                                                        <div class="row2">
                                                                <div class="width50">KML</div>
                                                                <div class="width20">
                                                                        <select id="dialog_user_edit_account_perm_kml" class="select width100">
                                                                                <option value="true"><? echo $la['YES']; ?></option>
                                                                                <option value="false"><? echo $la['NO']; ?></option>
                                                                        </select>
                                                                </div>
                                                        </div>

                                                        <div class="row2">
                                                                <div class="width50">GPRS</div>
                                                                <div class="width20">
                                                                        <select id="dialog_user_edit_account_perm_gprs" class="select width100">
                                                                                <option value="true"><? echo $la['YES']; ?></option>
                                                                                <option value="false"><? echo $la['NO']; ?></option>
                                                                        </select>
                                                                </div>
                                                        </div>

                                                        <div class="row2">
                                                                <div class="width50">SMS</div>
                                                                <div class="width20">
                                                                        <select id="dialog_user_edit_account_perm_sms" class="select width100">
                                                                                <option value="true"><? echo $la['YES']; ?></option>
                                                                                <option value="false"><? echo $la['NO']; ?></option>
                                                                        </select>
                                                                </div>
                                                        </div>

                                                        <div class="row2">
                                                                <div class="width50">Crear eventos</div>
                                                                <div class="width20">
                                                                        <select id="dialog_user_edit_account_perm_crear_eventos" class="select width100">
                                                                                <option value="true"><? echo $la['YES']; ?></option>
                                                                                <option value="false"><? echo $la['NO']; ?></option>
                                                                        </select>
                                                                </div>
                                                        </div>

                                                        <div class="row2">
                                                                <div class="width50">Interfaz usuario</div>
                                                                <div class="width20">
                                                                        <select id="dialog_user_edit_account_perm_interfaz_usuario" class="select width100">
                                                                                <option value="true"><? echo $la['YES']; ?></option>
                                                                                <option value="false"><? echo $la['NO']; ?></option>
                                                                        </select>
                                                                </div>
                                                        </div>

                                                        <div class="row2">
                                                                <div class="width50">Subcuentas</div>
                                                                <div class="width20">
                                                                        <select id="dialog_user_edit_account_perm_subcuentas" class="select width100">
                                                                                <option value="true"><? echo $la['YES']; ?></option>
                                                                                <option value="false"><? echo $la['NO']; ?></option>
                                                                        </select>
                                                                </div>
                                                        </div>

                                                        <div class="row2">
                                                                <div class="width50">Grupos</div>
                                                                <div class="width20">
                                                                        <select id="dialog_user_edit_account_perm_grupos" class="select width100">
                                                                                <option value="true"><? echo $la['YES']; ?></option>
                                                                                <option value="false"><? echo $la['NO']; ?></option>
                                                                        </select>
                                                                </div>
                                                        </div>

                                                        <div class="row2">
                                                                <div class="width50">Conductor</div>
                                                                <div class="width20">
                                                                        <select id="dialog_user_edit_account_perm_conductor" class="select width100">
                                                                                <option value="true"><? echo $la['YES']; ?></option>
                                                                                <option value="false"><? echo $la['NO']; ?></option>
                                                                        </select>
                                                                </div>
                                                        </div>

                                                        <div class="row2">
                                                                <div class="width50">Pasajero</div>
                                                                <div class="width20">
                                                                        <select id="dialog_user_edit_account_perm_pasajero" class="select width100">
                                                                                <option value="true"><? echo $la['YES']; ?></option>
                                                                                <option value="false"><? echo $la['NO']; ?></option>
                                                                        </select>
                                                                </div>
                                                        </div>

                                                        <div class="row2">
                                                                <div class="width50">Trailer</div>
                                                                <div class="width20">
                                                                        <select id="dialog_user_edit_account_perm_trailer" class="select width100">
                                                                                <option value="true"><? echo $la['YES']; ?></option>
                                                                                <option value="false"><? echo $la['NO']; ?></option>
                                                                        </select>
                                                                </div>
                                                        </div>

                                                        <div class="row2">
                                                                <div class="width50">Logs</div>
                                                                <div class="width20">
                                                                        <select id="dialog_user_edit_account_perm_logs" class="select width100">
                                                                                <option value="true"><? echo $la['YES']; ?></option>
                                                                                <option value="false"><? echo $la['NO']; ?></option>
                                                                        </select>
                                                                </div>
                                                        </div>

                                                        <!-- ===================== Permisos - Reportes ===================== -->
                                                        <div class="title-block">Permisos - Reportes</div>

                                                        <div class="row2">
                                                                <div class="width50">Crear reportes</div>
                                                                <div class="width20">
                                                                        <select id="dialog_user_edit_account_perm_crear_reportes" class="select width100">
                                                                                <option value="true"><? echo $la['YES']; ?></option>
                                                                                <option value="false"><? echo $la['NO']; ?></option>
                                                                        </select>
                                                                </div>
                                                        </div>

                                                        <div class="row2">
                                                                <div class="width50">Ver reportes</div>
                                                                <div class="width20">
                                                                        <select id="dialog_user_edit_account_perm_ver_reportes" class="select width100">
                                                                                <option value="true"><? echo $la['YES']; ?></option>
                                                                                <option value="false"><? echo $la['NO']; ?></option>
                                                                        </select>
                                                                </div>
                                                        </div>

                                                        <div class="row2">
                                                                <div class="width50">Eliminar reportes</div>
                                                                <div class="width20">
                                                                        <select id="dialog_user_edit_account_perm_eliminar_reportes" class="select width100">
                                                                                <option value="true"><? echo $la['YES']; ?></option>
                                                                                <option value="false"><? echo $la['NO']; ?></option>
                                                                        </select>
                                                                </div>
                                                        </div>

                                                        <div class="row2">
                                                                <div class="width50">Editar reportes</div>
                                                                <div class="width20">
                                                                        <select id="dialog_user_edit_account_perm_editar_reportes" class="select width100">
                                                                                <option value="true"><? echo $la['YES']; ?></option>
                                                                                <option value="false"><? echo $la['NO']; ?></option>
                                                                        </select>
                                                                </div>
                                                        </div>

                                                        <!-- ===================== Permisos - Mantenimiento ===================== -->
                                                        <div class="title-block">Permisos - Mantenimiento</div>

                                                        <div class="row2">
                                                                <div class="width50">Crear mantenimiento</div>
                                                                <div class="width20">
                                                                        <select id="dialog_user_edit_account_perm_crear_mantenimiento" class="select width100">
                                                                                <option value="true"><? echo $la['YES']; ?></option>
                                                                                <option value="false"><? echo $la['NO']; ?></option>
                                                                        </select>
                                                                </div>
                                                        </div>

                                                        <div class="row2">
                                                                <div class="width50">Eliminar mantenimientos</div>
                                                                <div class="width20">
                                                                        <select id="dialog_user_edit_account_perm_eliminar_mantenimientos" class="select width100">
                                                                                <option value="true"><? echo $la['YES']; ?></option>
                                                                                <option value="false"><? echo $la['NO']; ?></option>
                                                                        </select>
                                                                </div>
                                                        </div>

                                                        <div class="row2">
                                                                <div class="width50">Editar mantenimientos</div>
                                                                <div class="width20">
                                                                        <select id="dialog_user_edit_account_perm_editar_mantenimientos" class="select width100">
                                                                                <option value="true"><? echo $la['YES']; ?></option>
                                                                                <option value="false"><? echo $la['NO']; ?></option>
                                                                        </select>
                                                                </div>
                                                        </div>

                                                        <!-- ===================== Permisos - Comandos ===================== -->
                                                        <div class="title-block">Permisos - Comandos</div>

                                                        <div class="row2">
                                                                <div class="width50">Crear comandos GPRS</div>
                                                                <div class="width20">
                                                                        <select id="dialog_user_edit_account_perm_crear_comandos_gprs" class="select width100">
                                                                                <option value="true"><? echo $la['YES']; ?></option>
                                                                                <option value="false"><? echo $la['NO']; ?></option>
                                                                        </select>
                                                                </div>
                                                        </div>

                                                        <div class="row2">
                                                                <div class="width50">Crear comandos SMS</div>
                                                                <div class="width20">
                                                                        <select id="dialog_user_edit_account_perm_crear_comandos_sms" class="select width100">
                                                                                <option value="true"><? echo $la['YES']; ?></option>
                                                                                <option value="false"><? echo $la['NO']; ?></option>
                                                                        </select>
                                                                </div>
                                                        </div>

                                                        <div class="row2">
                                                                <div class="width50">Plantillas comandos</div>
                                                                <div class="width20">
                                                                        <select id="dialog_user_edit_account_perm_plantillas_comandos" class="select width100">
                                                                                <option value="true"><? echo $la['YES']; ?></option>
                                                                                <option value="false"><? echo $la['NO']; ?></option>
                                                                        </select>
                                                                </div>
                                                        </div>

                                                        <div class="row2">
                                                                <div class="width50">Programar comandos</div>
                                                                <div class="width20">
                                                                        <select id="dialog_user_edit_account_perm_programar_comandos" class="select width100">
                                                                                <option value="true"><? echo $la['YES']; ?></option>
                                                                                <option value="false"><? echo $la['NO']; ?></option>
                                                                        </select>
                                                                </div>
                                                        </div>

                                                        <!-- ===================== Permisos - Mapa ===================== -->
                                                        <div class="title-block">Permisos - Mapa</div>

                                                        <div class="row2">
                                                                <div class="width50">Activar / Desactivar tools</div>
                                                                <div class="width20">
                                                                        <select id="dialog_user_edit_account_perm_activar_desactivar_tools" class="select width100">
                                                                                <option value="true"><? echo $la['YES']; ?></option>
                                                                                <option value="false"><? echo $la['NO']; ?></option>
                                                                        </select>
                                                                </div>
                                                        </div>

                                                        <div class="row2">
                                                                <div class="width50">Marcadores mapa</div>
                                                                <div class="width20">
                                                                        <select id="dialog_user_edit_account_perm_marcadores_mapa" class="select width100">
                                                                                <option value="true"><? echo $la['YES']; ?></option>
                                                                                <option value="false"><? echo $la['NO']; ?></option>
                                                                        </select>
                                                                </div>
                                                        </div>

                                                        <div class="row2">
                                                                <div class="width50">Rutas mapa</div>
                                                                <div class="width20">
                                                                        <select id="dialog_user_edit_account_perm_rutas_mapa" class="select width100">
                                                                                <option value="true"><? echo $la['YES']; ?></option>
                                                                                <option value="false"><? echo $la['NO']; ?></option>
                                                                        </select>
                                                                </div>
                                                        </div>

                                                        <div class="row2">
                                                                <div class="width50">Geocercas mapa</div>
                                                                <div class="width20">
                                                                        <select id="dialog_user_edit_account_perm_geocercas_mapa" class="select width100">
                                                                                <option value="true"><? echo $la['YES']; ?></option>
                                                                                <option value="false"><? echo $la['NO']; ?></option>
                                                                        </select>
                                                                </div>
                                                        </div>
                                                </div>
                                        </div>
                                </div>
                        </div>
                </div>

                <div id="dialog_user_edit_contact_info">
                        <input type="hidden" id="dialog_task_doc1" name="dialog_task_doc1" value="" />
                        <div style="height: 460px; overflow-y: scroll;">
                                <div class="row2">
                                        <div class="width40"><? echo 'Persona Moral'; ?></div>
                                        <div class="width60"><input id="dialog_user_edit_account_contact_person" type="checkbox" class="checkbox" onChange="userAddCheck();" /></div>
                                </div>
                                <div class="row2">
                                        <div class="width40">
                                                <?php echo $la['CLIENT_NUMBER']; ?>
                                        </div>
                                        <div class="width15"><input class="inputbox" id="dialog_user_edit_account_contact_number"></div>
                                </div>
                                <div class="block width100">
                                        <div class="container last">
                                                <div class="row">
                                                        <div class="title-block"><?php echo $la['COMPANY_INFO']; ?>
                                                        </div>
                                                        <div class="row2">
                                                                <div class="width40">
                                                                        <?php echo $la['COMPANY_NAME']; ?>
                                                                </div>
                                                                <div class="width40"><input class="inputbox" id="dialog_user_edit_account_contact_company"></div>
                                                        </div>
                                                        <div class="row2">
                                                                <div class="width40">
                                                                        <?php echo $la['BUSINESS_NAME']; ?>
                                                                </div>
                                                                <div class="width40"><input class="inputbox" id="dialog_user_edit_account_contact_business_name">
                                                                </div>
                                                        </div>
                                                        <div class="row2">
                                                                <div class="width40">
                                                                        <?php echo $la['BUSINESS_RFC']; ?>
                                                                </div>
                                                                <div class="width40"><input class="inputbox" id="dialog_user_edit_account_contact_business_rfc">
                                                                </div>
                                                        </div>
                                                        <div class="row2">
                                                                <div class="width40">
                                                                        <?php echo $la['ADDRESS']; ?>
                                                                </div>
                                                                <div class="width40"><input class="inputbox" id="dialog_user_edit_account_contact_address"></div>
                                                        </div>
                                                        <div class="row2">
                                                                <div class="width40">
                                                                        <?php echo $la['POST_CODE']; ?>
                                                                </div>
                                                                <div class="width40"><input class="inputbox" id="dialog_user_edit_account_contact_post_code"></div>
                                                        </div>
                                                        <div class="row2">
                                                                <div class="width40">
                                                                        <?php echo $la['CITY']; ?>
                                                                </div>
                                                                <div class="width40"><input class="inputbox" id="dialog_user_edit_account_contact_city"></div>
                                                        </div>
                                                        <div class="row2">
                                                                <div class="width40">
                                                                        <?php echo $la['COUNTRY_STATE']; ?>
                                                                </div>
                                                                <div class="width40"><input class="inputbox" id="dialog_user_edit_account_contact_country"></div>
                                                        </div>
                                                        <div class="row2">
                                                                <div class="width40">
                                                                        <?php echo $la['PHONE']; ?>
                                                                </div>
                                                                <div class="width40"><input class="inputbox" id="dialog_user_edit_account_contact_phone1"></div>
                                                        </div>
                                                        <div class="row2">
                                                                <div class="width40">
                                                                        <?php echo $la['EMAIL']; ?>
                                                                </div>
                                                                <div class="width40"><input class="inputbox" id="dialog_user_edit_account_contact_email1"></div>
                                                        </div>
                                                        <div class="row2">
                                                                <div class="width40">
                                                                        <?php echo $la['CONTACT_PAGE_URL'] ?>
                                                                </div>
                                                                <div class="width40"><input class="inputbox" id="dialog_user_edit_account_contact_web"></div>
                                                        </div>
                                                        <div class="container last">
                                                                <div class="title-block"><? echo $la['JOURNEY_DOCUMENTS']; ?></div>
                                                        </div>
                                                        <div class="row2">
                                                                <div class="report-block block width50">
                                                                        <div class="row2">
                                                                                <input class="button icon-save icon" type="button" value="<? echo 'Mi Contrato' ?>" onclick="settingsUsersDocumentsMiContrato();" />
                                                                                <input id='dialog_EditDownloadUsersDocumentsMiContrato' class="button  icon-export icon" type="button" value="Descargar" onclick="downloadUsersDocuments('MiContrato');" />
                                                                        </div>
                                                                        <div class="row2"> <input class="button icon-save icon" type="button" value="<? echo 'Acta Constitutiva' ?>" onclick="settingsUsersDocumentsActaConstitutiva();" />
                                                                                <input id='dialog_EditDownloadUsersDocumentsActaConstitutiva' class="button  icon-export icon" type="button" value="Descargar" onclick="downloadUsersDocuments('ActaConstitutiva');" />
                                                                        </div>
                                                                        <div class="row2"> <input class="button icon-save icon" type="button" value="<? echo 'Rep Legal' ?>" onclick="settingsUsersDocumentsRepLegal();" />
                                                                                <input id='dialog_EditDownloadUsersDocumentsRepLegal' class="button  icon-export icon" type="button" value="Descargar" onclick="downloadUsersDocuments('RepLegal');" />
                                                                        </div>
                                                                        <div class="row2"> <input class="button icon-save icon" type="button" value="<? echo 'Identificación Oficial' ?>" onclick="settingsUsersDocumentsIdentificacionOficial();" />
                                                                                <input id='dialog_EditDownloadUsersDocumentsIdentificacionOficial' class="button  icon-export icon" type="button" value="Descargar" onclick="downloadUsersDocuments('IdentificacionOficial');" />
                                                                        </div>
                                                                </div>
                                                                <div class="report-block block width50">
                                                                        <div class="row2"> <input class="button icon-save icon" type="button" value="<? echo 'Opinión Positiva' ?>" onclick="settingsUsersDocumentsOpinionPositiva();" />
                                                                                <input id='dialog_EditDownloadUsersDocumentsOpinionPositiva' class="button  icon-export icon" type="button" value="Descargar" onclick="downloadUsersDocuments('OpinionPositiva');" />
                                                                        </div>
                                                                        <div class="row2"> <input class="button icon-save icon" type="button" value="<? echo 'Constancia Fiscal' ?>" onclick="settingsUsersDocumentsConstanciaFiscal();" />
                                                                                <input id='dialog_EditDownloadUsersDocumentsConstanciaFiscal' class="button  icon-export icon" type="button" value="Descargar" onclick="downloadUsersDocuments('ConstanciaFiscal');" />
                                                                        </div>
                                                                        <div class="row2"> <input class="button icon-save icon" type="button" value="<? echo 'Domicilio Fiscal' ?>" onclick="settingsUsersDocumentsDomicilioFiscal();" />
                                                                                <input id='dialog_EditDownloadUsersDocumentsDomicilioFiscal' class="button  icon-export icon" type="button" value="Descargar" onclick="downloadUsersDocuments('DomicilioFiscal');" />
                                                                        </div>
                                                                        <div class="row2"> <input class="button icon-save icon" type="button" value="<? echo 'Otros' ?>" onclick="settingsUsersDocumentsOtros();" />
                                                                                <input id='dialog_EditDownloadUsersDocumentsOtros' class="button  icon-export icon" type="button" value="Descargar" onclick="downloadUsersDocuments('Otros');" />
                                                                        </div>

                                                                </div>
                                                        </div>

                                                        <div class="title-block">
                                                                <?php echo $la['CONTACT_INFO']; ?>
                                                        </div>
                                                        <div class="row2">
                                                                <div class="width40">
                                                                        <?php echo $la['NAME_SURNAME']; ?>
                                                                </div>
                                                                <div class="width40"><input class="inputbox" id="dialog_user_edit_account_contact_name_surname">
                                                                </div>
                                                        </div>
                                                        <div class="row2">
                                                                <div class="width40">
                                                                        <?php echo $la['PHONE_NUMBER_2']; ?>
                                                                </div>
                                                                <div class="width40"><input class="inputbox" id="dialog_user_edit_account_contact_phone2"></div>
                                                        </div>
                                                        <div class="row2">
                                                                <div class="width40">
                                                                        <?php echo 'E-mail Ofertas y Promociones' ?>
                                                                </div>
                                                                <div class="width40"><input class="inputbox" id="dialog_user_edit_account_contact_email2"></div>
                                                        </div>
                                                        <div class="row2">
                                                                <div class="width40">
                                                                        <?php echo $la['COMMENT']; ?>
                                                                </div>
                                                                <div class="width60">
                                                                        <textarea id="dialog_user_edit_account_contact_comment" class="inputbox" style="height:109px;" maxlength="500" placeholder="<?php echo $la['COMMENT_ABOUT_USER']; ?>"></textarea>
                                                                </div>
                                                        </div>
                                                </div>
                                        </div>
                                </div>
                        </div>
                        <div class="controls">
                                <input class="button panel icon-save icon" type="button" onclick="userEdit('save');" value="<? echo $la['SAVE']; ?>">
                        </div>
                </div>



                <div id="dialog_user_edit_subaccounts">
                        <div id="dialog_user_edit_subaccount_list">
                                <table id="dialog_user_edit_subaccount_list_grid"></table>
                                <div id="dialog_user_edit_subaccount_list_grid_pager"></div>
                        </div>
                </div>

                <div id="dialog_user_edit_objects">
                        <div id="dialog_user_edit_object_list">
                                <table id="dialog_user_edit_object_list_grid"></table>
                                <div id="dialog_user_edit_object_list_grid_pager"></div>
                        </div>
                </div>
                <? if ($_SESSION["billing"] == true) { ?>
                        <div id="dialog_user_edit_billing_plans">
                                <div id="dialog_user_edit_billing_plan_list">
                                        <table id="dialog_user_edit_billing_plan_list_grid"></table>
                                        <div id="dialog_user_edit_billing_plan_list_grid_pager"></div>
                                </div>
                        </div>
                <? } ?>
                <div id="dialog_user_edit_usage">
                        <div id="dialog_user_edit_usage_list">
                                <table id="dialog_user_edit_usage_list_grid"></table>
                                <div id="dialog_user_edit_usage_list_grid_pager"></div>
                        </div>
                </div>
        </div>
</div>
<div id="dialog_user_object_add" title="<? echo $la['ADD_OBJECT'] ?>">
        <div class="row">
                <div class="row2">
                        <div class="width100">
                                <select id="dialog_user_object_add_objects" multiple="multiple" class="width100"></select>
                        </div>
                </div>
        </div>
        <center>
                <input class="button icon-new icon" type="button" onclick="userObjectAdd('add');" value="<? echo $la['ADD']; ?>" />&nbsp;
                <input class="button icon-close icon" type="button" onclick="userObjectAdd('cancel');" value="<? echo $la['CANCEL']; ?>" />
        </center>
</div>

<div id="dialog_user_billing_plan_add" title="<? echo $la['ADD_PLAN'] ?>">
        <div class="row">
                <div class="row2">
                        <div class="width35"><? echo $la['PLAN']; ?></div>
                        <div class="width65">
                                <select id="dialog_user_billing_plan_add_plan" class="select width100" onchange="userBillingPlanAdd('load');"></select>
                        </div>
                </div>
                <div class="row2">
                        <div class="width35"><? echo $la['NAME']; ?></div>
                        <div class="width65"><input id="dialog_user_billing_plan_add_name" class="inputbox" type="text" value="" maxlength="50"></div>
                </div>
                <div class="row2">
                        <div class="width35"><? echo $la['NUMBER_OF_OBJECTS']; ?></div>
                        <div class="width30"><input id="dialog_user_billing_plan_add_objects" onkeypress="return isNumberKey(event);" class="inputbox" type="text" value="" maxlength="10"></div>
                </div>
                <div class="row2">
                        <div class="width35"><? echo $la['PERIOD']; ?></div>
                        <div class="width30"><input id="dialog_user_billing_plan_add_period" onkeypress="return isNumberKey(event);" class="inputbox" type="text" value="" maxlength="10"></div>
                        <div class="width5"></div>
                        <div class="width30">
                                <select id="dialog_user_billing_plan_add_period_type" class="select width100">
                                        <option value="days"><? echo $la['DAYS']; ?></option>
                                        <option value="months"><? echo $la['MONTHS']; ?></option>
                                        <option value="years"><? echo $la['YEARS']; ?></option>
                                </select>
                        </div>
                </div>
                <div class="row2">
                        <div class="width35"><? echo $la['PRICE']; ?></div>
                        <div class="width30"><input id="dialog_user_billing_plan_add_price" onkeypress="return isNumberKey(event);" class="inputbox" type="text" value="" maxlength="10"></div>
                </div>
        </div>
        <center>
                <input class="button icon-new icon" type="button" onclick="userBillingPlanAdd('add');" value="<? echo $la['ADD']; ?>" />&nbsp;
                <input class="button icon-close icon" type="button" onclick="userBillingPlanAdd('cancel');" value="<? echo $la['CANCEL']; ?>" />
        </center>
</div>

<div id="dialog_user_billing_plan_edit" title="<? echo $la['EDIT_PLAN'] ?>">
        <div class="row">
                <div class="row2">
                        <div class="width35"><? echo $la['NAME']; ?></div>
                        <div class="width65"><input id="dialog_user_billing_plan_edit_name" class="inputbox" type="text" value="" maxlength="50"></div>
                </div>
                <div class="row2">
                        <div class="width35"><? echo $la['NUMBER_OF_OBJECTS']; ?></div>
                        <div class="width30"><input id="dialog_user_billing_plan_edit_objects" onkeypress="return isNumberKey(event);" class="inputbox" type="text" value="" maxlength="10"></div>
                </div>
                <div class="row2">
                        <div class="width35"><? echo $la['PERIOD']; ?></div>
                        <div class="width30"><input id="dialog_user_billing_plan_edit_period" onkeypress="return isNumberKey(event);" class="inputbox" type="text" value="" maxlength="10"></div>
                        <div class="width5"></div>
                        <div class="width30">
                                <select id="dialog_user_billing_plan_edit_period_type" class="select width100">
                                        <option value="days"><? echo $la['DAYS']; ?></option>
                                        <option value="months"><? echo $la['MONTHS']; ?></option>
                                        <option value="years"><? echo $la['YEARS']; ?></option>
                                </select>
                        </div>
                </div>
                <div class="row2">
                        <div class="width35"><? echo $la['PRICE']; ?></div>
                        <div class="width30"><input id="dialog_user_billing_plan_edit_price" onkeypress="return isNumberKey(event);" class="inputbox" type="text" value="" maxlength="10"></div>
                </div>
        </div>
        <center>
                <input class="button icon-save icon" type="button" onclick="userBillingPlanEdit('save');" value="<? echo $la['SAVE']; ?>" />&nbsp;
                <input class="button icon-close icon" type="button" onclick="userBillingPlanEdit('cancel');" value="<? echo $la['CANCEL']; ?>" />
        </center>
</div>