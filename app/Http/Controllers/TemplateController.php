<?php

namespace App\Http\Controllers;

use Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Spatie\YamlFrontMatter\YamlFrontMatter;

class TemplateController extends Controller
{
    function index(){
        $res = [];
        $components = Storage::disk('components')->files('');
        
        foreach ($components as $component) {
            $content = Storage::disk('components')->get($component);
            $name = explode(".", $component, 2)[0];
            $object = YamlFrontMatter::parse($content);
            $res[$name] = $object->matter();
        }

        return Response::json($res, 200);
    }
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
