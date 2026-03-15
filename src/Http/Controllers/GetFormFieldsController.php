<?php

namespace Lwekuiper\StatamicConnect\Http\Controllers;

use Illuminate\Routing\Controller;
use Statamic\Facades\Form;

class GetFormFieldsController extends Controller
{
    public function __invoke(string $form)
    {
        $formInstance = Form::find($form);

        if (! $formInstance) {
            return response()->json([]);
        }

        $fields = $formInstance->blueprint()->fields()->all()->map(fn ($field) => [
            'handle' => $field->handle(),
            'display' => $field->display(),
            'type' => $field->type(),
        ])->values();

        return response()->json($fields);
    }
}
