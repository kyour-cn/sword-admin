<?php declare(strict_types=1);

namespace app\admin\services;

use app\common\exception\BusinessException;
use app\common\services\BaseService;
use app\model\ConfigForm;

class ConfigFormService extends BaseService
{
    private const COMPONENTS = [
        'input',
        'switch',
        'select',
        'number',
        'radio',
        'checkboxGroup',
        'date',
        'color',
        'upload',
        'title',
    ];

    public function getList(array $params): array
    {
        $query = ConfigForm::query();

        if (!empty($params['keyword'])) {
            $keyword = $params['keyword'];
            $query->where(function ($query) use ($keyword) {
                $query->where('key', 'like', "%{$keyword}%")
                    ->orWhere('title', 'like', "%{$keyword}%")
                    ->orWhere('group_title', 'like', "%{$keyword}%");
            });
        }

        if (isset($params['status']) && $params['status'] !== '') {
            $query->where('status', (int)$params['status']);
        }

        $row = $query
            ->orderBy('sort')
            ->orderBy('id', 'desc')
            ->paginate(perPage: $params['page_size'] ?? 10, page: $params['page'] ?? 1);

        $rows = array_map(function (ConfigForm $item) {
            $data = $item->toArray();
            $data['schema'] = $this->decodeSchema($item->schema);
            return $data;
        }, $row->items());

        return [
            'rows' => $rows,
            'total' => $row->total(),
            'page' => $row->currentPage(),
            'page_size' => $row->perPage()
        ];
    }

    public function create(array $data): void
    {
        $this->assertUniqueKey($data['key'] ?? '');

        $form = new ConfigForm();
        $form->fill($this->formatData($data));
        $form->save();
    }

    public function update(array $data): void
    {
        $form = ConfigForm::find($data['id'] ?? 0);
        if (!$form) {
            throw new BusinessException('配置表单不存在');
        }

        $this->assertUniqueKey($data['key'] ?? '', (int)$form->id);
        $form->fill($this->formatData($data));
        $form->save();
    }

    public function delete(array $ids): void
    {
        ConfigForm::whereIn('id', $ids)->delete();
    }

    public function decodeSchema(?string $schema): array
    {
        if (empty($schema)) {
            return $this->defaultSchema();
        }

        $data = json_decode($schema, true);
        if (!is_array($data)) {
            return $this->defaultSchema();
        }

        return $this->normalizeSchema($data);
    }

    public function normalizeSchema(array $schema): array
    {
        $schema = array_merge($this->defaultSchema(), $schema);
        if (!is_array($schema['formItems'] ?? null)) {
            throw new BusinessException('表单字段配置不正确');
        }

        $names = [];
        foreach ($schema['formItems'] as $index => &$item) {
            if (!is_array($item)) {
                throw new BusinessException('表单字段配置不正确');
            }

            $component = trim((string)($item['component'] ?? ''));
            if (!in_array($component, self::COMPONENTS, true)) {
                throw new BusinessException("不支持的字段组件：{$component}");
            }

            if ($component !== 'title') {
                $name = trim((string)($item['name'] ?? ''));
                if (!preg_match('/^[A-Za-z][A-Za-z0-9_]*$/', $name)) {
                    throw new BusinessException('字段标识需以字母开头，仅支持字母、数字和下划线');
                }
                if (isset($names[$name])) {
                    throw new BusinessException("字段标识重复：{$name}");
                }
                $names[$name] = true;
                $item['name'] = $name;
            } else {
                $item['name'] = $item['name'] ?? ('title_' . $index);
            }

            $item['label'] = trim((string)($item['label'] ?? ''));
            if ($item['label'] === '') {
                throw new BusinessException('字段名称不能为空');
            }

            $item['component'] = $component;
            $item['span'] = empty($item['span']) ? 24 : (int)$item['span'];
            $item['options'] = is_array($item['options'] ?? null) ? $item['options'] : [];
            $item['rules'] = is_array($item['rules'] ?? null) ? $item['rules'] : [];
        }
        unset($item);

        return [
            'labelWidth' => $schema['labelWidth'] ?: '120px',
            'labelPosition' => in_array($schema['labelPosition'], ['left', 'right', 'top'], true) ? $schema['labelPosition'] : 'left',
            'formItems' => array_values($schema['formItems']),
        ];
    }

    public function components(): array
    {
        return self::COMPONENTS;
    }

    private function formatData(array $data): array
    {
        $key = trim((string)($data['key'] ?? ''));
        if (!preg_match('/^[A-Za-z][A-Za-z0-9_]*$/', $key)) {
            throw new BusinessException('表单标识需以字母开头，仅支持字母、数字和下划线');
        }

        $schema = $data['schema'] ?? [];
        if (is_string($schema)) {
            $schema = json_decode($schema, true);
        }
        if (!is_array($schema)) {
            throw new BusinessException('表单结构必须是合法 JSON');
        }

        $schema = $this->normalizeSchema($schema);

        $title = trim((string)($data['title'] ?? ''));
        $groupKey = trim((string)($data['group_key'] ?? 'base'));
        $groupTitle = trim((string)($data['group_title'] ?? '基础配置'));

        if ($title === '') {
            throw new BusinessException('表单名称不能为空');
        }
        if ($groupKey === '') {
            throw new BusinessException('分组标识不能为空');
        }
        if ($groupTitle === '') {
            throw new BusinessException('分组名称不能为空');
        }

        return [
            'key' => $key,
            'title' => $title,
            'group_key' => $groupKey,
            'group_title' => $groupTitle,
            'schema' => json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'status' => empty($data['status']) ? 0 : 1,
            'sort' => is_numeric($data['sort'] ?? null) ? (int)$data['sort'] : 0,
            'remark' => trim((string)($data['remark'] ?? '')),
        ];
    }

    private function assertUniqueKey(string $key, int $ignoreId = 0): void
    {
        $query = ConfigForm::where('key', $key);
        if ($ignoreId > 0) {
            $query->where('id', '<>', $ignoreId);
        }

        if ($query->exists()) {
            throw new BusinessException('表单标识已存在');
        }
    }

    private function defaultSchema(): array
    {
        return [
            'labelWidth' => '120px',
            'labelPosition' => 'left',
            'formItems' => [],
        ];
    }
}
