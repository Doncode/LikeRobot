<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Foundation\Inspiring;
use Zyberspace\Telegram\Cli\Client;

class tg extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tg';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Telegram';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        exec('/usr/local/bin/tg/bin/telegram-cli --json -W -k /usr/local/bin/tg/server.pub -P 7314 -d -vvvRC &');
        sleep(1);

        $telegram = new Client('localhost:7314');
//        $contactList = $telegram->getContactList();
//        print_r($contactList);

//        $dialogList = $telegram->getDialogList();
//        print_r($dialogList);

//        $userInfo = $telegram->getUserInfo('@doncode');
//        print_r($userInfo);

//        $msg = $telegram->msg('@doncode', 'test ' . date('Y-m-d'));
//        $msg = $telegram->msg('181889615', 'test');
//        $list = $telegram->exec('channel_list');
//        print_r($list);
//        $telegram->msg($contactList[0]->print_name, 'Hey man, what\'s up? :D');

        $list = $telegram->exec('safe_quit');
        $list = $telegram->exec('quit');
        print_r($list);

        exec('killall telegram-cli');

//        $list = $telegram->markRead('$010000004f6ad70adcf20ab64805a6ec');
//        $list = $telegram->markRead('$05000000341ef33e79bb09ac4d3a2464ma');
//        $this->comment(PHP_EOL.Inspiring::quote().PHP_EOL);
    }
}
