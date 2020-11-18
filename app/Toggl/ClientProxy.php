<?php

namespace App\Toggl;

use App\Http\Controllers\TogglController;
use Illuminate\Support\Facades\Cache;

/**
 * Class ClientProxy
 * @package App\Toggl
 *
 * @mixin \MorningTrain\TogglApi\TogglApi
 */
class ClientProxy
{
    protected $bEnableCache;
    protected $oLastCommand;

    /** * @var \MorningTrain\TogglApi\TogglApi */
    protected $oClient;

    /** @var  ToolHelper */
    protected $oToolHelper;
    /** @var \Illuminate\Http\Request  */
    protected $oRequest;

    public function __construct(\Illuminate\Http\Request $oRequest, ToolHelper $oToolHelper)
    {
        $this->bEnableCache = $oToolHelper->cacheEnabled();
        $this->bEnableCache = $this->bEnableCache ? !$oRequest->get('_by_pass_cache') : false;
        $this->oRequest = $oRequest;
        $this->resetClient();
    }

    public function resetClient()
    {
        $vTogglApiKey = $this->getKey();
        $this->oClient = new \MorningTrain\TogglApi\TogglApi($vTogglApiKey);
    }

    /**
     *
     * @param $methodName
     * @param $args
     *
     * @return mixed
     */
    public function __call($methodName, $args)
    {
        $vCacheKey = $this->getCacheKey($methodName, $args);
        if ($this->cacheMethod($methodName) && $vCacheKey) {
            $cachedValue =  Cache::get($vCacheKey);
            if ($cachedValue) {
                return $cachedValue;
            }
        }
        try {
            $return = call_user_func_array(array($this->oClient, $methodName), $args);
        } catch (\Exception $e) {
            $vRequest = (new \Namshi\Cuzzle\Formatter\CurlFormatter())->format($this->oClient->oLastRequest, []);
            throw $e;
        }
        $vRequest = (new \Namshi\Cuzzle\Formatter\CurlFormatter())->format($this->oClient->oLastRequest, []);

        if ($vCacheKey) {
            Cache::put($vCacheKey, $return, 20);
        }
        return $return;
    }

    protected function cacheMethod($methodName)
    {
        if ($this->bEnableCache && (substr($methodName, 0, 3) == 'get')) {
            return true;
        }
    }

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

    protected function getKey()
    {
        return $this->oRequest->cookie('toggl_api') ?: @$_ENV['TOGGL_API_KEY'];
    }

    public function getCacheEnable()
    {
        return $this->bEnableCache;
    }
    
    public function setEnableCache($bStatus)
    {
        $this->bEnableCache = $bStatus;
    }
}
