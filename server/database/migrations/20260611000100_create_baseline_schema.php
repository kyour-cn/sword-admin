<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateBaselineSchema extends AbstractMigration
{
    public function up(): void
    {
        $this->createAppTable();
        $this->createFileMenuTable();
        $this->createFileTable();
        $this->createAuditLogTable();
        $this->createMenuTable();
        $this->createMenuApiTable();
        $this->createRoleTable();
        $this->createTaskTable();
        $this->createUserTable();
        $this->createUserRoleTable();
        $this->createConfigFormTable();
        $this->createConfigTable();
    }

    public function down(): void
    {
        foreach ($this->dropOrder() as $table) {
            if ($this->hasTable($table)) {
                $this->table($table)->drop()->save();
            }
        }
    }

    private function createAppTable(): void
    {
        $this->table('app', $this->tableOptions('应用列表'))
            ->addColumn('id', 'integer', $this->idOptions())
            ->addColumn('name', 'string', ['limit' => 255, 'default' => '', 'null' => false, 'comment' => '应用名称'])
            ->addColumn('key', 'string', ['limit' => 255, 'null' => false, 'comment' => '应用KEY 别名'])
            ->addColumn('remark', 'string', ['limit' => 255, 'default' => '', 'null' => false, 'comment' => '备注'])
            ->addColumn('status', 'tinyinteger', ['default' => 0, 'null' => false, 'comment' => '状态'])
            ->addColumn('sort', 'integer', ['default' => 0, 'null' => false, 'comment' => '排序 ASC'])
            ->addIndex(['key'], ['unique' => true, 'name' => 'app_key_unique'])
            ->addIndex(['status', 'sort'], ['name' => 'app_status_sort_index'])
            ->create();
    }

    private function createFileMenuTable(): void
    {
        $this->table('file_menu', $this->tableOptions('文件分组'))
            ->addColumn('id', 'integer', $this->idOptions())
            ->addColumn('name', 'string', ['limit' => 255, 'null' => false, 'comment' => '名称'])
            ->create();
    }

    private function createFileTable(): void
    {
        $this->table('file', $this->tableOptions('文件'))
            ->addColumn('id', 'integer', $this->idOptions())
            ->addColumn('file_name', 'string', ['limit' => 255, 'null' => false, 'comment' => '文件名'])
            ->addColumn('file_type', 'string', ['limit' => 255, 'null' => false, 'comment' => '文件类型（MIME类型，如 image/png）'])
            ->addColumn('file_ext', 'string', ['limit' => 20, 'null' => false, 'comment' => '文件后缀（如 .jpg/.pdf）文件后缀'])
            ->addColumn('file_size', 'biginteger', ['signed' => false, 'null' => false, 'comment' => '文件大小（字节）'])
            ->addColumn('url', 'string', ['limit' => 255, 'null' => false, 'comment' => '链接地址'])
            ->addColumn('file_path', 'string', ['limit' => 1024, 'null' => false, 'comment' => '存储路径'])
            ->addColumn('menu_id', 'integer', ['signed' => false, 'default' => 0, 'null' => false, 'comment' => '文件分组ID，0表示未分组'])
            ->addColumn('storage_key', 'string', ['limit' => 20, 'null' => false, 'comment' => '储存方式key'])
            ->addColumn('hash_md5', 'string', ['limit' => 32, 'default' => '', 'null' => false, 'comment' => '文件内容的MD5'])
            ->addColumn('user_id', 'integer', ['signed' => false, 'default' => 0, 'null' => false, 'comment' => '上传用户id'])
            ->addColumn('status', 'tinyinteger', ['default' => 1, 'null' => false, 'comment' => '状态 1=正常 0=停用'])
            ->addColumn('created_at', 'datetime', ['null' => false, 'comment' => '创建时间'])
            ->addColumn('updated_at', 'datetime', ['null' => false, 'comment' => '更新时间'])
            ->addColumn('deleted_at', 'datetime', ['null' => true, 'comment' => '删除时间'])
            ->addIndex(['menu_id'], ['name' => 'file_menu_id_index'])
            ->addIndex(['user_id'], ['name' => 'file_user_id_index'])
            ->addIndex(['hash_md5'], ['name' => 'file_hash_md5_index'])
            ->addIndex(['status', 'created_at'], ['name' => 'file_status_created_at_index'])
            ->create();
    }

    private function createAuditLogTable(): void
    {
        $this->table('audit_log', $this->tableOptions('操作审计日志'))
            ->addColumn('id', 'integer', $this->idOptions())
            ->addColumn('app_id', 'integer', ['signed' => false, 'default' => 0, 'null' => false, 'comment' => '应用ID 0为未知'])
            ->addColumn('actor_id', 'integer', ['signed' => false, 'default' => 0, 'null' => false, 'comment' => '操作人ID 0为匿名或系统'])
            ->addColumn('actor_name', 'string', ['limit' => 64, 'default' => '', 'null' => false, 'comment' => '操作人名称'])
            ->addColumn('action', 'string', ['limit' => 64, 'default' => '', 'null' => false, 'comment' => '操作动作'])
            ->addColumn('module', 'string', ['limit' => 64, 'default' => '', 'null' => false, 'comment' => '业务模块标识'])
            ->addColumn('module_title', 'string', ['limit' => 64, 'default' => '', 'null' => false, 'comment' => '业务模块名称快照'])
            ->addColumn('resource_type', 'string', ['limit' => 64, 'default' => '', 'null' => false, 'comment' => '资源类型'])
            ->addColumn('resource_id', 'string', ['limit' => 64, 'default' => '', 'null' => false, 'comment' => '资源ID'])
            ->addColumn('title', 'string', ['limit' => 255, 'default' => '', 'null' => false, 'comment' => '审计标题'])
            ->addColumn('description', 'string', ['limit' => 500, 'default' => '', 'null' => false, 'comment' => '操作摘要'])
            ->addColumn('method', 'string', ['limit' => 10, 'default' => '', 'null' => false, 'comment' => 'HTTP方法'])
            ->addColumn('path', 'string', ['limit' => 255, 'default' => '', 'null' => false, 'comment' => '请求路径'])
            ->addColumn('ip', 'string', ['limit' => 64, 'default' => '', 'null' => false, 'comment' => '请求IP'])
            ->addColumn('user_agent', 'string', ['limit' => 500, 'default' => '', 'null' => false, 'comment' => '客户端信息'])
            ->addColumn('status', 'tinyinteger', ['signed' => false, 'default' => 1, 'null' => false, 'comment' => '结果状态 1=成功 0=失败'])
            ->addColumn('context', 'json', ['null' => true, 'comment' => '结构化补充信息'])
            ->addColumn('created_at', 'datetime', ['null' => false, 'comment' => '创建时间'])
            ->addIndex(['created_at'], ['name' => 'audit_log_created_at_index'])
            ->addIndex(['actor_id', 'created_at'], ['name' => 'audit_log_actor_created_at_index'])
            ->addIndex(['module', 'action', 'created_at'], ['name' => 'audit_log_module_action_created_at_index'])
            ->addIndex(['resource_type', 'resource_id'], ['name' => 'audit_log_resource_index'])
            ->addIndex(['status', 'created_at'], ['name' => 'audit_log_status_created_at_index'])
            ->create();
    }

    private function createMenuTable(): void
    {
        $this->table('menu', $this->tableOptions('菜单'))
            ->addColumn('id', 'integer', $this->idOptions())
            ->addColumn('app_id', 'integer', ['signed' => false, 'null' => false, 'comment' => '应用ID'])
            ->addColumn('pid', 'integer', ['signed' => false, 'default' => 0, 'null' => false, 'comment' => '上级ID'])
            ->addColumn('name', 'string', ['limit' => 255, 'default' => '', 'null' => false, 'comment' => '别名'])
            ->addColumn('title', 'string', ['limit' => 255, 'default' => '', 'null' => false, 'comment' => '显示名称'])
            ->addColumn('type', 'string', ['limit' => 12, 'default' => '', 'null' => false, 'comment' => '类型'])
            ->addColumn('path', 'string', ['limit' => 255, 'default' => '', 'null' => false, 'comment' => '路由地址'])
            ->addColumn('component', 'string', ['limit' => 255, 'default' => '', 'null' => false, 'comment' => '组件地址'])
            ->addColumn('sort', 'integer', ['signed' => false, 'default' => 0, 'null' => false, 'comment' => '排序'])
            ->addColumn('meta', 'json', ['null' => true, 'comment' => 'meta路由参数'])
            ->addForeignKey('app_id', 'app', 'id', [
                'constraint' => 'menu_app_id_fk',
                'delete' => 'RESTRICT',
                'update' => 'CASCADE',
            ])
            ->addIndex(['app_id', 'pid', 'sort'], ['name' => 'menu_app_pid_sort_index'])
            ->addIndex(['type'], ['name' => 'menu_type_index'])
            ->create();
    }

    private function createMenuApiTable(): void
    {
        $this->table('menu_api', $this->tableOptions('菜单权限接口'))
            ->addColumn('id', 'integer', $this->idOptions())
            ->addColumn('app_id', 'integer', ['signed' => false, 'null' => false, 'comment' => '应用ID'])
            ->addColumn('menu_id', 'integer', ['signed' => false, 'null' => false, 'comment' => '菜单ID'])
            ->addColumn('path', 'string', ['limit' => 255, 'default' => '', 'null' => false, 'comment' => 'API路由地址'])
            ->addColumn('tag', 'string', ['limit' => 255, 'default' => '', 'null' => false, 'comment' => '标识'])
            ->addForeignKey('app_id', 'app', 'id', [
                'constraint' => 'menu_api_app_id_fk',
                'delete' => 'RESTRICT',
                'update' => 'CASCADE',
            ])
            ->addForeignKey('menu_id', 'menu', 'id', [
                'constraint' => 'menu_api_menu_id_fk',
                'delete' => 'CASCADE',
                'update' => 'CASCADE',
            ])
            ->addIndex(['app_id', 'path'], ['name' => 'menu_api_app_path_index'])
            ->addIndex(['menu_id'], ['name' => 'menu_api_menu_id_index'])
            ->addIndex(['tag'], ['name' => 'menu_api_tag_index'])
            ->create();
    }

    private function createRoleTable(): void
    {
        $this->table('role', $this->tableOptions('用户角色'))
            ->addColumn('id', 'integer', $this->idOptions())
            ->addColumn('app_id', 'integer', ['signed' => false, 'null' => false, 'comment' => '应用ID'])
            ->addColumn('name', 'string', ['limit' => 12, 'default' => '', 'null' => false, 'comment' => '角色名称'])
            ->addColumn('rules', 'string', ['limit' => 1000, 'default' => '', 'null' => false, 'comment' => '权限ID ,分割'])
            ->addColumn('rules_checked', 'string', ['limit' => 1000, 'default' => '', 'null' => false, 'comment' => '权限树选中的字节点ID'])
            ->addColumn('remark', 'string', ['limit' => 255, 'default' => '', 'null' => false, 'comment' => '简介'])
            ->addColumn('status', 'tinyinteger', ['signed' => false, 'default' => 0, 'null' => false, 'comment' => '状态'])
            ->addColumn('sort', 'integer', ['signed' => false, 'default' => 0, 'null' => false, 'comment' => '排序'])
            ->addColumn('is_admin', 'tinyinteger', ['signed' => false, 'default' => 0, 'null' => false, 'comment' => '是否为管理员（所有权限）'])
            ->addColumn('created_at', 'datetime', ['null' => false, 'comment' => '创建时间'])
            ->addColumn('updated_at', 'datetime', ['null' => false, 'comment' => '更新时间'])
            ->addColumn('deleted_at', 'datetime', ['null' => true, 'comment' => '删除时间'])
            ->addForeignKey('app_id', 'app', 'id', [
                'constraint' => 'role_app_id_fk',
                'delete' => 'RESTRICT',
                'update' => 'CASCADE',
            ])
            ->addIndex(['app_id', 'sort'], ['name' => 'role_app_sort_index'])
            ->addIndex(['status'], ['name' => 'role_status_index'])
            ->create();
    }

    private function createTaskTable(): void
    {
        $this->table('task', $this->tableOptions('任务'))
            ->addColumn('id', 'integer', $this->idOptions())
            ->addColumn('title', 'string', ['limit' => 255, 'default' => '', 'null' => false, 'comment' => '任务标题'])
            ->addColumn('group', 'string', ['limit' => 20, 'default' => 'system', 'null' => false, 'comment' => '分组'])
            ->addColumn('user_id', 'integer', ['signed' => false, 'default' => 0, 'null' => false, 'comment' => '关联用户ID'])
            ->addColumn('type', 'string', ['limit' => 255, 'null' => false, 'comment' => '任务类型'])
            ->addColumn('label', 'string', ['limit' => 255, 'default' => '', 'null' => false, 'comment' => '任务标识，用于区分业务'])
            ->addColumn('content', 'text', ['null' => true, 'comment' => '任务内容'])
            ->addColumn('result', 'text', ['null' => true, 'comment' => '任务结果'])
            ->addColumn('status', 'tinyinteger', ['default' => 0, 'null' => false, 'comment' => '状态 0=待处理 1=处理中 2=已完成 -1=失败'])
            ->addColumn('created_at', 'datetime', ['null' => false, 'comment' => '创建时间'])
            ->addColumn('updated_at', 'datetime', ['null' => false, 'comment' => '更新时间'])
            ->addColumn('deleted_at', 'datetime', ['null' => true, 'comment' => '删除时间'])
            ->addIndex(['status', 'created_at'], ['name' => 'task_status_created_at_index'])
            ->addIndex(['user_id'], ['name' => 'task_user_id_index'])
            ->addIndex(['group'], ['name' => 'task_group_index'])
            ->create();
    }

    private function createUserTable(): void
    {
        $this->table('user', $this->tableOptions('用户表'))
            ->addColumn('id', 'integer', $this->idOptions())
            ->addColumn('nickname', 'string', ['limit' => 32, 'default' => '', 'null' => false, 'comment' => '昵称'])
            ->addColumn('username', 'string', ['limit' => 32, 'default' => '', 'null' => false, 'comment' => '用户名(登录账号)'])
            ->addColumn('mobile', 'string', ['limit' => 15, 'default' => '', 'null' => false, 'comment' => '手机号'])
            ->addColumn('avatar', 'string', ['limit' => 255, 'default' => '', 'null' => false, 'comment' => '头像'])
            ->addColumn('password', 'string', ['limit' => 32, 'default' => '', 'null' => false, 'comment' => '密码 md5'])
            ->addColumn('status', 'tinyinteger', ['default' => 0, 'null' => false, 'comment' => '状态'])
            ->addColumn('login_time', 'datetime', ['null' => true, 'comment' => '登录时间'])
            ->addColumn('created_at', 'datetime', ['null' => false, 'comment' => '创建时间'])
            ->addColumn('updated_at', 'datetime', ['null' => false, 'comment' => '更新时间'])
            ->addColumn('deleted_at', 'datetime', ['null' => true, 'comment' => '删除时间'])
            ->addIndex(['username'], ['unique' => true, 'name' => 'user_username_unique'])
            ->addIndex(['status'], ['name' => 'user_status_index'])
            ->create();
    }

    private function createUserRoleTable(): void
    {
        $this->table('user_role', $this->tableOptions('用户角色关联'))
            ->addColumn('id', 'integer', $this->idOptions())
            ->addColumn('user_id', 'integer', ['signed' => false, 'null' => false, 'comment' => '用户ID'])
            ->addColumn('role_id', 'integer', ['signed' => false, 'null' => false, 'comment' => '角色ID'])
            ->addColumn('created_at', 'datetime', ['null' => false, 'comment' => '创建时间'])
            ->addColumn('deleted_at', 'datetime', ['null' => true, 'comment' => '删除时间'])
            ->addForeignKey('user_id', 'user', 'id', [
                'constraint' => 'user_role_user_id_fk',
                'delete' => 'CASCADE',
                'update' => 'CASCADE',
            ])
            ->addForeignKey('role_id', 'role', 'id', [
                'constraint' => 'user_role_role_id_fk',
                'delete' => 'CASCADE',
                'update' => 'CASCADE',
            ])
            ->addIndex(['user_id'], ['name' => 'user_role_user_id_index'])
            ->addIndex(['role_id'], ['name' => 'user_role_role_id_index'])
            ->create();
    }

    private function createConfigFormTable(): void
    {
        $this->table('config_form', $this->tableOptions('配置表单'))
            ->addColumn('id', 'integer', $this->idOptions())
            ->addColumn('key', 'string', ['limit' => 50, 'default' => '', 'null' => false, 'comment' => '表单唯一标识'])
            ->addColumn('title', 'string', ['limit' => 50, 'default' => '', 'null' => false, 'comment' => '表单名称'])
            ->addColumn('group_key', 'string', ['limit' => 30, 'default' => '', 'null' => false, 'comment' => '分组标识'])
            ->addColumn('group_title', 'string', ['limit' => 50, 'default' => '', 'null' => false, 'comment' => '分组名称'])
            ->addColumn('schema', 'json', ['null' => true, 'comment' => '表单结构'])
            ->addColumn('status', 'tinyinteger', ['default' => 1, 'null' => false, 'comment' => '状态 1=启用 0=停用'])
            ->addColumn('sort', 'integer', ['default' => 0, 'null' => false, 'comment' => '排序'])
            ->addColumn('remark', 'string', ['limit' => 255, 'default' => '', 'null' => false, 'comment' => '备注'])
            ->addColumn('created_at', 'datetime', ['null' => false, 'comment' => '创建时间'])
            ->addColumn('updated_at', 'datetime', ['null' => false, 'comment' => '更新时间'])
            ->addColumn('deleted_at', 'datetime', ['null' => true, 'comment' => '删除时间'])
            ->addIndex(['key'], ['unique' => true, 'name' => 'config_form_key_unique'])
            ->addIndex(['group_key'], ['name' => 'config_form_group_key_index'])
            ->addIndex(['status', 'sort'], ['name' => 'config_form_status_sort_index'])
            ->create();
    }

    private function createConfigTable(): void
    {
        $this->table('config', $this->tableOptions('配置值'))
            ->addColumn('id', 'integer', $this->idOptions())
            ->addColumn('form_id', 'integer', ['signed' => false, 'null' => false, 'comment' => '配置表单ID'])
            ->addColumn('form_key', 'string', ['limit' => 50, 'default' => '', 'null' => false, 'comment' => '表单唯一标识'])
            ->addColumn('value', 'json', ['null' => true, 'comment' => '配置值'])
            ->addColumn('version', 'integer', ['default' => 1, 'null' => false, 'comment' => '版本号'])
            ->addColumn('status', 'tinyinteger', ['default' => 1, 'null' => false, 'comment' => '状态 1=生效 0=停用'])
            ->addColumn('created_at', 'datetime', ['null' => false, 'comment' => '创建时间'])
            ->addColumn('updated_at', 'datetime', ['null' => false, 'comment' => '更新时间'])
            ->addForeignKey('form_id', 'config_form', 'id', [
                'constraint' => 'config_form_id_fk',
                'delete' => 'CASCADE',
                'update' => 'CASCADE',
            ])
            ->addIndex(['form_key'], ['unique' => true, 'name' => 'config_value_form_key_unique'])
            ->addIndex(['form_id'], ['name' => 'config_value_form_id_index'])
            ->create();
    }

    private function tableOptions(string $comment): array
    {
        $options = [
            'id' => false,
            'primary_key' => ['id'],
            'comment' => $comment,
        ];

        if ($this->getAdapter()->getAdapterType() === 'mysql') {
            $options['engine'] = 'InnoDB';
            $options['row_format'] = 'DYNAMIC';
        }

        return $options;
    }

    private function idOptions(array $extra = []): array
    {
        return array_merge([
            'identity' => true,
            'signed' => false,
            'null' => false,
        ], $extra);
    }

    private function dropOrder(): array
    {
        return [
            'config',
            'config_form',
            'user_role',
            'user',
            'task',
            'role',
            'menu_api',
            'menu',
            'audit_log',
            'file',
            'file_menu',
            'app',
        ];
    }
}
