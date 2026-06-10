<?php declare(strict_types=1);

namespace app\admin\services;

use app\common\exception\BusinessException;
use app\common\services\BaseService;
use app\model\ConfigChangeLog;
use app\model\ConfigForm;
use app\model\ConfigValue;
use support\Db;

class ConfigService extends BaseService
{
    public function getList(): array
    {
        $forms = ConfigForm::where('status', 1)
            ->orderBy('sort')
            ->orderBy('id')
            ->get();

        $values = ConfigValue::whereIn('form_key', $forms->pluck('key')->toArray())
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
        $value = ConfigValue::where('form_key', $form->key)->where('status', 1)->first();

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

        Db::transaction(function () use ($form, $nextValue, $claims) {
            $configValue = ConfigValue::where('form_key', $form->key)->first();
            $beforeValue = [];
            if (!$configValue) {
                $configValue = new ConfigValue();
                $configValue->form_id = $form->id;
                $configValue->form_key = $form->key;
                $configValue->version = 0;
                $configValue->status = 1;
            } else {
                $beforeValue = $this->decodeValue($configValue->value);
            }

            $configValue->form_id = $form->id;
            $configValue->value = json_encode($nextValue, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            $configValue->version = ((int)$configValue->version) + 1;
            $configValue->status = 1;
            $configValue->save();

            $log = new ConfigChangeLog();
            $log->fill([
                'form_id' => $form->id,
                'form_key' => $form->key,
                'before_value' => json_encode($beforeValue, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'after_value' => json_encode($nextValue, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'operator_id' => (int)($claims['id'] ?? 0),
                'operator_name' => (string)($claims['name'] ?? ''),
                'created_at' => date('Y-m-d H:i:s'),
            ]);
            $log->save();
        });
    }

    public function getByKey(string $key): array
    {
        $form = ConfigForm::where('key', $key)->where('status', 1)->first();
        if (!$form) {
            return [];
        }

        $schema = (new ConfigFormService())->decodeSchema($form->schema);
        $value = ConfigValue::where('form_key', $key)->where('status', 1)->first();
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
