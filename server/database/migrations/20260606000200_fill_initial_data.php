<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class FillInitialData extends AbstractMigration
{
    public function up(): void
    {
        if ($this->fetchRow('select `id` from `app` where `id` = 1 limit 1')) {
            return;
        }

        $this->table('app')->insert([
            ['id' => 1, 'name' => '系统后台', 'key' => 'admin', 'remark' => '系统总后台', 'status' => 1, 'sort' => 0],
        ])->saveData();

        $this->table('config')->insert([
            ['key' => 'site', 'title' => '站点配置', 'group' => 'base', 'type' => 'json', 'value' => json_encode(['admin_captcha_switch' => false], JSON_UNESCAPED_UNICODE)],
        ])->saveData();

        $this->table('file_storage')->insert([
            ['id' => 1, 'name' => '本地储存', 'key' => 'local', 'config' => null, 'is_default' => 1, 'status' => 1],
        ])->saveData();

        $this->table('log_type')->insert($this->logTypes())->saveData();
        $this->table('menu')->insert($this->menus())->saveData();
        $this->table('menu_api')->insert($this->menuApis())->saveData();
        $this->table('role')->insert([
            [
                'id' => 1,
                'app_id' => 1,
                'name' => '管理员',
                'rules' => '',
                'rules_checked' => '',
                'remark' => '',
                'status' => 1,
                'sort' => 0,
                'is_admin' => 1,
                'created_at' => '2025-01-01 00:00:00',
                'updated_at' => '2025-01-01 00:00:00',
                'deleted_at' => null,
            ],
        ])->saveData();
        $this->table('user')->insert([
            [
                'id' => 1,
                'nickname' => '管理员',
                'username' => 'admin',
                'mobile' => '',
                'avatar' => '',
                'password' => '767e955464233667bfd855686a55b352',
                'status' => 1,
                'login_time' => '2025-01-01 00:00:00',
                'created_at' => '2025-01-01 00:00:00',
                'updated_at' => '2025-01-01 00:00:00',
                'deleted_at' => null,
            ],
        ])->saveData();
        $this->table('user_role')->insert([
            ['user_id' => 1, 'role_id' => 1, 'created_at' => '2025-01-01 00:00:00', 'deleted_at' => null],
        ])->saveData();
    }

    public function down(): void
    {
        $this->execute('delete from `user_role` where `user_id` = 1 and `role_id` = 1');
        $this->execute('delete from `user` where `id` = 1');
        $this->execute('delete from `role` where `id` = 1');
        $this->execute('delete from `menu_api` where `app_id` = 1');
        $this->execute('delete from `menu` where `app_id` = 1');
        $this->execute('delete from `log_type` where `id` in (1, 2, 3, 4, 10)');
        $this->execute('delete from `file_storage` where `id` = 1');
        $this->execute("delete from `config` where `key` = 'site'");
        $this->execute('delete from `app` where `id` = 1');
    }

    private function logTypes(): array
    {
        return [
            ['id' => 1, 'app_id' => 0, 'name' => '调试', 'label' => 'debug', 'remark' => '调试信息', 'status' => 1, 'color' => '#333333'],
            ['id' => 2, 'app_id' => 0, 'name' => '信息', 'label' => 'info', 'remark' => '一般信息', 'status' => 1, 'color' => '#3498db'],
            ['id' => 3, 'app_id' => 0, 'name' => '警告', 'label' => 'warn', 'remark' => '警告，非错误的异常情况', 'status' => 1, 'color' => '#f1c40f'],
            ['id' => 4, 'app_id' => 0, 'name' => '错误', 'label' => 'error', 'remark' => '运行时错误，记录并非紧急的问题', 'status' => 1, 'color' => '#d63031'],
            ['id' => 10, 'app_id' => 1, 'name' => '登录日志', 'label' => 'login', 'remark' => '用户登录时记录的日志', 'status' => 1, 'color' => '#23e279'],
        ];
    }

    private function menus(): array
    {
        return array_merge($this->baseMenus(), $this->ruleMenus());
    }

    private function baseMenus(): array
    {
        return [
            ['id' => 1, 'app_id' => 1, 'pid' => 0, 'name' => 'home', 'title' => '首页', 'type' => 'menu', 'path' => '/home', 'component' => '', 'sort' => 0, 'meta' => $this->meta('首页', 'el-icon-compass', ['fullpage' => false])],
            ['id' => 2, 'app_id' => 1, 'pid' => 1, 'name' => 'dashboard', 'title' => '控制台', 'type' => 'menu', 'path' => '/dashboard', 'component' => 'admin/home', 'sort' => 0, 'meta' => $this->meta('控制台', 'el-icon-coin', ['affix' => true])],
            ['id' => 3, 'app_id' => 1, 'pid' => 0, 'name' => 'system', 'title' => '系统管理', 'type' => 'menu', 'path' => '/admin', 'component' => '', 'sort' => 0, 'meta' => $this->meta('系统管理', 'el-icon-cpu', ['fullpage' => false])],
            ['id' => 4, 'app_id' => 1, 'pid' => 3, 'name' => 'app', 'title' => '应用管理', 'type' => 'menu', 'path' => '/admin/system/app', 'component' => 'admin/system/app', 'sort' => 1, 'meta' => $this->meta('应用管理', 'el-icon-message-box')],
            ['id' => 5, 'app_id' => 1, 'pid' => 3, 'name' => 'menus', 'title' => '菜单权限', 'type' => 'menu', 'path' => '/admin/system/menu', 'component' => 'admin/system/menu', 'sort' => 2, 'meta' => $this->meta('菜单权限', 'el-icon-menu', ['fullpage' => false])],
            ['id' => 6, 'app_id' => 1, 'pid' => 3, 'name' => 'role', 'title' => '角色管理', 'type' => 'menu', 'path' => '/admin/system/role', 'component' => 'admin/system/role', 'sort' => 3, 'meta' => $this->meta('角色管理', 'el-icon-briefcase', ['fullpage' => false])],
            ['id' => 7, 'app_id' => 1, 'pid' => 3, 'name' => 'user', 'title' => '用户管理', 'type' => 'menu', 'path' => '/admin/system/user', 'component' => 'admin/system/user', 'sort' => 4, 'meta' => $this->meta('用户管理', 'el-icon-avatar', ['fullpage' => false])],
            ['id' => 8, 'app_id' => 1, 'pid' => 3, 'name' => 'log', 'title' => '系统日志', 'type' => 'menu', 'path' => '/admin/system/log', 'component' => 'admin/system/log', 'sort' => 6, 'meta' => $this->meta('系统日志', 'el-icon-document')],
            ['id' => 9, 'app_id' => 1, 'pid' => 3, 'name' => 'file', 'title' => '文件管理', 'type' => 'menu', 'path' => '/admin/system/file', 'component' => 'admin/system/file', 'sort' => 7, 'meta' => $this->meta('文件管理', 'el-icon-folder-opened')],
            ['id' => 10, 'app_id' => 1, 'pid' => 1, 'name' => 'user_center', 'title' => '用户中心', 'type' => 'menu', 'path' => '/admin/home/user_center', 'component' => 'admin/home/user_center', 'sort' => 0, 'meta' => $this->meta('用户中心', 'el-icon-avatar', ['hidden' => true])],
        ];
    }

    private function ruleMenus(): array
    {
        $parentIds = [];
        foreach ($this->baseMenus() as $menu) {
            $parentIds[$menu['name']] = $menu['id'];
        }

        $menus = [];
        foreach ($this->rules() as $rule) {
            if (!isset($parentIds[$rule['parent']])) {
                throw new \RuntimeException("缺少父级菜单：{$rule['parent']}");
            }

            $menus[] = [
                'id' => $rule['id'],
                'app_id' => 1,
                'pid' => $parentIds[$rule['parent']],
                'name' => $rule['name'],
                'title' => $rule['title'],
                'type' => 'rule',
                'path' => '',
                'component' => '',
                'sort' => $rule['sort'],
                'meta' => $this->meta($rule['title'], '', [
                    'tag' => $rule['tag'],
                    'type' => 'rule',
                    'hidden' => true,
                ]),
            ];
        }

        return $menus;
    }

    private function menuApis(): array
    {
        $apis = [];
        foreach ($this->rules() as $rule) {
            foreach ($rule['apis'] as $api) {
                $apis[] = ['app_id' => 1, 'menu_id' => $rule['id'], 'path' => $api['path'], 'tag' => $api['tag']];
            }
        }

        return $apis;
    }

    private function rules(): array
    {
        return [
            ['id' => 11, 'parent' => 'app', 'name' => 'app_list', 'title' => '查询应用', 'sort' => 1, 'tag' => 'admin.system.app.list', 'apis' => [['path' => '/admin/system/app/list', 'tag' => 'admin.system.app.list']]],
            ['id' => 12, 'parent' => 'app', 'name' => 'app_add', 'title' => '新增应用', 'sort' => 2, 'tag' => 'admin.system.app.add', 'apis' => [['path' => '/admin/system/app/add', 'tag' => 'admin.system.app.add']]],
            ['id' => 13, 'parent' => 'app', 'name' => 'app_edit', 'title' => '编辑应用', 'sort' => 3, 'tag' => 'admin.system.app.edit', 'apis' => [['path' => '/admin/system/app/edit', 'tag' => 'admin.system.app.edit']]],
            ['id' => 14, 'parent' => 'app', 'name' => 'app_delete', 'title' => '删除应用', 'sort' => 4, 'tag' => 'admin.system.app.delete', 'apis' => [['path' => '/admin/system/app/delete', 'tag' => 'admin.system.app.delete']]],
            ['id' => 15, 'parent' => 'menus', 'name' => 'menu_list', 'title' => '查询菜单', 'sort' => 1, 'tag' => 'admin.system.menu.list', 'apis' => [['path' => '/admin/system/menu/list', 'tag' => 'admin.system.menu.list']]],
            ['id' => 16, 'parent' => 'menus', 'name' => 'menu_add', 'title' => '新增菜单', 'sort' => 2, 'tag' => 'admin.system.menu.add', 'apis' => [['path' => '/admin/system/menu/add', 'tag' => 'admin.system.menu.add']]],
            ['id' => 17, 'parent' => 'menus', 'name' => 'menu_edit', 'title' => '编辑菜单', 'sort' => 3, 'tag' => 'admin.system.menu.edit', 'apis' => [['path' => '/admin/system/menu/edit', 'tag' => 'admin.system.menu.edit']]],
            ['id' => 18, 'parent' => 'menus', 'name' => 'menu_delete', 'title' => '删除菜单', 'sort' => 4, 'tag' => 'admin.system.menu.delete', 'apis' => [['path' => '/admin/system/menu/delete', 'tag' => 'admin.system.menu.delete']]],
            ['id' => 19, 'parent' => 'role', 'name' => 'role_list', 'title' => '查询角色', 'sort' => 1, 'tag' => 'admin.system.role.list', 'apis' => [['path' => '/admin/system/role/list', 'tag' => 'admin.system.role.list']]],
            ['id' => 20, 'parent' => 'role', 'name' => 'role_add', 'title' => '新增角色', 'sort' => 2, 'tag' => 'admin.system.role.add', 'apis' => [['path' => '/admin/system/role/add', 'tag' => 'admin.system.role.add']]],
            ['id' => 21, 'parent' => 'role', 'name' => 'role_edit', 'title' => '编辑角色', 'sort' => 3, 'tag' => 'admin.system.role.edit', 'apis' => [['path' => '/admin/system/role/edit', 'tag' => 'admin.system.role.edit']]],
            ['id' => 22, 'parent' => 'role', 'name' => 'role_delete', 'title' => '删除角色', 'sort' => 4, 'tag' => 'admin.system.role.delete', 'apis' => [['path' => '/admin/system/role/delete', 'tag' => 'admin.system.role.delete']]],
            ['id' => 23, 'parent' => 'role', 'name' => 'role_permission', 'title' => '分配权限', 'sort' => 5, 'tag' => 'admin.system.role.permission', 'apis' => [['path' => '/admin/system/role/edit?type=permission', 'tag' => 'admin.system.role.permission']]],
            ['id' => 24, 'parent' => 'user', 'name' => 'user_list', 'title' => '查询用户', 'sort' => 1, 'tag' => 'admin.system.user.list', 'apis' => [['path' => '/admin/system/user/list', 'tag' => 'admin.system.user.list']]],
            ['id' => 25, 'parent' => 'user', 'name' => 'user_add', 'title' => '新增用户', 'sort' => 2, 'tag' => 'admin.system.user.add', 'apis' => [['path' => '/admin/system/user/add', 'tag' => 'admin.system.user.add']]],
            ['id' => 26, 'parent' => 'user', 'name' => 'user_edit', 'title' => '编辑用户', 'sort' => 3, 'tag' => 'admin.system.user.edit', 'apis' => [['path' => '/admin/system/user/edit', 'tag' => 'admin.system.user.edit']]],
            ['id' => 27, 'parent' => 'user', 'name' => 'user_delete', 'title' => '删除用户', 'sort' => 4, 'tag' => 'admin.system.user.delete', 'apis' => [['path' => '/admin/system/user/delete', 'tag' => 'admin.system.user.delete']]],
            ['id' => 28, 'parent' => 'user', 'name' => 'user_export', 'title' => '导出用户', 'sort' => 5, 'tag' => 'admin.system.user.export', 'apis' => [['path' => '/admin/system/user/export', 'tag' => 'admin.system.user.export']]],
            ['id' => 29, 'parent' => 'user', 'name' => 'user_reset_password', 'title' => '重置密码', 'sort' => 6, 'tag' => 'admin.system.user.resetPassword', 'apis' => [['path' => '/admin/system/user/resetPassword', 'tag' => 'admin.system.user.resetPassword']]],
            ['id' => 30, 'parent' => 'log', 'name' => 'log_list', 'title' => '查询日志', 'sort' => 1, 'tag' => 'admin.system.log.list', 'apis' => [['path' => '/admin/system/log/list', 'tag' => 'admin.system.log.list']]],
            ['id' => 31, 'parent' => 'log', 'name' => 'log_stat', 'title' => '日志统计', 'sort' => 2, 'tag' => 'admin.system.log.stat', 'apis' => [['path' => '/admin/system/log/logStat', 'tag' => 'admin.system.log.stat']]],
            ['id' => 32, 'parent' => 'log', 'name' => 'log_type_list', 'title' => '日志类型', 'sort' => 3, 'tag' => 'admin.system.log.typeList', 'apis' => [['path' => '/admin/system/log/typeList', 'tag' => 'admin.system.log.typeList']]],
            ['id' => 33, 'parent' => 'file', 'name' => 'file_list', 'title' => '查询文件', 'sort' => 1, 'tag' => 'admin.system.file.list', 'apis' => [['path' => '/admin/system/file/list', 'tag' => 'admin.system.file.list']]],
            ['id' => 34, 'parent' => 'file', 'name' => 'file_upload', 'title' => '上传文件', 'sort' => 2, 'tag' => 'admin.system.file.upload', 'apis' => [['path' => '/admin/system/file/upload', 'tag' => 'admin.system.file.upload']]],
            ['id' => 35, 'parent' => 'file', 'name' => 'file_delete', 'title' => '删除文件', 'sort' => 3, 'tag' => 'admin.system.file.delete', 'apis' => [['path' => '/admin/system/file/delete', 'tag' => 'admin.system.file.delete']]],
            ['id' => 36, 'parent' => 'file', 'name' => 'file_menu_list', 'title' => '查询文件夹', 'sort' => 4, 'tag' => 'admin.system.file.menuList', 'apis' => [['path' => '/admin/system/file/menuList', 'tag' => 'admin.system.file.menuList']]],
            ['id' => 37, 'parent' => 'file', 'name' => 'file_menu_add', 'title' => '新增文件夹', 'sort' => 5, 'tag' => 'admin.system.file.menuAdd', 'apis' => [['path' => '/admin/system/file/menuAdd', 'tag' => 'admin.system.file.menuAdd']]],
            ['id' => 38, 'parent' => 'file', 'name' => 'file_menu_delete', 'title' => '删除文件夹', 'sort' => 6, 'tag' => 'admin.system.file.menuDelete', 'apis' => [['path' => '/admin/system/file/menuDelete', 'tag' => 'admin.system.file.menuDelete']]],
        ];
    }

    private function meta(string $title, string $icon, array $extra = []): string
    {
        return json_encode(array_merge([
            'tag' => '',
            'icon' => $icon,
            'type' => 'menu',
            'color' => '',
            'title' => $title,
            'active' => '',
            'hidden' => false,
            'fullPage' => false,
            'hiddenBreadcrumb' => false,
        ], $extra), JSON_UNESCAPED_UNICODE);
    }
}
