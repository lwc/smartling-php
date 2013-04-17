<?php

namespace Smartling\Errors;

class ErrorFactory
{
	public static function createForGuzzleException($exception)
	{
		$outer = $exception->getResponse()->json();
		$response = $outer['response'];

		$exceptionMap = array(
			'VALIDATION_ERROR' => 'Smartling\Errors\ValidationError',
			'AUTHENTICATION_ERROR' => 'Smartling\Errors\AuthenticationError',
			'AUTHORIZATION_ERROR' => 'Smartling\Errors\AuthenticationError',
			'GENERAL_ERROR' => 'Smartling\Errors\GeneralError',
			'MAINTENANCE_MODE_ERROR' => 'Smartling\Errors\MaintenanceModeError',
			'INSUFFICIENT_FUNDS' => 'Smartling\Errors\InsufficientFundsError',
		);
		if (isset($exceptionMap[$response['code']])) {
			$className = $exceptionMap[$response['code']];
			$message = implode(', ', $response['messages']);
		}
		else {
			$className = 'Smartling\Errors\BadResponseError';
			$message = $exception->getMessage();
		}
		return new $className($message, $exception->getResponse()->getStatusCode(), $exception);
	}
}