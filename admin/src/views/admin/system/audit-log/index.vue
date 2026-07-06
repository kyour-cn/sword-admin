<template>
  <el-container class="admin-crud-page audit-log-page">
    <el-header class="admin-crud-table-header audit-log-table-header">
      <div class="admin-crud-search-row audit-log-search-row">
        <el-input
          v-model="state.search.keyword"
          placeholder="标题 / 摘要 / 操作人 / 资源 / 路径"
          clearable
          class="admin-crud-keyword-filter keyword-filter"
          @clear="clearSearch"
        />
        <el-select
          v-model="state.search.module"
          placeholder="模块"
          clearable
          class="admin-crud-base-filter base-filter"
        >
          <el-option
            v-for="item in state.options.modules"
            :key="item.value"
            :label="item.label"
            :value="item.value"
          />
        </el-select>
        <el-select
          v-model="state.search.action"
          placeholder="动作"
          clearable
          class="admin-crud-base-filter base-filter"
        >
          <el-option
            v-for="item in state.options.actions"
            :key="item.value"
            :label="item.label"
            :value="item.value"
          />
        </el-select>
        <el-date-picker
          v-model="state.search.date"
          type="datetimerange"
          value-format="YYYY-MM-DD HH:mm:ss"
          format="YYYY-MM-DD HH:mm:ss"
          range-separator="至"
          start-placeholder="开始时间"
          end-placeholder="结束时间"
          class="admin-crud-date-filter audit-log-date-filter date-filter"
        />
        <el-button type="primary" icon="el-icon-search" @click="upSearch">查询</el-button>
        <el-button icon="el-icon-refresh" @click="clearSearch">重置</el-button>
      </div>
    </el-header>
    <el-main class="audit-log-main nopadding">
      <div class="audit-log-stat" v-loading="state.statLoading">
        <div class="audit-log-stat-dashboard">
          <div class="audit-log-total-panel">
            <span class="audit-log-total-label">操作总量</span>
            <strong>{{ state.stat.total }}</strong>
            <div class="audit-log-status-grid">
              <div>
                <span>成功</span>
                <em class="success">{{ state.stat.status.success }}</em>
              </div>
              <div>
                <span>失败</span>
                <em class="danger">{{ state.stat.status.fail }}</em>
              </div>
              <div>
                <span>成功率</span>
                <em>{{ successRate }}</em>
              </div>
            </div>
          </div>
          <div class="audit-log-trend-panel">
            <div class="audit-log-stat-title">操作趋势</div>
            <sc-echarts height="126px" :option="trendOption"/>
          </div>
          <div class="audit-log-rank-panel">
            <div class="audit-log-rank-section">
              <div class="audit-log-stat-title">动作分布</div>
              <div v-if="topActions.length" class="audit-log-rank-bars">
                <div v-for="item in topActions" :key="item.value" class="audit-log-rank-bar">
                  <div class="audit-log-rank-meta">
                    <span>{{ item.label }}</span>
                    <em>{{ item.count }}</em>
                  </div>
                  <div class="audit-log-rank-track">
                    <i :style="{width: rankPercent(item, topActions)}"></i>
                  </div>
                </div>
              </div>
              <el-empty v-else description="暂无数据" :image-size="42"/>
            </div>
            <div class="audit-log-rank-section">
              <div class="audit-log-stat-title">模块排行</div>
              <div v-if="topModules.length" class="audit-log-rank-bars">
                <div v-for="item in topModules" :key="item.value" class="audit-log-rank-bar">
                  <div class="audit-log-rank-meta">
                    <span>{{ item.label }}</span>
                    <em>{{ item.count }}</em>
                  </div>
                  <div class="audit-log-rank-track">
                    <i :style="{width: rankPercent(item, topModules)}"></i>
                  </div>
                </div>
              </div>
              <el-empty v-else description="暂无数据" :image-size="42"/>
            </div>
          </div>
        </div>
      </div>
      <sc-table
        class="audit-log-table"
        ref="table"
        :apiObj="apiObj"
        :params="state.tableParams"
        row-key="id"
        stripe
      >
        <el-table-column label="ID" prop="id" width="90" sortable/>
        <el-table-column label="时间" prop="created_at" width="170" sortable/>
        <el-table-column label="操作人" prop="actor_name" min-width="140" show-overflow-tooltip>
          <template #default="scope">
            {{ scope.row.actor_name || '-' }}（{{ scope.row.actor_id || 0 }}）
          </template>
        </el-table-column>
        <el-table-column label="动作" prop="action" width="100">
          <template #default="scope">
            <el-tag size="small" effect="plain">{{ actionLabel(scope.row.action) }}</el-tag>
          </template>
        </el-table-column>
        <el-table-column label="模块" prop="module" width="110">
          <template #default="scope">
            {{ moduleLabel(scope.row.module) }}
          </template>
        </el-table-column>
        <el-table-column label="标题" prop="title" min-width="180" show-overflow-tooltip/>
        <el-table-column label="操作摘要" prop="description" min-width="220" show-overflow-tooltip/>
        <el-table-column label="资源" min-width="160" show-overflow-tooltip>
          <template #default="scope">
            {{ scope.row.resource_type || '-' }} / {{ scope.row.resource_id || '-' }}
          </template>
        </el-table-column>
        <el-table-column label="结果" prop="status" width="80">
          <template #default="scope">
            <sc-status-indicator v-if="scope.row.status" type="success"></sc-status-indicator>
            <sc-status-indicator v-else pulse type="danger"></sc-status-indicator>
          </template>
        </el-table-column>
        <el-table-column label="IP" prop="ip" width="140" show-overflow-tooltip/>
        <el-table-column label="请求路径" prop="path" min-width="220" show-overflow-tooltip/>
        <el-table-column label="操作" fixed="right" align="right" width="90">
          <template #default="scope">
            <el-button text plain type="primary" size="small" @click="showInfo(scope.row)">查看</el-button>
          </template>
        </el-table-column>
      </sc-table>
    </el-main>
  </el-container>

  <el-drawer v-model="state.infoDrawer" title="操作审计详情" :size="620" destroy-on-close>
    <info ref="infoRef" :options="state.options"/>
  </el-drawer>
</template>

<script setup>

import {computed, nextTick, onMounted, reactive, ref} from "vue"
import Info from './info'
import ScEcharts from "@/components/scEcharts/index.vue"
import ScStatusIndicator from "@/components/scMini/scStatusIndicator.vue"
import ScTable from "@/components/scTable/index.vue"
import systemApi from "@/api/admin/system.js"

defineOptions({
  name: 'audit_log',
})

const table = ref(null)
const infoRef = ref(null)
const apiObj = systemApi.auditLog.list
const initialDate = defaultDate()

function defaultStat() {
  return {
    days: [],
    daily: [],
    actions: [],
    modules: [],
    status: {
      success: 0,
      fail: 0
    },
    total: 0
  }
}

function defaultDate() {
  const now = new Date()
  const start = new Date(now.getFullYear(), now.getMonth(), 1, 0, 0, 0)
  const end = new Date(now.getFullYear(), now.getMonth(), now.getDate(), 23, 59, 59)
  return [formatDate(start), formatDate(end)]
}

const state = reactive({
  infoDrawer: false,
  search: {
    keyword: null,
    module: null,
    action: null,
    date: initialDate
  },
  tableParams: {
    start_time: initialDate[0],
    end_time: initialDate[1]
  },
  options: {
    actions: [],
    modules: []
  },
  statLoading: false,
  stat: defaultStat()
})

onMounted(async () => {
  const [res] = await Promise.all([
    systemApi.auditLog.actions.get(),
    loadStat(getSearchParams())
  ])
  state.options = res.data
})

const trendOption = computed(() => {
  return {
    tooltip: {
      trigger: 'axis'
    },
    grid: {
      top: 18,
      right: 16,
      bottom: 18,
      left: 36
    },
    xAxis: {
      type: 'category',
      boundaryGap: false,
      data: state.stat.days
    },
    yAxis: {
      type: 'value',
      minInterval: 1,
      splitNumber: 3,
      axisLabel: {
        hideOverlap: true
      },
      splitLine: {
        lineStyle: {
          type: 'dashed'
        }
      }
    },
    series: [
      {
        name: '操作次数',
        type: 'line',
        smooth: true,
        symbol: 'circle',
        symbolSize: 5,
        lineStyle: {
          width: 2,
          color: '#409EFF'
        },
        areaStyle: {
          opacity: 0.12,
          color: '#409EFF'
        },
        data: state.stat.daily
      }
    ]
  }
})

const topActions = computed(() => topItems(state.stat.actions))

const topModules = computed(() => topItems(state.stat.modules))

const successRate = computed(() => {
  if (!state.stat.total) {
    return '0%'
  }

  return `${Math.round(state.stat.status.success / state.stat.total * 100)}%`
})

const showInfo = (row) => {
  state.infoDrawer = true
  nextTick(() => {
    infoRef.value.setData(row)
  })
}

// 搜索
const upSearch = async () => {
  const params = getSearchParams()
  state.tableParams = params
  table.value.upData(params, 1)
  await loadStat(params)
}

// 删除搜索
const clearSearch = async () => {
  state.search.keyword = null
  state.search.module = null
  state.search.action = null
  state.search.date = defaultDate()

  const params = getSearchParams()
  state.tableParams = params
  table.value.reload(params, 1)
  await loadStat(params)
}

const getSearchParams = () => {
  return {
    keyword: state.search.keyword,
    module: state.search.module,
    action: state.search.action,
    start_time: state.search.date?.[0] || '',
    end_time: state.search.date?.[1] || ''
  }
}

const actionLabel = (value) => optionLabel('actions', value)

const moduleLabel = (value) => optionLabel('modules', value)

const optionLabel = (group, value) => {
  const item = state.options[group]?.find(item => item.value === value)
  return item?.label || value || '-'
}

const loadStat = async (params) => {
  state.statLoading = true
  try {
    const res = await systemApi.auditLog.stat.get(params)
    state.stat = normalizeStat(res.data)
  } finally {
    state.statLoading = false
  }
}

const normalizeStat = (data = {}) => {
  const stat = defaultStat()
  return {
    days: Array.isArray(data.days) ? data.days : stat.days,
    daily: Array.isArray(data.daily) ? data.daily : stat.daily,
    actions: Array.isArray(data.actions) ? data.actions : stat.actions,
    modules: Array.isArray(data.modules) ? data.modules : stat.modules,
    status: {
      success: Number(data.status?.success || 0),
      fail: Number(data.status?.fail || 0)
    },
    total: Number(data.total || 0)
  }
}

const topItems = (items = []) => {
  return [...items]
    .sort((a, b) => Number(b.count || 0) - Number(a.count || 0))
    .slice(0, 4)
}

const rankPercent = (item, items = []) => {
  const max = Math.max(...items.map(item => Number(item.count || 0)), 1)
  return `${Math.round(Number(item.count || 0) / max * 100)}%`
}

function formatDate(date) {
  const pad = (value) => String(value).padStart(2, '0')
  return [
    date.getFullYear(),
    pad(date.getMonth() + 1),
    pad(date.getDate())
  ].join('-') + ' ' + [
    pad(date.getHours()),
    pad(date.getMinutes()),
    pad(date.getSeconds())
  ].join(':')
}

</script>

<style scoped>
.audit-log-page {
  height: 100%;
  overflow: hidden;
}

:global(.adminui-main:has(.audit-log-page)) {
  overflow: hidden;
}

.audit-log-main {
  display: flex;
  flex-direction: column;
  min-height: 0;
  overflow: hidden;
}

.audit-log-date-filter {
  width: 360px !important;
  flex: 0 0 360px;
}

.admin-crud-search-row :deep(.audit-log-date-filter.el-date-editor) {
  width: 360px !important;
  flex: 0 0 360px;
}

.audit-log-stat {
  flex: 0 0 auto;
  padding: 10px 14px;
  border-bottom: 1px solid var(--el-border-color-light);
  background: var(--el-bg-color);
}

.audit-log-table {
  flex: 1 1 auto;
  height: auto !important;
  min-height: 0;
}

.audit-log-stat-dashboard {
  display: grid;
  grid-template-columns: minmax(220px, 0.7fr) minmax(320px, 1fr) minmax(300px, 0.9fr);
  gap: 10px;
  align-items: stretch;
}

.audit-log-total-panel,
.audit-log-trend-panel,
.audit-log-rank-panel {
  height: 176px;
  box-sizing: border-box;
  border: 1px solid var(--el-border-color-light);
  border-radius: 6px;
  background: var(--el-fill-color-blank);
}

.audit-log-total-panel {
  display: flex;
  flex-direction: column;
  padding: 12px;
}

.audit-log-total-label {
  display: block;
  margin-bottom: 10px;
  color: var(--el-text-color-secondary);
  font-size: 12px;
}

.audit-log-total-panel strong {
  margin-bottom: 12px;
  color: var(--el-text-color-primary);
  font-size: 32px;
  line-height: 1.05;
}

.audit-log-status-grid {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 8px;
  margin-top: auto;
}

.audit-log-status-grid div {
  min-height: 46px;
  padding: 8px;
  border-radius: 5px;
  background: var(--el-fill-color-light);
}

.audit-log-status-grid span {
  display: block;
  margin-bottom: 6px;
  color: var(--el-text-color-secondary);
  font-size: 12px;
}

.audit-log-status-grid em {
  color: var(--el-text-color-primary);
  font-style: normal;
  font-weight: 600;
}

.audit-log-status-grid em.success {
  color: var(--el-color-success);
}

.audit-log-status-grid em.danger {
  color: var(--el-color-danger);
}

.audit-log-trend-panel,
.audit-log-rank-panel {
  padding: 12px;
}

.audit-log-stat-title {
  margin-bottom: 8px;
  color: var(--el-text-color-primary);
  font-size: 13px;
  font-weight: 600;
}

.audit-log-rank-panel {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 12px;
}

.audit-log-rank-bars {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.audit-log-rank-meta {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 4px;
  color: var(--el-text-color-regular);
  font-size: 12px;
}

.audit-log-rank-meta span {
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.audit-log-rank-meta em {
  flex: 0 0 auto;
  margin-left: 10px;
  color: var(--el-text-color-primary);
  font-style: normal;
  font-weight: 600;
}

.audit-log-rank-track {
  height: 5px;
  overflow: hidden;
  border-radius: 999px;
  background: var(--el-fill-color-light);
}

.audit-log-rank-track i {
  display: block;
  height: 100%;
  min-width: 5px;
  border-radius: 999px;
  background: var(--el-color-primary);
}

@media (max-width: 1280px) {
  .audit-log-stat-dashboard {
    grid-template-columns: minmax(220px, 0.8fr) minmax(360px, 1.2fr);
  }

  .audit-log-rank-panel {
    grid-column: 1 / -1;
  }
}

@media (max-width: 768px) {
  .audit-log-date-filter,
  .admin-crud-search-row :deep(.audit-log-date-filter.el-date-editor) {
    width: 100% !important;
    flex: 1 1 100%;
  }

  .audit-log-stat-dashboard {
    grid-template-columns: 1fr;
  }

  .audit-log-status-grid,
  .audit-log-rank-panel {
    grid-template-columns: 1fr;
  }
}
</style>
