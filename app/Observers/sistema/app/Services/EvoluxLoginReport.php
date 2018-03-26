<?php

namespace App\Services;

use GuzzleHttp\Client                     as HttpClient;
use GuzzleHttp\Exception\ClientException  as HttpClientException;
use GuzzleHttp\Exception\ConnectException as ConnectException;
use League\Csv\Writer;
use Monolog\Logger;

class EvoluxLoginReport
{

    private $pauses    = [];
    private $lines     = [];
    private $token     = 'c663cb14-30bf-4629-be1e-1c40b2f8b128';
    private $url_login = "https://evolux.contactamax.com/api/v1/report/logon";
    private $url_pause = "https://evolux.contactamax.com/api/v1/report/complete_pause";
    private $user;
    private $start_date;
    private $end_date;

    public function __construct($user, $start_date, $end_date = "")
    {
        $this->user       = $user;
        $this->start_date = $start_date;
        $this->end_date   = ($end_date == "") ? $start_date : $end_date;
    }


    private function remoteRequest($var_id, $url = 'pause')
    {
        sleep(1);
        $query = [
            'token'      => $this->token     ,
            'start_date' => $this->start_date,
            'end_date'   => $this->end_date
        ];

        if (in_array($url, ['pause','login'])) {
            $var      = ($url == 'pause') ? 'agent_id' : 'queue_id';
            $query   += [ $var => $var_id ];
            $url_type = "url_" . $url;
            $url      = $this->$url_type;
        }

        $http = new HttpClient;
        $request = $http->request('GET', $url, [
            'query' => $query,
            //'debug' => true,
        ]);
        $pause_body = json_decode($request->getBody()->__toString());
        if (isset($pause_body->data)) {
            return $pause_body->data;
        } else {
            return false;
        }
    }

    private function getPauses($agent_id)
    {
        $pause_body = $this->remoteRequest($agent_id, 'pause');
        if ($pause_body !== false && !isset($this->pauses[$agent_id])) {
            $this->pauses[$agent_id] = [];
            foreach ($pause_body as $sreg) {
                if (strpos($sreg->description, 'NR17') !== false) {
                    //$pauses[$sreg->agent->id]['user'] = $sreg->agent->login;
                    $this->pauses[$sreg->agent->id][$sreg->description][] = [
                        'time_start'  => strtotime($sreg->time_start),
                        'time_end'    => strtotime($sreg->time_end) ,
                        'duration'    => $sreg->duration   ,
                        //'description' => $sreg->description,
                    ];
                }
            }
            ksort($this->pauses);
            return $this->pauses[$agent_id];
        } else {
            return [];
        }
    }

    private function nr17($_pauses)
    {
        $ten_label    = '10 minutos/NR17';
        $twenty_label = '20 minutos/NR17';
        $last_10  = $first_10 = $twenty_min = ['start' => '','end' => '' ];
        $errors = ini_get('error_reporting');
        ini_set('error_reporting', 0);
        if (isset($_pauses[$twenty_label])) {
            $twenty_min = [
                'start' => $_pauses[$twenty_label][0]['time_start'],
                'end'   => $_pauses[$twenty_label][0]['time_end']
            ];
        }

        if (count($_pauses[$ten_label]) == 2) {
            $first_10 = [
                'start' => $_pauses[$ten_label][0]['time_start'],
                'end'   => $_pauses[$ten_label][0]['time_end']
            ];
            $last_10 = [
                'start' => $_pauses[$ten_label][1]['time_start'],
                'end'   => $_pauses[$ten_label][1]['time_end']
            ];
        } elseif (count($_pauses[$ten_label]) == 1) {
            $result = [
               'start' => $_pauses[$ten_label][0]['time_start'],
               'end'   => $_pauses[$ten_label][0]['time_end']
            ];

            if ($_pauses[$ten_label][0]['time_end'] < $_pauses[$twenty_label][0]['time_start']) {
                $first_10 = $result;
            } else {
                $last_10 = $result;
            }
        }
        ini_set('error_reporting', $errors);
        return [
            'first_10' => $first_10,
            'last_10'  => $last_10 ,
            '20_min'   => $twenty_min,
        ];
    }

    private function getLines()
    {
        $body_login = $this->remoteRequest(1, 'login');
        foreach ($body_login as $reg) {
            if (!isset($this->lines[$reg->agent->id])) {
                $this->lines[$reg->agent->id] = [
                    'user' => $reg->agent->login,
                    'pauses' => $this->getPauses($reg->agent->id),
                    'logins' => [
                        'login'  => [],
                        'logoff' => []
                    ]
                ];
            }
            $this->lines[$reg->agent->id]['logins']['login'][] = strtotime($reg->time_login);
            $this->lines[$reg->agent->id]['logins']['logoff'][] = strtotime($reg->time_logoff);
        }
        ksort($this->lines);
    }

    private function getReportPath()
    {
        $file = $this->start_date .'_'. $this->user .'_report.csv';
        $path = storage_path('reports/evolux/'. $file);
        $dir = preg_replace("/{$file}/", '', $path);
        if (! file_exists($dir)) {
            @mkdir($dir, 0777, true);
        }
        return $path;
    }

    private function writer()
    {
        $path   = $this->getReportPath();
        $writer = Writer::createFromPath($path, 'w+');
        $fields = ['usuario','login','saida','entrada','saida','entrada','saida','entrada','logout'];
        $writer->insertOne($fields);
        $format = 'H:i:s';
        foreach ($this->lines as $id => $line) {
            if ($line['user'] == $this->user) {
                $nr17 = $this->nr17($line['pauses']);
                $line_data = [
                    $line['user'],
                    @date($format, min($line['logins']['login'])),
                    @date($format, $nr17['first_10']['start']) ?: '--',
                    @date($format, $nr17['first_10']['end']) ?: '--',
                    @date($format, $nr17['20_min']['start']) ?: '--',
                    @date($format, $nr17['20_min']['end']) ?: '--',
                    @date($format, $nr17['last_10']['start']) ?: '--',
                    @date($format, $nr17['last_10']['end']) ?: '--',
                    @date($format, max($line['logins']['logoff']))
                ];
                $writer->insertOne($line_data);
                break;
            }
        }
    }

    public function make()
    {
        if (!file_exists($this->getReportPath())) {
            $this->getLines();
            $this->writer();
        }
        return $this->getReportPath();
    }
}
