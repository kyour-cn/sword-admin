<?php

namespace app\admin\controller;

use app\admin\services\UserService;
use app\BaseController;
use app\common\utils\AuditLog;
use support\Request;
use support\Response;
use Throwable;

/**
 * 当前登录用户相关接口
 * @api
 */
class User extends BaseController
{
    public function info(Request $req): Response
    {
        $serv = new UserService();

        if ($req->method() === 'POST') {
            $user = $serv->getCurrentUser();
            try {
                $serv->updateCurrentUser($req->post());
            } catch (Throwable $e) {
                $this->recordCurrentUserAudit($req, $user, '修改个人信息', false);
                throw $e;
            }

            $user = $serv->getCurrentUser();
            $this->recordCurrentUserAudit($req, $user, '修改个人信息', true);
            return $this->success('保存成功', $user);
        }

        return $this->success(data: $serv->getCurrentUser());
    }

    public function password(Request $req): Response
    {
        if ($req->method() !== 'POST') {
            return $this->fail(405, '请求方法不支持');
        }

        $serv = new UserService();
        $user = $serv->getCurrentUser();
        try {
            $serv->updateCurrentPassword($req->post());
        } catch (Throwable $e) {
            $this->recordCurrentUserAudit($req, $user, '修改密码', false);
            throw $e;
        }

        $this->recordCurrentUserAudit($req, $user, '修改密码', true);
        return $this->success('密码修改成功');
    }

    public function taskList(Request $req): Response
    {
        $serv = new UserService();
        $res = $serv->getTaskList();
        return $this->success(data: $res);
    }

    /**
     * 获取当前登录用户的操作日志
     * @api
     */
    public function operationLog(Request $req): Response
    {
        if ($req->method() !== 'GET') {
            return $this->fail(405, '请求方法不支持');
        }

        $serv = new UserService();
        $res = $serv->getCurrentAuditLogList($req->get());
        return $this->success(data: $res);
    }

    /**
     * 记录用户中心操作，日志查询接口本身不记录，避免查询行为淹没实际操作。
     * @param Request $req
     * @param array $user
     * @param string $title
     * @param bool $status
     * @return void
     */
    private function recordCurrentUserAudit(Request $req, array $user, string $title, bool $status): void
    {
        AuditLog::record([
            'actor_id' => (int)($user['id'] ?? 0),
            'actor_name' => (string)($user['nickname'] ?? ''),
            'action' => 'update',
            'module' => 'user_center',
            'module_title' => '用户中心',
            'resource_type' => 'user',
            'resource_id' => (string)($user['id'] ?? ''),
            'title' => $title,
            'description' => $title . ($status ? '成功' : '失败'),
            'status' => $status ? 1 : 0,
            'request' => $req,
        ]);
    }
}
