<?php
/**
 * Created by PhpStorm.
 * User: a-basov
 * Date: 20.05.16
 * Time: 15:07
 */

namespace App\Http\Controllers;


use App\helpers\Base62;
use App\KeyboardsLike;
use App\LikeUser;
use App\StringsLike;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use unreal4u\Telegram\Methods\SendMessage;
use unreal4u\Telegram\Methods\SetWebhook;
use unreal4u\Telegram\Types\ReplyKeyboardMarkup;

class LikeController extends LikeBaseController
{
    protected function route(array $data)
    {
        $routes = [
            ['text' => '/start', 'action' => 'startCommand'],
            ['match' => '|^/start |', 'action' => 'startCommand'],
            ['text' => '/main', 'action' => 'mainCommand'],
            ['text' => StringsLike::BTN_POPULAR, 'action' => 'popularCommand'],
            ['text' => StringsLike::BTN_NEW, 'action' => 'newCommand'],
            ['text' => StringsLike::BTN_CREATE, 'action' => 'createCommand'],
            ['text' => '/help', 'action' => 'helpCommand'],
            ['text' => '/off', 'action' => 'offCommand'],
            ['text' => '/on', 'action' => 'onCommand'],
            ['inline_query' => true, 'action' => 'inlineCommand'],
        ];

        $text = strtolower(trim(array_get($data, 'message.text', '')));
        $state = $this->user->state;
        $action = 'unknownCommand';
        $params = [$data[$this->queryType]];

        foreach ($routes as $r) {
            if (
                (!array_key_exists('state', $r) || $r['state'] == $state) &&
                (!array_key_exists('inline_query', $r) || $this->queryType == 'inline_query') &&
                (!array_key_exists('text', $r) || $r['text'] == $text) &&
                (!array_key_exists('match', $r) || preg_match($r['match'], $text, $match)) &&
                (!array_key_exists('in', $r) || in_array($text, $r['in'])) &&
                (!array_key_exists('func', $r) || $r['func']($data[$this->queryType]))
            ) {
                $this->botan->track($data[$this->queryType], $r['action']);
                $action = $r['action'];
                if (isset($match)) {
                    $params[] = $match;
                }
                break;
            }
        }

        call_user_func_array([$this, $action], $params);
    }


    public function hookAction(Request $request)
    {
        $data = $request->all();
        $dataText = json_encode($data);

        try {
            file_put_contents(
                public_path().'/LikeLog'.date('Y-m-d').'.txt',
                json_encode($data).PHP_EOL.PHP_EOL,
                FILE_APPEND
            );

            if(array_key_exists('message', $data)){
                $this->queryType = 'message';
            }
            if(array_key_exists('inline_query', $data)){
                $this->queryType = 'inline_query';
            }

            if (!$this->queryType) {
                exit('invalid');
            }
            

            $userId = intval(array_get($data, "{$this->queryType}.from.id", 0));
            if (!$userId) {
                exit('invalid user id');
            }

            $first_name = array_get($data, "{$this->queryType}.from.first_name", '');
            $last_name = array_get($data, "{$this->queryType}.from.last_name", '');
            $username = array_get($data, "{$this->queryType}.from.username", '');
            $text = array_get($data, "{$this->queryType}.text", '');
            $live = date("Y-m-d H:i:s")." id:{$userId} ({$username} {$first_name} {$last_name}) $text ".PHP_EOL;
            file_put_contents(public_path().'/LikeLive.txt', $live, FILE_APPEND);

            try {
                $this->user = LikeUser::findOrFail($userId);
            } catch (ModelNotFoundException $e) {
                $this->isNew = true;
                $userData =  array_get($data, "{$this->queryType}.from", []);
                $userData['score'] = self::BONUS_REG;
                $this->user = LikeUser::create($userData);
            }

            $this->route($data);
            $this->user->save();
        } catch (\Exception $e) {
            $err = date('Y-m-d H:i:s ');
            $err .= 'code:'.$e->getCode().' file:'.$e->getFile().' line:'.$e->getLine().PHP_EOL;
            $err .= $e->getMessage().PHP_EOL;
            $err .= $e->getTraceAsString().PHP_EOL.PHP_EOL;
            $err .= $dataText;
            $err .= PHP_EOL.PHP_EOL.PHP_EOL.PHP_EOL;

            file_put_contents(public_path().'/LikeError.txt', $err, FILE_APPEND);

            return 'err'.$err;
        }

        return 'ok';
    }

    public function setupAction()
    {
        $setWeHook = new SetWebhook();
        $setWeHook->url = 'https://laravel.32x.com.ua/like/hook';

        return var_export($this->performApiRequest($setWeHook), 1);
    }

    public function startCommand(array $message)
    {
        $sendMessage = new SendMessage();
        $sendMessage->parse_mode = 'HTML';
        $sendMessage->chat_id = $message['from']['id'];
        $sendMessage->text = "Привет!\nЗдесь ты можешь участвовать в конкурсе или объявить свой.";
        $sendMessage->reply_markup = new ReplyKeyboardMarkup();
        $sendMessage->reply_markup->keyboard = $this->keyboard->setType(KeyboardsLike::MAIN)->genKeyboard();
        $sendMessage->reply_markup->resize_keyboard = true;
        $sendMessage->reply_markup->one_time_keyboard = false;

        $this->performApiRequest($sendMessage);
    }

    public function mainCommand(array $message)
    {
        $sendMessage = new SendMessage();
        $sendMessage->parse_mode = 'HTML';
        $sendMessage->chat_id = $message['from']['id'];
        $sendMessage->text = "Главное меню: (/help - помощь)";
        $sendMessage->reply_markup = new ReplyKeyboardMarkup();
        $sendMessage->reply_markup->keyboard = $this->keyboard->setType(KeyboardsLike::MAIN)->genKeyboard();
        $sendMessage->reply_markup->resize_keyboard = true;
        $sendMessage->reply_markup->one_time_keyboard = false;

        $this->performApiRequest($sendMessage);
    }


    public function helpCommand(array $message)
    {
        $sendMessage = new SendMessage();
        $sendMessage->parse_mode = 'HTML';
        $sendMessage->chat_id = $message['from']['id'];
        $sendMessage->text = "Здесь ты можешь участвовать в конкурсе или объявить свой.\n\n";
        $sendMessage->text .= "/main - главное меню\n";
        $sendMessage->text .= "/help - помощь\n";
        $sendMessage->text .= "/on - включить оповещения\n";
        $sendMessage->text .= "/off - выключить оповещения\n";
        $sendMessage->text .= "[Мы отправляем не больше 2 сообщений в день]\n";
        $sendMessage->reply_markup = new ReplyKeyboardMarkup();
        $sendMessage->reply_markup->keyboard = $this->keyboard->setType(KeyboardsLike::MAIN)->genKeyboard();
        $sendMessage->reply_markup->resize_keyboard = true;
        $sendMessage->reply_markup->one_time_keyboard = false;

        $this->performApiRequest($sendMessage);
    }

    public function popularCommand(array $message)
    {
        $sendMessage = new SendMessage();
        $sendMessage->parse_mode = 'HTML';
        $sendMessage->chat_id = $message['from']['id'];
        $sendMessage->text = "Здесь ты можешь участвовать в конкурсе или объявить свой. popularCommand";
        $sendMessage->reply_markup = new ReplyKeyboardMarkup();
        $sendMessage->reply_markup->keyboard = $this->keyboard->setType(KeyboardsLike::MAIN)->genKeyboard();
        $sendMessage->reply_markup->resize_keyboard = true;
        $sendMessage->reply_markup->one_time_keyboard = false;

        $this->performApiRequest($sendMessage);
    }

    public function newCommand(array $message)
    {
        $sendMessage = new SendMessage();
        $sendMessage->parse_mode = 'HTML';
        $sendMessage->chat_id = $message['from']['id'];
        $sendMessage->text = "Здесь ты можешь участвовать в конкурсе или объявить свой. newCommand";
        $sendMessage->reply_markup = new ReplyKeyboardMarkup();
        $sendMessage->reply_markup->keyboard = $this->keyboard->setType(KeyboardsLike::MAIN)->genKeyboard();
        $sendMessage->reply_markup->resize_keyboard = true;
        $sendMessage->reply_markup->one_time_keyboard = false;

        $this->performApiRequest($sendMessage);
    }

    public function createCommand(array $message)
    {
        $sendMessage = new SendMessage();
        $sendMessage->parse_mode = 'HTML';
        $sendMessage->chat_id = $message['from']['id'];
        $sendMessage->text = "Здесь ты можешь участвовать в конкурсе или объявить свой. createCommand";
        $sendMessage->reply_markup = new ReplyKeyboardMarkup();
        $sendMessage->reply_markup->keyboard = $this->keyboard->setType(KeyboardsLike::MAIN)->genKeyboard();
        $sendMessage->reply_markup->resize_keyboard = true;
        $sendMessage->reply_markup->one_time_keyboard = false;

        $buttons = [];
        $id62 = Base62::convert($this->user->id, 10, 62);
        foreach ([10, 50, 100, 500] as $amount) {
            $buttons[] = ['text' => "{$amount}р", 'callback_data' => $id62];
        }



        $sendMessage->reply_markup = [
            'inline_keyboard' => [
                $buttons,
                [['text' => "Поделиться", 'switch_inline_query' => $id62]]
            ],
        ];

        $this->performApiRequest($sendMessage);
    }

    public function inlineCommand(array $message)
    {

        file_put_contents(
            public_path().'/LikeLog'.date('Y-m-d').'.txt',
            'inlineCommand  '.PHP_EOL.PHP_EOL,
            FILE_APPEND
        );


        $sendMessage = new SendMessage();
        $sendMessage->parse_mode = 'HTML';
        $sendMessage->chat_id = $message['from']['id'];
        $sendMessage->text = "inlineCommand";
        $sendMessage->reply_markup = new ReplyKeyboardMarkup();
        $sendMessage->reply_markup->keyboard = $this->keyboard->setType(KeyboardsLike::MAIN)->genKeyboard();
        $sendMessage->reply_markup->resize_keyboard = true;
        $sendMessage->reply_markup->one_time_keyboard = false;

        $buttons = [];
        $id62 = Base62::convert($this->user->id, 10, 62);
        foreach ([10, 50, 100, 500] as $amount) {
            $buttons[] = ['text' => "{$amount}р", 'callback_data' => $id62];
        }



        $sendMessage->reply_markup = [
            'inline_keyboard' => [
                $buttons,
                [['text' => "Поделиться", 'switch_inline_query' => $id62]]
            ],
        ];

        $this->performApiRequest($sendMessage);
    }
}