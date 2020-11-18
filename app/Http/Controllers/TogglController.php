<?php

namespace App\Http\Controllers;

use App\Toggl;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class TogglController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        return response()->json(Toggl::timer());
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
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
     * @param  \App\Toggl  $toggl
     * @return \Illuminate\Http\Response
     */
    public function show(Toggl $toggl)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Toggl  $toggl
     * @return \Illuminate\Http\Response
     */
    public function edit(Toggl $toggl)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Toggl  $toggl
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Toggl $toggl)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Toggl  $toggl
     * @return \Illuminate\Http\Response
     */
    public function destroy(Toggl $toggl)
    {
        //
    }
}
