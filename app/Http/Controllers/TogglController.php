<?php

namespace App\Http\Controllers;

use App\Toggl\Toggl;
use App\Toggl\ClientProxy;
use App\Toggl\ToolHelper;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;

class TogglController extends Controller
{
    /**
     * @var ClientProxy
     */
    protected $clientProxy;

    /**
     * @var ToolHelper
     */
    protected $toolHelper;

    /**
     * @var Toggl
     */
    protected $toggl;

    /**
     * Display a listing of the resource.
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $this->toolHelper = new ToolHelper($request);
        $this->clientProxy = new ClientProxy($request, $this->toolHelper);
        $this->toggl = new Toggl($this->clientProxy);
        return response()->json(
            $this->toggl->getTimeEntries(
                date("r", strtotime('last week')),
                date('r')
            )
        );
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Request $request
     * @return Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  Request  $request
     * @return Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Toggl  $toggl
     * @return Response
     */
    public function show(Request $request)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  Request $request
     * @return Response
     */
    public function edit(Request $request)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  Request $request
     * @return Response
     */
    public function update(Request $request)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Request $request
     * @return Response
     */
    public function destroy(Request $request)
    {
        //
    }
}
