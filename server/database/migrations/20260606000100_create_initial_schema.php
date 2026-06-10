<?php

declare(strict_types=1);

use Phinx\Db\Adapter\MysqlAdapter;
use Phinx\Migration\AbstractMigration;

final class CreateInitialSchema extends AbstractMigration
{
    public function up(): void
    {
        $this->createAppTable();
        $this->createFileMenuTable();
        $this->createFileTable();
        $this->createLogTypeTable();
        $this->createLogTable();
        $this->createMenuTable();
        $this->createMenuApiTable();
        $this->createRoleTable();
        $this->createTaskTable();
        $this->createUserTable();
        $this->createUserRoleTable();
    }

    public function down(): void
    {
        $this->execute('set foreign_key_checks = 0');

        foreach ([
            'user_role',
            'user',
            'task',
            'role',
            'menu_api',
            'menu',
            'log',
            'log_type',
            'file',
            'file_menu',
            'app',
        ] as $table) {
            if ($this->hasTable($table)) {
                $this->table($table)->drop()->save();
            }
        }

        $this->execute('set foreign_key_checks = 1');
    }

    private function createAppTable(): void
    {
        $this->table('app', $this->tableOptions('应用列表'))
            ->addColumn('id', 'integer', $this->idOptions())
            ->addColumn('name', 'string', ['limit' => 255, 'default' => '', 'null' => false, 'comment' => '应用名称'])
            ->addColumn('key', 'string', ['limit' => 255, 'null' => false, 'comment' => '应用KEY 别名'])
            ->addColumn('remark', 'string', ['limit' => 255, 'default' => '', 'null' => false, 'comment' => '备注'])
            ->addColumn('status', 'integer', ['limit' => MysqlAdapter::INT_TINY, 'default' => 0, 'null' => false, 'comment' => '状态'])
            ->addColumn('sort', 'integer', ['default' => 0, 'null' => false, 'comment' => '排序 ASC'])
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
            ->addColumn('file_type', 'string', ['limit' => 50, 'null' => false, 'comment' => '文件类型（MIME类型，如 image/png）'])
            ->addColumn('file_ext', 'string', ['limit' => 20, 'null' => false, 'comment' => '文件后缀（如 .jpg/.pdf）文件后缀'])
            ->addColumn('file_size', 'biginteger', ['null' => false, 'comment' => '文件大小（字节）'])
            ->addColumn('url', 'string', ['limit' => 255, 'null' => false, 'comment' => '链接地址'])
            ->addColumn('file_path', 'string', ['limit' => 1024, 'null' => false, 'comment' => '存储路径'])
            ->addColumn('menu_id', 'integer', ['signed' => false, 'default' => 0, 'null' => false])
            ->addColumn('storage_key', 'string', ['limit' => 20, 'null' => false, 'comment' => '储存方式key'])
            ->addColumn('hash_md5', 'string', ['limit' => 32, 'default' => '', 'null' => false, 'comment' => '文件内容的MD5'])
            ->addColumn('user_id', 'integer', ['signed' => false, 'default' => 0, 'null' => false, 'comment' => '上传用户id'])
            ->addColumn('status', 'integer', ['limit' => MysqlAdapter::INT_TINY, 'default' => 1, 'null' => false, 'comment' => '状态 1=正常 0=停用'])
            ->addColumn('created_at', 'datetime', ['null' => false, 'comment' => '创建时间'])
            ->addColumn('updated_at', 'datetime', ['null' => false, 'comment' => '更新时间'])
            ->addColumn('deleted_at', 'datetime', ['null' => true, 'comment' => '删除时间'])
            ->create();
    }

    private function createLogTypeTable(): void
    {
        $this->table('log_type', $this->tableOptions('日志级别'))
            ->addColumn('id', 'integer', $this->idOptions(['comment' => '<10为系统日志']))
            ->addColumn('app_id', 'integer', ['signed' => false, 'default' => 0, 'null' => false, 'comment' => '应用ID 0为通用'])
            ->addColumn('name', 'string', ['limit' => 32, 'default' => '', 'null' => false, 'comment' => '中文名称'])
            ->addColumn('label', 'string', ['limit' => 32, 'default' => '', 'null' => false, 'comment' => '英文别名'])
            ->addColumn('remark', 'string', ['limit' => 255, 'default' => '', 'null' => false, 'comment' => '备注'])
            ->addColumn('status', 'integer', ['limit' => MysqlAdapter::INT_TINY, 'default' => 1, 'null' => false, 'comment' => '日志开启状态'])
            ->addColumn('color', 'string', ['limit' => 255, 'default' => '', 'null' => false, 'comment' => '日志颜色 #ff0000'])
            ->create();
    }

    private function createLogTable(): void
    {
        $this->table('log', $this->tableOptions('日志表'))
            ->addColumn('id', 'integer', $this->idOptions())
            ->addColumn('app_id', 'integer', ['signed' => false, 'default' => 0, 'null' => false, 'comment' => '应用ID 0为未知'])
            ->addColumn('type_id', 'integer', ['signed' => false, 'default' => 0, 'null' => false, 'comment' => '日志级别 <10为系统日志'])
            ->addColumn('type_name', 'string', ['limit' => 32, 'default' => '', 'null' => false, 'comment' => '日志级别名称'])
            ->addColumn('title', 'string', ['limit' => 500, 'default' => '', 'null' => false, 'comment' => '标题'])
            ->addColumn('value', 'text', ['null' => true, 'comment' => '日志内容'])
            ->addColumn('value_type', 'string', ['limit' => 32, 'default' => 'text', 'null' => false, 'comment' => '日志类型  text,json,html'])
            ->addColumn('request_source', 'string', ['limit' => 255, 'default' => '', 'null' => false, 'comment' => '请求来源'])
            ->addColumn('request_ip', 'string', ['limit' => 64, 'default' => '', 'null' => false, 'comment' => '请求来源IP'])
            ->addColumn('request_user_id', 'integer', ['signed' => false, 'default' => 0, 'null' => false, 'comment' => '操作人ID'])
            ->addColumn('request_user', 'string', ['limit' => 255, 'default' => '', 'null' => false, 'comment' => '操作人'])
            ->addColumn('status', 'integer', ['limit' => MysqlAdapter::INT_TINY, 'signed' => false, 'default' => 0, 'null' => false, 'comment' => '状态 0=未处理 1=已查看 2=已处理'])
            ->addColumn('created_at', 'datetime', ['null' => false, 'comment' => '创建时间'])
            ->addColumn('updated_at', 'datetime', ['null' => false, 'comment' => '更新时间'])
            ->addForeignKey('type_id', 'log_type', 'id', ['constraint' => 'log_log_type_id_fk'])
            ->addIndex(['type_id'], ['name' => 'log_log_level_id_fk'])
            ->create();
    }

    private function createMenuTable(): void
    {
        $this->table('menu', $this->tableOptions('菜单'))
            ->addColumn('id', 'integer', $this->idOptions())
            ->addColumn('app_id', 'integer', ['signed' => false, 'default' => 0, 'null' => false, 'comment' => '应用ID'])
            ->addColumn('pid', 'integer', ['signed' => false, 'default' => 0, 'null' => false, 'comment' => '上级ID'])
            ->addColumn('name', 'string', ['limit' => 255, 'default' => '', 'null' => false, 'comment' => '别名'])
            ->addColumn('title', 'string', ['limit' => 255, 'default' => '', 'null' => false, 'comment' => '显示名称'])
            ->addColumn('type', 'string', ['limit' => 12, 'default' => '', 'null' => false, 'comment' => '类型'])
            ->addColumn('path', 'string', ['limit' => 255, 'default' => '', 'null' => false, 'comment' => '路由地址'])
            ->addColumn('component', 'string', ['limit' => 255, 'default' => '', 'null' => false, 'comment' => '组件地址'])
            ->addColumn('sort', 'integer', ['signed' => false, 'default' => 0, 'null' => false, 'comment' => '排序'])
            ->addColumn('meta', 'json', ['null' => true, 'comment' => 'meta路由参数'])
            ->addForeignKey('app_id', 'app', 'id', ['constraint' => 'menu_app_id_fk'])
            ->addIndex(['sort'], ['name' => 'sort'])
            ->create();
    }

    private function createMenuApiTable(): void
    {
        $this->table('menu_api', $this->tableOptions('菜单权限接口'))
            ->addColumn('id', 'integer', $this->idOptions())
            ->addColumn('app_id', 'integer', ['signed' => false, 'default' => 0, 'null' => false, 'comment' => '应用ID'])
            ->addColumn('menu_id', 'integer', ['signed' => false, 'null' => false, 'comment' => '菜单ID'])
            ->addColumn('path', 'string', ['limit' => 255, 'default' => '', 'null' => false, 'comment' => 'API路由地址'])
            ->addColumn('tag', 'string', ['limit' => 255, 'default' => '', 'null' => false, 'comment' => '标识'])
            ->addForeignKey('app_id', 'app', 'id', ['constraint' => 'menu_api_app_id_fk'])
            ->addForeignKey('menu_id', 'menu', 'id', ['constraint' => 'menu_api_menu_id_fk'])
            ->addIndex(['menu_id'], ['name' => 'menu_id'])
            ->addIndex(['path'], ['name' => 'path'])
            ->create();
    }

    private function createRoleTable(): void
    {
        $this->table('role', $this->tableOptions('用户角色'))
            ->addColumn('id', 'integer', $this->idOptions())
            ->addColumn('app_id', 'integer', ['signed' => false, 'default' => 0, 'null' => false, 'comment' => '应用ID'])
            ->addColumn('name', 'string', ['limit' => 12, 'default' => '', 'null' => false, 'comment' => '角色名称'])
            ->addColumn('rules', 'string', ['limit' => 1000, 'default' => '', 'null' => false, 'comment' => '权限ID ,分割'])
            ->addColumn('rules_checked', 'string', ['limit' => 1000, 'default' => '', 'null' => false, 'comment' => '权限树选中的字节点ID'])
            ->addColumn('remark', 'string', ['limit' => 255, 'default' => '', 'null' => false, 'comment' => '简介'])
            ->addColumn('status', 'integer', ['limit' => MysqlAdapter::INT_TINY, 'signed' => false, 'default' => 0, 'null' => false, 'comment' => '状态'])
            ->addColumn('sort', 'integer', ['signed' => false, 'default' => 0, 'null' => false, 'comment' => '排序'])
            ->addColumn('is_admin', 'integer', ['limit' => MysqlAdapter::INT_TINY, 'signed' => false, 'default' => 0, 'null' => false, 'comment' => '是否为管理员（所有权限）'])
            ->addColumn('created_at', 'datetime', ['null' => false, 'comment' => '创建时间'])
            ->addColumn('updated_at', 'datetime', ['null' => false, 'comment' => '更新时间'])
            ->addColumn('deleted_at', 'datetime', ['null' => true, 'comment' => '删除时间'])
            ->addForeignKey('app_id', 'app', 'id', ['constraint' => 'role_ibfk_1'])
            ->addIndex(['app_id'], ['name' => 'role_app_id_fk'])
            ->addIndex(['sort'], ['name' => 'sort'])
            ->create();
    }

    private function createTaskTable(): void
    {
        $this->table('task', $this->tableOptions('任务'))
            ->addColumn('id', 'integer', $this->idOptions())
            ->addColumn('title', 'string', ['limit' => 255, 'default' => '', 'null' => false, 'comment' => '任务标题'])
            ->addColumn('group', 'enum', ['values' => ['user', 'system'], 'default' => 'system', 'null' => false, 'comment' => '分组'])
            ->addColumn('user_id', 'integer', ['signed' => false, 'default' => 0, 'null' => false, 'comment' => '关联用户ID'])
            ->addColumn('type', 'string', ['limit' => 255, 'null' => false, 'comment' => '任务类型'])
            ->addColumn('label', 'string', ['limit' => 255, 'default' => '', 'null' => false, 'comment' => '任务标识，用于区分业务'])
            ->addColumn('content', 'text', ['limit' => MysqlAdapter::TEXT_LONG, 'null' => true, 'comment' => '任务内容'])
            ->addColumn('result', 'text', ['limit' => MysqlAdapter::TEXT_LONG, 'null' => true, 'comment' => '任务结果'])
            ->addColumn('status', 'integer', ['limit' => MysqlAdapter::INT_TINY, 'default' => 0, 'null' => false, 'comment' => '状态 0=待处理 1=处理中 2=已完成 -1=失败'])
            ->addColumn('created_at', 'datetime', ['null' => false, 'comment' => '创建时间'])
            ->addColumn('updated_at', 'datetime', ['null' => false, 'comment' => '更新时间'])
            ->addColumn('deleted_at', 'datetime', ['null' => true, 'comment' => '删除时间'])
            ->create();
    }

    private function createUserTable(): void
    {
        $this->table('user', $this->tableOptions('用户表'))
            ->addColumn('id', 'integer', $this->idOptions())
            ->addColumn('nickname', 'string', ['limit' => 32, 'default' => '', 'null' => false, 'comment' => '昵称'])
            ->addColumn('username', 'string', ['limit' => 32, 'default' => '', 'null' => false, 'comment' => '用户名(登录账号)'])
            ->addColumn('mobile', 'string', ['limit' => 15, 'null' => false, 'comment' => '手机号'])
            ->addColumn('avatar', 'string', ['limit' => 255, 'default' => '', 'null' => false, 'comment' => '头像'])
            ->addColumn('password', 'string', ['limit' => 32, 'default' => '', 'null' => false, 'comment' => '密码 md5'])
            ->addColumn('status', 'integer', ['limit' => MysqlAdapter::INT_TINY, 'default' => 0, 'null' => false, 'comment' => '状态'])
            ->addColumn('login_time', 'datetime', ['null' => true, 'comment' => '登录时间'])
            ->addColumn('created_at', 'datetime', ['null' => false, 'comment' => '创建时间'])
            ->addColumn('updated_at', 'datetime', ['null' => false, 'comment' => '更新时间'])
            ->addColumn('deleted_at', 'datetime', ['null' => true, 'comment' => '删除时间'])
            ->addIndex(['username', 'deleted_at'], ['unique' => true, 'name' => 'username_unique'])
            ->create();
    }

    private function createUserRoleTable(): void
    {
        $this->table('user_role', $this->tableOptions('用户角色'))
            ->addColumn('id', 'integer', $this->idOptions())
            ->addColumn('user_id', 'integer', ['signed' => false, 'null' => false, 'comment' => '用户ID'])
            ->addColumn('role_id', 'integer', ['signed' => false, 'null' => false, 'comment' => '角色ID'])
            ->addColumn('created_at', 'datetime', ['null' => false, 'comment' => '创建时间'])
            ->addColumn('deleted_at', 'datetime', ['null' => true, 'comment' => '删除时间'])
            ->addForeignKey('role_id', 'role', 'id', ['constraint' => 'user_role_role_id_fk'])
            ->addForeignKey('user_id', 'user', 'id', ['constraint' => 'user_role_user_id_fk'])
            ->create();
    }

    private function tableOptions(string $comment): array
    {
        return [
            'id' => false,
            'primary_key' => ['id'],
            'comment' => $comment,
            'engine' => 'InnoDB',
            'row_format' => 'DYNAMIC',
        ];
    }

    private function idOptions(array $extra = []): array
    {
        return array_merge([
            'identity' => true,
            'signed' => false,
            'null' => false,
        ], $extra);
    }
}
