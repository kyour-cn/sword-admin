<template>
  <el-card shadow="never" header="个人操作日志">
    <div class="operation-log-search-row">
      <el-input
        v-model="state.search.keyword"
        clearable
        class="keyword-filter"
        placeholder="操作标题 / 摘要 / 路径"
        @clear="clearSearch"
        @keyup.enter="upSearch"
      />
      <el-select v-model="state.search.action" clearable class="base-filter" placeholder="操作动作">
        <el-option
          v-for="item in actionOptions"
          :key="item.value"
          :label="item.label"
          :value="item.value"
        />
      </el-select>
      <el-date-picker
        v-model="state.search.date"
        type="daterange"
        value-format="YYYY-MM-DD"
        format="YYYY-MM-DD"
        range-separator="至"
        start-placeholder="开始日期"
        end-placeholder="结束日期"
        class="date-filter"
      />
      <el-button type="primary" icon="el-icon-search" @click="upSearch">查询</el-button>
      <el-button icon="el-icon-refresh" @click="clearSearch">重置</el-button>
    </div>
    <sc-table ref="table" :apiObj="apiObj" :params="state.tableParams" height="auto" hideDo stripe>
      <el-table-column type="index" label="序号" width="70"/>
      <el-table-column label="操作标题" prop="title" min-width="180" show-overflow-tooltip/>
      <el-table-column label="操作摘要" prop="description" min-width="220" show-overflow-tooltip/>
      <el-table-column label="模块" min-width="110" show-overflow-tooltip>
        <template #default="scope">
          {{ scope.row.module_title || scope.row.module || '-' }}
        </template>
      </el-table-column>
      <el-table-column label="动作" width="90">
        <template #default="scope">
          <el-tag size="small" effect="plain">{{ actionLabel(scope.row.action) }}</el-tag>
        </template>
      </el-table-column>
      <el-table-column label="结果" width="80">
        <template #default="scope">
          <el-tag :type="scope.row.status ? 'success' : 'danger'" size="small">
            {{ scope.row.status ? '成功' : '失败' }}
          </el-tag>
        </template>
      </el-table-column>
      <el-table-column label="IP" prop="ip" width="140" show-overflow-tooltip/>
      <el-table-column label="操作时间" prop="created_at" width="170"/>
    </sc-table>
  </el-card>
</template>

<script setup>

import {reactive, ref} from "vue"
import ScTable from "@/components/scTable/index.vue"
import userApi from "@/api/common/user.js"

const table = ref(null)
const apiObj = userApi.operationLog
const actionOptions = [
  {label: '登录', value: 'login'},
  {label: '新增', value: 'create'},
  {label: '编辑', value: 'update'},
  {label: '删除', value: 'delete'},
  {label: '导出', value: 'export'},
  {label: '导入', value: 'import'},
  {label: '上传', value: 'upload'}
]

const state = reactive({
  search: {
    keyword: null,
    action: null,
    date: []
  },
  tableParams: {
    keyword: '',
    action: '',
    start_time: '',
    end_time: ''
  }
})

const actionLabel = (value) => {
  const item = actionOptions.find(item => item.value === value)
  return item?.label || value || '-'
}

const getSearchParams = () => {
  return {
    keyword: state.search.keyword?.trim() || '',
    action: state.search.action || '',
    start_time: state.search.date?.[0] ? `${state.search.date[0]} 00:00:00` : '',
    end_time: state.search.date?.[1] ? `${state.search.date[1]} 23:59:59` : ''
  }
}

const upSearch = () => {
  const params = getSearchParams()
  state.tableParams = params
  table.value.upData(params, 1)
}

const clearSearch = () => {
  state.search.keyword = null
  state.search.action = null
  state.search.date = []

  const params = getSearchParams()
  state.tableParams = params
  table.value.reload(params, 1)
}

</script>

<style scoped>
.operation-log-search-row {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 10px;
  padding-bottom: 14px;
  margin-bottom: 14px;
  border-bottom: 1px solid var(--el-border-color-light);
}

.keyword-filter {
  width: 240px;
}

.base-filter {
  width: 140px;
}

.date-filter {
  width: 260px;
}

@media (max-width: 768px) {
  .operation-log-search-row :deep(.el-input),
  .operation-log-search-row :deep(.el-select),
  .operation-log-search-row :deep(.el-date-editor),
  .operation-log-search-row :deep(.el-button) {
    width: 100%;
  }
}
</style>
