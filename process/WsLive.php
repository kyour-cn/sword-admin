<?php
namespace process;

use think\facade\Cache;
use Workerman\Connection\TcpConnection;

class WsLive
{
    public $cache = [

    ];

    public $puller = [];

    public function onWebSocketConnect(TcpConnection $connection, $http_buffer)
    {
        echo "onWebSocketConnect\n";

        $connection->send("666");

    }

    public function onMessage(TcpConnection $connection, $res)
    {
//        var_dump(microtime());

        $userCacheKey = "user_type:{$connection->id}";
//        $userType = Cache::get($userCacheKey);

        //设置用户鉴权
        $action = substr($res, 0, 4);
        $data = substr($res, 5);

//        var_dump($action);
        switch ($action){
            case "logi":
                $this->cache[$userCacheKey] = $data;
//                Cache::set($userCacheKey, $data, 86400);
                var_dump($data);
                break;
            case "push":
//                $this->cache["live"] = $data;
//                Cache::set("live", $data);

                //开始推
                foreach ($this->puller as $id){
                    if(isset($connection->worker->connections[$id])){
                        $connection->worker->connections[$id]->send($data);
                        echo '+';
                    }
                }
                $connection->send("push");
                break;
            case "pull":
                echo '.';
                //获取图像
//                $num = rand(1,5);
//                $msg = file_get_contents("stream ({$num}).jpg");
//                $connection->send(base64_encode($msg));

//                $img = Cache::get("live", '');
//                $img = $this->cache["live"]??'';
                $this->puller[] = $connection->id;
                var_dump("注册拉");
//                $connection->send($img);
                break;
        }
//        var_dump(microtime());

    }

    public function onClose(TcpConnection $connection)
    {
        echo "onClose\n";
    }
}