<template>
  <el-container class="admin-crud-page role-page">
    <el-header class="admin-crud-table-header role-table-header">
      <div class="admin-crud-search-row role-search-row">
        <el-select
          v-if="state.appList.length"
          v-model="state.selectedApp"
          filterable
          placeholder="所属应用"
          class="admin-crud-base-filter role-app-filter"
          @change="filterChange"
        >
          <el-option label="全部" value=""/>
          <el-option
            v-for="item in state.appList"
            :key="item.id"
            :label="item.name"
            :value="item.id"
          />
        </el-select>
        <el-input
          v-model="state.search.keyword"
          placeholder="角色名称"
          clearable
          class="admin-crud-keyword-filter keyword-filter"
          @clear="clearSearch"
        />
        <el-button type="primary" icon="el-icon-search" @click="upSearch">查询</el-button>
        <el-button icon="el-icon-refresh" @click="clearSearch">重置</el-button>
      </div>
      <div class="admin-crud-action-row role-action-row">
        <el-button v-auth="'admin.system.role.add'" type="primary" icon="el-icon-plus" @click="add">新增角色</el-button>
        <el-button
          v-auth="'admin.system.role.delete'"
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
        v-if="state.appList.length"
        ref="table"
        :apiObj="apiObj"
        :params="state.tableParams"
        row-key="id"
        @selection-change="selectionChange"
        stripe
      >
        <el-table-column type="selection" width="50"/>
        <el-table-column label="ID" prop="id" width="100"/>
        <el-table-column label="角色名称" prop="name" width="150"/>
        <el-table-column label="管理员" prop="status" width="70">
          <template #default="scope">
            {{ scope.row.is_admin ? '是' : '否' }}
          </template>
        </el-table-column>
        <el-table-column label="状态" prop="status" width="60">
          <template #default="scope">
            <sc-status-indicator
              :pulse="!!scope.row.status"
              :type="scope.row.status? 'success': 'danger'"
            />
          </template>
        </el-table-column>
        <el-table-column label="排序" prop="sort" width="80"/>
        <el-table-column label="创建时间" prop="created_at" width="170"/>
        <el-table-column label="备注" prop="remark" min-width="150"/>
        <el-table-column label="操作" fixed="right" align="right" width="165">
          <template #default="scope">
            <el-button-group>
              <el-button text plain type="primary" size="small" @click="tableEdit(scope.row)">
                编辑
              </el-button>
              <el-button text plain type="warning" size="small" @click="openPermission(scope.row)">
                权限
              </el-button>
              <el-popconfirm title="确定删除吗？" @confirm="tableDel(scope.row)">
                <template #reference>
                  <el-button text plain type="danger" size="small">删除</el-button>
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

  <permission-dialog
    v-if="dialog.permission"
    ref="permissionDialogRef"
    :app-id="state.selectedApp"
    @getNewData="refreshTable"
    @closed="dialog.permission = false"
  />

</template>

<script setup>
import {nextTick, onMounted, reactive, ref} from "vue"
import SaveDialog from './save'
import PermissionDialog from './permission.vue'
import ScStatusIndicator from "@/components/scMini/scStatusIndicator.vue"
import ScTable from "@/components/scTable/index.vue"
import systemApi from "@/api/admin/system.js";
import {ElMessage, ElMessageBox} from "element-plus";

defineOptions({
  name: 'role',
})

const permissionDialogRef = ref(null)
const saveDialogRef = ref(null)
const table = ref(null)

const state = reactive({
  selection: [],
  search: {
    keyword: null
  },
  appList: [],
  selectedApp: '',
  tableParams: {
    app_id: '',
    keyword: null
  }
})

const apiObj = systemApi.role.list

const dialog = reactive({
  save: false,
  permission: false
})

onMounted(() => {
  getApp()
})

const getApp = async () => {
  const res = await systemApi.app.list.get({page: 1, page_size: 500});
  if (res.code !== 0) {
    await ElMessageBox.alert(res.message, "提示", {type: 'error'});
    return
  }

  state.selectedApp = ''
  state.tableParams.app_id = ''
  state.appList = res.data.rows
}

const refreshTable = () => {
  table.value.refresh()
}

const filterChange = (appId) => {
  table.value.upData({
    app_id: appId
  }, 1)
}

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
  const res = await systemApi.role.delete.post({
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
  const res = await systemApi.role.delete.post({ids})
  if (res.code === 0) {
    table.value.removeKeys(ids)
    ElMessage.success("操作成功");
  } else {
    await ElMessageBox.alert(res.message, "提示", {type: 'error'});
  }
}

//权限设置
const openPermission = (row) => {
  dialog.permission = true
  nextTick(() => {
    permissionDialogRef.value.open(row)
  })
}

//搜索
const upSearch = () => {
  table.value.upData({
    app_id: state.selectedApp,
    keyword: state.search.keyword
  }, 1)
}

// 删除搜索
const clearSearch = () => {
  state.search.keyword = null
  state.selectedApp = ''
  table.value.reload({
    app_id: '',
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
