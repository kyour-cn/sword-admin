<?php
namespace process;

use Workerman\Connection\TcpConnection;
use Workerman\Protocols\Websocket;

class WsLive
{
    public $puller = [];
    public $pusher = [];

    public function onWebSocketConnect(TcpConnection $connection, $http_buffer)
    {
        echo "onWebSocketConnect\n";

        $connection->send("666");

    }

    public function onMessage(TcpConnection $connection, $res)
    {
        //设置用户鉴权
        $action = substr($res, 0, 4);
        $data = substr($res, 5);

        switch ($action){
            case "logi":
                if($data == 'push'){
                    var_dump("注册推");
                    $this->pusher[$connection->id] = 1;
                }elseif($data == 'pull'){
                    var_dump("注册拉");
                    $connection->websocketType = Websocket::BINARY_TYPE_ARRAYBUFFER;

                    //告诉推流 上传图像
                    if(count($this->puller) == 0){
                        foreach ($this->pusher as $id => $val){
                            if(isset($connection->worker->connections[$id])){
                                $connection->worker->connections[$id]->send("push");
                            }
                        }
                    }
                    $this->puller[$connection->id] = 1;
                }
                break;
            default:
                $hex = bin2hex($action);
                if($hex == "ffd8ffe0") {
//                    echo ".";
                    foreach ($this->puller as $id => $val){
                        if(isset($connection->worker->connections[$id])){
                            $connection->worker->connections[$id]->send($res);
                        }
                    }
                    if(count($this->puller)){
                        $connection->send("push");
                    }
                }
                break;
        }
    }

    public function onClose(TcpConnection $connection)
    {
        if(isset($this->puller[$connection->id])){
            echo "onClose Pull\n";
            unset($this->puller[$connection->id]);
        }
        if(isset($this->pusher[$connection->id])){
            echo "onClose Push\n";
            unset($this->pusher[$connection->id]);
        }
    }
}