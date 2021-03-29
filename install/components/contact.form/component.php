<?php

defined("B_PROLOG_INCLUDED") or exit();

/**
 * @var array $arParams
 * @var array $arResult
 * @var CBitrixComponent $this
 * @global CMain $APPLICATION
 * @global CUser $USER
 */

use Bitrix\Main\Loader;
use Bitrix\Iblock;
use Bitrix\Main\Application;
use Bitrix\Main\Mail\Event;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Security\SecurityException;

global $APPLICATION;

if (! Loader::includeModule("iblock")) return;

require_once __DIR__ . '/lib/FormAttributes.php';
require_once __DIR__ . '/lib/iFormField.php';
require_once __DIR__ . '/lib/FormField.php';

require_once __DIR__ . '/lib/FormFieldHidden.php';
require_once __DIR__ . '/lib/FormFieldText.php';
// require_once __DIR__ . '/lib/FormFieldTextarea.php';
// require_once __DIR__ . '/lib/FormFieldCheckbox.php';
// require_once __DIR__ . '/lib/FormAttributesOption.php';
// require_once __DIR__ . '/lib/FormFieldSelect.php';
// require_once __DIR__ . '/lib/FormFieldFile.php';

require __DIR__ . '/functions.php';

$arResult["PARAMS_HASH"] = md5(serialize($arParams) . $this->GetTemplateName());

$arParams["MODULE_OPTIONS"] = [
	'FORMS_USE_CAPTCHA' => 'N',
	'CAPTCHA_TYPE' => '',
	'RECAPTCHA_SECRET_KEY' => '',
];
$arResult['ERROR_MESSAGE'] = [];
$arResult['SUCCESS_MESSAGE'] = [];

try {
	if (0 >= $arParams["IBLOCK_ID"] = intval($arParams["IBLOCK_ID"])) {
		throw new Exception(Loc::getMessage("FORM_ERROR_NO_IBLOCK_ID"));
	}

	if ('D' === CIBlock::GetPermission($arParams["IBLOCK_ID"])) {
		throw new Exception(Loc::getMessage("FORM_ERROR_ACCESS_DENIED"));
	}

	$obCache = new CPHPCache();
	$arResultCacheID = ["IBLOCK_ID" => $arParams["IBLOCK_ID"]];
	$path = '/' . SITE_ID . $this->GetRelativePath();

	if ($obCache->InitCache(intval($arParams["CACHE_TIME"]), serialize($arResultCacheID), $path)) {
		$arResult = $obCache->GetVars();
	} elseif ($obCache->StartDataCache()) {
		$arResult["IBLOCK"] = CIBlock::GetList(["SORT" => "ASC"], [
			"ID" => $arParams["IBLOCK_ID"],
			"ACTIVE" => "Y",
		])->Fetch();

		if (empty($arResult["IBLOCK"])) {
			throw new Exception(Loc::getMessage("FORM_ERROR_IBLOCK_NOT_FOUND"));
		}

		if (empty($arResult["IBLOCK"]['CODE'])) {
			// 'Iblock code is required for event unique.'
			throw new Exception(Loc::getMessage("FORM_ERROR_IBLOCK_CODE_REQUIRED"));
		}

		/** @var string $arResult["ELEMENT_AREA_ID"]  area id by iblock code */
		$arResult["ELEMENT_AREA_ID"] = $arResult["IBLOCK"]["CODE"];

		/** @var array List of iField */
		$arResult["IBLOCK"]["PROPERTIES"] = [];

		/** @var Bitrix\Main\DB\Result */
		$rsProps = CIBlock::GetProperties(
			$arResult["IBLOCK"]["ID"],
			["SORT" => "ASC", "NAME" => "ASC"],
			["ACTIVE" => "Y"]
		);

		while ($arProperty = $rsProps->fetch()) {
			$obField = getFormFieldObject($arProperty);

			if (! $obField->getPropertyCode()) {
				throw new Exception('Property "' . $obField->getPropertyName() . '" code is empty.');
			}

			$arResult["IBLOCK"]["PROPERTIES"][ $obField->getPropertyCode() ] = $obField;
		}

		if (empty($arResult["IBLOCK"]["PROPERTIES"])) {
			throw new Exception(Loc::getMessage("FORM_ERROR_IBLOCK_PROPERTIES_NOT_FOUND"));
		}

		$obCache->EndDataCache($arResult);
	}
}
catch (Exception $exception) {
	array_push($arResult["ERROR_MESSAGE"], $exception->getMessage());
	$this->abortResultCache();
}

try {
	$request = Application::getInstance()->getContext()->getRequest();

	if ($request->isPost() && $arResult["PARAMS_HASH"] === $request->getPost("PARAMS_HASH")) {
		if (! check_bitrix_sessid()) {
			throw new SecurityException(Loc::getMessage("FORM_ERROR_SESSION"));
		}

		/**
		 * @todo validate captcha
		 * @todo add files validation
		 */
		foreach($arResult["IBLOCK"]["PROPERTIES"] as $obProperty) {
			$valueRequest = $request->getPost($obProperty->getPropertyCode());
			$obProperty->setValue($value);

			if (! $obProperty->validate()) {
				array_push($arResult["ERROR_MESSAGE"], ...$obProperty->getErrors());
			}
		}

		if (sizeof($arResult["ERROR_MESSAGE"]) <= 0) {
			/** @var array [PropertyCode => RequestedValue] */
			$arValues = array_map(static function($obField) {
				return $obField->getValue();
			}, $arResult["IBLOCK"]["PROPERTIES"]);

			/**
			 * Insert Iblock element.
			 */
			$arFields = [
				"IBLOCK_ID" => $arParams["IBLOCK_ID"],
				"ACTIVE" => "N",
				"NAME" => Loc::getMessage("FORM_IBLOCK_ELEMENT_NAME") .
					ConvertTimeStamp(time(), "FULL", SITE_ID),
				"PROPERTY_VALUES" => $arValues,
			];

			$el = new CIBlockElement;
			/**
			 * Validate params by iblock settings.
			 */
			if ($resultID = $el->Add($arFields)) {
				/** @var string Event code name */
				$eventName = "FORM_" . $arResult["IBLOCK"]["CODE"];

				/**
				 * EMAIL Event type
				 */
				$arEvent = Bitrix\Main\Mail\Internal\EventTypeTable::getList([
					'select' => ['ID'],
					'filter' => [
						'LID' => LANGUAGE_ID,
						'EVENT_NAME' => $eventName,
					]
				])->fetch();

				if (empty($arEvent)) {
					formStoreEventType($eventName, $arResult["IBLOCK"]["NAME"], $arResult["IBLOCK"]["PROPERTIES"]);
				}

				/**
				 * EMAIL Template
				 */
				$arMessage = Bitrix\Main\Mail\Internal\EventMessageTable::getList([
					'select' => ['ID'],
					'filter' => [
						'LID' => SITE_ID,
						'EVENT_NAME' => $eventName,
					],
				])->fetch();

				if (empty($arMessage)) {
					formStoreEventMessage($arResult["IBLOCK"]["PROPERTIES"], $eventName);
				}

				/**
				 * Send email (event trigger)
				 */
				$arProperties = array_map(static function($obProperty) {
					return $obProperty->getValue();
				}, $arResult["IBLOCK"]["PROPERTIES"]);

				/**
				 * @todo PREPARE LINKS TO FILES FOR EMAIL
				 */
				$arProperties["FORM_NAME"] = $arResult["IBLOCK"]["NAME"];

				Event::send(array(
					"EVENT_NAME" => $eventName,
					"LID" => SITE_ID,
					"C_FIELDS" => $arProperties,
				));

				/**
				 * @todo add param success message.
				 */
				$arResult["SUCCESS_MESSAGE"][] = Loc::getMessage("FORM_SUCCESS_MESSAGE") . "<br />";
			} else {
				$arResult["ERROR_MESSAGE"][] = Loc::getMessage("FORM_ERROR_MESSAGE")."<br />".$el->LAST_ERROR;
			}
		}
	}
}
catch (SecurityException $exception) {
	array_push($arResult["ERROR_MESSAGE"], $exception->getMessage());
}

$this->IncludeComponentTemplate();
