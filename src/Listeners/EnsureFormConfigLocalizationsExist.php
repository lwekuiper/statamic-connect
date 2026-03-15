<?php

declare(strict_types=1);

namespace Lwekuiper\StatamicConnect\Listeners;

use Lwekuiper\StatamicConnect\Facades\FormConfig;
use Statamic\Events\FormSaved;
use Statamic\Facades\Addon;

class EnsureFormConfigLocalizationsExist
{
    public function handle(FormSaved $event): void
    {
        if (Addon::get('lwekuiper/statamic-connect')->edition() !== 'pro') {
            return;
        }

        $integrations = array_keys(config('statamic.connect.integrations', []));

        foreach ($integrations as $integration) {
            FormConfig::ensureLocalizationsExist($integration, $event->form->handle());
        }
    }
}
