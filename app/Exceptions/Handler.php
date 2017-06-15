<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use App\ContentMeta as Messages;

class Handler extends ExceptionHandler
{
	/**
	 * A list of the exception types that should not be reported.
	 *
	 * @var array
	 */
	protected $dontReport = [
		\Illuminate\Auth\AuthenticationException::class,
		\Illuminate\Auth\Access\AuthorizationException::class,
		\Symfony\Component\HttpKernel\Exception\HttpException::class,
		\Illuminate\Database\Eloquent\ModelNotFoundException::class,
		\Illuminate\Session\TokenMismatchException::class,
		\Illuminate\Validation\ValidationException::class,
	];

	/**
	 * Report or log an exception.
	 *
	 * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
	 *
	 * @param  \Exception  $exception
	 * @return void
	 */
	public function report(Exception $exception) {

		parent::report($exception);
	}

	/**
	 * Render an exception into an HTTP response.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Exception  $exception
	 * @return \Illuminate\Http\Response
	 */
	public function render($request, Exception $exception) {

		if (
			$exception instanceof \Tymon\JWTAuth\Exceptions\TokenExpiredException
			|| $exception instanceof \Tymon\JWTAuth\Exceptions\JWTException
			|| $exception instanceof \Tymon\JWTAuth\Exceptions\TokenInvalidException
			) {
			
			// Token expired they must login again
			$msg = Messages::create([
				'content' => __("Do I recognise you? Can you remind me by giving me your email address?"), 
				'key' => "answer", 
				'name' => "warning", 
				'title' => get_class($exception) . ", code: " . $exception->getStatusCode(), 
				'stage' => 0, 
				'type' => Messages::TYPES["User"],
				'method' => "authenticate"
			]);
			return response()->json($msg, $exception->getStatusCode());
		} else if ($exception instanceof \Illuminate\Validation\ValidationException) {

			// Validation of user or message request failed
			$content = implode(" ", $exception->validator->errors()->all());
			return response()->json(Messages::create([
				"content" => $content,
				'key' => 'answer',
				'name' => 'error',
				'title' => get_class($exception) . ", code: 401",
				'type' => Messages::TYPES["User"],
				'method' => 'authenticate'
			]), 401);
		}
		return parent::render($request, $exception);
	}

	/**
	 * Convert an authentication exception into an unauthenticated response.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Illuminate\Auth\AuthenticationException  $exception
	 * @return \Illuminate\Http\Response
	 */
	protected function unauthenticated($request, AuthenticationException $exception) {

		if ($request->expectsJson()) {
			return response()->json(['error' => 'Unauthenticated.'], 401);
		}
		return redirect()->guest('login');
	}
}
