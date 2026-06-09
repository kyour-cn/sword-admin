<template>
  <el-container>
    <el-aside width="220px" style="border-radius: 10px 0 0 0;">
      <el-tree
        ref="category"
        class="menu"
        node-key="label"
        :data="state.category"
        :default-expanded-keys="['app', 'system']"
        :highlight-current="true"
        :expand-on-click-node="false"
        @current-change="handleCurrentChange"
      >
        <template #default="{node, data}">
          <span class="custom-tree-node">
            <sc-status-indicator
              v-if="data.color"
              type="success"
              :style="{background: data.color, marginRight: '5px'}"
            />
            <span class="label">
              {{ data.name }}
            </span>
          </span>
        </template>
      </el-tree>
    </el-aside>
    <el-container>
      <el-main class="nopadding">
        <el-container>
          <el-header>
            <div class="left-panel">
              <el-date-picker
                v-model="state.date"
                format="YYYY-MM-DD HH:mm:ss"
                type="datetimerange"
                range-separator="至"
                start-placeholder="开始日期"
                end-placeholder="结束日期"
                @change="timeChange"
              />
            </div>
            <div class="right-panel">

            </div>
          </el-header>
          <el-header style="height:150px;">
            <scEcharts height="100%" width="100%" :option="state.logsChartOption"/>
          </el-header>
          <el-main class="nopadding">
            <scTable
              ref="tableRef"
              :apiObj="state.apiObj"
              :params="{start_time:state.date?.[0] || null,end_time:state.date?.[1] || null}"
              stripe
              highlightCurrentRow @row-click="rowClick">
              <el-table-column label="ID" prop="id" width="100"/>
              <el-table-column label="名称" prop="type_name" width="100"/>
              <el-table-column label="标题" show-overflow-tooltip prop="title"/>
              <el-table-column label="请求来源" show-overflow-tooltip prop="request_source"
                               width="250"/>
              <el-table-column label="请求ip" prop="request_ip" width="150"/>
              <el-table-column label="用户" prop="request_user" width="100"/>
              <el-table-column label="请求时间" prop="created_at" width="170"/>
            </scTable>
          </el-main>
        </el-container>
      </el-main>
    </el-container>
  </el-container>

  <el-drawer v-model="state.infoDrawer" title="日志详情" :size="600" destroy-on-close>
    <info ref="infoRef"/>
  </el-drawer>
</template>

<script setup>

import {ref, reactive, onMounted, nextTick} from "vue"
import info from './info'
import scEcharts from '@/components/scEcharts'
import ScStatusIndicator from "@/components/scMini/scStatusIndicator.vue";
import scTable from "@/components/scTable/index.vue";
import systemApi from "@/api/admin/system.js";
import tool from '@/utils/tool'
import {ElMessage} from "element-plus";

// 定义组件名称
defineOptions({
  name: 'log',
})

const getCurrentMonthFirst = () => {
  let date = new Date()
  date.setDate(1)
  date.setHours(0, 0, 0, 0)
  return date
}

const tableRef = ref(null)
const infoRef = ref(null)

const state = reactive({
  map: [],
  dateMaps: [],
  infoDrawer: false,
  echartsData: [],
  // 动态图表数据
  seriesData: [],
  logsChartOption: {
    color: ['#409eff', '#e6a23c', '#f56c6c'],
    grid: {
      top: '0px',
      left: '10px',
      // right: '10px',
      bottom: '0px'
    },
    tooltip: {
      show: true,
      trigger: 'axis',
      formatter: (params) => {
        const xAxis_val = `${params[0].axisValue}</br>`
        const result = params.map((p, i) => {
          const color = p.color || '#000'
          return `
            <span style="display:inline-block;margin-right:5px;border-radius:10px;width:10px;height:10px;background-color:${color}"></span>
            ${p.seriesName}:${p.data}</br>
        `
        }).join('')
        return xAxis_val + result
      }
    },
    xAxis: {
      type: 'category',
      boundaryGap: false,
      data: ['']
    },
    yAxis: {
      show: false,
      type: 'value'
    },
    series: []
  },
  category: [],
  types: [],
  date: [
    tool.dateFormat(getCurrentMonthFirst()),
    tool.dateFormat(new Date()),
  ],
  apiObj: systemApi.log.list,
  search: {
    keyword: ""
  }
})

// 左侧树形菜单
const renderTreeMenu = (data) => {
  const sysList = []
  const appList = []
  for (const i in data) {
    const item = data[i]
    if (item.app_id > 0) {
      appList.push(item)
    } else {
      sysList.push(item)
    }
  }

  return [
    {
      id: 1,
      name: "系统日志",
      label: "system",
      children: sysList
    },
    {
      id: 2,
      name: "应用日志",
      label: "app",
      children: appList
    },
  ]
}

const echartsRender = async () => {
  state.seriesData = []
  state.map = []

  const start_time = tool.dateFormat(state.date[0])
  const end_time = tool.dateFormat(state.date[1])

  let res = await systemApi.log.logStat.get({start_time, end_time});

  const dateMaps = {}
  for (const i in res.data.days) {
    dateMaps[res.data.days[i]] = i
  }
  // 填充x轴的数据
  state.logsChartOption.xAxis.data = res.data.days.length ? res.data.days : [0]

  // 填充图表数据
  if (res.data.rows.length !== 0) {

    let seriesData = {}
    const typeMap = {}
    for (const i in state.types) {
      typeMap[String(state.types[i].id)] = state.types[i]
    }

    for (const i in res.data.rows) {
      const item = res.data.rows[i]
      if (seriesData[item.type_id]) {
        continue
      }
      seriesData[item.type_id] = {
        id: item.type_id,
        name: item.type_name,
        color: typeMap[String(item.type_id)].color,
        data: arrayPad([], res.data.days.length, 0)
      }
    }

    // 填充堆叠图表数据
    for (const key in res.data.rows) {
      const item = res.data.rows[key]
      seriesData[item.type_id].data[dateMaps[item.date]] = item.count
    }

    state.map = []
    for (const key in seriesData) {
      state.map.push(seriesData[key])
    }

    for (const key in state.map) {
      state.seriesData.push({
        name: state.map[key].name,
        data: state.map[key].data,
        type: 'bar',
        stack: 'log',
        barWidth: '15px',
        color: state.map[key].color
      })
    }
    state.logsChartOption.series = state.seriesData
  } else {
    state.seriesData = [];
    state.logsChartOption.series = [];
    state.logsChartOption.xAxis.data = [0];
    ElMessage.success("操作成功");
  }
}

const arrayPad = (arr, len, val) => {
  if (arr.length >= len) {
    return arr
  }
  return arr.concat(Array(len - arr.length).fill(val))
}

onMounted(() => {
  systemApi.log.typeList.get({page: 1,page_size: 500}).then((res) => {
    state.types = res.data.rows
    state.category = renderTreeMenu(res.data.rows)
    echartsRender();
  })
})

const timeChange = () => {
  // 点击时间清除判断是否为null
  if (state.date !== null) {
    tableRef.value.upData({
      start_time: tool.dateFormat(state.date[0]),
      end_time: tool.dateFormat(state.date[1])
    })
    echartsRender();
  } else {
    tableRef.value.upData({
      start_time: '',
      end_time: ''
    })
    echartsRender();
  }
}

const rowClick = (row) => {
  state.infoDrawer = true
  nextTick(() => {
    infoRef.value.setData(row)
  })
}
const handleCurrentChange = (data) => {
  tableRef.value.upData({
    type_id: data.id
  })
}

</script>
