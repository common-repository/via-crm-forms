<?php
namespace ViaGF;

class Salesforce {
    public $client = null;
    public $token = null;

    private $baseUrl = "";

    public function __construct($opts = []) {
        $this->opts = $opts;

        foreach ($opts as $key => $val) {
            $this->$key = $val;
        }


        $this->client = new \GuzzleHttp\Client();

        if ($this->validOpts()) {
            $this->auth();
        }
    }
    
    private function getenv($name) {
        return $this->opts[$name];
    }

    public function isLoggedIn() {
        return !empty($this->token);
    }

    private function validOpts() {
        return isset($this->salesforce_key) &&
            isset($this->salesforce_secret) &&
            isset($this->salesforce_user) &&
            isset($this->salesforce_pass);
    }

    /**
     * Check to see if we're close to the limit of daily API requests.
     * @param int $threshold    Check to see if we're within this value of maxing out our requests.
     * @return bool
     */
    public function nearApiLimit($threshold = 5) {
        $usage = $this->usage();
        $pctRemaining = $usage['Remaining'] / $usage['Max'] * 100;

        return $pctRemaining <= $threshold;
    }

    /**
     * Check to see if we're close to the limit of daily Bulk API requests.
     * @param int $threshold    Check to see if we're within this value of maxing out our requests.
     * @return bool
     */
    public function nearBulkApiLimit($threshold = 100) {
        $usage = $this->usage('DailyBulkApiRequests');

        return abs($usage['Max'] - $usage['Remaining']) > $threshold;
    }

    /**
     * Get current Daily API request data from Salesforce
     * @return array    The important properties of the response are `Max` and `Remaining`
     */
    public function usage($type = 'DailyApiRequests') {
        list($code, $data) = $this->get('/services/data/v37.0/limits');

        return $data[$type];
    }

    /**
     * Delete the sobject pointed to by $url
     * @param string $url
     * @throws \Exception
     */
    public function delete($url) {
        $resp = $this->client->request('DELETE', "{$this->baseUrl}$url", [
            'headers' => [
                'Authorization' => "Bearer {$this->token}",
                'Content-Type' => 'application/json'
            ]
        ]);
        $code = $resp->getStatusCode();
        $ret = (string) $resp->getBody();

        if ($code >= 200 && $code < 300) {
            return [$code, $ret];
        } else {
            throw new \Exception("Unable to delete $url ($code) ($ret)");
        }
    }

    public function update($url, $fields) {
        if (is_array($fields)) {
            $fields = json_encode($fields);
        }

        $resp = $this->client->request('PATCH', "{$this->baseUrl}$url", [
            'headers' => [
                'Authorization' => "Bearer {$this->token}",
                'Content-Type' => 'application/json'
            ],
            'body' => $fields
        ]);

        $code = $resp->getStatusCode();
        $ret = (string) $resp->getBody();

        if ($code >= 200 && $code < 300) {
            return [$code, $ret];
        } else {
            throw new \Exception("Unable to update fields ($code) ($ret)");
        }
    }

    public function create($url, $fields) {
        if (is_array($fields)) {
            $fields = json_encode($fields);
        }

        $resp = $this->client->request('POST', "{$this->baseUrl}$url", [
            'headers' => [
                'Authorization' => "Bearer {$this->token}",
                'Content-Type' => 'application/json'
            ],
            'body' => $fields
        ]);
        $code = $resp->getStatusCode();
        $ret = (string) $resp->getBody();
        if ($code >= 200 && $code < 300) {
            return [$code, $ret];
        } else {
            throw new \Exception("Unable to create record ($code) ($ret)");
        }
    }

    /**
     * Authenticate with Salesforce using the login credentials in .env
     * @return string   The auth token for future requests
     * @throws \Exception
     */
    private function auth() {
        $params = [
            'grant_type' => 'password',
            'client_id' => $this->salesforce_key,
            'client_secret' => $this->salesforce_secret,
            'username' => $this->salesforce_user,
            'password' => $this->salesforce_pass,
        ];

        $authUrl = $this->getenv('sandbox') ? 'https://test.salesforce.com' : 'https://login.salesforce.com';

        $resp = $this->client->request('POST', "$authUrl/services/oauth2/token", [
                    'form_params' => $params
                ]);

        $code = $resp->getStatusCode();
        if ($code != 200) {
            throw new \Exception("Unable to authenticate with Salesforce ($code)");
        }

        $rawBody = (string) $resp->getBody();
        $body = json_decode($rawBody, true);
        if (!isset($body['access_token']) || empty($body['access_token'])) {
            throw new \Exception("Invalid access token received ($rawBody)");
        }

        $this->token = $body['access_token'];
        $this->baseUrl = $body['instance_url'];

        return $this->token;
    }

    public function query($soql) {
        $url = "/services/data/v37.0/query/?q=" . urlencode($soql);

        return $this->get($url);
    }

    public function get($url, $decode = true) {
        $resp = $this->client->request('GET', "{$this->baseUrl}$url", [
            'headers' => [
            'Authorization' => "Bearer {$this->token}"
            ]
        ]);

        $code = $resp->getStatusCode();
        $ret = (string) $resp->getBody();

        if ($decode) {
            $ret = json_decode($ret, true);
        }

        return [$code, $ret];
    }

    public function describe($sobject, $decode = true) {
        return $this->get("/services/data/v37.0/sobjects/$sobject/describe", $decode);
    }

    public function getBaseUrl() {
        return $this->baseUrl;
    }
}
