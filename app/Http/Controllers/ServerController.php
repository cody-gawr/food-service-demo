<?php

namespace App\Http\Controllers;

use Knuckles\Scribe\Attributes\{Endpoint, Group, Response};
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\DB;
use Queue;

#[Group("Server Check", "APIs for checking server")]
class ServerController extends Controller
{
    #[Endpoint("HealthCheck Endpoint", <<<DESC
        Check that the service is up. If everything is okay, you'll get a 200 OK response.
        Otherwise, the request will fail with a 400 error, and a response listing the failed services.
    DESC)]
    #[Response(description: 'service is health', content: [
        'status' => 'up',
            'app' => 'EatThat.API',
            'code' => '200',
            'services' => [
                'database' => 'up',
                'redis' => 'up',
                'horizon' => 'up',
                'scheduler' => 'up',
            ]
    ])]
    #[Response(status: 500, description: "server is unhealthy", content: [
        'status' => 'down',
        'app' => 'EatThat.API',
        'code' => '500',
    ])]
    public function healthCheck() : \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'status' => 'up',
            'app' => 'EatThat.API',
            'code' => '200',
            'version' => '1.0.0',
            'services' => [
                'database' => DB::connection()->getName(),
                'queue' => Queue::getConnectionName(),
                'redis' => Redis::connection()->getName(),
                'horizon' => 'up',
                'scheduler' => 'up',
            ]
        ]);
    }
}
