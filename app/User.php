<?php
/**
* Users need no explanation, here it is.
*
* @author   Chris Rogers
* @since    1.0.0 (2017-04-27)
*/

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'firstname', 'lastname', 'email', 'username', 'password', 'lat', 'lng', 'stage'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token', 'enabled'
    ];

    /**
    * Roles can have many users. Equally users can have many roles. This is a many-to-many relationship 
    *
    * @return   object  role
    */
    public function roles() {
        return $this->belongsToMany('App\Role', 'users_roles', 'user_id', 'role_id');
    }

    /**
    * Pages are owned by a single user. This is a one-to-many relationship
    *
    * @return   object  Page
    */
    public function pages() {
        return $this->hasMany('Page');
    }
}
