<template>
  <el-container class="admin-crud-page user-page">
    <el-header class="admin-crud-table-header user-table-header">
      <div class="admin-crud-search-row user-search-row">
        <el-input
          v-model="state.search.keyword"
          placeholder="登录账号 / 昵称 / 手机号"
          clearable
          class="admin-crud-keyword-filter keyword-filter"
          @clear="clearSearch"
        />
        <el-date-picker
          v-model="state.search.date"
          type="daterange"
          value-format="YYYY-MM-DD"
          format="YYYY-MM-DD"
          range-separator="至"
          start-placeholder="注册开始"
          end-placeholder="注册结束"
          class="admin-crud-date-filter date-filter"
        />
        <el-button type="primary" icon="el-icon-search" @click="upSearch">查询</el-button>
        <el-button icon="el-icon-refresh" @click="clearSearch">重置</el-button>
      </div>
      <div class="admin-crud-action-row user-action-row">
        <el-button v-auth="'admin.system.user.add'" type="primary" icon="el-icon-plus" @click="add">新增用户</el-button>
        <el-button
          v-auth="'admin.system.user.delete'"
          type="danger"
          plain
          icon="el-icon-delete"
          :disabled="!state.selection.length"
          @click="batchDel"
        >
          批量删除
        </el-button>
        <el-button v-auth="'admin.system.user.export'" icon="el-icon-download" @click="exportData">导出</el-button>
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
        <el-table-column label="ID" prop="id" width="90" sortable/>
        <el-table-column label="头像" prop="avatar" width="88" align="center" header-align="center">
          <template #default="scope">
            <div class="avatar-cell">
              <el-avatar :src="tool.resUrl(scope.row.avatar)" :size="30"/>
            </div>
          </template>
        </el-table-column>
        <el-table-column label="登录账号" prop="username" min-width="150" show-overflow-tooltip/>
        <el-table-column label="昵称" prop="nickname" min-width="150" show-overflow-tooltip/>
        <el-table-column label="手机号" prop="mobile" width="140" show-overflow-tooltip/>
        <el-table-column label="所属角色" prop="role" min-width="180" show-overflow-tooltip>
          <template #default="scope">
            <span class="role-name" v-for="item in scope.row.user_role" :key="item.role_id">{{ item.role.name }}</span>
          </template>
        </el-table-column>
        <el-table-column label="状态" prop="status" width="80">
          <template #default="scope">
            <sc-status-indicator v-if="scope.row.status" type="success"></sc-status-indicator>
            <sc-status-indicator v-else pulse type="danger"></sc-status-indicator>
          </template>
        </el-table-column>
        <el-table-column label="注册时间" prop="created_at" width="170" sortable/>
        <el-table-column label="操作" fixed="right" align="right" width="165">
          <template #default="scope">
            <el-button-group>
              <el-button text plain type="success" size="small" @click="tableShow(scope.row)">查看</el-button>
              <el-button v-auth="'admin.system.user.edit'" text plain type="primary" size="small" @click="tableEdit(scope.row)">编辑</el-button>
              <el-popconfirm title="确定删除吗？" @confirm="tableDel(scope.row)">
                <template #reference>
                  <el-button v-auth="'admin.system.user.delete'" text plain type="danger" size="small">删除</el-button>
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
import tool from "@/utils/tool.js";

defineOptions({
  name: 'user',
})

const saveDialogRef = ref(null)
const table = ref(null)

const state = reactive({
  selection: [],
  search: {
    keyword: null,
    date: []
  },
  tableParams: {
    keyword: null,
    start_time: null,
    end_time: null
  }
})

const apiObj = systemApi.user.list

const dialog = reactive({
  save: false
})

const selectionChange = (val) => {
  state.selection = val
}

const tableShow = (row) => {
  dialog.save = true
  nextTick(() => {
    saveDialogRef.value.open('show')
    saveDialogRef.value.setData(row)
  })
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
  const res = await systemApi.user.delete.post({
    ids: [row.id]
  })
  if (res.code === 0) {
    ElMessage.success("操作成功");
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
  const res = await systemApi.user.delete.post({ids})
  if (res.code === 0) {
    table.value.removeKeys(ids)
    ElMessage.success("操作成功");
  } else {
    await ElMessageBox.alert(res.message, "提示", {type: 'error'});
  }
}

// 搜索
const upSearch = () => {
  table.value.upData(getSearchParams(), 1)
}

// 导出
const exportData = async () => {
  try{
    await ElMessageBox.confirm(`确定创建该导出任务吗？`, '提示')
  }catch (e) {
    return
  }

  const res = await systemApi.user.export.get(getSearchParams())
  if (res.code === 0) {
    ElMessageBox({
      title: "成功发起任务",
      message: `<div><img style="height:200px" src="admin/img/tasks-example.png"/></div><p>已成功发起导出任务，您可以继续其他事务</p><p>稍后可在 <b>任务中心</b> 查看执行结果</p>`,
      type: "success",
      confirmButtonText: "知道了",
      dangerouslyUseHTMLString: true,
      center: true
    }).catch(() => {})
  } else {
    await ElMessageBox.alert(res.message, "提示", {type: 'error'});
  }
}

// 删除搜索
const clearSearch = () => {
  state.search.keyword = null
  state.search.date = []
  table.value.reload({
    keyword: '',
    start_time: '',
    end_time: ''
  }, 1)
}

const getSearchParams = () => {
  return {
    keyword: state.search.keyword,
    start_time: state.search.date?.[0] ? `${state.search.date[0]} 00:00:00` : '',
    end_time: state.search.date?.[1] ? `${state.search.date[1]} 23:59:59` : ''
  }
}

//本地更新数据
const handleSaveSuccess = () => {
  table.value.refresh()
}

</script>

<style scoped>
.role-name {
  display: inline-block;
  margin-right: 5px;
}

.avatar-cell {
  display: flex;
  align-items: center;
  justify-content: center;
}
</style>
