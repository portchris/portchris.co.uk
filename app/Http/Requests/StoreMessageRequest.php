<?php
/**
* Validate a store request for the MessageStreamController
*
* @author 	Chris Rogers
* @since 	1.0.0 <2017-05-07>
*/
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Controllers\MessageStreamController;
use App\Http\Requests\Request;
use Illuminate\Contracts\Validation\Validator;
use App\ContentMeta as Messages;

class StoreMessageRequest extends FormRequest
{
	/**
	* Determine if the user is authorized to make this request. Currently all users can create messages
	*
	* @return 	bool
	*/
	public function authorize() {
		
		return true;
	}

	/**
	* Get the validation rules that apply to the request.
	*
	* @return 	array
	*/
	public function rules() {

		return [
			'key' => 'required|max:255',
			'content' => 'required',
			'user_id' => 'required|numeric',
			'page_id' => 'required|numeric',
			'stage' => 'required|numeric',
			'method' => 'required',
			'type' => 'required'
		];
	}

	/**
	* Customise the format of the errors, return JSON 
	* {@inheritdoc}
	*
	* @return 	JSON
	*/
	protected function formatErrors(Validator $validator) {

		$MessageStreamController = new MessageStreamController();
		return (array)$MessageStreamController->error(implode(", ", $validator->errors()->all()));
	}
}
