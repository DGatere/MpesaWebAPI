<?php

namespace App\Http\Controllers\C2B;

use App\Http\Controllers\ApiController;
use GuzzleHttp\Client;
use Illuminate\Http\Request;

class C2BRegistrationController extends ApiController
{
    /**
     * C2BRegistrationController constructor.
     */
    public function __construct()
    {
        $this->middleware('client.credentials');
    }

    /**
     * @param Request $request
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function registrationRequest(Request $request)
    {
        $body = [
            'ShortCode' => env('C2B_SHORTCODE'),
            'ResponseType' => 'Completed',
            'ConfirmationURL' => $request->ConfirmationURL,
            'ValidationURL' => $request->ValidationURL
        ];

        $response = $this->initiateRequest($body);

        return json_decode($response->getBody(), true);
    }

    /**
     * @param array $body
     * @return mixed|\Psr\Http\Message\ResponseInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function initiateRequest($body = [])
    {
        $transaction_endpoint = self::SANDBOX_ENDPOINT . self::C2B_REGISTER;
        $client = new Client();

        return $client->request('POST', $transaction_endpoint, [
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $this->authenticate()
            ],
            'json' => $body
        ]);
    }
}
