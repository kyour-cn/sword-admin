<template>
  <div class="test-config-panel">
    <el-form
      ref="formRef"
      class="test-config-form"
      :model="form"
      :rules="rules"
      label-position="top"
      v-loading="saving"
    >
      <div class="form-header">
        <div>
          <div class="form-title">Test 自定义配置</div>
          <div class="form-desc">配置表单标识为 test 时，会自动使用这个页面渲染。</div>
        </div>
        <el-tag effect="plain">custom renderer</el-tag>
      </div>

      <el-row :gutter="16">
        <el-col :xs="24" :md="12">
          <el-form-item label="标题" prop="title">
            <el-input v-model="form.title" placeholder="请输入标题" clearable />
          </el-form-item>
        </el-col>
        <el-col :xs="24" :md="12">
          <el-form-item label="数量" prop="count">
            <el-input-number
              v-model="form.count"
              controls-position="right"
              :min="0"
              style="width: 100%;"
            />
          </el-form-item>
        </el-col>
        <el-col :span="24">
          <div class="switch-line">
            <div>
              <div class="switch-title">启用状态</div>
              <div class="switch-desc">这个开关会保存到 enabled 字段。</div>
            </div>
            <el-switch v-model="form.enabled" />
          </div>
        </el-col>
      </el-row>

      <el-form-item class="form-actions">
        <el-button v-auth="'admin.system.config.save'" type="primary" :loading="saving" @click="submit">
          保 存
        </el-button>
      </el-form-item>
    </el-form>

    <div class="preview-panel">
      <div class="preview-label">当前值预览</div>
      <pre>{{ previewText }}</pre>
    </div>
  </div>
</template>

<script setup>
import {computed, nextTick, reactive, ref, watch} from "vue"

const props = defineProps({
  modelValue: {type: Object, default: () => ({})},
  saving: {type: Boolean, default: false},
})

const emit = defineEmits(["update:modelValue", "submit"])

const formRef = ref(null)
const form = reactive({})
let syncing = false

const rules = {
  title: [{required: true, message: "请输入标题"}],
}

const previewText = computed(() => {
  return JSON.stringify(form, null, 2)
})

function syncForm(value) {
  syncing = true
  Object.keys(form).forEach(key => {
    delete form[key]
  })
  Object.assign(form, {
    title: "",
    count: 0,
    enabled: false,
  }, value)
  nextTick(() => {
    syncing = false
  })
}

watch(() => props.modelValue, (value) => {
  syncForm(value || {})
}, {deep: true, immediate: true})

watch(form, (value) => {
  if (syncing) {
    return
  }
  emit("update:modelValue", JSON.parse(JSON.stringify(value)))
}, {deep: true})

const validate = (callback) => {
  return formRef.value.validate(callback)
}

const submit = () => {
  emit("submit")
}

defineExpose({
  validate,
})
</script>

<style scoped>
.test-config-panel {
  display: grid;
  grid-template-columns: minmax(0, 1fr) 320px;
  gap: 16px;
  align-items: start;
}

.test-config-form,
.preview-panel {
  border: 1px solid var(--el-border-color-light);
  border-radius: 4px;
  background: var(--el-fill-color-blank);
}

.test-config-form {
  padding: 16px 16px 0;
}

.form-header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 12px;
  margin-bottom: 16px;
  padding-bottom: 14px;
  border-bottom: 1px solid var(--el-border-color-lighter);
}

.form-title {
  color: var(--el-text-color-primary);
  font-size: 16px;
  font-weight: 600;
  line-height: 24px;
}

.form-desc,
.switch-desc,
.preview-label {
  color: var(--el-text-color-secondary);
  font-size: 12px;
  line-height: 18px;
}

.switch-line {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  min-height: 58px;
  padding: 10px 12px;
  border: 1px solid var(--el-border-color-lighter);
  border-radius: 4px;
  background: var(--el-fill-color-light);
}

.switch-title {
  color: var(--el-text-color-primary);
  font-size: 13px;
  line-height: 20px;
}

.form-actions {
  margin-top: 16px;
}

.preview-panel {
  padding: 16px;
}

.preview-panel pre {
  min-height: 150px;
  margin: 10px 0 0;
  padding: 12px;
  overflow: auto;
  color: var(--el-text-color-primary);
  font-size: 12px;
  line-height: 18px;
  border-radius: 4px;
  background: var(--el-fill-color-light);
}

@media (max-width: 1100px) {
  .test-config-panel {
    grid-template-columns: 1fr;
  }
}
</style>
