<?php
/**
* Validate a store request for the UserController
*
* @author 	Chris Rogers
* @since 	1.0.0 <2017-06-03>
*/
namespace App\Http\Requests;

// use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;
use App\ContentMeta as Messages;

class StoreUserRequest extends Request
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
	* @todo 	The password doesn't need to be strong right now because information isn't sensitive.
	* 			But this may change in the future, I have a commented out a secure regex but haven't built the 
	* 			functionality around it yet (message.compnent.ts).
	* @return   array
	*/
	public function rules() {

		return [
			'email' => 'unique:users|email',
			'username' => 'required|max:255|unique:users',
			// 'password' => 'required|min:6|regex:/^.*(?=.{3,})(?=.*[a-zA-Z])(?=.*[0-9])(?=.*[\d\X])(?=.*[!$#%]).*$/',
			'password' => 'required|min:1',
			'name' => 'required|max:255',
			'firstname' => 'required|max:255',
			'lastname' => 'required|max:255',
			'stage' => 'required|numeric',
			'lat' => 'required|numeric',
			'lng' => 'required|numeric'
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
