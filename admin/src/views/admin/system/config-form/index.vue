<template>
  <el-container class="admin-crud-page config-form-page">
    <el-header class="admin-crud-table-header config-form-table-header">
      <div class="admin-crud-search-row config-form-search-row">
        <el-select
          v-model="state.search.status"
          class="admin-crud-base-filter status-filter"
          placeholder="状态"
          clearable
        >
          <el-option label="启用" :value="1"/>
          <el-option label="停用" :value="0"/>
        </el-select>
        <el-input
          v-model="state.search.keyword"
          class="admin-crud-keyword-filter keyword-filter"
          placeholder="名称 / key / 分组"
          clearable
          @clear="clearSearch"
        />
        <el-button type="primary" icon="el-icon-search" @click="upSearch">查询</el-button>
        <el-button icon="el-icon-refresh" @click="clearSearch">重置</el-button>
      </div>
      <div class="admin-crud-action-row config-form-action-row">
        <el-button v-auth="'admin.system.configForm.add'" type="primary" icon="el-icon-plus" @click="add">新增配置表单</el-button>
        <el-button
          v-auth="'admin.system.configForm.delete'"
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
        <el-table-column label="名称" prop="title" width="160"/>
        <el-table-column label="标识" prop="key" width="160"/>
        <el-table-column label="分组" prop="group_title" width="150">
          <template #default="scope">
            {{ scope.row.group_title }} / {{ scope.row.group_key }}
          </template>
        </el-table-column>
        <el-table-column label="状态" prop="status" width="80">
          <template #default="scope">
            <el-tag v-if="scope.row.status" type="success">启用</el-tag>
            <el-tag v-else type="info">停用</el-tag>
          </template>
        </el-table-column>
        <el-table-column label="排序" prop="sort" width="80"/>
        <el-table-column label="备注" prop="remark" min-width="180"/>
        <el-table-column label="操作" fixed="right" align="right" width="140">
          <template #default="scope">
            <el-button-group>
              <el-button v-auth="'admin.system.configForm.edit'" text plain type="primary" size="small" @click="tableEdit(scope.row)">
                编辑
              </el-button>
              <el-popconfirm title="确定删除吗？" @confirm="tableDel(scope.row)">
                <template #reference>
                  <el-button v-auth="'admin.system.configForm.delete'" text plain type="danger" size="small">删除</el-button>
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
import {ElMessage, ElMessageBox} from "element-plus"
import ScTable from "@/components/scTable"
import systemApi from "@/api/admin/system.js"
import SaveDialog from "./save"

defineOptions({
  name: "config_form",
})

const table = ref(null)
const saveDialogRef = ref(null)
const apiObj = systemApi.configForm.list

const state = reactive({
  selection: [],
  search: {
    keyword: "",
    status: ""
  },
  tableParams: {
    keyword: "",
    status: ""
  }
})

const dialog = reactive({
  save: false
})

const selectionChange = (val) => {
  state.selection = val
}

const add = () => {
  dialog.save = true
  nextTick(() => {
    saveDialogRef.value.open()
  })
}

const tableEdit = (row) => {
  dialog.save = true
  nextTick(() => {
    saveDialogRef.value.open("edit")
    saveDialogRef.value.setData(row)
  })
}

const tableDel = async (row) => {
  const res = await systemApi.configForm.delete.post({ids: [row.id]})
  if (res.code === 0) {
    table.value.refresh()
  } else {
    await ElMessageBox.alert(res.message, "提示", {type: "error"})
  }
}

const batchDel = async () => {
  try {
    await ElMessageBox.confirm(`确定删除选中的 ${state.selection.length} 项吗？`, "提示", {
      type: "warning",
      confirmButtonText: "删除",
      confirmButtonClass: "el-button--danger"
    })
  } catch (e) {
    return
  }

  const ids = state.selection.map(v => v.id)
  const res = await systemApi.configForm.delete.post({ids})
  if (res.code === 0) {
    table.value.removeKeys(ids)
    ElMessage.success("操作成功")
  } else {
    await ElMessageBox.alert(res.message, "提示", {type: "error"})
  }
}

const upSearch = () => {
  table.value.upData({
    keyword: state.search.keyword,
    status: state.search.status
  }, 1)
}

const clearSearch = () => {
  state.search.keyword = ""
  state.search.status = ""
  table.value.reload({
    keyword: "",
    status: ""
  }, 1)
}

const handleSaveSuccess = () => {
  table.value.refresh()
}
</script>
