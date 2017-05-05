<?php

namespace App\Http\Controllers;

use App\Page;
use Illuminate\Http\Request;

class PagesController extends Controller
{
	public const HOMEPAGE_KEY = "home";

	/**
	* Display a listing of the resource.
	*
	* @return \Illuminate\Http\Response
	*/
	public function index() {

		$homepage = Page::where("slug", "=", "/")->first();
		// $homepage = Page::all();
		return response()->json($homepage);
	}
 
	/**
	* Display the specified resource. ID can either be slug or ID of page
	*
	* @param  int | string  $id
	* @return \Illuminate\Http\Response
	*/
	public function show($id) {

		$id = ($id === self::HOMEPAGE_KEY) ? "/" : $id;
		$page = (is_numeric($id) && $id !== self::HOMEPAGE_KEY) ? Page::find($id) : Page::where("slug", "=", $id)->first();
		return response()->json($page);
	}
}
