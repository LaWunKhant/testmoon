<?php

namespace App\Http\Controllers;

class DashboardController extends Controller
{
    public function index()
    {
        //  Add your logic here to display the dashboard
        return view('dashboard'); // For example, return the dashboard view
    }
}
