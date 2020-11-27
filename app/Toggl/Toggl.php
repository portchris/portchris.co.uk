<?php

namespace App\Toggl;

use App\Toggl\ClientProxy;
use Ixudra\Toggl\TogglService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
// use Illuminate\Support\Facades\Cache;


class Toggl extends Model
{
    protected $bEnableCache;

    /** 
     * @var  ClientProxy  
     */
    protected $oClientProxy;

    /** 
     * @var Request 
     */
    protected $oRequest;

    /** 
     * @param ClientProxy 
     */
    public function __construct(
        ClientProxy $oClientProxy
    ) {
        $this->oClientProxy = $oClientProxy;
    }

    public function resetClient()
    {
        $vTogglApiKey = $this->oClientProxy->getKey();
        $vTogglWorkspace = $this->oClientProxy->getWorkspace();
        $this->oClient = new TogglService($vTogglWorkspace, $vTogglApiKey);
    }

    /**
     * @return mixed $aProjects
     */
    public function getProjects()
    {
        $aProjects = $this->oClient->getProjects();
        return $aProjects;
    }

    /**
     * @param string $vStartDate
     * @param string $vEndDate
     * @return 
     */
    public function getTimeEntries($vStartDate, $vEndDate)
    {
        $aParam = [];
        if ($vStartDate) {
            $aParam['start_date'] = $vStartDate;
        }
        if ($vEndDate) {
            $aParam['end_date'] = $vEndDate;
        }
        $aTimeList = $this->oClientProxy->getClient()->summaryThisMonth();
        return $aTimeList;
    }

    /**
     * @return bool
     */
    public function isValidKey()
    {
        return $this->oClientProxy->isValidKey();
    }

    /**
     * @param array $aTask
     */
    public function updateEntry($aTask)
    {
        $iId = $aTask['id'];
        // Not working
        //$task = $this->oClientProxy->getTask($iId);
        // Not working
        return $this->oClient->updateTask($iId, $aTask);
    }

    /**
     * @return bool
     */
    public function getCacheStatus()
    {
        return $this->oClientProxy->getCacheEnable();
    }

    /**
     * @param bool
     */
    public function setCacheEnable($bStatus)
    {
        $this->oClientProxy->setEnableCache($bStatus);
    }
}
