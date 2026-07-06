<template>
  <el-container class="admin-crud-page app-page">
    <el-header class="admin-crud-table-header app-table-header">
      <div class="admin-crud-search-row app-search-row">
        <el-input
          v-model="state.search.keyword"
          placeholder="应用名称"
          clearable
          class="admin-crud-keyword-filter keyword-filter"
          @clear="clearSearch"
        />
        <el-button type="primary" icon="el-icon-search" @click="upSearch">查询</el-button>
        <el-button icon="el-icon-refresh" @click="clearSearch">重置</el-button>
      </div>
      <div class="admin-crud-action-row app-action-row">
        <el-button v-auth="'admin.system.app.add'" type="primary" icon="el-icon-plus" @click="add">新增应用</el-button>
        <el-button
          v-auth="'admin.system.app.delete'"
          type="danger"
          plain
          icon="el-icon-delete"
          :disabled="!state.selection.length"
          @click="batchDel"
        >
          批量删除
        </el-button>
      </div>
    </el-header>
    <el-main class="nopadding">
      <sc-table
        ref="table"
        :apiObj="apiObj"
        :params="state.tableParams"
        row-key="id"
        @selection-change="selectionChange"
        stripe
      >
        <el-table-column type="selection" width="50"/>
        <el-table-column label="名称" prop="name" width="180"/>
        <el-table-column label="应用key" prop="key" width="180"/>
        <el-table-column label="状态" prop="status" width="60">
          <template #default="scope">
            <sc-status-indicator v-if="scope.row.status" type="success"></sc-status-indicator>
            <sc-status-indicator v-if="!scope.row.status" pulse type="danger"></sc-status-indicator>
          </template>
        </el-table-column>
        <el-table-column label="备注信息" prop="remark"/>
        <el-table-column label="操作" fixed="right" align="right" width="120">
          <template #default="scope">
            <el-button-group>
              <el-button v-auth="'admin.system.app.edit'" text plain type="primary" size="small"
               @click="tableEdit(scope.row)">
                编辑
              </el-button>
              <el-popconfirm title="确定删除吗？" @confirm="tableDel(scope.row)">
                <template #reference>
                  <el-button v-auth="'admin.system.app.delete'" text plain type="danger" size="small">删除</el-button>
                </template>
              </el-popconfirm>
            </el-button-group>
          </template>
        </el-table-column>
      </sc-table>
    </el-main>
  </el-container>

  <save-dialog
    v-if="dialog.save"
    ref="saveDialogRef"
    @success="handleSaveSuccess"
    @closed="dialog.save=false"
  />

</template>

<script setup>
import {nextTick, reactive, ref} from "vue"
import SaveDialog from './save'
import ScStatusIndicator from "@/components/scMini/scStatusIndicator.vue"
import ScTable from "@/components/scTable/index.vue"
import systemApi from "@/api/admin/system.js";
import {ElMessage, ElMessageBox} from "element-plus";

defineOptions({
  name: 'app',
})

const saveDialogRef = ref(null)
const table = ref(null)

const state = reactive({
  selection: [],
  search: {
    keyword: null
  },
  tableParams: {
    keyword: null
  }
})

const apiObj = systemApi.app.list

const dialog = reactive({
  save: false,
  info: false
})

const selectionChange = (val) => {
  state.selection = val
}

//添加
const add = () => {
  dialog.save = true
  nextTick(() => {
    saveDialogRef.value.open()
  })
}

const tableEdit = (row) => {
  dialog.save = true
  nextTick(() => {
    saveDialogRef.value.open('edit')
    saveDialogRef.value.setData(row)
  })
}

//删除
const tableDel = async (row) => {
  const res = await systemApi.app.delete.post({
    ids: [row.id]
  })
  if (res.code === 0) {
    table.value.upData()
  }
}

//批量删除
const batchDel = async () => {
  try{
    await ElMessageBox.confirm(`确定删除选中的 ${state.selection.length} 项吗？`, '提示', {
      type: 'warning',
      confirmButtonText: '删除',
      confirmButtonClass: 'el-button--danger'
    })
  }catch (e) {
    return
  }

  const ids = state.selection.map(v => v.id)
  const res = await systemApi.app.delete.post({ids})
  if (res.code === 0) {
    table.value.removeKeys(ids)
    ElMessage.success("操作成功");
  } else {
    await ElMessageBox.alert(res.message, "提示", {type: 'error'});
  }
}

//搜索
const upSearch = () => {
  table.value.upData({
    keyword: state.search.keyword
  }, 1)
}

// 删除搜索
const clearSearch = () => {
  state.search.keyword = null
  table.value.reload({
    keyword: ''
  }, 1)
}

//本地更新数据
const handleSaveSuccess = (data, mode) => {
  if (mode === 'add') {
    table.value.refresh()
  } else if (mode === 'edit') {
    table.value.refresh()
  }
}

</script>
