<?php

namespace App\Http\Controllers\B2B;

use App\Http\Controllers\ApiController;
use App\Http\Requests\B2BRequests;
use App\Models\B2B\B2BCallbacks;
use App\Models\B2B\B2BPayment;
use App\Traits\RetryHandler;
use GuzzleHttp\Client;
use Illuminate\Http\Request as Requests;


class B2B_Controller extends ApiController
{
    use RetryHandler;

    /**
     * B2B_Controller constructor.
     */
    public function __construct()
    {
        $this->middleware('client.credentials')->except('storeResponse');
    }

    /**
     * @param B2BRequests $request
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function transactionRequest(B2BRequests $request)
    {
        $body = [
            'Initiator' => env('INITIATOR'),
            'SecurityCredential' => env('SECURITY_CREDENTIAL'),
            'CommandID' => $request->command_id,
            'SenderIdentifierType' => '4',
            'RecieverIdentifierType' => '4',
            'Amount' => $request->amount,
            'PartyA' => env('B2B_SHORTCODE'),
            'PartyB' => $request->shortcode,
            'AccountReference' => $request->account_reference,
            'Remarks' => 'test',
            'QueueTimeOutURL' => env('B2B_TIMEOUT_URL'),
            'ResultURL' => env('B2B_RESULT_URL'),
        ];

        $transaction_data = new B2BPayment($request->all());
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
        $transaction_endpoint = self::SANDBOX_ENDPOINT . self::B2B_PAYMENT;

        $client = new Client(['handler' => $this->handler()]);

        return $client->request('POST', $transaction_endpoint, [
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $this->authenticate()
            ],
            'json' => $body
        ]);
    }

    /**
     * @param Requests $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function storeResponse(Requests $request)
    {
        $response = new B2BCallbacks();
        $response->ResultCode = $request->input('Result.ResultCode');
        $response->OriginatorConversationID = $request->input('Result.OriginatorConversationID');
        $response->ConversationID = $request->input('Result.ConversationID');
        $response->TransactionID = $request->input('Result.TransactionID');
        $response->Amount = $request->input('Result.ResultParameters.ResultParameter.2.Value');
        $response->TransactionCompletedTime = $request->input('Result.ResultParameters.ResultParameter.4.Value');
        $response->ReceiverPartyPublicName = $request->input('Result.ResultParameters.ResultParameter.6.Value');
        $response->BillReferenceNumber = $request->input('Result.ReferenceData.ReferenceItem.0.Value');
        $response->save();

        logger($request->all());

        return response()->json([
            'ResponseCode' => 0,
            'ResponseDesc' => 'The service was accepted successfully']);
    }
}
