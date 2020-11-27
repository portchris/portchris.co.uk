<?php

namespace App\Toggl;

use Illuminate\Http\Request;
use GuzzleHttp\Client;

class JiraHelper
{
    protected $vBaseUrl;
    protected $vAuthKey;

    /** @var  Client */
    protected $oClient;

    /** @var  TimeEntries */
    protected $oTimeHelper;

    public function __construct(
        Request $oRequest,
        TimeEntries $oTimeHelper
    ) {

        $this->oTimeHelper = $oTimeHelper;

        // Move logic to java script
        if (empty($_COOKIE['jira_config'])) {
            return;
        }
        $vJiraConfig = $_COOKIE['jira_config'];
        $aJsonConfig = json_decode($vJiraConfig, true);
        $this->vBaseUrl = $aJsonConfig['base_url'];
        $this->vAuthKey = $aJsonConfig['auth_key'];
        if (!$this->vBaseUrl || !$this->vAuthKey) {
            throw (new \Exception('Need Jira base url and authentication before can continue'));
        }
        $this->vBaseUrl = rtrim($this->vBaseUrl, "/") . '/';

        // Create a client and provide a base URL
        $this->oClient = new Client(['base_uri' => $this->vBaseUrl . 'rest/api/2']);
    }

    /**
     * @param string $vIssue
     * @param string $vStartedAt
     * @param string|int $vTime
     * @param string $vComment
     */
    public function addTimeLog($vIssue, $vStartedAt, $vTime, $vComment)
    {
        $headers = [
            'Authorization' => 'Basic ' . $this->vAuthKey,
            'content-type', 'application/json'
        ];
        $aData = [
            'comment'   => $vComment,
            'started'   => $this->jiraRestDateFormat($vStartedAt),
            'timeSpent' => $this->oTimeHelper->getJiraTime($vTime, false)
        ];
        $vData = json_encode($aData, JSON_PRETTY_PRINT);
        return $this->oClient->post(
            "issue/$vIssue/worklog", 
            null, 
            [
                'json' => $vData,
                'headers' => $headers
            ]
        );
    }

    /**
     * @param string
     */
    public function jiraRestDateFormat($vTime)
    {
        $vFormatted = date(DATE_ISO8601, strtotime($vTime));
        if (!strpos($vFormatted, '.')) {
            /**
             * Jira is not pure compatible with ISO8601, it won't work until you have decimal place in date
             * Otherwise it results in an Internal Server Error (which is strange)
             *
             * Also note that PHP recommends using DATE_ATOM for full compatibility with
             * ISO8601 but it is even worse (basically it uses 00 time zone)
             *
             * Sample: 2017-04-18T10:19:41.0+0930
             */
            $vFormatted = str_replace("+", ".0+", $vFormatted);
        }
        return $vFormatted;
    }
}
