<template>
  <el-container>
    <el-header>
      <div class="left-panel">
        <el-button type="primary" icon="el-icon-plus" @click="add"/>
        <el-button type="danger" plain icon="el-icon-delete" :disabled="!state.selection.length" @click="batchDel"/>
      </div>
      <div class="right-panel">
        <div class="right-panel-search">
          <el-input v-model="state.search.keyword" placeholder="登录账号 / 昵称 / 手机号" clearable @clear="clearSearch"/>
          <el-button type="primary" icon="el-icon-search" @click="upSearch"/>
          <el-button type="primary" icon="el-icon-download" @click="exportData"/>
        </div>
      </div>
    </el-header>
    <el-main class="nopadding">
      <sc-table
        ref="table"
        :apiObj="apiObj"
        :params="state.tableParams"
        :column="state.column"
        row-key="id"
        @selection-change="selectionChange"
        stripe
      >
        <el-table-column type="selection" width="50"/>
        <template #avatar="scope">
          <el-avatar :src="tool.resUrl(scope.row.avatar)" size="small"></el-avatar>
        </template>
        <template #role="scope">
          <span class="role-name" v-for="item in scope.row.user_role" :key="item.role_id">{{item.role.name}}</span>
        </template>
        <el-table-column label="操作" fixed="right" align="right" width="165">
          <template #default="scope">
            <el-button-group>
              <el-button text plain type="success" size="small" @click="tableShow(scope.row)">查看</el-button>
              <el-button text plain type="primary" size="small" @click="tableEdit(scope.row)">编辑</el-button>
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

</template>

<script setup>

import {nextTick, reactive, ref} from "vue"
import SaveDialog from './save'
import ScTable from "@/components/scTable/index.vue"
import systemApi from "@/api/admin/system.js";
import {ElMessage, ElMessageBox} from "element-plus";
import tool from "@/utils/tool.js";

defineOptions({
  name: 'product_list',
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
  },
  column: [
    {
      label: "ID",
      prop: "id",
      width: "80",
      sortable: true
    },
    {
      label: "头像",
      prop: "avatar",
      width: "80",
    },
    {
      label: "登录账号",
      prop: "username",
      width: "150",
    },
    {
      label: "昵称",
      prop: "nickname",
      width: "150",
    },
    {
      label: "手机号",
      prop: "mobile",
      width: "150",
    },
    {
      label: "所属角色",
      prop: "role",
      width: "200",
    },
    {
      label: "注册时间",
      prop: "created_at",
      width: "150",
      sortable: true
    }
  ]
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
  table.value.upData({
    keyword: state.search.keyword
  }, 1)
}

// 导出
const exportData = async () => {
  try{
    await ElMessageBox.confirm(`确定创建该导出任务吗？`, '提示')
  }catch (e) {
    return
  }

  const res = await systemApi.user.export.get({
    keyword: state.search.keyword
  })
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
  table.value.reload({
    keyword: ''
  }, 1)
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
</style>
