<?php
if(!$USER->IsAdmin())
	return;
CModule::IncludeModule('crm');
CModule::IncludeModule('mcart.verifieraddr');
$aTabs = array(
	array("DIV" => "edit1", "TAB" => GetMessage("VER_OPT_SETTINGS"), "ICON" => "", "TITLE" => GetMessage("VER_SETTINGS_TITLE")),
    	array("DIV" => "edit2", "TAB" => GetMessage("VER_OPT_TASKS"), "ICON" => "", "TITLE" => GetMessage("VER_TASKS_TITLE"))
);

$crm_entity_type = array("1" => "Лид", "3" => "Контакт", "4" => "Компания");

$tabControl = new CAdminTabControl("tabControl", $aTabs);
$arr = CCrmLead::GetFields();
foreach($arr as $key => $value) {
    if (CCrmLead::GetFieldCaption($key)) {
        $arLeadFields[$key] = CCrmLead::GetFieldCaption($key);
    }
}
$arr = CCrmLead::GetUserFields();
foreach($arr as $key => $value) {
        $arLeadUsersFields[$key] = $value["USER_TYPE"]["DESCRIPTION"];
}

$arr = CCrmCompany::GetFields();
foreach($arr as $key => $value) {
    if (CCrmCompany::GetFieldCaption($key)) {
        $arCompanyFields[$key] = CCrmCompany::GetFieldCaption($key);
    }
}
$arr = CCrmCompany::GetUserFields();
foreach($arr as $key => $value) {
        $arCompanyUsersFields[$key] = $value["USER_TYPE"]["DESCRIPTION"];
}

$arr = CCrmContact::GetFields();
foreach($arr as $key => $value) {
    if (CCrmContact::GetFieldCaption($key)) {
         $arContactFields[$key] = CCrmContact::GetFieldCaption($key);
    }
}
$arr = CCrmContact::GetUserFields();
foreach($arr as $key => $value) {
        $arContactUsersFields[$key] = $value["USER_TYPE"]["DESCRIPTION"];
}

//все задачи
$result = \MCArt\Verifier\TaskTable::getList(array('order' => array('TASK_DATE' => 'DESC')));
$tasks = array();
while ($t = $result->fetch())
{
    $tasks[] = $t;
}
$error = "";
//$arFieldTypes = CCrmLead::GetFields();
if($REQUEST_METHOD=="POST" && check_bitrix_sessid())
{
	if (isset($_REQUEST['entity_type']) && strlen($_REQUEST['item_count'])  && strlen($_REQUEST['api_key']) && strlen($_REQUEST['cleanup_days'])) {
                    \Bitrix\Main\Config\Option::set("mcart.verifieraddr", "cleanup_days", $_REQUEST['cleanup_days']);
                    \Bitrix\Main\Config\Option::set("mcart.verifieraddr", "api_key", $_REQUEST['api_key']);
                $field_old = '';
                $field_new = '';
                $user_field = "N";
                switch ($_REQUEST['entity_type']) {
                    case 1:
                        if (substr($_REQUEST['lead_field_type_old'],0,5) === "USER_")  $user_field = "Y";
                        $field_old = mb_substr($_REQUEST['lead_field_type_old'],5);
                        $field_new = $_REQUEST['lead_field_type_new'];
                        break;
                    case 3:
                        if (substr($_REQUEST['contact_field_type_old'],0,5) === "USER_")  $user_field = "Y";
                        $field_old = mb_substr($_REQUEST['contact_field_type_old'],5);
                        $field_new = $_REQUEST['contact_field_type_new'];   
                        break;
                    case 4:
                        if (substr($_REQUEST['company_field_type_old'],0,5) === "USER_")  $user_field = "Y";
                        $field_old = mb_substr($_REQUEST['company_field_type_old'],5);
                        $field_new = $_REQUEST['company_field_type_new']; 
                        break;
                }
                $result = \MCArt\Verifier\TaskTable::add(array( 
					'CRM_ENTITY_TYPE' => $_REQUEST['entity_type'], 
					'TASK_DATE' => new \Bitrix\Main\Type\DateTime(),
					'FIELD_USER_OLD' => $user_field,
					'FIELD_NAME_OLD' => $field_old,
					'FIELD_NAME_NEW' => $field_new,
					'ITEM_COUNT' => $_REQUEST['item_count'],
                ));
                
                if ($result->isSuccess()) {
                    $id = $result->getId();
                } 

		if(strlen($Update)>0 && strlen($_REQUEST["back_url_settings"])>0)
		{
			LocalRedirect($_REQUEST["back_url_settings"]);
		}
		else
		{
			LocalRedirect($APPLICATION->GetCurPage()."?mid=".urlencode($mid)."&lang=".urlencode(LANGUAGE_ID)."&back_url_settings=".urlencode($_REQUEST["back_url_settings"])."&".$tabControl->ActiveTabParam());
		}
	} else {
		$error = "Не выбрантип сущности или не указан ключ Google API или не заполнено количество элементов, отрабатываемых за шаг, интервал чистки старых записей";
	}
}
?>
<form method="post" name="cal_opt_form" action="<?= $APPLICATION->GetCurPage()?>?mid=<?= urlencode($mid)?>&amp;lang=<?= LANGUAGE_ID?>">
<?= bitrix_sessid_post();?>
<?$tabControl->Begin();
$tabControl->BeginNextTab();
?>

<tr><td>
<?if (strlen($error)) { ?>
<div class="adm-info-message-wrap adm-info-message-red">
	<div class="adm-info-message">
		<div class="adm-info-message-title">Ошибка ввода параметров</div>
			<? echo $error; ?>
		<div class="adm-info-message-icon"></div>
	</div>
</div>
<?}?>
<table class="internal">
        <tr class="heading">
            <td>Сущность CRM</td>
            <td>Старое поле Адрес</td>
            <td>Адрес Google карты</td>
        </tr>
	<tr>
            <td><p><input name="entity_type" type="radio" value="1"><?= GetMessage("VER_LEAD_NAME")?></p></td>
		<td>
			<select id="cal_work_time" name="lead_field_type_old">
				<?foreach($arLeadFields as $key => $val):?>
					<option value="NORM_<?= $key?>"><?= $val?> (<?= $key?>)</option>
				<?endforeach;?>
				<?foreach($arLeadUsersFields as $key => $val):?>
					<option value="USER_<?= $key?>"><?= $val?> (<?= $key?>)</option>
				<?endforeach;?>
			</select>
                        
		</td>
                <td>
			<select id="cal_work_time" name="lead_field_type_new">
				<?foreach($arLeadUsersFields as $key => $val):?>
					<option value="<?= $key?>"><?= $val?> (<?= $key?>)</option>
				<?endforeach;?>
			</select>                    
                </td>
	</tr>
	<tr>
		<td><p><input name="entity_type" type="radio" value="3"><?= GetMessage("VER_CONTACT_NAME")?></p></td>
		<td>
			<select id="cal_work_time" name="contact_field_type_old">
				<?foreach($arContactFields as $key => $val):?>
					<option value="NORM_<?= $key?>"><?= $val?> (<?= $key?>)</option>
				<?endforeach;?>
				<?foreach($arContactUsersFields as $key => $val):?>
					<option value="USER_<?= $key?>"><?= $val?> (<?= $key?>)</option>
				<?endforeach;?>
			</select>
		</td>
                <td>
			<select id="cal_work_time" name="contact_field_type_new">
				<?foreach($arContactUsersFields as $key => $val):?>
					<option value="<?= $key?>" ><?= $val?> (<?= $key?>)</option>
				<?endforeach;?>
			</select>                    
                </td>
	</tr>
	<tr>
		<td><p><input name="entity_type" type="radio" value="4"><?= GetMessage("VER_COMPANY_NAME")?></p></td>
		<td>
			<select id="cal_work_time" name="company_field_type_old">
				<?foreach($arCompanyFields as $key => $val):?>
					<option value="NORM_<?= $key?>" ><?= $val?> (<?= $key?>)</option>
				<?endforeach;?>
				<?foreach($arCompanyUsersFields as $key => $val):?>
					<option value="USER_<?= $key?>" ><?= $val?> (<?= $key?>)</option>
				<?endforeach;?>
			</select>
		</td>
                <td>
			<select id="cal_work_time" name="company_field_type_new">
				<?foreach($arCompanyUsersFields as $key => $val):?>
					<option value="<?= $key?>" ><?= $val?> (<?= $key?>)</option>
				<?endforeach;?>
			</select>                    
                </td>
	</tr>
	<tr>
		<td><label for="cal_user_name_template"><?= GetMessage("VER_COUNT_ITEM")?>:</label></td>
		<td colspan="2">
			<input name="item_count" type="text" value="10" id="cal_user_name_template" />
		</td>
	</tr>
	<tr>
		<td><label for="cal_user_name_template">Google API KEY:</label></td>
		<td colspan="2">
			<input name="api_key" type="text" value="<?= COption::GetOptionString("mcart.verifieraddr", "api_key") ?>" id="cal_user_name_template" style="width:80%" />
		</td>
        </tr>
	<tr>
		<td><label for="cal_user_name_template"><?= GetMessage("VER_CLEANUP_DAYS")?>:</label></td>
		<td colspan="2">
			<input name="cleanup_days" type="text" value="<?= COption::GetOptionString("mcart.verifieraddr", "cleanup_days") ?>" style="width:80%" />
		</td>
        </tr>
        </table>
    </td></tr>
        <?$tabControl->BeginNextTab();?>
        <tr><td><table style="width:100%" class="internal">
            <tr class="heading"><td>ID</td><td>Дата</td><td>Тип сущности</td><td>Исходное поле</td><td>Новое поле</td><td>Количество итераций</td><td>Статус</td></tr>
<?foreach ($tasks as $task) {?>
<tr>
    <td><?= $task['ID']?> </td>
    <td><?= $task['TASK_DATE'];?></td>
    <td><?= $crm_entity_type[$task['CRM_ENTITY_TYPE']];?></td>
    <td><?=$task['FIELD_NAME_OLD'];?></td>
    <td><?=$task['FIELD_NAME_NEW'];?></td>
    <td><?=$task['ITEM_COUNT'];?></td>
    <td><?if($task['STATUS']) {?>Завершено<?} else {?>В процессе<?}?></td>
</tr>
<?}?>
            </table></td></tr>
<?php $tabControl->Buttons();?>
	<input type="submit" name="Update" value="<?php echo GetMessage("ADD_TASK")?>" title="<?php echo GetMessage("ADD_TASK")?>" />
        <?=bitrix_sessid_post();?>
            <?php $tabControl->End();?>
               </form>