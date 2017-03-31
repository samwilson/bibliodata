<?php

namespace App\Http\Controllers;

class HomeController extends \App\Http\Controllers\Controller {

    public function home()
    {
        return view('home')->with('navbar_active', 'home');
    }

}
