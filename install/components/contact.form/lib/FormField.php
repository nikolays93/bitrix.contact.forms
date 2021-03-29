<?php

abstract class FormField implements iFormField
{
	protected $arProperty = [];
	protected $obAttributes;
	protected $value;

	protected $arErrors = [];

	function __construct(array $arProperty = [])
	{
		$this->arProperty = $arProperty;
		$this->obAttributes = new FormAttributes($this);
	}

	/**
	 * Getters
	 * ********************************************************************** */

	public function getProperty()
	{
		return $this->arProperty;
	}

	public function getPropertyCode()
	{
		return $this->arProperty['CODE'] ?? '';
	}

	public function getPropertyName()
	{
		return $this->arProperty['NAME'] ?? '';
	}

	public function isRequired(): bool
	{
		return 'Y' === $this->arProperty["IS_REQUIRED"];
	}

	public function getErrors()
	{
		return $this->arErrors;
	}

	public function getValue()
	{
		return $this->value;
	}

	public function setValue($value = null)
	{
		$this->value = $value;
	}

	/**
	 * Construct HTML
	 * ********************************************************************** */

	abstract function getFieldType(): string;

	public function label(): string
	{
		$label = $this->arProperty["NAME"];

		if ('Y' === $this->arProperty['IS_REQUIRED']) {
			$label .= '<span class="req" style="color: red">*</span>';
		}

		return '<label for="' . $this->obAttributes->get('id') . '">' . $label . '</label>';
	}

	abstract public function input(): string;

	public function hint(): string
	{
		if (! empty($this->arProperty["HINT"])) {
			return '<small class="form-text text-muted">'
				. $this->arProperty["HINT"] . '</small></div>';
		}

		return '';
	}

	public function group(): string
	{
		$field = [];
		$field[] = '<div class="form-group">';
		$field[] = "\t" . $this->label();
		$field[] = "\t" . $this->input();
		$field[] = "\t" . $this->hint();
		$field[] = '</div>';

		return implode("\n", $field);
	}

	abstract public function validate(): bool;
}
