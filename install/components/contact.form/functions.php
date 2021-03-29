<?php

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Text\Encoding;

if (! function_exists('reverse_two_args')):
    function reverse_two_args(callable $cb, $item, $key) {
        return $cb($key, $item);
    }
endif;

if (! function_exists('renderAttribute')):
	function renderAttribute(&$value, $key = '')
	{
		$value = $key . '="' . (is_array($value) ? implode(' ', $value) : $value) . '"';
		return $value;
	}
endif;

function escapePostValue($value)
{
	$esc = static function($value) {
		return Encoding::convertEncodingToCurrent(strip_tags(trim($value)));
	};

	return is_array($value) ? array_map($esc, $value) : call_user_func($esc, $value);
}

/**
 * @todo Add DI
 */
function getFormFieldObject($arProperty) {
	$propType = mb_strtoupper($arProperty["PROPERTY_TYPE"]);
	$userType = mb_strtoupper($arProperty["USER_TYPE"]);

	// String
	if ('S' === $propType):
		if ('HIDDEN' === $userType) {
			return new FormFieldHidden($arProperty);
		}
		elseif ('HTML' === $userType) {
			// return new FormFieldTextarea($arProperty);
		} else {
			return new FormFieldText($arProperty);
		}
	elseif ('L' === $propType):
		$arProperty["OPTIONS"] = [];
		$rsSelectValues = CIBlockProperty::GetPropertyEnum(
			$arProperty["CODE"],
			["SORT" => "ASC", "ID" => "ASC"],
			["IBLOCK_ID" => $arProperty["IBLOCK_ID"]]
		);

		while ($arOption = $rsSelectValues->Fetch()) {
			$arProperty["OPTIONS"][$arOption['XML_ID']] = $arOption;
		}

		if ('C' === $arProperty["LIST_TYPE"]) {
			// Checkboxes.
			// return new FormFieldCheckbox($arProperty);
		} else {
			// Select input.
			// return new FormFieldSelect($arProperty);
		}
	elseif ('N' === $propType):
		// integer.
	elseif ('E' === $propType):
		// iblock element link.
	elseif ('F' === $propType):
		// File input.
		// return new FormFieldFile($arProperty);
	endif;

	throw new Exception('Undefined property type: ' . $propType);
}

function formStoreEventType($eventName, $iblockName = '', $arProperties = []) {
	$arFields = array_map(static function($obField) {
		$code = $obField->getPropertyCode();
		$name = $obField->getPropertyName();

		return "#{$code}# - {$name}";
	}, $arProperties);

	$arEventFields = array(
		"LID" => LANGUAGE_ID,
		"EVENT_NAME" => $eventName,
		"NAME" => GetMessage("CONTACT_FORM_MAIL_EVENT_TYPE_NAME") . ' "' . $iblockName . '"',
		"DESCRIPTION" => GetMessage("CONTACT_FORM_MAIL_EVENT_DESCRIPTION") . "\n" . implode("\n", $arFields),
	);

	return (new CEventType)->Add($arEventFields);
}

function formStoreEventMessage($arProperties, $eventName) {
	$arFields = array_map(static function($obField) {
		$code = $obField->getPropertyCode();
		$name = $obField->getPropertyName();

		return "{$name}: #{$code}#";
	}, $arProperties);

	return (new CEventMessage)->Add([
		"ACTIVE" => "Y",
		"EVENT_NAME" => $eventName,
		"LID" => SITE_ID,
		"EMAIL_FROM" => "#DEFAULT_EMAIL_FROM#",
		"EMAIL_TO" => "#DEFAULT_EMAIL_FROM#",
		"BCC" => "",
		"SUBJECT" => GetMessage("CONTACT_FORM_MAIL_EVENT_MESSAGE_SUBJECT"),
		"BODY_TYPE" => "text",
		"MESSAGE" => GetMessage("CONTACT_FORM_MAIL_EVENT_MESSAGE_MESSAGE_HEADER")
			. implode("\n", $arFields) . "\n"
			. GetMessage("CONTACT_FORM_MAIL_EVENT_MESSAGE_MESSAGE_FOOTER")
	]);
}
