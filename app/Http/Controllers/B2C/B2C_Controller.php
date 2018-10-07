<?php

namespace App\Http\Controllers\B2C;

use App\Http\Controllers\ApiController;
use App\Http\Requests\B2CRequests;
use App\Models\B2C\B2C_Callbacks;
use App\Models\B2C\B2CPayment;
use App\Traits\RetryHandler;
use GuzzleHttp\Client;
use Illuminate\Http\Request as Requests;


class B2C_Controller extends ApiController
{
    use RetryHandler;

    /**
     * B2C_Controller constructor.
     */
    public function __construct()
    {
        $this->middleware('client.credentials')->except('storeResponse');
    }

    /**
     * @param B2CRequests $request
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function transactionRequest(B2CRequests $request)
    {
        $body = [
            'InitiatorName' => env('INITIATOR_NAME'),
            'SecurityCredential' => env('SECURITY_CREDENTIAL'),
            'CommandID' => $request->command_id,
            'Amount' => $request->amount,
            'PartyA' => env('B2C_SHORTCODE'),
            'PartyB' => $request->number,
            'Remarks' => 'test',
            'QueueTimeOutURL' => env('B2C_TIMEOUT_URL'),
            'ResultURL' => env('B2C_RESULT_URL'),
            'Occassion' => 'test',
        ];

        $transaction_data = new B2CPayment($request->all());
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
        $transaction_endpoint = self::SANDBOX_ENDPOINT . self::B2C_PAYMENT;

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
        $response = new B2C_Callbacks();
        $response->ResultCode = $request->input('Result.ResultCode');
        $response->OriginatorConversationID = $request->input('Result.OriginatorConversationID');
        $response->ConversationID = $request->input('Result.ConversationID');
        $response->TransactionID = $request->input('Result.TransactionID');
        $response->TransactionAmount = $request->input('Result.ResultParameters.ResultParameter.0.Value');
        $response->ReceiverPartyPublicName = $request->input('Result.ResultParameters.ResultParameter.4.Value');
        $response->TransactionCompletedTime = $request->input('Result.ResultParameters.ResultParameter.5.Value');
        $response->save();

        logger($request->all());

        return response()->json([
            'ResponseCode' => 0,
            'ResponseDesc' => 'The service was accepted successfully']);
    }
}
