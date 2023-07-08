<?php

namespace plugin\admin\app\controller;

use app\BaseController;
use app\exception\MsgException;
use app\middleware\AuthMiddleware;
use plugin\admin\app\service\AdminRbacService;
use plugin\admin\app\service\AdminLogService;
use plugin\admin\app\service\AdminUserService;
use support\Request;
use support\Response;
use sword\http\middleware\MiddlewareAttr;
use think\db\exception\DbException;

/**
 * 系统管理
 * @api
 */
#[MiddlewareAttr(AuthMiddleware::class)]
class System extends BaseController
{

    /**
     * 应用列表
     * @param Request $request
     * @return Response
     * @throws DbException
     * @api
     */
    public function appList(Request $request): Response
    {
        $params = $request->all();
        $service = new AdminRbacService();
        $list = $service->getAppList($params);
        return $this->withData(data: $list);
    }

    /**
     * 编辑、新增应用
     * @param Request $request
     * @return Response
     * @api
     */
    public function editApp(Request $request): Response
    {
        $data = $request->all();
        $service = new AdminRbacService();
        if (!empty($data['id'])) {
            $res = $service->editApp($data);
        } else {
            $res = $service->addApp($data);
        }
        return $this->withData(0, '编辑成功', $res);
    }

    /**
     * 删除应用
     * @param Request $request
     * @return Response
     * @api
     */
    public function deleteApp(Request $request): Response
    {
        $ids = $request->post('ids');
        $service = new AdminRbacService();
        $res = $service->deleteApp($ids);
        return $this->withData(0, '删除成功', $res);
    }

    /**
     * 菜单列表
     * @param Request $request
     * @return Response
     * @api
     */
    public function menuList(Request $request): Response
    {
        $params = $request->all();
        $service = new AdminRbacService();
        $menu = $service->getMenuList($params);
        return $this->withData(data: $menu);
    }

    /**
     * 编辑、新增菜单
     * @param Request $request
     * @return Response
     * @api
     */
    public function editMenu(Request $request): Response
    {
        $data = $request->all();
        $service = new AdminRbacService();
        if (!empty($data['id'])) {
            $res = $service->editMenu($data);
        } else {
            $res = $service->addMenu($data);
        }
        return $this->withData(0, '编辑成功', $res);
    }

    /**
     * 删除菜单
     * @param Request $request
     * @return Response
     * @api
     */
    public function deleteMenu(Request $request): Response
    {
        $ids = $request->post('ids');
        $service = new AdminRbacService();
        $res = $service->deleteMenu($ids);
        return $this->withData(0, '删除成功', $res);
    }

    /**
     * 权限菜单
     * @param Request $request
     * @return Response
     * @api
     */
    public function ruleList(Request $request): Response
    {
        $params = $request->all();
        $service = new AdminRbacService();
        $list = $service->getRuleList($params);
        return $this->withData(data: $list);
    }

    /**
     * 编辑、新增权限
     * @param Request $request
     * @return Response
     * @api
     */
    public function editRule(Request $request): Response
    {
        $data = $request->all();
        $service = new AdminRbacService();
        if (!empty($data['id'])) {
            $res = $service->editRule($data);
        } else {
            $res = $service->addRule($data);
        }
        return $this->withData(0, '编辑成功', $res);
    }

    /**
     * 删除权限
     * @param Request $request
     * @return Response
     * @api
     */
    public function deleteRule(Request $request): Response
    {
        $ids = $request->post('ids');
        $service = new AdminRbacService();
        $res = $service->deleteRule($ids);
        return $this->withData(0, '删除成功', $res);
    }

    /**
     * 角色菜单
     * @param Request $request
     * @return Response
     * @throws DbException
     * @api
     */
    public function roleList(Request $request): Response
    {
        $params = $request->all();
        $service = new AdminRbacService();

        //只显示手动创建的
        $params['is_auto'] = 0;

        $list = $service->getRoleList($params);
        return $this->withData(data: $list);
    }

    /**
     * 编辑、新增角色
     * @param Request $request
     * @return Response
     * @api
     */
    public function editRole(Request $request): Response
    {
        $data = $request->all();
        $service = new AdminRbacService();
        if (!empty($data['id'])) {
            $res = $service->editRole($data);
        } else {
            $res = $service->addRole($data);
        }
        return $this->withData(0, '编辑成功', $res);
    }

    /**
     * 删除角色
     * @param Request $request
     * @return Response
     * @api
     */
    public function deleteRole(Request $request): Response
    {
        $ids = $request->post('ids');
        $service = new AdminRbacService();
        $res = $service->deleteRole($ids);
        return $this->withData(0, '删除成功', $res);
    }

    /**
     * 用户
     * @param Request $request
     * @return Response
     * @throws DbException
     * @api
     */
    public function userList(Request $request): Response
    {
        $params = $request->all();
        $service = new AdminUserService();
        $list = $service->getList($params);
        return $this->withData(data: $list);
    }

    /**
     * 编辑、新增用户
     * @param Request $request
     * @return Response
     * @throws MsgException
     * @api
     */
    public function editUser(Request $request): Response
    {
        $data = $request->all();
        $service = new AdminUserService();
        if (!empty($data['id'])) {
            $res = $service->edit($data);
        } else {
            $res = $service->add($data);
        }
        return $this->withData(0, '编辑成功', $res);
    }

    /**
     * 删除用户
     * @param Request $request
     * @return Response
     * @api
     */
    public function deleteUser(Request $request): Response
    {
        $ids = $request->post('ids');
        $service = new AdminUserService();
        $res = $service->delete($ids);
        return $this->withData(0, '删除成功', $res);
    }

    /**
     * 日志页数据
     * @param Request $request
     * @return Response
     * @throws DbException|MsgException
     * @api
     */
    public function logPageInfo(Request $request): Response
    {
        $params = $request->all();

        $service = new AdminLogService();
        $levels = $service->getLevelList();
        $levelIdMap = array_column($levels, null, 'id');

        $levelGroup = [
            'system' => [],
            'app' => [],
        ];

        //日志级别分类
        foreach ($levels as $item) {
            $levelGroup[$item['id'] < 10 ? 'system' : 'app'][] = $item;
        }

        //根据时间范围返回日期数组
        $dates = $service->getDateList(strtotime($params['start_time']), strtotime($params['end_time']));
        $dateMaps = [];
        foreach ($dates as $index => $item) {
            $dateMaps[$item] = $index;
        }

        //获取统计数据
        $count = $service->getCount($params);

        //组成图表所需数据
        $levelMap = [];
        foreach ($count as $item) {
            $levelMap[$item['level_id']] = [
                'id' => $item['level_id'],
                'name' => $item['level_name'],
                'color' => $levelIdMap[(string)$item['level_id']]['color'],
                'data' => array_pad([], count($dates), 0)
            ];
        }
        foreach ($count as $item) {
            $levelMap[$item['level_id']]['data'][$dateMaps[$item['date']]] = $item['count'];
        }

        return $this->withData(0, 'success', [
            'levels' => $levelGroup,
            'dates' => $dates,
            'map' => array_column($levelMap, null)
        ]);
    }

    /**
     * 日志列表
     * @param Request $request
     * @return Response
     * @throws DbException|MsgException
     * @api
     */
    public function logList(Request $request): Response
    {
        $params = $request->all();
        $service = new AdminLogService();
        $list = $service->getList($params);
        return $this->withData(data: $list);
    }
}
