<?php

namespace App;

use Google_Service_Sheets;
use Google\Client;
use Revolution\Google\Sheets\Sheets as GoogleSheets;
use Illuminate\Database\Eloquent\Model;

class Sheets extends Model
{
    /** @var GoogleSheets */
    private $sheets;

    public function __construct()
    {
        $client = new Client();
        $client->setScopes([Google_Service_Sheets::DRIVE, Google_Service_Sheets::SPREADSHEETS]);
        $client->setApplicationName('Resource Planning');
        $client->setScopes(Google_Service_Sheets::SPREADSHEETS_READONLY);
        $client->setAuthConfig(storage_path(env('GOOGLE_SERVICE_ACCOUNT_JSON_LOCATION', '../storage/credentials.json')));
        $client->setAccessType('online');
        $client->setPrompt('select_account consent');

        /** @var \Google_Client $client */
        $service = new Google_Service_Sheets($client);
        $this->sheets = new GoogleSheets();
        $this->sheets->setService($service);
    }

    /**
     * Where the magic happens
     * @return array
     */
    public static function resourcePlanner(): array
    {
        $r = __('ERROR: Something went wrong trying to obtain the Convert Digital Resource Planning Spreadsheet');
        $self = new self();
        $range = 'A21:Q23';
        $spreadsheet = $self->sheets
            ->spreadsheet(env('POST_SPREADSHEET_ID', '16FOESipZyh_gZK3mnsVzba3R3ZDQXz0KWtTFn1bAmQQ'))
            ->sheet(date("d/m", strtotime('monday this week')));

        $values = $spreadsheet->all();
        foreach ($values as $rowId => $row) {
            if (is_array($row) && !empty($row) && strpos(strtolower($row[0]), 'chris') !== false) {
                $range = "A" . ((int)$rowId + 1) . ":Q" . ((int)$rowId + 4);
                break;
            }
        }

        $spreadsheetChris = $self->sheets
            ->spreadsheet(env('POST_SPREADSHEET_ID', '16FOESipZyh_gZK3mnsVzba3R3ZDQXz0KWtTFn1bAmQQ'))
            ->sheet(date("d/m", strtotime('monday this week')))
            ->range($range);

        $valuesChris = $spreadsheetChris->all() ?: [];

        if (is_array($valuesChris) && !empty($valuesChris)) {
            $mon = $tue = $wed = $thur = $fri = [];
            $segments = array_chunk(array_filter($self->arrayFlatten($valuesChris)), 12);
            if (count($segments[0]) >= 10) {
                foreach ($segments as $segment) {
                    $mon[] = $segment[2];
                    $mon[] = $segment[3];
                    $tue[] = $segment[4];
                    $tue[] = $segment[5];
                    $wed[] = $segment[6];
                    $wed[] = $segment[7];
                    $thur[] = $segment[8];
                    $thur[] = $segment[9];
                    $fri[] = $segment[10];
                    $fri[] = (isset($segment[11])) ? $segment[11] : __("Go Home!");
                }
                $r = [
                    __('Monday') => $mon,
                    __('Tuesday') => $tue,
                    __('Wednesday') => $wed,
                    __('Thursday') => $thur,
                    __('Friday') => $fri
                ];
            } else {
                $r = $segments;
            }
        } else {
            $r = __('Specific value range for Chris Rogers is not available');
        }
        return $r;
    }

    /**
     * @param array $array
     * @return array $result
     */
    private function arrayFlatten(array $array): array
    {
        if (!is_array($array)) {
            return FALSE;
        }
        $result = array();
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $result = array_merge($result, $this->arrayFlatten($value));
            } else {
                $result[$key] = $value;
            }
        }
        return $result;
    }
}
