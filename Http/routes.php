<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

use App\User;

Route::post('/', 'BetController@indexAction');
Route::any('/login', 'BetController@loginAction');

Route::any('/result', 'ResultController@indexAction');
Route::any('/result/list', 'ResultController@listAction');
Route::any('/result/cancel', 'ResultController@cancelAction');
Route::any('/result/withdraws', 'ResultController@listWithdrawsAction');
Route::any('/result/withdraw', 'ResultController@withdrawAction');
Route::any('/result/pay', 'ResultController@payAction');
Route::any('/result/pay/auto', 'QiwiController@histAction');
Route::any('/result/msg', 'ResultController@msgAction');

Route::get('/', function () {
    //User::where('id', 1);
    return __DIR__ .' '. (microtime(true) - T0);
//    '    position: relative; background: url(/assets/facestemplate-160c5f9ae28c93ed3d894cb33fdd2800c2494dca05b6f9ad6718b68110ea11eb.jpg) 50% 0 repeat fixed;'
    //return view('welcome');
});

Route::any('base', 'BeeLineController@eAction');
Route::any('search', 'BeeLineController@searchAction');
Route::any('hook', 'BeeLineController@hookAction');
Route::any('setup', 'BeeLineController@setupAction');
Route::any('payeer', 'PayeerController@indexAction');
Route::any('pay/{id}/{amount}', 'PayeerController@payLinkAction');
Route::any('payeer/success', 'PayeerController@successAction');
Route::any('payeer/fail', 'PayeerController@failAction');
Route::any('payeer/status', 'PayeerController@statusAction');
Route::any('payeer/pay', 'PayeerController@payAction');

Route::any('users', 'IndexController@usersAction');
Route::any('builder/api/save', 'ApiController@indexAction');
Route::any('builder/api/load', 'ApiController@loadAction');


Route::any('/like/hook', 'LikeController@hookAction');
Route::any('/like/setup', 'LikeController@setupAction');


