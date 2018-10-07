<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;

class ApiController extends Controller
{
    const OAUTH_URL = 'oauth/v1/generate?grant_type=client_credentials';
    const TOKEN = 'token_';

    const C2B_REGISTER = 'mpesa/c2b/v1/registerurl';
    const C2B_SIMULATE = 'mpesa/c2b/v1/simulate';
    const STK_PUSH = 'mpesa/stkpush/v1/processrequest';
    const B2B_PAYMENT = 'mpesa/b2b/v1/paymentrequest';
    const B2C_PAYMENT = 'mpesa/b2c/v1/paymentrequest';

    const SANDBOX_ENDPOINT = 'https://sandbox.safaricom.co.ke/';
    const PRODUCTION_ENDPOINT = 'https://api.safaricom.co.ke/';

    /**
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function authenticate()
    {
        if ($token = Cache::get(self::TOKEN)) {
            return $token;
        }

        $response = $this->generateToken();
        $body = json_decode($response->getBody());
        $this->saveToken($body);
        return $body->access_token;
    }

    /**
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function generateToken()
    {
        $request_url = self::SANDBOX_ENDPOINT . self::OAUTH_URL;
        $client = new Client();
        return $client->request('GET', $request_url, [
            'headers' => [
                'Authorization' => 'Basic ' . $this->authorization()
            ]
        ]);
    }

    /**
     * @return string
     */
    protected function authorization()
    {
        $key = env('CONSUMER_KEY');
        $secret = env('CONSUMER_SECRET');

        return base64_encode($key . ':' . $secret);
    }

    /**
     * @param $authorizationKey
     */
    protected function saveToken($authorizationKey)
    {
        Cache::put(self::TOKEN, $authorizationKey->access_token, 50);
    }
}
