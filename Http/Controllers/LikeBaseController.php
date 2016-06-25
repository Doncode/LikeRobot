<?php
/**
 * Created by PhpStorm.
 * User: a-basov
 * Date: 20.05.16
 * Time: 15:07
 */

namespace App\Http\Controllers;


use App\User;
use Elasticsearch\ClientBuilder;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use unreal4u\Telegram\Methods\SendMessage;
use unreal4u\Telegram\Methods\SetWebhook;
use unreal4u\TgLog;

class LikeBaseController extends Controller
{
    private $tgLog;


    public function performApiRequest($sendMessage)
    {
        if (is_null($this->tgLog)) {
            $this->tgLog = new TgLog('228041087:AAFfxvhRV-c9Zosy7cG3BNChNcfAPOMiOMk');
        }

        return $this->tgLog->performApiRequest($sendMessage);
    }
}