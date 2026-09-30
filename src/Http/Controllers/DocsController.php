<?php
namespace Oxalis\Http\Controllers;

use Illuminate\Routing\Controller;

class DocsController extends Controller
{
    public function index()
    {
        return view('oxalis::docs.index');
    }
}
