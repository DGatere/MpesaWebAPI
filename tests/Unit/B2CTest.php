<?php

namespace Tests\Unit;

use GuzzleHttp\Client;
use Tests\TestCase;

class B2CTest extends TestCase
{
    private $http;

    public function setUp()
    {
        $this->http = new Client(['base_uri' => 'http://kapuwebapi.me/']);
    }

    public function setCredentials()
    {
        $response = $this->http->request('POST', 'oauth/token', [
            'form_params' => [
                'grant_type' => 'client_credentials',
                'client_id' => '3',
                'client_secret' => 'CghBxZuwkryvsIw6HNuzfZQF3iNPK00a3n4nsOay'
            ]
        ]);

        $access_token = json_decode($response->getBody())->{"access_token"};
        return $access_token;
    }

    public function testPost()
    {
        $body = [
            'command_id' => 'BusinessPayment',
            'amount' => '300',
            'number' => '254708374149'
        ];

        $response = $this->http->request('POST', 'b2c/payment', [
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $this->setCredentials()
            ],
            'json' => $body
        ]);

        $this->assertEquals(200, $response->getStatusCode());
        $contentType = $response->getHeaders()["Content-Type"][0];

        $this->assertEquals("application/json", $contentType);
        $ResponseCode = json_decode($response->getBody())->{"ResponseCode"};

        $this->assertRegexp('/0/', $ResponseCode);
        $ResponseDescription = json_decode($response->getBody())->{"ResponseDescription"};

        $this->assertRegexp('/Accept the service request successfully./', $ResponseDescription);
        $originatorConversationId = json_decode($response->getBody())->{"OriginatorConversationID"};

        $this->assertNotEmpty($originatorConversationId);
    }

    public function testGet()
    {
        $response = $this->http->request('GET', 'b2c/payment', ['http_errors' => false]);
        $this->assertEquals($response->getStatusCode(), 405);
    }

    public function testPut()
    {
        $response = $this->http->request('PUT', 'b2c/payment', ['http_errors' => false]);
        $this->assertEquals($response->getStatusCode(), 405);
    }

    public function testDelete()
    {
        $response = $this->http->request('DELETE', 'b2c/payment', ['http_errors' => false]);
        $this->assertEquals($response->getStatusCode(), 405);
    }

    public function testCommandIdEmpty()
    {
        $body = [
            'command_id' => '',
            'amount' => '300',
            'number' => '254708374149'
        ];

        $response = $this->http->request('POST', 'b2c/payment', [
            'http_errors' => false,
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $this->setCredentials()
            ],
            'json' => $body
        ]);

        $command_id = json_decode($response->getBody())->{"error"}->{"command_id"};
        $this->assertRegexp('/Command ID is required/', $command_id[0]);

        $this->assertEquals(422, $response->getStatusCode());
    }

    public function testInvalidCommandId()
    {
        $body = [
            'command_id' => 'dadad3212',
            'amount' => '300',
            'number' => '254708374149'
        ];

        $response = $this->http->request('POST', 'b2c/payment', [
            'http_errors' => false,
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $this->setCredentials()
            ],
            'json' => $body
        ]);

        $command_id = json_decode($response->getBody())->{"error"};
        $this->assertRegexp('/Bad request from client/', $command_id);

        $this->assertEquals(400, $response->getStatusCode());
    }


    public function testAmountEmpty()
    {
        $body = [
            'command_id' => 'BusinessPayment',
            'amount' => '',
            'number' => '254708374149'
        ];

        $response = $this->http->request('POST', 'b2c/payment', [
            'http_errors' => false,
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $this->setCredentials()
            ],
            'json' => $body
        ]);

        $amount = json_decode($response->getBody())->{"error"}->{"amount"};
        $this->assertRegexp('/The amount must be an integer/', $amount[0]);
        $this->assertRegexp('/The amount is required/', $amount[1]);

        $this->assertEquals(422, $response->getStatusCode());
    }

    public function testAmountInvalid()
    {
        $body = [
            'command_id' => 'BusinessPayment',
            'amount' => 'asdf',
            'number' => '254708374149'
        ];

        $response = $this->http->request('POST', 'b2c/payment', [
            'http_errors' => false,
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $this->setCredentials()
            ],
            'json' => $body
        ]);

        $amount = json_decode($response->getBody())->{"error"}->{"amount"};
        $this->assertRegexp('/The amount must be an integer/', $amount[0]);

        $this->assertEquals(422, $response->getStatusCode());
    }

    public function testAmountExcessiveValue()
    {
        $body = [
            'command_id' => 'BusinessPayment',
            'amount' => '5446516516516',
            'number' => '254708374149'
        ];

        $response = $this->http->request('POST', 'b2c/payment', [
            'http_errors' => false,
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $this->setCredentials()
            ],
            'json' => $body
        ]);

        $amount = json_decode($response->getBody())->{"error"};
        $this->assertRegexp('/Request not acceptable/', $amount);

        $this->assertEquals(406, $response->getStatusCode());
    }

    public function testNumberEmpty()
    {
        $body = [
            'command_id' => 'BusinessPayment',
            'amount' => '300',
            'number' => ''
        ];

        $response = $this->http->request('POST', 'b2c/payment', [
            'http_errors' => false,
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $this->setCredentials()
            ],
            'json' => $body
        ]);

        $number = json_decode($response->getBody())->{"error"}->{"number"};
        $this->assertRegexp('/A number is required/', $number[0]);

        $this->assertEquals(422, $response->getStatusCode());
    }


    public function testNumberLessThanRule()
    {
        $body = [
            'command_id' => 'BusinessPayment',
            'amount' => '300',
            'number' => '25470837414'
        ];

        $response = $this->http->request('POST', 'b2c/payment', [
            'http_errors' => false,
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $this->setCredentials()
            ],
            'json' => $body
        ]);

        $number = json_decode($response->getBody())->{"error"}->{"number"};
        $this->assertRegexp('/The number must not be less than 12 characters/', $number[0]);
        $this->assertRegexp('/Numbers must be in the format 254xxxxxxxxx/', $number[1]);

        $this->assertEquals(422, $response->getStatusCode());
    }

    public function testNumberGreaterThanRule()
    {
        $body = [
            'command_id' => 'BusinessPayment',
            'amount' => '300',
            'number' => '2547083741492'
        ];

        $response = $this->http->request('POST', 'b2c/payment', [
            'http_errors' => false,
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $this->setCredentials()
            ],
            'json' => $body
        ]);

        $number = json_decode($response->getBody())->{"error"}->{"number"};
        $this->assertRegexp('/The number must not exceed 12 characters/', $number[0]);
        $this->assertRegexp('/Numbers must be in the format 254xxxxxxxxx/', $number[1]);

        $this->assertEquals(422, $response->getStatusCode());
    }

    public function testEmptyDataSet()
    {
        $body = [
            'command_id' => '',
            'amount' => '',
            'number' => ''
        ];

        $response = $this->http->request('POST', 'b2c/payment', [
            'http_errors' => false,
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $this->setCredentials()
            ],
            'json' => $body
        ]);

        $command_id = json_decode($response->getBody())->{"error"}->{"command_id"};
        $this->assertRegexp('/Command ID is required/', $command_id[0]);

        $amount = json_decode($response->getBody())->{"error"}->{"amount"};
        $this->assertRegexp('/The amount must be an integer/', $amount[0]);
        $this->assertRegexp('/The amount is required/', $amount[1]);

        $number = json_decode($response->getBody())->{"error"}->{"number"};
        $this->assertRegexp('/A number is required/', $number[0]);

        $this->assertEquals(422, $response->getStatusCode());
    }
}