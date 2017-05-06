<?php
/**
* Content Metas are specific to pages, they are content blocks. There is added functionality that 
* allows content metas to be used as questions and answers thus creating our text based adventure.
*
* @author 	Chris Rogers
* @since 	1.0.0 (2017-04-27)
*/

namespace App;

use Illuminate\Database\Eloquent\Model;

class ContentMeta extends Model
{
	protected $fillable = [
		'id_linked_content_meta', 'name', 'title', 'key', 'content', 'stage', 'user_id', 'page_id'
	];

	/**
	* ContentMetas belong to pages. This is a one-to-many (inverse) relationship. 
	*
	* @return 	object 	Page
	*/
	public function page() {
		return $this->belongsTo('App\Page');
	}

	/**
	* ContentMetas also belong to users. Again this is a one-to-many (inverse) relationship. 
	*
	* @return 	object 	User
	*/
	public function user() {
		return $this->belongsTo('App\User');
	}
}
