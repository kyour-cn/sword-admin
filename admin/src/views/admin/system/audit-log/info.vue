<template>
  <el-main class="audit-log-info">
    <el-descriptions class="audit-log-descriptions" :column="1" border size="small">
      <el-descriptions-item label="审计标题">{{ state.data.title }}</el-descriptions-item>
      <el-descriptions-item label="操作人">
        {{ state.data.actor_name || '-' }}（{{ state.data.actor_id || 0 }}）
      </el-descriptions-item>
      <el-descriptions-item label="模块 / 动作">
        {{ moduleLabel(state.data.module) }} / {{ actionLabel(state.data.action) }}
      </el-descriptions-item>
      <el-descriptions-item label="资源">
        {{ state.data.resource_type || '-' }} / {{ state.data.resource_id || '-' }}
      </el-descriptions-item>
      <el-descriptions-item label="请求">
        {{ state.data.method || '-' }} {{ state.data.path || '-' }}
      </el-descriptions-item>
      <el-descriptions-item label="请求 IP">{{ state.data.ip || '-' }}</el-descriptions-item>
      <el-descriptions-item label="客户端">{{ state.data.user_agent || '-' }}</el-descriptions-item>
      <el-descriptions-item label="结果">
        <el-tag :type="state.data.status ? 'success' : 'danger'" size="small">
          {{ state.data.status ? '成功' : '失败' }}
        </el-tag>
      </el-descriptions-item>
      <el-descriptions-item label="操作时间">{{ state.data.created_at || '-' }}</el-descriptions-item>
      <el-descriptions-item label="操作摘要">{{ state.data.description || '-' }}</el-descriptions-item>
    </el-descriptions>
    <el-collapse v-model="state.activeNames" class="context-collapse">
      <el-collapse-item title="上下文" name="context">
        <pre class="context-code">{{ contextText }}</pre>
      </el-collapse-item>
    </el-collapse>
  </el-main>
</template>

<script setup>

import {computed, reactive} from "vue"

const props = defineProps({
  options: {
    type: Object,
    default: () => ({
      actions: [],
      modules: []
    })
  }
})

const state = reactive({
  activeNames: ['context'],
  data: {}
})

const contextText = computed(() => {
  if (!state.data.context) {
    return '-'
  }
  return JSON.stringify(state.data.context, null, 2)
})

const actionLabel = (value) => optionLabel('actions', value)

const moduleLabel = (value) => optionLabel('modules', value)

const optionLabel = (group, value) => {
  const item = props.options[group]?.find(item => item.value === value)
  return item?.label || value || '-'
}

const setData = (data) => {
  state.data = data || {}
}

defineExpose({
  setData
})

</script>

<style scoped>
.audit-log-info {
  padding: 0 20px;
}

.context-collapse {
  margin-top: 20px;
}

.audit-log-descriptions :deep(.el-descriptions__label) {
  width: 88px;
  min-width: 88px;
  white-space: nowrap;
  word-break: keep-all;
}

.context-code {
  min-height: 120px;
  max-height: 360px;
  margin: 0;
  padding: 14px;
  overflow: auto;
  color: var(--el-text-color-primary);
  background: var(--el-fill-color-light);
  border-radius: 4px;
  font-size: 12px;
  line-height: 1.6;
}
</style>
