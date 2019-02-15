<?php
namespace  MCArt\Verifier;

use Bitrix\Main\Entity;

class TaskDetailTable extends Entity\DataManager
{
    
    public static function getTableName()
    {
        return 'b_verifier_task_detail';
    }
    
    public static function getMap()
    {
        return array(
            new Entity\IntegerField('TASK_ID'), 
            new Entity\IntegerField('CRM_ENTITY_ID'), 
            new Entity\StringField('QUERY'),
            new Entity\IntegerField('STATUS'),
            new Entity\StringField('VERIFIER_RESULT'),
            new Entity\IntegerField('ID', array(
                'primary' => true,
                'autocomplete' => true
            ))
        );
    }
}