<?php

if (class_exists('mcart_verifieraddr'))
    return;

Class mcart_verifieraddr extends CModule
{
    
    var $MODULE_ID = "mcart.verifieraddr";
    var $MODULE_VERSION;
    var $MODULE_VERSION_DATE;
    var $MODULE_NAME;
    var $MODULE_DESCRIPTION;
    
    function __construct() {
        $arModuleVersion = array();

        $path = str_replace("\\", "/", __FILE__);
        $path = substr($path, 0, strlen($path) - strlen("/index.php"));
        include($path."/version.php");

        if (is_array($arModuleVersion) && array_key_exists("VERSION", $arModuleVersion))
        {
			$this->MODULE_VERSION = $arModuleVersion["VERSION"];
			$this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
        }

        $this->MODULE_NAME = GetMessage("MCART_VERIFIERADDR_MODULE_NAME");
        $this->MODULE_DESCRIPTION = GetMessage("MCART_VERIFIERADDR_MODULE_DESCRIPTION");
        $this->PARTNER_NAME = GetMessage('MCART_VERIFIERADDR_PARTNER_NAME');
        $this->PARTNER_URI  = 'http://mcart.ru/';
    }
    
    function InstallDB($arParams = array())
    {
        global $DB;
        if($DB->type === "MYSQL")
        {
            $DB->RunSQLBatch(__DIR__."/db/".strtolower($DB->type)."/install.sql");
        }
    }
    
    function UnInstallDB($arParams = array())
    {
        global $DB;
        if($DB->type === "MYSQL")
        {
            $DB->RunSQLBatch(__DIR__."/db/".strtolower($DB->type)."/uninstall.sql");
        }
    }
    
    function DoInstall()
    {
        $this->InstallDB();        
        RegisterModule($this->MODULE_ID);
        CAgent::AddAgent("CVerifier::VerifierAgent();", $this->MODULE_ID, "N", 120);

    }
    
    function DoUninstall()
    {
       UnRegisterModule($this->MODULE_ID); 
       COption::RemoveOption($this->MODULE_ID);
       CAgent::RemoveModuleAgents($this->MODULE_ID);
       $this->UnInstallDB();
    }
    
    
}

