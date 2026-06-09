<template>

  <el-main style="padding:0 20px;">
    <el-descriptions :column="1" border size="small">
      <el-descriptions-item label="标题">{{ state.data.title }}</el-descriptions-item>
      <el-descriptions-item label="日志类型">{{ state.data.type_name }}</el-descriptions-item>
      <el-descriptions-item label="请求来源">{{ state.data.request_source }}</el-descriptions-item>
      <el-descriptions-item label="操作人">{{ state.data.request_user }} （{{ state.data.request_user_id }}）</el-descriptions-item>
      <el-descriptions-item label="请求ip">{{ state.data.request_ip }}</el-descriptions-item>
      <el-descriptions-item label="请求时间">{{ state.data.created_at }}</el-descriptions-item>
    </el-descriptions>
    <el-collapse v-model="state.activeNames" style="margin-top: 20px;">
      <el-collapse-item title="记录内容" name="1">
        <div class="code">
          {{state.data.value}}
        </div>
      </el-collapse-item>
    </el-collapse>
  </el-main>
</template>

<script setup>

import {reactive} from 'vue'
import tool from '@/utils/tool'

const state = reactive({
  data: {
    title: '',
    type_name: '',
    request_source: '',
    request_user: '',
    request_user_id: '',
    request_ip: '',
    created_at: '',
    value: ''
  },
  activeNames: ['1'],
  typeMap: {
    'info': "info",
    'warn': "warning",
    'error': "error"
  }
});

const setData = (data) => {
  state.data = data
}

//暴露给父组件的方法
defineExpose({
  setData
})
</script>

<style scoped>
:deep(.is-bordered-label) {
  min-width: 6em;
}

.code {
  background: #848484;
  padding: 15px;
  color: #fff;
  font-size: 12px;
  border-radius: 4px;
}
</style>
