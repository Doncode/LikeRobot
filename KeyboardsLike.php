<?php

namespace App;


class KeyboardsLike
{
    const MAIN = 1;

    const YES_NO = 5;
    private $type = 0;

    public function setType($type)
    {
        $_SESSION['last_keyboard_type'] = $type;
        $this->type = $type;

        return $this;
    }

    public function genKeyboard()
    {
        switch ($this->type) {
            case self::MAIN:
                $keyboard = [
                    [StringsLike::BTN_POPULAR],
                    [StringsLike::BTN_NEW],
                    [StringsLike::BTN_CREATE],
                ];
                break;
            default:
                $keyboard = [];
        }

        return $keyboard;
    }


    /**
     * Set keyboard from session as current
     */
    public function setLastAsCurrent()
    {
        if (isset($_SESSION['last_keyboard_type'])) {
            $this->setType($_SESSION['last_keyboard_type']);
        }
    }
}