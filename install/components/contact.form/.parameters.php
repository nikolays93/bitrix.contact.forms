<?php

defined("B_PROLOG_INCLUDED") or exit();

use Bitrix\Main\Loader;

if (! Loader::includeModule("iblock"))
	return;

use Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

/** @var $arIBlockType array */
$arIBlockType = CIBlockParameters::GetIBlockTypes();

/** @var $arIBlock array */
$arIBlock = [];
$rsIBlock = CIBlock::GetList(array("sort" => "asc"), [
	"TYPE" => $arCurrentValues["IBLOCK_TYPE"],
	"ACTIVE" => "Y"
]);

while($arr = $rsIBlock->Fetch()) {
	$arIBlock[$arr["ID"]] = "[{$arr["ID"]}] {$arr["NAME"]}";
}

$arComponentParameters = array(
	"GROUPS" => array(
		"PRIVACY_POLICY" => array(
			"NAME" => Loc::getMessage("CONTACT_FORM_PRIVACY_POLICY_NAME"),
			"SORT" => "410",
		),
	),
	"PARAMETERS" => array(
		"IBLOCK_TYPE" => Array(
			"PARENT" => "DATA_SOURCE",
			"NAME" => Loc::getMessage("CONTACT_FORM_IBLOCK_TYPE"),
			"TYPE" => "LIST",
			"VALUES" => $arIBlockType,
			"ADDITIONAL_VALUES" => "N",
			"REFRESH" => "Y",
		),
		"IBLOCK_ID" => Array(
			"PARENT" => "DATA_SOURCE",
			"NAME" => Loc::getMessage("CONTACT_FORM_IBLOCK_ID"),
			"TYPE" => "LIST",
			"VALUES" => $arIBlock,
			"ADDITIONAL_VALUES" => "Y",
			"REFRESH" => "Y",
		),
		"BUTTON_TITLE" => array(
			"PARENT" => "VISUAL",
			"NAME" => Loc::getMessage("CONTACT_FORM_BUTTON_TITLE"),
			"TYPE" => "STRING",
			"DEFAULT" => Loc::getMessage("CONTACT_FORM_BUTTON_TITLE_DEFAULT"),
		),
		"SHOW_PRIVACY_POLICY" => array(
			"PARENT" => "PRIVACY_POLICY",
			"NAME" => Loc::getMessage("CONTACT_FORM_PRIVACY_POLICY_SHOW"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "N",
		),
		"TEXT_PRIVACY_POLICY" => array(
			"PARENT" => "PRIVACY_POLICY",
			"NAME" => Loc::getMessage("CONTACT_FORM_PRIVACY_POLICY_TEXT"),
			"TYPE" => "STRING",
			"DEFAULT" => Loc::getMessage("CONTACT_FORM_PRIVACY_POLICY_TEXT_DEFAULT"),
		),
		"AJAX_MODE" => array(),
		"CACHE_TIME"  => array(
			"DEFAULT" => 36000000
		)
	)
);
