<div id="dialog_expenses" title="<? echo $la['EXPENSES_LIST']; ?>">
	<table id="expenses_list_grid"></table>
	<div id="expenses_list_grid_pager"></div>
</div>

<div id="dialog_expense_properties" title="<? echo $la['EXPENSE_PROPERTIES'];?>">
	<div class="row">
		<div class="title-block"><? echo  'Datos Generales'; ?></div>
		<div class="block width50">
			<div class="container">
			    <div class="row2">
				    <div class="width30"><? echo 'Código de Cliente'; ?></div>
					<div class="width20"><input id="dialog_expense_code" class="inputbox" type="text" value="" maxlength="50"></div>
				</div>
				<div class="row2">
					<div class="width30"><? echo $la['TRADENAME']; ?></div>
					<div class="width70"><input id="dialog_expense_tradename" class="inputbox" type="text" value="" maxlength="50"></div>
				</div>
				<div class="row2">
					<div class="width30"><? echo 'Direccón'; ?></div>
					<div class="width70"><input id="dialog_expense_address" class="inputbox" type="text" value="" maxlength="50"></div>
				</div>
				<div class="row2">
					<div class="width30"><? echo 'Rubro/Giro'; ?></div>
					<div class="width70"><input id="dialog_expense_line" class="inputbox" type="text" value="" maxlength="50"></div>
				</div>
				<div class="row2">
					<div class="width30"><? echo 'Productos/Servicios'; ?></div>
					<div class="width70"><input id="dialog_expense_services" class="inputbox" type="text" value="" maxlength="50"></div>
				</div>
				<div class="row2">
					<div class="width30"><? echo 'Correo Electrónico'; ?></div>
					<div class="width70"><input id="dialog_expense_email" class="inputbox" type="text" value="" maxlength="50"></div>
				</div>
			</div>
		</div>
		<div class="block width50">
			<div class="container last">
				<div class="row2">
					<div class="width30"><? echo $la['TAX_NAME']; ?></div>
					<div class="width70"><input id="dialog_expense_taxname" class="inputbox" type="text" value="" maxlength="50"></div>
				</div>
				<div class="row2">
					<div class="width30"><? echo 'Teléfono'; ?></div>
					<div class="width70"><input id="dialog_expense_phone" class="inputbox" type="text" value="" maxlength="50"></div>
				</div>
				<div class="row2">
					<div class="width30"><? echo 'Teléfono 1'; ?></div>
					<div class="width70"><input id="dialog_expense_phone1" class="inputbox" type="text" value="" maxlength="50"></div>
				</div>
				<div class="row2">
					<div class="width30"><? echo 'Sitio Web'; ?></div>
					<div class="width70"><input id="dialog_expense_web" class="inputbox" type="text" value="" maxlength="50"></div>
				</div>
				<div class="row2">
					<div class="width30"><? echo $la['DESCRIPTION']; ?></div>
					<div class="width70"><textarea id="dialog_expense_desc" class="inputbox" style="height:50px;" maxlength="500"></textarea></div>
				</div>
			</div>
		</div>
	</div>
	    <div class="title-block"><? echo  'Documentos'; ?></div>
	    <div class="row">
			<div class="container last">
				<input class="button" type="button" value="<? echo 'Documentos' ?>" onclick="settingsObjectDriverPhotoUpload();" />
			</div>
		</div>
	
	<center>
		<input class="button icon-save icon" type="button" onclick="expensesProperties('save');" value="<? echo $la['SAVE']; ?>" />&nbsp;
		<input class="button icon-close icon" type="button" onclick="expensesProperties('cancel');" value="<? echo $la['CANCEL']; ?>" />
	</center>
</div>
