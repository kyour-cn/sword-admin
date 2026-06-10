<?php declare(strict_types=1);

namespace app\admin\services;

use app\common\exception\BusinessException;
use app\common\services\BaseService;
use app\model\Config as ConfigModel;
use app\model\ConfigForm;

class ConfigService extends BaseService
{
    public function getList(): array
    {
        $forms = ConfigForm::where('status', 1)
            ->orderBy('sort')
            ->orderBy('id')
            ->get();

        $values = ConfigModel::whereIn('form_key', $forms->pluck('key')->toArray())
            ->where('status', 1)
            ->get()
            ->keyBy('form_key');

        $groups = [];
        $formService = new ConfigFormService();

        foreach ($forms as $form) {
            $schema = $formService->decodeSchema($form->schema);
            $savedValue = isset($values[$form->key]) ? $this->decodeValue($values[$form->key]->value) : [];
            $value = $this->mergeDefaultValue($schema, $savedValue);

            $groupKey = $form->group_key ?: 'base';
            if (!isset($groups[$groupKey])) {
                $groups[$groupKey] = [
                    'key' => $groupKey,
                    'title' => $form->group_title ?: '基础配置',
                    'forms' => [],
                ];
            }

            $groups[$groupKey]['forms'][] = [
                'id' => $form->id,
                'key' => $form->key,
                'title' => $form->title,
                'schema' => $schema,
                'value' => $value,
                'remark' => $form->remark,
            ];
        }

        return array_values($groups);
    }

    public function detail(string $key): array
    {
        $form = $this->getEnabledForm($key);
        $formService = new ConfigFormService();
        $schema = $formService->decodeSchema($form->schema);
        $value = ConfigModel::where('form_key', $form->key)->where('status', 1)->first();

        return [
            'id' => $form->id,
            'key' => $form->key,
            'title' => $form->title,
            'schema' => $schema,
            'value' => $this->mergeDefaultValue($schema, $value ? $this->decodeValue($value->value) : []),
        ];
    }

    public function save(string $key, array $value, array $claims = []): void
    {
        $form = $this->getEnabledForm($key);
        $schema = (new ConfigFormService())->decodeSchema($form->schema);
        $nextValue = $this->filterValue($schema, $value);

        $config = ConfigModel::where('form_key', $form->key)->first();
        if (!$config) {
            $config = new ConfigModel();
            $config->form_key = $form->key;
            $config->version = 0;
        }

        $config->form_id = $form->id;
        $config->value = json_encode($nextValue, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $config->version = ((int)$config->version) + 1;
        $config->status = 1;
        $config->save();
    }

    public function getByKey(string $key): array
    {
        $form = ConfigForm::where('key', $key)->where('status', 1)->first();
        if (!$form) {
            return [];
        }

        $schema = (new ConfigFormService())->decodeSchema($form->schema);
        $value = ConfigModel::where('form_key', $key)->where('status', 1)->first();
        return $this->mergeDefaultValue($schema, $value ? $this->decodeValue($value->value) : []);
    }

    public function mergeDefaultValue(array $schema, array $value): array
    {
        $defaults = [];
        foreach ($schema['formItems'] ?? [] as $item) {
            if (($item['component'] ?? '') === 'title') {
                continue;
            }
            $defaults[$item['name']] = $item['value'] ?? $this->defaultValueByComponent($item['component'] ?? '');
        }

        return array_merge($defaults, $value);
    }

    private function filterValue(array $schema, array $value): array
    {
        $result = [];
        foreach ($schema['formItems'] ?? [] as $item) {
            if (($item['component'] ?? '') === 'title') {
                continue;
            }
            $name = $item['name'];
            $result[$name] = array_key_exists($name, $value)
                ? $value[$name]
                : ($item['value'] ?? $this->defaultValueByComponent($item['component'] ?? ''));
        }

        return $result;
    }

    private function getEnabledForm(string $key): ConfigForm
    {
        $form = ConfigForm::where('key', $key)->where('status', 1)->first();
        if (!$form) {
            throw new BusinessException('配置表单不存在或已停用');
        }

        return $form;
    }

    private function decodeValue(?string $value): array
    {
        if (empty($value)) {
            return [];
        }

        $data = json_decode($value, true);
        return is_array($data) ? $data : [];
    }

    private function defaultValueByComponent(string $component): mixed
    {
        return match ($component) {
            'switch' => false,
            'number' => 0,
            'checkboxGroup', 'upload' => [],
            default => '',
        };
    }
}
