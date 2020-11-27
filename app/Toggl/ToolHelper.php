<?php

namespace App\Toggl;

use Illuminate\Http\Request;

class ToolHelper
{
    protected $oRequest;
    
    public function __construct(
        Request $oRequest
    )
    {
        $this->oRequest = $oRequest;
    }

    /**
     * @return bool
     */
    public function cacheEnabled()
    {
        return $this->oRequest->has('enable_cache') ? $this->oRequest->get('enable_cache') : true;
    }
}
