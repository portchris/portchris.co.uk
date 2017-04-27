<?php
/**
* This model defines the content for the sites routes. Each page has content, this is where it is defined.
*
* @author 	Chris Rogers
* @since 	1.0.0 (2017-04-27)
*/

namespace App;

use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
	protected $fillable = [
		'name', 'title', 'slug', 'content', 'meta_title', 'meta_description', 'meta_image_path', 'enabled'
	];

	/**
	* Pages can have many content blocks. This is a one-to-many relationship
	*
	* @return 	object 	ContentMeta
	*/
	public function contentMetas() {
		return $this->hasMany('App\ContentMeta');
	}

	/**
	* Pages are owned by a single user. This is a one-to-many (inverse) relationship
	*
	* @return 	object 	User
	*/
	public function user() {
		return $this->belongsTo('App\User');
	}
}
