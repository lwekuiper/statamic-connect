<?php

namespace Lwekuiper\StatamicConnect\Http\Controllers;

use Illuminate\Routing\Controller;
use Lwekuiper\StatamicConnect\IntegrationManager;

class GetRemoteTagsController extends Controller
{
    public function __invoke(string $integration, IntegrationManager $manager)
    {
        $instance = $manager->find($integration);

        if (! $instance || empty($instance->getTags())) {
            return response()->json([]);
        }

        return response()->json($instance->getTags());
    }
}
