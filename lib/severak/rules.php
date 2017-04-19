<?php
namespace severak;
class rules
{
	static function required()
	{
		return function($v, $f) {
			return !empty($v);
		};
	}
	
	
}
