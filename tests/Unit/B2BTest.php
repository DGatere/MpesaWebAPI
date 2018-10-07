<?php

namespace Tests\Unit;

use GuzzleHttp\Client;
use Tests\TestCase;

class B2BTest extends TestCase
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
            'command_id' => 'BusinessPayBill',
            'amount' => '300',
            'shortcode' => '600000',
            'account_reference' => 'test'
        ];

        $response = $this->http->request('POST', 'b2b/payment', [
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
        $response = $this->http->request('GET', 'b2b/payment', ['http_errors' => false]);
        $this->assertEquals($response->getStatusCode(), 405);
    }

    public function testPut()
    {
        $response = $this->http->request('PUT', 'b2b/payment', ['http_errors' => false]);
        $this->assertEquals($response->getStatusCode(), 405);
    }

    public function testDelete()
    {
        $response = $this->http->request('DELETE', 'b2b/payment', ['http_errors' => false]);
        $this->assertEquals($response->getStatusCode(), 405);
    }

    public function testCommandIdEmpty()
    {
        $body = [
            'command_id' => '',
            'amount' => '300',
            'shortcode' => '600000',
            'account_reference' => 'test'
        ];

        $response = $this->http->request('POST', 'b2b/payment', [
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
            'command_id' => 'sfsbb',
            'amount' => '300',
            'shortcode' => '600000',
            'account_reference' => 'test'
        ];

        $response = $this->http->request('POST', 'b2b/payment', [
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
            'command_id' => 'BusinessPayBill',
            'amount' => '',
            'shortcode' => '600000',
            'account_reference' => 'test'
        ];

        $response = $this->http->request('POST', 'b2b/payment', [
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

    public function testAmountExcessiveValue()
    {
        $body = [
            'command_id' => 'BusinessPayBill',
            'amount' => '54165165474445454',
            'shortcode' => '600000',
            'account_reference' => 'test'
        ];

        $response = $this->http->request('POST', 'b2b/payment', [
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

    public function testShortcodeEmpty()
    {
        $body = [
            'command_id' => 'BusinessPayBill',
            'amount' => '300',
            'shortcode' => '',
            'account_reference' => 'test'
        ];

        $response = $this->http->request('POST', 'b2b/payment', [
            'http_errors' => false,
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $this->setCredentials()
            ],
            'json' => $body
        ]);

        $shortcode = json_decode($response->getBody())->{"error"}->{"shortcode"};
        $this->assertRegexp('/A shortcode is required/', $shortcode[0]);

        $this->assertEquals(422, $response->getStatusCode());
    }

    public function testInvalidShortcode()
    {
        $body = [
            'command_id' => 'BusinessPayBill',
            'amount' => '300',
            'shortcode' => 'sadgge',
            'account_reference' => 'test'
        ];

        $response = $this->http->request('POST', 'b2b/payment', [
            'http_errors' => false,
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $this->setCredentials()
            ],
            'json' => $body
        ]);

        $shortcode = json_decode($response->getBody())->{"error"}->{"shortcode"};
        $this->assertRegexp('/The shortcode format is invalid/', $shortcode[0]);

        $this->assertEquals(422, $response->getStatusCode());
    }
    public function testShortcodeLessThanRule()
    {
        $body = [
            'command_id' => 'BusinessPayBill',
            'amount' => '300',
            'shortcode' => '123',
            'account_reference' => 'test'
        ];

        $response = $this->http->request('POST', 'b2b/payment', [
            'http_errors' => false,
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $this->setCredentials()
            ],
            'json' => $body
        ]);

        $shortcode = json_decode($response->getBody())->{"error"}->{"shortcode"};
        $this->assertRegexp('/The shortcode must not be less than 6 characters/', $shortcode[0]);
        $this->assertRegexp('/The shortcode format is invalid/', $shortcode[1]);

        $this->assertEquals(422, $response->getStatusCode());
    }

    public function testShortcodeGreaterThanRule()
    {
        $body = [
            'command_id' => 'BusinessPayBill',
            'amount' => '300',
            'shortcode' => '1234567',
            'account_reference' => 'test'
        ];

        $response = $this->http->request('POST', 'b2b/payment', [
            'http_errors' => false,
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $this->setCredentials()
            ],
            'json' => $body
        ]);

        $shortcode = json_decode($response->getBody())->{"error"}->{"shortcode"};
        $this->assertRegexp('/The shortcode must not exceed 6 characters/', $shortcode[0]);
        $this->assertRegexp('/The shortcode format is invalid/', $shortcode[1]);

        $this->assertEquals(422, $response->getStatusCode());
    }

    public function testAccountReferenceEmpty()
    {
        $body = [
            'command_id' => 'BusinessPayBill',
            'amount' => '300',
            'shortcode' => '600000',
            'account_reference' => ''
        ];

        $response = $this->http->request('POST', 'b2b/payment', [
            'http_errors' => false,
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $this->setCredentials()
            ],
            'json' => $body
        ]);

        $account_reference = json_decode($response->getBody())->{"error"}->{"account_reference"};
        $this->assertRegexp('/The account reference field is required/', $account_reference[0]);

        $this->assertEquals(422, $response->getStatusCode());
    }

    public function testEmptyDataSet()
    {
        $body = [
            'command_id' => '',
            'amount' => '',
            'shortcode' => '',
            'account_reference' => ''
        ];

        $response = $this->http->request('POST', 'b2b/payment', [
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

        $shortcode = json_decode($response->getBody())->{"error"}->{"shortcode"};
        $this->assertRegexp('/A shortcode is required/', $shortcode[0]);

        $account_reference = json_decode($response->getBody())->{"error"}->{"account_reference"};
        $this->assertRegexp('/The account reference field is required/', $account_reference[0]);

        $this->assertEquals(422, $response->getStatusCode());
    }
}