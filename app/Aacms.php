<?php

namespace App;

use Illuminate\Support\Facades\Storage;
use Spatie\YamlFrontMatter\YamlFrontMatter;
use App\Converter;
use Prezly\DraftPhp\Converter as DraftConverter;

class Aacms
{
    /**
     * Return a list of all components available
     */
    public static function getComponentList()
    {
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

        return $res;
    }

    /**
     * Rendering components as html
     */
    public static function render($components)
    {
        $rendered = '';

        $fieldParsers = [
            'RichText' => function ($v) {
                $res = '';
                $contentState = DraftConverter::convertFromJson(json_encode($v));
                $converter = new Converter;
                try {
                    $res = $converter
                        ->setState($contentState)
                        ->toHtml();
                } catch (\Throwable $th) {
                    // Do nothing
                }
                return $res;
            },
            // 'Markdown' => function ($v) {
            //     $Parsedown = new Parsedown();
            //     return $Parsedown->line($v);
            // }
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
                } else if (array_key_exists($field->field, $fieldParsers)) {
                    $value = $fieldParsers[$field->field]($field->value);
                } else {
                    $value = $field->value;
                }
                $res[$key] = $value;
            }
            return $res;
        };

        foreach ($components as $component) {
            $rawBlade = Storage::disk('components')->get($component->component . '.blade.php');
            $rawBladeBody = YamlFrontMatter::parse($rawBlade)->body();
            $values = loopFields($component, $fieldParsers);

            $rendered .=  view(['template' => $rawBladeBody], $values);
        }

        return $rendered;
    }
}
