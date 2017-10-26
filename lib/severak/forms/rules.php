<?php
namespace severak\forms;

class rules
{
	static function required($value, $others)
	{
		return !empty($value);
	}
}