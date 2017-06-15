<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\Request;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;
use App\ContentMeta as Messages;

class AuthenticateUserRequest extends FormRequest
{
	/**
	 * Determine if the user is authorized to make this request.
	 *
	 * @return bool
	 */
	public function authorize() {

		return true;
	}

	/**
	 * Get the validation rules that apply to the request.
	 *
	 * @return array
	 */
	public function rules() {

		return [
			'email' => 'email',
			// 'username' => 'required|max:255|unique:users',
			// 'password' => 'required|min:6|regex:/^.*(?=.{3,})(?=.*[a-zA-Z])(?=.*[0-9])(?=.*[\d\X])(?=.*[!$#%]).*$/',
			'password' => 'required|min:1'
		];
	}

	/**
	* Customise the format of the errors when validation fails, return JSON in Handler.php
	* {@inheritdoc}
	*
	* @see \Illuminate\Validation\ValidatesWhenResolvedTrait for more info.
	* @param Validator $validator
	* @throws ValidationException
	*/
	protected function failedValidation(Validator $validator) {

		throw new ValidationException($validator);
	}
}
