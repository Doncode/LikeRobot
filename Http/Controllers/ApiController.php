<?php
/**
 * Created by PhpStorm.
 * User: a-basov
 * Date: 20.05.16
 * Time: 15:07
 */

namespace App\Http\Controllers;


use App\Http\Requests\Request;

class ApiController extends Controller
{
    public function saveAction(Request $request)
    {
        return file_put_contents(storage_path() . '/data.json', json_encode($request->all()));
    }

    public function loadAction(Request $request)
    {
        return file_get_contents(storage_path() . '/data.json');
    }
}