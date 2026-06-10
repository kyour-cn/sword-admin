<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class FillConfigCenterInitialData extends AbstractMigration
{
    public function up(): void
    {
        $now = '2026-06-10 00:00:00';

        if (!$this->fetchRow("select `id` from `config_form` where `key` = 'site' limit 1")) {
            $schema = [
                'labelWidth' => '120px',
                'labelPosition' => 'left',
                'formItems' => [
                    [
                        'name' => 'admin_captcha_switch',
                        'label' => '登录验证码',
                        'component' => 'switch',
                        'value' => false,
                        'span' => 24,
                        'message' => '开启后后台登录需要完成验证码校验',
                    ],
                ],
            ];

            $this->table('config_form')->insert([
                [
                    'key' => 'site',
                    'title' => '站点配置',
                    'group_key' => 'base',
                    'group_title' => '基础配置',
                    'schema' => json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                    'status' => 1,
                    'sort' => 1,
                    'remark' => '系统站点基础配置',
                    'created_at' => $now,
                    'updated_at' => $now,
                    'deleted_at' => null,
                ],
            ])->saveData();
        }

        $siteForm = $this->fetchRow("select `id`, `key` from `config_form` where `key` = 'site' limit 1");
        if ($siteForm && !$this->fetchRow("select `id` from `config_value` where `form_key` = 'site' limit 1")) {
            $this->table('config_value')->insert([
                [
                    'form_id' => $siteForm['id'],
                    'form_key' => $siteForm['key'],
                    'value' => json_encode(['admin_captcha_switch' => false], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                    'version' => 1,
                    'status' => 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
            ])->saveData();
        }

        $this->insertMenus();
    }

    public function down(): void
    {
        $this->execute("delete from `menu_api` where `tag` like 'admin.system.config%'");
        $this->execute("delete from `menu` where `name` in ('config', 'config_form', 'config_list', 'config_detail', 'config_save', 'config_form_list', 'config_form_add', 'config_form_edit', 'config_form_delete')");
        $this->execute("delete from `config_value` where `form_key` = 'site'");
        $this->execute("delete from `config_form` where `key` = 'site'");
    }

    private function insertMenus(): void
    {
        if ($this->fetchRow("select `id` from `menu` where `name` = 'config' limit 1")) {
            return;
        }

        $menus = [
            ['app_id' => 1, 'pid' => 3, 'name' => 'config', 'title' => '系统配置', 'type' => 'menu', 'path' => '/admin/system/config', 'component' => 'admin/system/config', 'sort' => 5, 'meta' => $this->meta('系统配置', 'el-icon-setting')],
            ['app_id' => 1, 'pid' => 3, 'name' => 'config_form', 'title' => '配置表单管理', 'type' => 'menu', 'path' => '/admin/system/config-form', 'component' => 'admin/system/config-form', 'sort' => 6, 'meta' => $this->meta('配置表单管理', 'el-icon-document-copy')],
        ];

        $this->table('menu')->insert($menus)->saveData();

        $parentIds = [
            'config' => $this->fetchRow("select `id` from `menu` where `name` = 'config' limit 1")['id'],
            'config_form' => $this->fetchRow("select `id` from `menu` where `name` = 'config_form' limit 1")['id'],
        ];

        $ruleMenus = [];

        foreach ($this->rules() as $rule) {
            $ruleMenus[] = [
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

        $this->table('menu')->insert($ruleMenus)->saveData();

        $apis = [];
        foreach ($this->rules() as $rule) {
            $menu = $this->fetchRow("select `id` from `menu` where `name` = '{$rule['name']}' limit 1");
            foreach ($rule['apis'] as $api) {
                $apis[] = ['app_id' => 1, 'menu_id' => $menu['id'], 'path' => $api['path'], 'tag' => $api['tag']];
            }
        }

        $this->table('menu_api')->insert($apis)->saveData();
    }

    private function rules(): array
    {
        return [
            ['parent' => 'config', 'name' => 'config_list', 'title' => '查询系统配置', 'sort' => 1, 'tag' => 'admin.system.config.list', 'apis' => [['path' => '/admin/system/config/list', 'tag' => 'admin.system.config.list']]],
            ['parent' => 'config', 'name' => 'config_detail', 'title' => '配置详情', 'sort' => 2, 'tag' => 'admin.system.config.detail', 'apis' => [['path' => '/admin/system/config/detail', 'tag' => 'admin.system.config.detail']]],
            ['parent' => 'config', 'name' => 'config_save', 'title' => '保存系统配置', 'sort' => 3, 'tag' => 'admin.system.config.save', 'apis' => [['path' => '/admin/system/config/save', 'tag' => 'admin.system.config.save']]],
            ['parent' => 'config_form', 'name' => 'config_form_list', 'title' => '查询配置表单', 'sort' => 1, 'tag' => 'admin.system.configForm.list', 'apis' => [['path' => '/admin/system/configForm/list', 'tag' => 'admin.system.configForm.list']]],
            ['parent' => 'config_form', 'name' => 'config_form_add', 'title' => '新增配置表单', 'sort' => 2, 'tag' => 'admin.system.configForm.add', 'apis' => [['path' => '/admin/system/configForm/add', 'tag' => 'admin.system.configForm.add']]],
            ['parent' => 'config_form', 'name' => 'config_form_edit', 'title' => '编辑配置表单', 'sort' => 3, 'tag' => 'admin.system.configForm.edit', 'apis' => [['path' => '/admin/system/configForm/edit', 'tag' => 'admin.system.configForm.edit']]],
            ['parent' => 'config_form', 'name' => 'config_form_delete', 'title' => '删除配置表单', 'sort' => 4, 'tag' => 'admin.system.configForm.delete', 'apis' => [['path' => '/admin/system/configForm/delete', 'tag' => 'admin.system.configForm.delete']]],
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
