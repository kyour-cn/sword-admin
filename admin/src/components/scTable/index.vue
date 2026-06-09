<!--
 * @Descripttion: 数据表格组件
 * @version: 1.11
 * @Author: sakuya
 * @Date: 2021年11月29日21:51:15
 * @LastEditors: sakuya
 * @LastEditTime: 2023年3月2日10:43:35
-->

<template>
  <div class="scTable" :style="{'height':_height}" ref="scTableMain" v-loading="loading">
    <div class="scTable-table" :style="{'height':_table_height}">
      <el-table v-bind="$attrs" :data="tableData" :row-key="rowKey" :key="toggleIndex" ref="scTable" :height="height=='auto'?null:'100%'" :size="config.size" :border="config.border" :stripe="config.stripe" :summary-method="remoteSummary?remoteSummaryMethod:summaryMethod" @sort-change="sortChange" @filter-change="filterChange">
        <slot></slot>
        <template v-for="(item, index) in userColumn" :key="index">
          <el-table-column v-if="!item.hide" :column-key="item.prop" :label="item.label" :prop="item.prop" :width="item.width" :sortable="item.sortable" :fixed="item.fixed" :filters="item.filters" :filter-method="remoteFilter||!item.filters?null:filterHandler" :show-overflow-tooltip="item.showOverflowTooltip">
            <template #default="scope">
              <slot :name="item.prop" v-bind="scope">
                {{scope.row[item.prop]}}
              </slot>
            </template>
          </el-table-column>
        </template>
        <el-table-column min-width="1"/>
        <template #empty>
          <el-empty :description="emptyText" :image-size="100"></el-empty>
        </template>
      </el-table>
    </div>
    <div class="scTable-page" v-if="!hidePagination || !hideDo">
      <div class="scTable-pagination">
        <el-pagination v-if="!hidePagination" background size="small" :layout="paginationLayout" :total="total" :page-size="scPageSize" :page-sizes="pageSizes" v-model:currentPage="currentPage" @current-change="paginationChange" @update:page-size="pageSizeChange"></el-pagination>
      </div>
      <div class="scTable-do" v-if="!hideDo">
        <el-button v-if="!hideRefresh" @click="refresh" icon="el-icon-refresh" circle style="margin-left:15px"></el-button>
        <el-popover v-if="column" placement="top" title="列设置" :width="500" trigger="click" :hide-after="0" @show="customColumnShow=true" @after-leave="customColumnShow=false">
          <template #reference>
            <el-button icon="el-icon-set-up" circle style="margin-left:15px"></el-button>
          </template>
          <columnSetting v-if="customColumnShow" ref="columnSettingRef" @userChange="columnSettingChange" @save="columnSettingSave" @back="columnSettingBack" :column="userColumn"></columnSetting>
        </el-popover>
        <el-popover v-if="!hideSetting" placement="top" title="表格设置" :width="400" trigger="click" :hide-after="0">
          <template #reference>
            <el-button icon="el-icon-setting" circle style="margin-left:15px"></el-button>
          </template>
          <el-form label-width="80px" label-position="left">
            <el-form-item label="表格尺寸">
              <el-radio-group v-model="config.size" size="small" @change="configSizeChange">
                <el-radio-button label="large" value="large">大</el-radio-button>
                <el-radio-button label="default" value="default">正常</el-radio-button>
                <el-radio-button label="small" value="small">小</el-radio-button>
              </el-radio-group>
            </el-form-item>
            <el-form-item label="样式">
              <el-checkbox v-model="config.border" label="纵向边框"></el-checkbox>
              <el-checkbox v-model="config.stripe" label="斑马纹"></el-checkbox>
            </el-form-item>
          </el-form>
        </el-popover>
      </div>
    </div>
  </div>
</template>

<script setup>
import {computed, getCurrentInstance, onActivated, onDeactivated, onMounted, ref, watch} from 'vue'
import tableConfig from "@/config/table";
import columnSetting from './columnSetting'
import {ElMessage} from "element-plus";

const { proxy } = getCurrentInstance()

  const props = defineProps({
    tableName: { type: String, default: "" },
    apiObj: { type: Object, default: () => {} },
    params: { type: Object, default: () => ({}) },
    data: { type: Object, default: () => {} },
    height: { type: [String,Number], default: "100%" },
    size: { type: String, default: "default" },
    border: { type: Boolean, default: false },
    stripe: { type: Boolean, default: false },
    pageSize: { type: Number, default: tableConfig.pageSize },
    pageSizes: { type: Array, default: tableConfig.pageSizes },
    rowKey: { type: String, default: "" },
    summaryMethod: { type: Function, default: null },
    column: { type: Object, default: () => {} },
    remoteSort: { type: Boolean, default: false },
    remoteFilter: { type: Boolean, default: false },
    remoteSummary: { type: Boolean, default: false },
    hidePagination: { type: Boolean, default: false },
    hideDo: { type: Boolean, default: false },
    hideRefresh: { type: Boolean, default: false },
    hideSetting: { type: Boolean, default: false },
    paginationLayout: { type: String, default: tableConfig.paginationLayout },
  })

  const emit = defineEmits(['dataChange'])

  const scPageSize = ref(props.pageSize)
  const isActivat = ref(true)
  const emptyText = ref("暂无数据")
  const toggleIndex = ref(0)
  const tableData = ref([])
  const total = ref(0)
  const currentPage = ref(1)
  const prop = ref(null)
  const order = ref(null)
  const loading = ref(false)
  const tableParams = ref(props.params)
  const userColumn = ref([])
  const customColumnShow = ref(false)
  const summary = ref({})
  const config = ref({
    size: props.size,
    border: props.border,
    stripe: props.stripe
  })

  const scTableMain = ref(null)
  const scTable = ref(null)
  const columnSettingRef = ref(null)

  const _height = computed(() => {
    return Number(props.height)?Number(props.height)+'px':props.height
  })

  const _table_height = computed(() => {
    return props.hidePagination && props.hideDo ? "100%" : "calc(100% - 50px)"
  })

  //监听从props里拿到值了
  watch(() => props.data, () => {
    tableData.value = props.data;
    total.value = tableData.value.length;
  })

  watch(() => props.apiObj, () => {
    tableParams.value = props.params;
    refresh();
  })

  watch(() => props.column, () => {
    userColumn.value = props.column;
  })

  //获取列
  const getCustomColumn = async () => {
    userColumn.value = await tableConfig.columnSettingGet(props.tableName, props.column)
  }

  //获取数据
  const getData = async () => {
    loading.value = true;
    const reqData = {
      [tableConfig.request.page]: currentPage.value,
      [tableConfig.request.pageSize]: scPageSize.value,
      [tableConfig.request.prop]: prop.value,
      [tableConfig.request.order]: order.value
    }
    if(props.hidePagination){
      delete reqData[tableConfig.request.page]
      delete reqData[tableConfig.request.pageSize]
    }
    Object.assign(reqData, tableParams.value)

    let res,response;
    try {
      res = await props.apiObj.get(reqData);
    }catch(error){
      _clearData()
      loading.value = false;
      emptyText.value = error.statusText;
      return false;
    }
    try {
      response = tableConfig.parseData(res);
    }catch(error){
      _clearData()
      loading.value = false;
      emptyText.value = "数据格式错误";
      return false;
    }
    if(response.code !== tableConfig.successCode){
      _clearData()
      loading.value = false;
      emptyText.value = response.msg;
    }else{
      emptyText.value = "暂无数据";
      if(props.hidePagination){
        tableData.value = response.data || [];
      }else{
        tableData.value = response.rows || [];
      }
      total.value = response.total || 0;
      summary.value = response.summary || {};
      loading.value = false;
    }
    scTable.value.setScrollTop(0)
    emit('dataChange', res, tableData.value)
  }

  //清空数据
  const _clearData = () => {
    tableData.value = []
  }

  //分页点击
  const paginationChange = () => {
    getData();
  }

  //条数变化
  const pageSizeChange = (size) => {
    scPageSize.value = size
    getData();
  }

  //刷新数据
  const refresh = () => {
    scTable.value.clearSelection();
    getData();
  }

  //更新数据 合并上一次params
  const upData = (params, page=1) => {
    currentPage.value = page;
    scTable.value.clearSelection();
    Object.assign(tableParams.value, params || {})
    getData()
  }

  //重载数据 替换params
  const reload = (params, page=1) => {
    currentPage.value = page;
    tableParams.value = params || {}
    scTable.value.clearSelection();
    scTable.value.clearSort()
    scTable.value.clearFilter()
    getData()
  }

  //自定义变化事件
  const columnSettingChange = (userColumnData) => {
    userColumn.value = userColumnData;
    toggleIndex.value += 1;
  }

  //自定义列保存
  const columnSettingSave = async (userColumnData) => {
    columnSettingRef.value.isSave = true
    try {
      await tableConfig.columnSettingSave(props.tableName, userColumnData)
    }catch(error){
      ElMessage.error('保存失败')
      columnSettingRef.value.isSave = false
    }
    proxy.$message.success('保存成功')
    columnSettingRef.value.isSave = false
  }

  //自定义列重置
  const columnSettingBack = async () => {
    columnSettingRef.value.isSave = true
    try {
      userColumn.value = await tableConfig.columnSettingReset(props.tableName, props.column)
      columnSettingRef.value.usercolumn = JSON.parse(JSON.stringify(userColumn.value||[]))
    }catch(error){
      ElMessage.error('重置失败')
      columnSettingRef.value.isSave = false
    }
    columnSettingRef.value.isSave = false
  }

  //排序事件
  const sortChange = (obj) => {
    if(!props.remoteSort){
      return false
    }
    if(obj.column && obj.prop){
      prop.value = obj.prop
      order.value = obj.order
    }else{
      prop.value = null
      order.value = null
    }
    getData()
  }

  //本地过滤
  const filterHandler = (value, row, column) => {
    const property = column.property;
    return row[property] === value;
  }

  //过滤事件
  const filterChange = (filters) => {
    if(!props.remoteFilter){
      return false
    }
    Object.keys(filters).forEach(key => {
      filters[key] = filters[key].join(',')
    })
    upData(filters)
  }

  //远程合计行处理
  const remoteSummaryMethod = (param) => {
    const {columns} = param
    const sums = []
    columns.forEach((column, index) => {
      if(index === 0) {
        sums[index] = '合计'
        return
      }
      const values =  summary.value[column.property]
      if(values){
        sums[index] = values
      }else{
        sums[index] = ''
      }
    })
    return sums
  }

  const configSizeChange = () => {
    scTable.value.doLayout()
  }

  //插入行 unshiftRow
  const unshiftRow = (row) => {
    tableData.value.unshift(row)
  }

  //插入行 pushRow
  const pushRow = (row) => {
    tableData.value.push(row)
  }

  //根据key覆盖数据
  const updateKey = (row, rowKey=props.rowKey) => {
    tableData.value.filter(item => item[rowKey]===row[rowKey] ).forEach(item => {
      Object.assign(item, row)
    })
  }

  //根据index覆盖数据
  const updateIndex = (row, index) => {
    Object.assign(tableData.value[index], row)
  }

  //根据index删除
  const removeIndex = (index) => {
    tableData.value.splice(index, 1)
  }

  //根据index批量删除
  const removeIndexes = (indexes=[]) => {
    indexes.forEach(index => {
      tableData.value.splice(index, 1)
    })
  }

  //根据key删除
  const removeKey = (key, rowKey=props.rowKey) => {
    tableData.value.splice(tableData.value.findIndex(item => item[rowKey]===key), 1)
  }

  //根据keys批量删除
  const removeKeys = (keys=[], rowKey=props.rowKey) => {
    keys.forEach(key => {
      tableData.value.splice(tableData.value.findIndex(item => item[rowKey]===key), 1)
    })
  }

  //原生方法转发
  const clearSelection = () => {
    scTable.value.clearSelection()
  }

  const toggleRowSelection = (row, selected) => {
    scTable.value.toggleRowSelection(row, selected)
  }

  const toggleAllSelection = () => {
    scTable.value.toggleAllSelection()
  }

  const toggleRowExpansion = (row, expanded) => {
    scTable.value.toggleRowExpansion(row, expanded)
  }

  const setCurrentRow = (row) => {
    scTable.value.setCurrentRow(row)
  }

  const clearSort = () => {
    scTable.value.clearSort()
  }

  const clearFilter = (columnKey) => {
    scTable.value.clearFilter(columnKey)
  }

  const doLayout = () => {
    scTable.value.doLayout()
  }

  const sort = (prop, order) => {
    scTable.value.sort(prop, order)
  }

  onMounted(() => {
    //判断是否开启自定义列
    if(props.column){
      getCustomColumn()
    }else{
      userColumn.value = props.column
    }
    //判断是否静态数据
    if(props.apiObj){
      getData();
    }else if(props.data){
      tableData.value = props.data;
      total.value = tableData.value.length
    }
  })

  onActivated(() => {
    if(!isActivat.value){
      scTable.value.doLayout()
    }
  })

  onDeactivated(() => {
    isActivat.value = false;
  })

  // 暴露方法给父组件调用
  defineExpose({
    refresh,
    upData,
    reload,
    unshiftRow,
    pushRow,
    updateKey,
    updateIndex,
    removeIndex,
    removeIndexes,
    removeKey,
    removeKeys,
    clearSelection,
    toggleRowSelection,
    toggleAllSelection,
    toggleRowExpansion,
    setCurrentRow,
    clearSort,
    clearFilter,
    doLayout,
    sort
  })
</script>

<style scoped>
  .scTable {}
  .scTable-table {height: calc(100% - 50px);}
  .scTable-page {height:50px;display: flex;align-items: center;justify-content: space-between;padding:0 15px;}
  .scTable-do {white-space: nowrap;}
  .scTable:deep(.el-table__footer) .cell {font-weight: bold;}
  .scTable:deep(.el-table__body-wrapper) .el-scrollbar__bar.is-horizontal {height: 12px;border-radius: 12px;}
  .scTable:deep(.el-table__body-wrapper) .el-scrollbar__bar.is-vertical {width: 12px;border-radius: 12px;}

  .scTable:deep(.el-table thead) {
    border-radius: 5px;
  }
  .scTable:deep(.el-table th.el-table__cell) {
    background: var(--el-color-primary-light-9) !important;
  }

  .scTable:deep(.el-table__inner-wrapper::before) {
    display: none;
  }
</style>
