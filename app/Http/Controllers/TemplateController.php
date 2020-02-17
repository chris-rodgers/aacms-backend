<?php

namespace App\Http\Controllers;

use Response;
use App\Aacms;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Spatie\YamlFrontMatter\YamlFrontMatter;
use Illuminate\Support\Facades\Blade;

class TemplateController extends Controller
{
    function index(){
        $res = [];
        $components = Storage::disk('components')->files('');
        
        foreach ($components as $component) {
            $content = Storage::disk('components')->get($component);
            $name = explode(".", $component, 2)[0];
            $object = YamlFrontMatter::parse($content);
            preg_match_all('/\$(\w*-?>?\[?\'?"?\]?+)/', $object->body(), $variables);
            $res[$name] = [
                'fields' => $object->matter(),
                'variableNames' =>  $variables[1]
            ];
        }

        return Response::json($res, 200);
    }
    function preview(Request $request)
    {
        $rendered = '';
        $components = json_decode($request->getContent());
        $fieldParsers = [];

        $fieldParsers = [
            'RichText' => function () {
                return 'rich text string';
            }
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
            $rawBlade = Storage::disk('components')->get($component->component.'.blade.php');
            $rawBladeBody = YamlFrontMatter::parse($rawBlade)->body();
            $compiledBlade = Blade::compileString($rawBladeBody);
            $values = loopFields($component, $fieldParsers);

            $rendered .= Aacms::render($compiledBlade, $values);
        }

        return view('landing-page', ['rendered' => $rendered]);
    }
}
