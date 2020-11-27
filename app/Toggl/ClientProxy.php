<?php

namespace App\Toggl;

// use App\Http\Controllers\TogglController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Ixudra\Toggl\TogglService;
use Namshi\Cuzzle\Formatter\CurlFormatter;

/**
 * Class ClientProxy
 * @package App\Toggl
 * @mixin TogglApi
 */
class ClientProxy
{
    /**
     * @var bool
     */
    protected $bEnableCache;

    /**
     * @var mixed
     */
    protected $oLastCommand;

    /** 
     * @var TogglApi 
     */
    protected $oClient;

    /** 
     * @var  ToolHelper 
     */
    protected $oToolHelper;

    /** 
     * @var Request  
     */
    protected $oRequest;

    /**
     * @param Request $request
     * @param ToolHelper $oToolHelper
     */
    public function __construct(
        Request $oRequest,
        ToolHelper $oToolHelper
    ) {
        $this->bEnableCache = $oToolHelper->cacheEnabled();
        $this->bEnableCache = $this->bEnableCache ? !$oRequest->get('_by_pass_cache') : false;
        $this->oRequest = $oRequest;
        $this->resetClient();
    }

    public function resetClient()
    {
        $vTogglApiKey = $this->getKey();
        $vTogglWorkspace = $this->getWorkspace();
        $this->oClient = new TogglService($vTogglWorkspace, $vTogglApiKey);
        // echo "<pre>"; var_dump($this->oClient->summaryThisMonth()); die;
    }

    /**
     * @param string $methodName
     * @param mixed $args
     * @return mixed
     */
    // public function __call($methodName, $args)
    // {
    //     $vCacheKey = $this->getCacheKey($methodName, $args);
    //     if ($this->cacheMethod($methodName) && $vCacheKey) {
    //         $cachedValue =  Cache::get($vCacheKey);
    //         if ($cachedValue) {
    //             return $cachedValue;
    //         }
    //     }
    //     try {
    //         $return = call_user_func_array(array($this->oClient, $methodName), $args);
    //     } catch (\Exception $e) {
    //         $vRequest = (new CurlFormatter())->format($this->oClient->oLastRequest, []);
    //         throw $e;
    //     }
    //     $vRequest = (new CurlFormatter())->format($this->oClient->oLastRequest, []);

    //     if ($vCacheKey) {
    //         Cache::put($vCacheKey, $return, 20);
    //     }
    //     return $return;
    // }

    /**
     * @return TogglService
     */
    public function getClient()
    {
        return $this->oClient;
    }

    /**
     * @param string $methodName
     */
    protected function cacheMethod($methodName)
    {
        if ($this->bEnableCache && (substr($methodName, 0, 3) == 'get')) {
            return true;
        }
    }

    /**
     * @param string $methodName
     * @param mixed $args
     * @return string
     */
    protected function getCacheKey($methodName, $args)
    {
        $vMethodType = substr($methodName, 0, 3);
        if ($vMethodType == 'get') {
            $vCacheKey = implode('-', [$this->getKey(), $methodName, @json_encode($args)]);
            return sha1($vCacheKey);
        }
    }

    public function isValidKey()
    {
        try {
            $this->resetClient();
            $this->oClient->getClients();
        } catch (\Exception $e) {
            return false;
        }
        return true;
    }

    /**
     * @return string
     */
    public function getKey()
    {
        return $this->oRequest->cookie('toggl_api') ?: @$_ENV['TOGGL_TOKEN'];
    }

    /**
     * @return string
     */
    public function getWorkspace()
    {
        return $this->oRequest->cookie('toggl_workspace') ?: @$_ENV['TOGGL_WORKSPACE'];
    }

    /**
     * @return bool
     */
    public function getCacheEnable()
    {
        return $this->bEnableCache;
    }

    /**
     * @param bool $bStatus
     */
    public function setEnableCache($bStatus)
    {
        $this->bEnableCache = $bStatus;
    }
}
