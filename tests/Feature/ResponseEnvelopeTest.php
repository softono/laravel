<?php

namespace Tests\Feature;

use App\Helpers\ApiResult;
use App\Helpers\Response;
use Tests\TestCase;

class ResponseEnvelopeTest extends TestCase
{
    public function test_api_results_have_the_next_shape(): void
    {
        $this->assertSame(
            ['http_status' => 200, 'status' => 1, 'message' => '', 'data' => []],
            ApiResult::success(),
        );
        $this->assertSame(
            ['http_status' => 422, 'status' => 0, 'message' => 'Nope', 'data' => ['restart' => true]],
            ApiResult::failure('Nope', ['restart' => true], 422),
        );
    }

    public function test_send_result_sends_the_http_status_and_only_status_message_data(): void
    {
        $response = Response::sendResult(ApiResult::failure('Nope', ['x' => 1], 409));

        $this->assertSame(409, $response->getStatusCode());
        $this->assertSame(['status' => 0, 'message' => 'Nope', 'data' => ['x' => 1]], $response->getData(true));
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
