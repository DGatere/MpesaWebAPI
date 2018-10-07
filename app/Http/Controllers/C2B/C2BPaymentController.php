<?php

namespace App\Http\Controllers\C2B;

use App\Http\Controllers\ApiController;
use App\Http\Requests\C2BRequests;
use App\Models\C2B\C2BCallbacks;
use App\Models\C2B\C2BPayment;
use GuzzleHttp\Client;
use Illuminate\Http\Request;

class C2BPaymentController extends ApiController
{
    /**
     * C2BPaymentController constructor.
     */
    public function __construct()
    {
        $this->middleware('client.credentials')->except('storeResponse');
    }

    /**
     * @param C2BRequests $request
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function transactionRequest(C2BRequests $request)
    {
        $body = [
            'ShortCode' => env('C2B_SHORTCODE'),
            'CommandID' => 'CustomerPayBillOnline',
            'Amount' => $request->Amount,
            'Msisdn' => $request->PhoneNumber,
            'BillRefNumber' => $request->BillRefNumber
        ];

        $transaction_data = new C2BPayment($request->all());
        $transaction_data->save();

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
        $transaction_endpoint = self::SANDBOX_ENDPOINT . self::C2B_SIMULATE;
        $client = new Client();

        return $client->request('POST', $transaction_endpoint, [
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $this->authenticate()
            ],
            'json' => $body
        ]);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function storeResponse(Request $request)
    {
        $response = new C2BCallbacks();
        $response->TransactionType = $request->input('TransactionType');
        $response->TransID = $request->input('TransID');
        $response->TransactionCompletedTime = $request->input('TransTime');
        $response->Amount = $request->input('TransAmount');
        $response->BillRefNumber = $request->input('BillRefNumber');
        $response->PhoneNumber = $request->input('MSISDN');
        $response->FirstName = $request->input('FirstName');
        $response->MiddleName = $request->input('MiddleName');
        $response->LastName = $request->input('LastName');
        $response->save();

        logger($request->all());

        return response()->json([
            'ResponseCode' => 0,
            'ResponseDesc' => 'The service was accepted successfully']);
    }
}
