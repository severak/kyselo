<?php
// for testing new features
Flight::route('/act/test', function(){
	throw new Exception('Testing exception page.');
});

