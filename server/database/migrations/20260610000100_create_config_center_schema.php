<?php

declare(strict_types=1);

use Phinx\Db\Adapter\MysqlAdapter;
use Phinx\Migration\AbstractMigration;

final class CreateConfigCenterSchema extends AbstractMigration
{
    public function up(): void
    {
        $this->createConfigFormTable();
        $this->createConfigValueTable();
        $this->createConfigChangeLogTable();
    }

    public function down(): void
    {
        $this->execute('set foreign_key_checks = 0');

        foreach ([
            'config_change_log',
            'config_value',
            'config_form',
        ] as $table) {
            if ($this->hasTable($table)) {
                $this->table($table)->drop()->save();
            }
        }

        $this->execute('set foreign_key_checks = 1');
    }

    private function createConfigFormTable(): void
    {
        if ($this->hasTable('config_form')) {
            return;
        }

        $this->table('config_form', $this->tableOptions('配置表单'))
            ->addColumn('id', 'integer', $this->idOptions())
            ->addColumn('key', 'string', ['limit' => 50, 'default' => '', 'null' => false, 'comment' => '表单唯一标识'])
            ->addColumn('title', 'string', ['limit' => 50, 'default' => '', 'null' => false, 'comment' => '表单名称'])
            ->addColumn('group_key', 'string', ['limit' => 30, 'default' => '', 'null' => false, 'comment' => '分组标识'])
            ->addColumn('group_title', 'string', ['limit' => 50, 'default' => '', 'null' => false, 'comment' => '分组名称'])
            ->addColumn('schema', 'json', ['null' => true, 'comment' => '表单结构'])
            ->addColumn('status', 'integer', ['limit' => MysqlAdapter::INT_TINY, 'default' => 1, 'null' => false, 'comment' => '状态 1=启用 0=停用'])
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

    private function createConfigValueTable(): void
    {
        if ($this->hasTable('config_value')) {
            return;
        }

        $this->table('config_value', $this->tableOptions('配置值'))
            ->addColumn('id', 'integer', $this->idOptions())
            ->addColumn('form_id', 'integer', ['signed' => false, 'null' => false, 'comment' => '配置表单ID'])
            ->addColumn('form_key', 'string', ['limit' => 50, 'default' => '', 'null' => false, 'comment' => '表单唯一标识'])
            ->addColumn('value', 'json', ['null' => true, 'comment' => '配置值'])
            ->addColumn('version', 'integer', ['default' => 1, 'null' => false, 'comment' => '版本号'])
            ->addColumn('status', 'integer', ['limit' => MysqlAdapter::INT_TINY, 'default' => 1, 'null' => false, 'comment' => '状态 1=生效 0=停用'])
            ->addColumn('created_at', 'datetime', ['null' => false, 'comment' => '创建时间'])
            ->addColumn('updated_at', 'datetime', ['null' => false, 'comment' => '更新时间'])
            ->addForeignKey('form_id', 'config_form', 'id', ['constraint' => 'config_value_form_id_fk'])
            ->addIndex(['form_key'], ['unique' => true, 'name' => 'config_value_form_key_unique'])
            ->addIndex(['form_id'], ['name' => 'config_value_form_id_index'])
            ->create();
    }

    private function createConfigChangeLogTable(): void
    {
        if ($this->hasTable('config_change_log')) {
            return;
        }

        $this->table('config_change_log', $this->tableOptions('配置变更日志'))
            ->addColumn('id', 'integer', $this->idOptions())
            ->addColumn('form_id', 'integer', ['signed' => false, 'null' => false, 'comment' => '配置表单ID'])
            ->addColumn('form_key', 'string', ['limit' => 50, 'default' => '', 'null' => false, 'comment' => '表单唯一标识'])
            ->addColumn('before_value', 'json', ['null' => true, 'comment' => '修改前配置'])
            ->addColumn('after_value', 'json', ['null' => true, 'comment' => '修改后配置'])
            ->addColumn('operator_id', 'integer', ['signed' => false, 'default' => 0, 'null' => false, 'comment' => '操作人ID'])
            ->addColumn('operator_name', 'string', ['limit' => 50, 'default' => '', 'null' => false, 'comment' => '操作人名称'])
            ->addColumn('created_at', 'datetime', ['null' => false, 'comment' => '创建时间'])
            ->addIndex(['form_key', 'created_at'], ['name' => 'config_change_log_form_key_created_at_index'])
            ->addIndex(['operator_id'], ['name' => 'config_change_log_operator_id_index'])
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
