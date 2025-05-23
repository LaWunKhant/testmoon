<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController; // Standard practice uses BaseController alias here

class Controller extends BaseController // *** Extend the core Laravel controller ***
{
    use AuthorizesRequests, ValidatesRequests;
}
