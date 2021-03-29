<?php

interface iFormField
{
	// Validate methods.
	public function getErrors();
	public function getValue();
	public function setValue();
	public function validate(): bool;
	// Required in attributes.
	public function getProperty();
	public function getFieldType(): string;
	// Show methods.
	public function label(): string;
	public function input(): string;
	public function group(): string;
}
