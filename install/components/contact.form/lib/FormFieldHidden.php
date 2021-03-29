<?php

use Bitrix\Main\Localization\Loc;

class FormFieldHidden extends FormField
{
	public function __construct($arProperty)
	{
		parent::__construct($arProperty);

		$this->obAttributes->add([
			'value' => $this->getValue(),
			'type' => $this->getFieldType(),
		]);
	}

	public function getFieldType(): string
	{
		return 'hidden';
	}

	public function input(): string
	{
		return '<input ' . $this->obAttributes->render() . '>';
	}

	public function group(): string
	{
		return $this->input();
	}

	public function setValue($value = null)
	{
		$this->setFieldValue($value);
	}

	public function validate(): bool
	{
		$arProperty = $this->getProperty();
		if ('Y' === $arProperty["IS_REQUIRED"] && empty($arProperty['VALUE'])) {
			array_push($this->arErrors, Loc::getMessage("FORM_ERROR_FIELD_REQUIRED", [
				"#FIELD#" => $arProperty["NAME"]
			]));
		}

		return sizeof($this->arErrors) === 0;
	}
}