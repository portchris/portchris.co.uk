<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use App\ContentMeta as Messages;

class UserController extends Controller
{
	/**
	 * Try and identify the user and display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index()
	{
		// 
	}

	/**
	 * Show the form for creating a new resource. Should not be accessible
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function create()
	{
		//
	}

	/**
	 * Store a newly created resource in storage. Should not be accessible
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\Response
	 */
	public function store(Request $request)
	{
		//
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  \App\User  $user
	 * @return \Illuminate\Http\Response
	 */
	public function show(User $user) {
		
		//
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  \App\User  $user
	 * @return \Illuminate\Http\Response
	 */
	public function edit(User $user) {
		
		//
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \App\User  $user
	 * @return \Illuminate\Http\Response
	 */
	public function update(Request $request, User $user) {
		
		//
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  \App\User  $user
	 * @return \Illuminate\Http\Response
	 */
	public function destroy(User $user) {
		
		//
	}

	/**
	* Attempt to log the user in using JSON Web Tokens. 
	* Try catch custom exceptions as opposed to leaving it up to app/Exceptions/Handler
	*
	* @param 	Request 	$request
	*/
	public function authenticate(Request $request) {
		
		// Grab credentials from the request
		$credentials = $request->only('email', 'password');
		try {

			// Attempt to verify the credentials and create a token for the user
			if (!$token = JWTAuth::attempt($credentials)) {
				$msg = Messages::create(
					__("Sorry, I have no record of that user, perhaps you mis-typed something. Please try again or type 'Forget it' to continue as a guest."), 
					"answer", 
					"error", 
					 "Incorrect credentials. Code: 401", 
					0, 
					Messages::TYPES["User"],
					"authenticate"
				);
				return response()->json($msg, 401);
			}
		} catch (JWTException $e) {
			
			// Something went wrong whilst attempting to encode the token
			$msg = Messages::create(
				__("Something went wrong. Please try again or say 'Forget it' to continue as a guest."), 
				"answer", 
				"error", 
				get_class($e) . ", code: 500", 
				0, 
				Messages::TYPES["User"],
				"authenticate"
			);
			return response()->json($msg, 500);
		}

		// All good so return the token and relay message back
		$usr = json_decode($this->getAuthenticatedUser(), true);
		$msg = Messages::create(
			__("Welcome back %s.", $usr["name"]), 
			"answer", 
			"success", 
			"User access token grantend code: 200", 
			0, 
			Messages::TYPES["ContentMeta"],
			""
		);
		return response()->json($msg);
	}

	/**
	* Get the logged in user
	*
	* @return 	JSON response of user
	*/
	public function getAuthenticatedUser() {

		if (!$user = JWTAuth::parseToken()->authenticate()) {

			$msg = Message::create(
				__("Sorry, I have no record of that user, perhaps you mis-typed something. Please try again or type 'Forget it' to continue as a guest."), 
				"answer", 
				"error", 
				 "Incorrect credentials. Code: 401", 
				0, 
				Messages::TYPES["User"],
				"authenticate"
			);
			return response()->json($msg, 404);
		}

		// The token is valid and we have found the user via the sub claim
		return response()->json(compact('user'));
	}

	/**
	 * Attempt to identify the user.
	 *
	 * @param  array 	$data
	 * @return \Illuminate\Http\Response 	JSON
	 */
	public function identify($data) {
		
		$return = User::message("Welcome, with whom is it I speak?");
		$username = $data["username"] ?? false;
		$password = $data["password"] ?? false;
		if (Auth::check()) {
			$return = Auth::user();		
		} else if ($username && $password) {
			$return = Auth::attempt([
				'username' => $username, 
				'password' => $password
				]);
		}
		return $return;
	}
}
