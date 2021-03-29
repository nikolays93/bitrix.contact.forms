<?php

use Bitrix\Main\Localization\Loc;

class FormFieldText extends FormField implements iFormField
{
	/** @var array */
	private $arVariables = [];

	public function __construct($arProperty)
	{
		global $APPLICATION;

		parent::__construct($arProperty);

		$this->arVariables = [
			'#CURRENT_PAGE#' => $APPLICATION->GetCurPage(),
		];

		$this->obAttributes->add([
			'value' => $this->getValue(),
			'type' => $this->getFieldType(),
			'placeholder' => $this->arProperty["DEFAULT_VALUE"] ?: '',
		]);
	}

	function getFieldType(): string
	{
		if (false !== strpos(mb_strtoupper($this->arProperty["CODE"]), 'EMAIL')) return 'email';
		if (false !== strpos(mb_strtoupper($this->arProperty["CODE"]), 'PHONE')) return 'tel';
		return 'text';
	}

	public function input(): string
	{
		return '<input ' . $this->obAttributes->render() . '>';
	}

	public function setValue($value = null)
	{
		$this->value = str_replace(array_keys($this->arVariables),
			$this->arVariables, $valueRequest);
	}

	public function validate(): bool
	{
		if (empty($this->getValue()) && $this->isRequired()) {
			array_push($this->arErrors, Loc::getMessage("CONTACT_FORM_ERROR_FIELD_REQUIRED", [
				"#FIELD#" => $this->getPropertyName(),
			]));
		}

		if (! empty($this->getValue())) {
			if ('email' === $this->getFieldType() && mb_strlen($this->getValue()) < 6) {
				array_push($this->arErrors, Loc::getMessage("CONTACT_FORM_ERROR_FIELD_INVALID", [
					"#FIELD#" => $this->getPropertyName(),
				]));
			}

			if ('tel' === $this->getFieldType()) {
				$len = mb_strlen($this->getValue());

				if ($len < 5 || $len > 20) {
					array_push($this->arErrors, Loc::getMessage("CONTACT_FORM_ERROR_FIELD_INVALID", [
						"#FIELD#" => $this->getPropertyName(),
					]));
				}
			}
		}

		return sizeof($this->arErrors) === 0;
	}
}
