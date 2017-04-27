<?php
/**
* User roles, provides a level of security. Preventing the wrong users from doing admin functionality
*
* @author 	Chris Rogers
* @since 	1.0.0 (2017-04-27)
*/

namespace App;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
	protected $fillable = ['name', 'description'];

	/**
	* Roles can have many users. Equally users can have many roles. This is a many-to-many relationship 
	*
	* @return 	object 	user
	*/
	public function users() {

		return $this->belongsToMany('App\User', 'users_roles', 'role_id', 'user_id');
	}
}
