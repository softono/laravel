<?php

namespace Tests\Feature;

use App\Helpers\Response;
use Tests\TestCase;

class ResponseEnvelopeTest extends TestCase
{
    public function test_send_result_maps_ok_to_status_and_nests_extras_in_data(): void
    {
        $body = Response::sendResult(['ok' => false, 'message' => 'Nope', 'user' => null, 'http_status' => 409])->getData(true);

        $this->assertSame(['status' => 0, 'message' => 'Nope', 'data' => ['user' => null]], $body);
    }

    public function test_the_body_is_always_status_message_data(): void
    {
        $this->assertSame(
            ['status' => 1, 'message' => 'Ok', 'data' => []],
            Response::sendMessage()->getData(true),
        );
        $this->assertSame(
            ['status' => 0, 'message' => 'Boom', 'data' => []],
            Response::sendError(500, 'Boom')->getData(true),
        );
        $this->assertSame(500, Response::sendError(500, 'Boom')->getStatusCode());
    }

    public function test_a_next_style_result_keeps_its_http_status(): void
    {
        $response = Response::sendResult(['status' => 0, 'message' => 'Missing', 'http_status' => 404]);

        $this->assertSame(404, $response->getStatusCode());
        $this->assertSame(0, $response->getData(true)['status']);
    }
}
