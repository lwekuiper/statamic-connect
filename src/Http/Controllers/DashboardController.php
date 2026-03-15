<?php

namespace Lwekuiper\StatamicConnect\Http\Controllers;

use Illuminate\Routing\Controller;
use Lwekuiper\StatamicConnect\IntegrationManager;
use Statamic\Facades\Form;

class DashboardController extends Controller
{
    public function index(IntegrationManager $manager)
    {
        return view('connect::dashboard', [
            'integrations' => $manager->all()->map(fn ($integration) => [
                'name' => $integration->name(),
                'label' => $integration->label(),
                'configured' => $integration->isConfigured(),
            ]),
            'forms' => Form::all()->map(fn ($form) => [
                'handle' => $form->handle(),
                'title' => $form->title(),
            ]),
        ]);
    }
}
