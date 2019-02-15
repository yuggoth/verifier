<?php

CModule::AddAutoloadClasses(
	"mcart.verifieraddr",
	array(
		"CVerifier" => "classes/agent/verifier.php",
		"MCArt\\Verifier\\TaskTable" => "lib/task.php",
		"MCArt\\Verifier\\TaskDetailTable" => "lib/taskdetail.php",
	)
);

