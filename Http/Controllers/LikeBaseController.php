<?php
/**
 * Created by PhpStorm.
 * User: a-basov
 * Date: 20.05.16
 * Time: 15:07
 */

namespace App\Http\Controllers;


use App\Botan;
use App\KeyboardsLike;
use App\LikeUser;
use unreal4u\Telegram\Methods\SendMessage;
use unreal4u\Telegram\Types\ReplyKeyboardMarkup;
use unreal4u\TgLog;

class LikeBaseController extends Controller
{
    /**
     * @var LikeUser
     */
    protected $user;

    /**
     * @var Botan
     */
    protected $botan;

    /**
     * @var KeyboardsLike
     */
    protected $keyboard;

    /**
     * @var TgLog
     */
    protected $tgLog;

    const BONUS_REG = 5;

    public function __construct()
    {
        //todo config
        $this->tgLog = new TgLog('228041087:AAFfxvhRV-c9Zosy7cG3BNChNcfAPOMiOMk');
        $this->botan = new Botan('1sRWBS8-J2jBcQqIpSEph6:GeYP8bLW2');

        //todo translate
        $this->keyboard = new KeyboardsLike();
    }


    public function performApiRequest($sendMessage)
    {
        return $this->tgLog->performApiRequest($sendMessage);
    }


    public function unknownCommand($message)
    {
        $sendMessage = new SendMessage();
        $sendMessage->parse_mode = 'HTML';
        $sendMessage->chat_id = $message['from']['id'];
        $sendMessage->text = 'Неизвестная команда. '.PHP_EOL; //todo translate
        $sendMessage->reply_markup = new ReplyKeyboardMarkup();
        $sendMessage->reply_markup->keyboard = $this->keyboard->setType(KeyboardsLike::MAIN)->genKeyboard();
        $sendMessage->reply_markup->resize_keyboard = true;
        $sendMessage->reply_markup->one_time_keyboard = false;
        $this->performApiRequest($sendMessage);
    }


    public function offCommand($message)
    {
        $this->user->is_notify = false;
        $sendMessage = new SendMessage();
        $sendMessage->parse_mode = 'HTML';
        $sendMessage->chat_id = $message['from']['id'];
        $sendMessage->text = "Оповещения отключены.\n/on - включить\n(Мы отправляем не больше 2 сообщений в день)";
        $sendMessage->reply_markup = new ReplyKeyboardMarkup();
        $sendMessage->reply_markup->keyboard = $this->keyboard->setType(KeyboardsLike::MAIN)->genKeyboard();
        $sendMessage->reply_markup->resize_keyboard = true;
        $sendMessage->reply_markup->one_time_keyboard = false;
        $this->performApiRequest($sendMessage);
    }


    public function onCommand($message)
    {
        $this->user->is_notify = true;
        $sendMessage = new SendMessage();
        $sendMessage->parse_mode = 'HTML';
        $sendMessage->chat_id = $message['from']['id'];
        $sendMessage->text = "Оповещения включены.\n/off - отключить\n(Мы отправляем не больше 2 сообщений в день)";
        $sendMessage->reply_markup = new ReplyKeyboardMarkup();
        $sendMessage->reply_markup->keyboard = $this->keyboard->setType(KeyboardsLike::MAIN)->genKeyboard();
        $sendMessage->reply_markup->resize_keyboard = true;
        $sendMessage->reply_markup->one_time_keyboard = false;
        $this->performApiRequest($sendMessage);
    }

}