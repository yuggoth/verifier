<?php
namespace MCArt\Verifier;

use Bitrix\Main\Entity;

if (!\Bitrix\Main\Loader::includeModule('crm')) {
	echo 'Module crm not included';
	return true;
}

class TaskTable extends Entity\DataManager
{
    
    public static function getTableName()
    {
        return 'b_verifier_task';
    }
    
    public static function getMap()
    {
        return array(
            new Entity\DateField('TASK_DATE'),
            new Entity\IntegerField('STATUS'),
            new Entity\IntegerField('CRM_ENTITY_TYPE'), 
            new Entity\StringField('FIELD_USER_OLD'),
            new Entity\StringField('FIELD_NAME_OLD'),
            new Entity\StringField('FIELD_NAME_NEW'),
            new Entity\IntegerField('ITEM_COUNT'), 
            new Entity\IntegerField('ID', array(
                'primary' => true,
                'autocomplete' => true
            ))
        );
    }
    
    public static function onAfterAdd(Entity\Event $event)
    {
        $id = $event->getParameter("primary");
        $data = $event->getParameter("fields");
        $field = $data['FIELD_NAME_OLD'];
        switch ($data['CRM_ENTITY_TYPE']) {
            case 1:
                $res = \CCrmLead::getListEx(array(),array(),false,false,array($field));
                break;
            case 3:
                $res = \CCrmContact::getListEx(array(),array(),false,false,array($field));
                break;
            case 4:
                $res = \CCrmCompany::getListEx(array(),array(),false,false,array($field));
                break;
        }
        while($ar_fields = $res->GetNext())
        {  
            if (isset($ar_fields[$field]) && strlen($ar_fields[$field])) {
                \MCArt\Verifier\TaskDetailTable::add(array( 
                    'TASK_ID' => $id['ID'], 
                    'CRM_ENTITY_ID' => $ar_fields['ID'],
                    'QUERY' => \CVerifier::getQuery($ar_fields[$field]),
                ));
            }
        }
        return true;
    }
    
}

