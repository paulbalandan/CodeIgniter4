<?php

namespace App\Controllers;

use LogicException;

class Home extends BaseController
{
    public function index(): string
    {
        $this->bar();

        return view('welcome_message');
    }

    public function bar()
    {
        throw new LogicException("I'm here!");
    }
}
