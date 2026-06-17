<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class FillBaselineData extends AbstractMigration
{
    private const APP_ID = 1;
    private const ADMIN_ROLE_ID = 1;
    private const ADMIN_USER_ID = 1;
    private const CREATED_AT = '2026-06-11 00:00:00';

    public function up(): void
    {
        $this->insertMissingRows('app', 'id', $this->apps());
        $this->insertMenusAndApis();
        $this->insertAdminRoleAndUser();
        $this->insertConfigFormsAndValues();
    }

    public function down(): void
    {
        $this->deleteWhere('user_role', $this->eq('user_id', self::ADMIN_USER_ID) . ' AND ' . $this->eq('role_id', self::ADMIN_ROLE_ID));
        $this->deleteIn('user', 'id', [self::ADMIN_USER_ID]);
        $this->deleteIn('role', 'id', [self::ADMIN_ROLE_ID]);
        $this->deleteIn('menu_api', 'tag', $this->menuApiTags());
        $this->deleteIn('menu', 'id', array_column($this->menus(), 'id'));
        $this->deleteIn('config', 'form_key', $this->configKeys());
        $this->deleteIn('config_form', 'key', $this->configKeys());
        $this->deleteIn('app', 'id', [self::APP_ID]);
    }

    private function apps(): array
    {
        return [
            [
                'id' => self::APP_ID,
                'name' => '系统后台',
                'key' => 'admin',
                'remark' => '系统总后台',
                'status' => 1,
                'sort' => 0,
            ],
        ];
    }

    private function insertMenusAndApis(): void
    {
        $this->insertMissingRows('menu', 'id', $this->menus());
        $this->insertMissingMenuApis();
    }

    private function menus(): array
    {
        return array_merge($this->baseMenus(), $this->ruleMenus());
    }

    private function baseMenus(): array
    {
        return [
            $this->menu(1, 0, 'home', '首页', '/home', '', 0, 'el-icon-compass'),
            $this->menu(2, 1, 'dashboard', '控制台', '/dashboard', 'admin/home', 0, 'el-icon-coin', ['affix' => true]),
            $this->menu(3, 0, 'system', '系统管理', '/admin', '', 0, 'el-icon-cpu'),
            $this->menu(4, 3, 'app', '应用管理', '/admin/system/app', 'admin/system/app', 1, 'el-icon-message-box'),
            $this->menu(5, 3, 'menus', '菜单权限', '/admin/system/menu', 'admin/system/menu', 2, 'el-icon-menu'),
            $this->menu(6, 3, 'role', '角色管理', '/admin/system/role', 'admin/system/role', 3, 'el-icon-briefcase'),
            $this->menu(7, 3, 'user', '用户管理', '/admin/system/user', 'admin/system/user', 4, 'el-icon-avatar'),
            $this->menu(8, 3, 'config', '系统配置', '/admin/system/config', 'admin/system/config', 5, 'el-icon-setting'),
            $this->menu(9, 3, 'config_form', '配置表单管理', '/admin/system/config-form', 'admin/system/config-form', 6, 'el-icon-document-copy'),
            $this->menu(10, 3, 'audit_log', '操作审计', '/admin/system/audit-log', 'admin/system/audit-log', 7, 'el-icon-document'),
            $this->menu(11, 3, 'file', '文件管理', '/admin/system/file', 'admin/system/file', 8, 'el-icon-folder-opened'),
            $this->menu(12, 1, 'user_center', '用户中心', '/admin/home/user_center', 'admin/home/user_center', 0, 'el-icon-avatar', ['hidden' => true]),
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
                throw new RuntimeException("缺少父级菜单：{$rule['parent']}");
            }

            $menus[] = [
                'id' => $rule['id'],
                'app_id' => self::APP_ID,
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
            foreach ($rule['paths'] as $path) {
                $apis[] = [
                    'app_id' => self::APP_ID,
                    'menu_id' => $rule['id'],
                    'path' => $path,
                    'tag' => $rule['tag'],
                ];
            }
        }

        return $apis;
    }

    private function rules(): array
    {
        return [
            $this->rule(13, 'app', 'app_list', '查询应用', 1, 'admin.system.app.list', ['/admin/system/app/list']),
            $this->rule(14, 'app', 'app_add', '新增应用', 2, 'admin.system.app.add', ['/admin/system/app/add']),
            $this->rule(15, 'app', 'app_edit', '编辑应用', 3, 'admin.system.app.edit', ['/admin/system/app/edit']),
            $this->rule(16, 'app', 'app_delete', '删除应用', 4, 'admin.system.app.delete', ['/admin/system/app/delete']),

            $this->rule(17, 'menus', 'menu_list', '查询菜单', 1, 'admin.system.menu.list', ['/admin/system/menu/list']),
            $this->rule(18, 'menus', 'menu_add', '新增菜单', 2, 'admin.system.menu.add', ['/admin/system/menu/add']),
            $this->rule(19, 'menus', 'menu_edit', '编辑菜单', 3, 'admin.system.menu.edit', ['/admin/system/menu/edit']),
            $this->rule(20, 'menus', 'menu_delete', '删除菜单', 4, 'admin.system.menu.delete', ['/admin/system/menu/delete']),

            $this->rule(21, 'role', 'role_list', '查询角色', 1, 'admin.system.role.list', ['/admin/system/role/list']),
            $this->rule(22, 'role', 'role_add', '新增角色', 2, 'admin.system.role.add', ['/admin/system/role/add']),
            $this->rule(23, 'role', 'role_edit', '编辑角色', 3, 'admin.system.role.edit', ['/admin/system/role/edit']),
            $this->rule(24, 'role', 'role_delete', '删除角色', 4, 'admin.system.role.delete', ['/admin/system/role/delete']),
            $this->rule(25, 'role', 'role_permission', '分配权限', 5, 'admin.system.role.permission', ['/admin/system/role/edit?type=permission']),

            $this->rule(26, 'user', 'user_list', '查询用户', 1, 'admin.system.user.list', ['/admin/system/user/list']),
            $this->rule(27, 'user', 'user_add', '新增用户', 2, 'admin.system.user.add', ['/admin/system/user/add']),
            $this->rule(28, 'user', 'user_edit', '编辑用户', 3, 'admin.system.user.edit', ['/admin/system/user/edit']),
            $this->rule(29, 'user', 'user_delete', '删除用户', 4, 'admin.system.user.delete', ['/admin/system/user/delete']),
            $this->rule(30, 'user', 'user_export', '导出用户', 5, 'admin.system.user.export', ['/admin/system/user/export']),
            $this->rule(31, 'user', 'user_reset_password', '重置密码', 6, 'admin.system.user.resetPassword', ['/admin/system/user/resetPassword']),

            $this->rule(32, 'config', 'config_list', '查询系统配置', 1, 'admin.system.config.list', ['/admin/system/config/list']),
            $this->rule(33, 'config', 'config_detail', '配置详情', 2, 'admin.system.config.detail', ['/admin/system/config/detail']),
            $this->rule(34, 'config', 'config_save', '保存系统配置', 3, 'admin.system.config.save', ['/admin/system/config/save']),

            $this->rule(35, 'config_form', 'config_form_list', '查询配置表单', 1, 'admin.system.configForm.list', ['/admin/system/configForm/list']),
            $this->rule(36, 'config_form', 'config_form_add', '新增配置表单', 2, 'admin.system.configForm.add', ['/admin/system/configForm/add']),
            $this->rule(37, 'config_form', 'config_form_edit', '编辑配置表单', 3, 'admin.system.configForm.edit', ['/admin/system/configForm/edit']),
            $this->rule(38, 'config_form', 'config_form_delete', '删除配置表单', 4, 'admin.system.configForm.delete', ['/admin/system/configForm/delete']),

            $this->rule(39, 'audit_log', 'audit_log_list', '查询操作审计', 1, 'admin.system.auditLog.list', ['/admin/system/auditLog/list']),
            $this->rule(40, 'audit_log', 'audit_log_stat', '操作审计统计', 2, 'admin.system.auditLog.stat', ['/admin/system/auditLog/stat']),
            $this->rule(41, 'audit_log', 'audit_log_actions', '操作审计动作', 3, 'admin.system.auditLog.actions', ['/admin/system/auditLog/actions']),

            $this->rule(42, 'file', 'file_list', '查询文件', 1, 'admin.system.file.list', ['/admin/system/file/list']),
            $this->rule(43, 'file', 'file_upload', '上传文件', 2, 'admin.system.file.upload', ['/admin/system/file/upload']),
            $this->rule(44, 'file', 'file_delete', '删除文件', 3, 'admin.system.file.delete', ['/admin/system/file/delete']),
            $this->rule(45, 'file', 'file_menu_list', '查询文件夹', 4, 'admin.system.file.menuList', ['/admin/system/file/menuList']),
            $this->rule(46, 'file', 'file_menu_add', '新增文件夹', 5, 'admin.system.file.menuAdd', ['/admin/system/file/menuAdd']),
            $this->rule(47, 'file', 'file_menu_delete', '删除文件夹', 6, 'admin.system.file.menuDelete', ['/admin/system/file/menuDelete']),
        ];
    }

    private function insertAdminRoleAndUser(): void
    {
        $this->insertMissingRows('role', 'id', [
            [
                'id' => self::ADMIN_ROLE_ID,
                'app_id' => self::APP_ID,
                'name' => '管理员',
                'rules' => '',
                'rules_checked' => '',
                'remark' => '',
                'status' => 1,
                'sort' => 0,
                'is_admin' => 1,
                'created_at' => self::CREATED_AT,
                'updated_at' => self::CREATED_AT,
                'deleted_at' => null,
            ],
        ]);

        $this->insertMissingRows('user', 'id', [
            [
                'id' => self::ADMIN_USER_ID,
                'nickname' => '管理员',
                'username' => 'admin',
                'mobile' => '',
                'avatar' => '',
                'password' => '767e955464233667bfd855686a55b352',
                'status' => 1,
                'login_time' => self::CREATED_AT,
                'created_at' => self::CREATED_AT,
                'updated_at' => self::CREATED_AT,
                'deleted_at' => null,
            ],
        ]);

        if (!$this->existsUserRole(self::ADMIN_USER_ID, self::ADMIN_ROLE_ID)) {
            $this->table('user_role')->insert([
                [
                    'user_id' => self::ADMIN_USER_ID,
                    'role_id' => self::ADMIN_ROLE_ID,
                    'created_at' => self::CREATED_AT,
                    'deleted_at' => null,
                ],
            ])->saveData();
        }
    }

    private function insertConfigFormsAndValues(): void
    {
        $forms = [];
        foreach ($this->configDefinitions() as $definition) {
            $forms[] = [
                'id' => $definition['form_id'],
                'key' => $definition['key'],
                'title' => $definition['title'],
                'group_key' => $definition['group_key'],
                'group_title' => $definition['group_title'],
                'schema' => $this->json($definition['schema']),
                'status' => 1,
                'sort' => $definition['sort'],
                'remark' => $definition['remark'],
                'created_at' => self::CREATED_AT,
                'updated_at' => self::CREATED_AT,
                'deleted_at' => null,
            ];
        }
        $this->insertMissingRows('config_form', 'key', $forms);

        $values = [];
        foreach ($this->configDefinitions() as $definition) {
            $form = $this->fetchRowBy('config_form', 'key', $definition['key']);
            if (!$form) {
                continue;
            }

            $values[] = [
                'id' => $definition['config_id'],
                'form_id' => (int)$form['id'],
                'form_key' => $definition['key'],
                'value' => $this->json($definition['value']),
                'version' => 1,
                'status' => 1,
                'created_at' => self::CREATED_AT,
                'updated_at' => self::CREATED_AT,
            ];
        }
        $this->insertMissingRows('config', 'form_key', $values);
    }

    private function configDefinitions(): array
    {
        return [
            [
                'form_id' => 1,
                'config_id' => 1,
                'key' => 'site',
                'title' => '站点配置',
                'group_key' => 'base',
                'group_title' => '基础配置',
                'sort' => 1,
                'remark' => '系统站点基础配置',
                'schema' => [
                    'labelWidth' => '120px',
                    'labelPosition' => 'left',
                    'formItems' => [
                        $this->switchItem('admin_captcha_switch', '登录验证码', false, '开启后后台登录需要完成验证码校验'),
                    ],
                ],
                'value' => [
                    'admin_captcha_switch' => false,
                ],
            ],
            [
                'form_id' => 2,
                'config_id' => 2,
                'key' => 'uploader_local',
                'title' => '本地上传',
                'group_key' => 'upload',
                'group_title' => '上传配置',
                'sort' => 10,
                'remark' => '本地磁盘存储上传参数',
                'schema' => [
                    'labelWidth' => '160px',
                    'labelPosition' => 'left',
                    'formItems' => [
                        $this->switchItem('enabled', '启用', true, '开启后作为默认上传器；多个启用时按排序取第一个'),
                        $this->inputItem('domain', '访问域名', '', '如 https://cdn.xx.com，留空则返回相对路径'),
                        $this->inputItem('root', '存储子目录', '', '如填写 app，则文件保存到 public/app/uploads 下'),
                        $this->numberItem('max_size', '大小限制(MB)', 10, '单文件最大体积，0 表示不限制'),
                        $this->inputItem('allow_ext', '允许的扩展名', '', '逗号分隔，如 jpg,png,pdf；留空不限制'),
                    ],
                ],
                'value' => [
                    'enabled' => true,
                    'domain' => '',
                    'root' => '',
                    'max_size' => 10,
                    'allow_ext' => '',
                ],
            ],
            [
                'form_id' => 3,
                'config_id' => 3,
                'key' => 'uploader_s3',
                'title' => 'S3上传',
                'group_key' => 'upload',
                'group_title' => '上传配置',
                'sort' => 20,
                'remark' => 'S3及兼容对象存储上传参数',
                'schema' => [
                    'labelWidth' => '160px',
                    'labelPosition' => 'left',
                    'formItems' => [
                        $this->switchItem('enabled', '启用', false, '开启后作为默认上传器；多个启用时按排序取第一个'),
                        $this->inputItem('access_key', 'Access Key', '', '留空时使用服务端默认凭证链'),
                        $this->inputItem('secret_key', 'Secret Key', '', '与Access Key需同时填写'),
                        $this->inputItem('session_token', 'Session Token', '', '临时凭证可填写'),
                        $this->inputItem('region', 'Region', 'us-east-1', 'AWS区域或兼容服务区域', [['required' => true, 'message' => '请输入Region']]),
                        $this->inputItem('bucket', 'Bucket', '', '对象存储桶名称', [['required' => true, 'message' => '请输入Bucket']]),
                        $this->inputItem('endpoint', 'Endpoint', '', 'S3兼容服务地址，AWS官方S3可留空'),
                        $this->inputItem('custom_domain', '访问域名', '', 'CDN或自定义域名，留空时按endpoint或AWS默认域名生成'),
                        $this->inputItem('root', '存储前缀', '', '如填写 app，则文件保存到 app/uploads 目录下'),
                        $this->switchItem('use_path_style_endpoint', '路径风格Endpoint', false, 'MinIO等服务通常需要开启'),
                        $this->inputItem('acl', 'ACL', '', '如public-read；留空则不设置ACL'),
                        $this->numberItem('max_size', '大小限制(MB)', 10, '单文件最大体积，0 表示不限制'),
                        $this->inputItem('allow_ext', '允许的扩展名', '', '逗号分隔，如 jpg,png,pdf；留空不限制'),
                    ],
                ],
                'value' => [
                    'enabled' => false,
                    'access_key' => '',
                    'secret_key' => '',
                    'session_token' => '',
                    'region' => 'us-east-1',
                    'bucket' => '',
                    'endpoint' => '',
                    'custom_domain' => '',
                    'root' => '',
                    'use_path_style_endpoint' => false,
                    'acl' => '',
                    'max_size' => 10,
                    'allow_ext' => '',
                ],
            ],
        ];
    }

    private function menu(
        int $id,
        int $pid,
        string $name,
        string $title,
        string $path,
        string $component,
        int $sort,
        string $icon,
        array $meta = []
    ): array {
        return [
            'id' => $id,
            'app_id' => self::APP_ID,
            'pid' => $pid,
            'name' => $name,
            'title' => $title,
            'type' => 'menu',
            'path' => $path,
            'component' => $component,
            'sort' => $sort,
            'meta' => $this->meta($title, $icon, $meta),
        ];
    }

    private function rule(int $id, string $parent, string $name, string $title, int $sort, string $tag, array $paths): array
    {
        return compact('id', 'parent', 'name', 'title', 'sort', 'tag', 'paths');
    }

    private function meta(string $title, string $icon, array $extra = []): string
    {
        return $this->json(array_merge([
            'tag' => '',
            'icon' => $icon,
            'type' => 'menu',
            'color' => '',
            'title' => $title,
            'active' => '',
            'hidden' => false,
            'fullPage' => false,
            'hiddenBreadcrumb' => false,
            'affix' => false,
        ], $extra));
    }

    private function inputItem(string $name, string $label, mixed $value, string $message, array $rules = []): array
    {
        return [
            'name' => $name,
            'label' => $label,
            'component' => 'input',
            'value' => $value,
            'span' => 24,
            'options' => [],
            'rules' => $rules,
            'message' => $message,
        ];
    }

    private function switchItem(string $name, string $label, bool $value, string $message): array
    {
        return [
            'name' => $name,
            'label' => $label,
            'component' => 'switch',
            'value' => $value,
            'span' => 24,
            'options' => [],
            'rules' => [],
            'message' => $message,
        ];
    }

    private function numberItem(string $name, string $label, int $value, string $message): array
    {
        return [
            'name' => $name,
            'label' => $label,
            'component' => 'number',
            'value' => $value,
            'span' => 24,
            'options' => [],
            'rules' => [],
            'message' => $message,
        ];
    }

    private function insertMissingRows(string $table, string $uniqueColumn, array $rows): void
    {
        $missingRows = [];
        foreach ($rows as $row) {
            if (!$this->fetchRowBy($table, $uniqueColumn, $row[$uniqueColumn])) {
                $missingRows[] = $row;
            }
        }

        if ($missingRows !== []) {
            $this->table($table)->insert($missingRows)->saveData();
        }
    }

    private function insertMissingMenuApis(): void
    {
        $missingRows = [];
        foreach ($this->menuApis() as $row) {
            if (!$this->existsMenuApi($row['path'], $row['tag'])) {
                $missingRows[] = $row;
            }
        }

        if ($missingRows !== []) {
            $this->table('menu_api')->insert($missingRows)->saveData();
        }
    }

    private function existsMenuApi(string $path, string $tag): bool
    {
        $where = $this->eq('path', $path) . ' AND ' . $this->eq('tag', $tag);
        $sql = sprintf('SELECT 1 FROM %s WHERE %s LIMIT 1', $this->quoteTable('menu_api'), $where);
        return (bool)$this->fetchRow($sql);
    }

    private function existsUserRole(int $userId, int $roleId): bool
    {
        $where = $this->eq('user_id', $userId) . ' AND ' . $this->eq('role_id', $roleId);
        $sql = sprintf('SELECT 1 FROM %s WHERE %s LIMIT 1', $this->quoteTable('user_role'), $where);
        return (bool)$this->fetchRow($sql);
    }

    private function fetchRowBy(string $table, string $column, mixed $value): array|false
    {
        $sql = sprintf(
            'SELECT * FROM %s WHERE %s = %s LIMIT 1',
            $this->quoteTable($table),
            $this->quoteColumn($column),
            $this->quoteValue($value)
        );

        return $this->fetchRow($sql);
    }

    private function deleteIn(string $table, string $column, array $values): void
    {
        if ($values === []) {
            return;
        }

        $items = implode(', ', array_map(fn (mixed $value): string => $this->quoteValue($value), $values));
        $this->deleteWhere($table, sprintf('%s IN (%s)', $this->quoteColumn($column), $items));
    }

    private function deleteWhere(string $table, string $where): void
    {
        $this->execute(sprintf('DELETE FROM %s WHERE %s', $this->quoteTable($table), $where));
    }

    private function eq(string $column, mixed $value): string
    {
        return sprintf('%s = %s', $this->quoteColumn($column), $this->quoteValue($value));
    }

    private function quoteTable(string $table): string
    {
        return $this->getAdapter()->quoteTableName($table);
    }

    private function quoteColumn(string $column): string
    {
        return $this->getAdapter()->quoteColumnName($column);
    }

    private function quoteValue(mixed $value): string
    {
        if ($value === null) {
            return 'NULL';
        }

        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        if (is_int($value) || is_float($value)) {
            return (string)$value;
        }

        return $this->getAdapter()->getConnection()->quote((string)$value);
    }

    private function json(array $data): string
    {
        return json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    private function menuApiTags(): array
    {
        return array_column($this->menuApis(), 'tag');
    }

    private function configKeys(): array
    {
        return array_column($this->configDefinitions(), 'key');
    }
}
