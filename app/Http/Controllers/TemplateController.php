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
        $components = Aacms::getComponentList();

        return Response::json($components, 200);
    }
    function preview(Request $request)
    {
        $components = json_decode($request->getContent());

        $rendered = Aacms::render($components);

        return view('landing-page', ['rendered' => $rendered]);
    }
}
