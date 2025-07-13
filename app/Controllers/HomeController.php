<?php
namespace App\Controllers;

use Core\Controller;

class HomeController extends Controller
{
    // рендеринг:
    public function showIndexPage()
    {
        $this->render('main');
    }
}