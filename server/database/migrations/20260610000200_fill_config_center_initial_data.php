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
        if ($siteForm && !$this->fetchRow("select `id` from `config` where `form_key` = 'site' limit 1")) {
            $this->table('config')->insert([
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

        $this->insertUploaderConfigs($now);
        $this->insertMenus();
    }

    public function down(): void
    {
        $this->execute("delete from `menu_api` where `tag` like 'admin.system.config%'");
        $this->execute("delete from `menu` where `name` in ('config', 'config_form', 'config_list', 'config_detail', 'config_save', 'config_form_list', 'config_form_add', 'config_form_edit', 'config_form_delete')");
        $this->execute("delete from `config` where `form_key` in ('site', 'uploader_local', 'uploader_s3')");
        $this->execute("delete from `config_form` where `key` in ('site', 'uploader_local', 'uploader_s3')");
    }

    private function insertUploaderConfigs(string $now): void
    {
        $forms = [
            [
                'key' => 'uploader_local',
                'title' => '本地上传',
                'sort' => 10,
                'remark' => '本地磁盘存储上传参数',
                'schema' => $this->localConfigSchema(),
                'value' => $this->localDefaultValue(),
            ],
            [
                'key' => 'uploader_s3',
                'title' => 'S3上传',
                'sort' => 20,
                'remark' => 'S3及兼容对象存储上传参数',
                'schema' => $this->s3ConfigSchema(),
                'value' => $this->s3DefaultValue(),
            ],
        ];

        foreach ($forms as $item) {
            if (!$this->fetchRow("select `id` from `config_form` where `key` = '{$item['key']}' limit 1")) {
                $this->table('config_form')->insert([
                    [
                        'key' => $item['key'],
                        'title' => $item['title'],
                        'group_key' => 'upload',
                        'group_title' => '上传配置',
                        'schema' => json_encode($item['schema'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                        'status' => 1,
                        'sort' => $item['sort'],
                        'remark' => $item['remark'],
                        'created_at' => $now,
                        'updated_at' => $now,
                        'deleted_at' => null,
                    ],
                ])->saveData();
            }

            $form = $this->fetchRow("select `id`, `key` from `config_form` where `key` = '{$item['key']}' limit 1");
            if ($form && !$this->fetchRow("select `id` from `config` where `form_key` = '{$item['key']}' limit 1")) {
                $this->table('config')->insert([
                    [
                        'form_id' => $form['id'],
                        'form_key' => $form['key'],
                        'value' => json_encode($item['value'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                        'version' => 1,
                        'status' => 1,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ],
                ])->saveData();
            }
        }
    }

    private function localDefaultValue(): array
    {
        return [
            'enabled' => true,
            'domain' => '',
            'root' => '',
            'max_size' => 10,
            'allow_ext' => '',
        ];
    }

    private function s3DefaultValue(): array
    {
        return [
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
        ];
    }

    private function localConfigSchema(): array
    {
        return [
            'labelWidth' => '160px',
            'labelPosition' => 'left',
            'formItems' => [
                $this->switchItem('enabled', '启用', false, '开启后作为默认上传器；多个启用时按排序取第一个'),
                $this->inputItem('domain', '访问域名', '', '如 https://cdn.xx.com，留空则返回相对路径'),
                $this->inputItem('root', '存储子目录', '', '如填写 app，则文件保存到 public/app/uploads 下'),
                $this->numberItem('max_size', '大小限制(MB)', 10, '单文件最大体积，0 表示不限制'),
                $this->inputItem('allow_ext', '允许的扩展名', '', '逗号分隔，如 jpg,png,pdf；留空不限制'),
            ],
        ];
    }

    private function s3ConfigSchema(): array
    {
        return [
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
        ];
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
