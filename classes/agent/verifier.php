<?php

use Bitrix\Main;

class CVerifier {
    
    public function getQuery($address) {
        return 'https://maps.googleapis.com/maps/api/geocode/json?address='.urlencode($address).'&language=ru&key='.\Bitrix\Main\Config\Option::get('mcart.verifieraddr', 'api_key');
    }
    
    function VerifierAgent() {
        CModule::IncludeModule('mcart.verifieraddr');
        CModule::IncludeModule('crm');
        global $USER_FIELD_MANAGER;
        $result = \MCArt\Verifier\TaskTable::getList(array('filter'  => array('=STATUS' => 0), 'order' => array('TASK_DATE' => 'ASC'), 'limit' => 1));
        $task = array();
        while ($t = $result->fetch())
        {
            $task = $t;
        }
        if(isset($task['ID'])){
            $res = \MCArt\Verifier\TaskDetailTable::getList(array('filter'  => array('=TASK_ID' => $task['ID'], '=STATUS' => 0), 'limit' => $task['ITEM_COUNT']));
            while ($r = $res->fetch()) {
                $items[] = $r;
                $items_ids[] = $r['CRM_ENTITY_ID'];
            }
            //массив старых значений
            switch ($task['CRM_ENTITY_TYPE']) {
                case 1:
                    $ress = CCrmLead::getListEx(array(),array('ID' => $items_ids, 'CHECK_PERMISSIONS'=> 'N'),false,false,array($task['FIELD_NAME_OLD']));
                    break;
                case 3:
                    $ress = CCrmContact::getListEx(array(),array('ID' => $items_ids, 'CHECK_PERMISSIONS'=> 'N'),false,false,array($task['FIELD_NAME_OLD']));
                    break;
                case 4:
                    $ress = CCrmCompany::getListEx(array(),array('ID' => $items_ids, 'CHECK_PERMISSIONS'=> 'N'),false,false,array($task['FIELD_NAME_OLD']));
                    break;
            }
            while($ar_old = $ress->GetNext()){ 
                $arr_old_values[$ar_old['ID']] = $ar_old[$task['FIELD_NAME_OLD']];
            }
            foreach ($items as $item) {
                $params = array();
                $fields = array();
                $result = file_get_contents($item["QUERY"], false);
                if($result) {
                    $resp = \Bitrix\Main\Web\Json::decode($result);
                    if (($resp["status"] !== "OK") || isset($resp["results"][0]['partial_match']) || isset($resp["error_message"])) {
                        //несоответствие адреса или ошибка, запишем старое значение без координат
                        $fields = array($task['FIELD_NAME_NEW'] =>  array('0' => $arr_old_values[$item['CRM_ENTITY_ID']]));
                        $params['VERIFIER_RESULT'] = "N";
                    } else {
                        //все ок, записываем новый адрес
                        $fields = array($task['FIELD_NAME_NEW'] =>  array('0' => $resp["results"][0]['formatted_address'].'|'.$resp["results"][0]['geometry']['location']['lat'].';'.$resp["results"][0]['geometry']['location']['lng']));
                        $params['VERIFIER_RESULT'] = "Y"; 
                    }
                } else {
                        //несоответствие адреса или ошибка, запишем старое значение без координат
                        $fields = array($task['FIELD_NAME_NEW'] =>  array('0' => $arr_old_values[$item['CRM_ENTITY_ID']]));
                	$params['VERIFIER_RESULT'] = "N";                 
                }
                switch ($task['CRM_ENTITY_TYPE']) {
                    case 1:
                        $USER_FIELD_MANAGER->Update('CRM_LEAD', $item['CRM_ENTITY_ID'], $fields);
                        break;
                    case 3:
                        $USER_FIELD_MANAGER->Update('CRM_CONTACT', $item['CRM_ENTITY_ID'], $fields);
                        break;
                    case 4:
                        $USER_FIELD_MANAGER->Update('CRM_COMPANY', $item['CRM_ENTITY_ID'], $fields);
                        break;
                }
                $params['STATUS'] = 1;
                \MCArt\Verifier\TaskDetailTable::update($item['ID'], $params);
            }
            $countRes = \MCArt\Verifier\TaskDetailTable::getList(array(
                'select' => array('CNT'),
                'filter'  => array('=TASK_ID' => $task['ID'], '=STATUS' => 0),
                'runtime' => array(
                    new \Bitrix\Main\Entity\ExpressionField('CNT', 'COUNT(*)')
                )
	    ));
            $count = $countRes->fetchAll();
            if(!$count[0]['CNT']) {
                \MCArt\Verifier\TaskTable::update($task['ID'],array("STATUS" => 1));
            }
        }
        //почистим старые задания
        $cleanup_days = \Bitrix\Main\Config\Option::get('mcart.verifieraddr', 'cleanup_days');
            if($cleanup_days > 0)
            {
                $arDate = localtime(time());
                $date = mktime(0, 0, 0, $arDate[4]+1, $arDate[3]-$cleanup_days, 1900+$arDate[5]);
                $q = \MCArt\Verifier\TaskTable::getList(array('select' => array('ID'),'filter'  => array('<=TASK_DATE' => date("d.m.Y H:i:s", $date), 'STATUS' => 1)));
                while ($item = $q->fetch()) {
                    \MCArt\Verifier\TaskTable::delete($item['ID']);
                }
            }
        return "CVerifier::VerifierAgent();";
    
    }
}
?>

