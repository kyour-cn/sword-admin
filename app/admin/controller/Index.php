<?php
namespace app\admin\controller;

use app\BaseController;
use app\middleware\JwtMiddleware;

class Index extends BaseController
{
    protected $middleware = [
        JwtMiddleware::class
    ];

    public function index()
    {
        echo "hello admin.";
    }

    public function menu()
    {

    }

}
