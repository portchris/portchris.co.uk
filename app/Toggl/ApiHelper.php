<?php

namespace App\Toggl;

use App\Toggl\ClientProxy;
use Illuminate\Support\Facades\Cache;

class ApiHelper
{
    /** @var string */
    protected $key;

    protected $bEnableCache;

    /** @var  ClientProxy */
    protected $oClientProxy;

    /** @var \Illuminate\Http\Request */
    protected $oRequest;

    public function __construct(ClientProxy $oClientProxy, $vTogglApiKey)
    {
        $this->key = $vTogglApiKey;
        $this->oClientProxy = $oClientProxy;
    }

    public function resetClient()
    {
        $this->oClient = new \MorningTrain\TogglApi\TogglApi($this->key);
    }

    public function getProjects()
    {
        $aProjects = $this->oClientProxy->getProjects();
        return $aProjects;
    }

    public function getTimeEntries($vStartDate, $vEndDate)
    {
        $aParam = [];
        if ($vStartDate) {
            $aParam['start_date'] = $vStartDate;
        }
        if ($vEndDate) {
            $aParam['end_date'] = $vEndDate;
        }
        $aTimeList = $this->oClientProxy->getTimeEntries($aParam);
        return $aTimeList;
    }

    public function isValidKey()
    {
        return $this->oClientProxy->isValidKey();
    }

    public function updateEntry($aTask)
    {
        $iId = $aTask['id'];
        return $this->oClientProxy->updateTask($iId, $aTask);
    }

    public function getCacheStatus()
    {
        return $this->oClientProxy->getCacheEnable();
    }

    public function setCacheEnable($bStatus)
    {
        $this->oClientProxy->setEnableCache($bStatus);
    }
}
