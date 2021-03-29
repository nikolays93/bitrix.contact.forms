<?php

defined("B_PROLOG_INCLUDED") or exit();

use Bitrix\Main\Localization\Loc;

$arComponentDescription = array(
	"NAME" => Loc::getMessage("CONTACT_FORMS_COMPONENT_NAME"),
	"DESCRIPTION" => Loc::getMessage("CONTACT_FORMS_COMPONENT_DESCRIPTION"),
	"ICON" => "/images/icon.gif",
	"PATH" => array(
		"ID" => "service",
	),
);
