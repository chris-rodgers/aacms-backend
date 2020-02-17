<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Response;

class TemplateController extends Controller
{
    function preview(Request $request)
    {
        
        $res = [];
        $components = json_decode($request->getContent());
        $fieldParsers = [];

        $fieldParsers = [
            'RichText' => function () {
                return 'rich text string';
            },
            'Url' => function () {
                return 'http://url.com';
            },
        ];

        function loopFields($component, $fieldParsers)
        {
            $res = [];
            foreach ($component->fields as $key => $field) {
                $value = '';
                if ($field->field == 'ForEach') {
                    $subfields = $field->value;
                    $value = [];

                    foreach ($subfields as $subfield) {
                        $value[] = loopFields($subfield, $fieldParsers);
                    }
                }
                else if (array_key_exists($field->field, $fieldParsers)) {
                    $value = $fieldParsers[$field->field]($field->value);
                } else {
                    $value = $field->value;
                }
                $res[$key] = $value;
            }
            return $res;
        };

        foreach ($components as $component) {
            $values = loopFields($component, $fieldParsers);
            $res[] = [
                'component' => $component->component,
                'values' => $values
            ];
        }

        return Response::json($res, 200);
    }
}
