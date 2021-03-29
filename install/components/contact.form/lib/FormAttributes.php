<?php

class FormAttributes
{
    private $arAttributes = [];

    public function __construct(iFormField $obProperty)
    {
        $arProperty = $obProperty->getProperty();

        $this->arAttributes = [
			'id' => mb_strtolower("form_{$arProperty['IBLOCK_ID']}--{$arProperty["CODE"]}"),
			'class' => [
				'form-control',
				$obProperty->getFieldType() . '-control'
			],
			'name' => $arProperty["CODE"],
		];

        if ('Y' === $arProperty['IS_REQUIRED']) {
			$arAttributes['required'] = true;
			$arAttributes['class'][] = 'required';
		}

		if ('Y' === $arProperty["MULTIPLE"]) {
			$arAttributes['class'][] = 'multiple';
		}
    }

    public function add($key, $value = true)
    {
        if (is_array($key)) {
            $this->arAttributes = array_merge($this->arAttributes, $key);
        } else {
            $this->arAttributes[$key] = $value;
        }
    }

    public function push($key, $value)
    {
        if (! is_array($this->arAttributes[$key])) {
            throw new Exception('Attribute "' . $key . '" is not array.');
        }

        $this->arAttributes[$key][] = $value;
    }

    public function get($key, $default = null)
    {
        $this->arAttributes[$key] ?? $default;
    }

    public function remove($key)
    {
        unset($this->arAttributes[$key]);
    }

    public function render()
    {
        /** @var array $arAttributes $this->arAttributes duplicate (for idempotent save) */
        $arAttributes = $this->arAttributes;
        // Merge key value.
        array_walk($arAttributes, 'renderAttribute');
        // To string.
		return implode(' ', $arAttributes);
    }
}
