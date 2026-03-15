<?php

namespace Lwekuiper\StatamicConnect\Http\Controllers;

use Illuminate\Routing\Controller;
use Lwekuiper\StatamicConnect\IntegrationManager;

class GetRemoteListsController extends Controller
{
    public function __invoke(string $integration, IntegrationManager $manager)
    {
        $instance = $manager->find($integration);

        if (! $instance) {
            return response()->json([]);
        }

        return response()->json($instance->getLists());
    }
}
