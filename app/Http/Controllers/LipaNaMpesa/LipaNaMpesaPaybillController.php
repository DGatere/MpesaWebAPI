<?php

namespace App\Http\Controllers\LipaNaMpesa;

use App\Http\Controllers\ApiController;
use App\Http\Requests\LipaNaMpesaPaybillRequests;
use App\Models\LipaNaMpesa\LipaNaMpesaPaybillCallbacks;
use App\Models\LipaNaMpesa\LipaNaMpesaPaybillPayment;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class LipaNaMpesaPaybillController extends ApiController
{
    /**
     * LipaNaMpesaPaybillController constructor.
     */
    public function __construct()
    {
        $this->middleware('client.credentials')->except('storeResponse');
    }

    /**
     * @param LipaNaMpesaPaybillRequests $request
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function transactionRequest(LipaNaMpesaPaybillRequests $request)
    {
        $time = Carbon::now()->format('YmdHis');
        $shortCode = env('LNMP_SHORT_CODE');
        $passkey = env('LNMP_PASSKEY');
        $callback = env('LNMP_CALLBACK');
        $password = base64_encode($shortCode . $passkey . $time);


        $body = [
            'BusinessShortCode' => $shortCode,
            'Password' => $password,
            'Timestamp' => $time,
            'TransactionType' => 'CustomerPayBillOnline',
            'Amount' => $request->amount,
            'PartyA' => $request->number,
            'PartyB' => $shortCode,
            'PhoneNumber' => $request->number,
            'CallBackURL' => $callback,
            'AccountReference' => $request->account_reference,
            'TransactionDesc' => env('LNMP_TRX_DESCRIPTION'),
        ];

        $transaction_data = new LipaNaMpesaPaybillPayment($request->all());
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
        $transaction_endpoint = self::SANDBOX_ENDPOINT . self::STK_PUSH;
        $client = new Client();

        return $client->request('POST', $transaction_endpoint, [
            "headers" => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $this->authenticate()
            ],
            "json" => $body,
        ]);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function storeResponse(Request $request)
    {
        $response = new LipaNaMpesaPaybillCallbacks();
        $response->MerchantRequestID = $request->input('Body.stkCallback.MerchantRequestID');
        $response->CheckoutRequestID = $request->input('Body.stkCallback.CheckoutRequestID');
        $response->ResultCode = $request->input('Body.stkCallback.ResultCode');
        $response->Amount = $request->input('Body.stkCallback.CallbackMetadata.Item.0.Value');
        $response->TransactionID = $request->input('Body.stkCallback.CallbackMetadata.Item.1.Value');
        $response->TransactionCompletedTime = $request->input('Body.stkCallback.CallbackMetadata.Item.3.Value');
        $response->PhoneNumber = $request->input('Body.stkCallback.CallbackMetadata.Item.4.Value');
        $response->save();

        logger($request->all());

        return response()->json([
            'ResponseCode' => 0,
            'ResponseDesc' => 'The service was accepted successfully']);
    }
}
