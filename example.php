<?php

require_once ('vendor/autoload.php');


$client = new Smartling\Client('APIKEY', 'PROJECTID', true);

try {
	$response = $client->upload('composer.json', 'TESTTEST.json', 'json', false);
	var_dump($response);
}
catch (Smartling\Exception $e)
{
	var_dump(get_class($e));
	var_dump($e->getMessage());
}

try {
	$response = $client->status('TESTTEST.json', 'de-DE');
	var_dump($response);
}
catch (Smartling\Exception $e)
{
	var_dump(get_class($e));
	var_dump($e->getMessage());
}

try {
	$response = $client->get('TESTTEST.json', 'fetched.json', 'de-DE');
	var_dump($response);
}
catch (Smartling\Exception $e)
{
	var_dump(get_class($e));
	var_dump($e->getMessage());
}

try {
	$response = $client->files(array(
		'locale' => 'de-DE'
	));
	var_dump($response);
}
catch (Smartling\Exception $e)
{
	var_dump(get_class($e));
	var_dump($e->getMessage());
}
