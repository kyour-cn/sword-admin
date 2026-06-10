<template>
  <el-container>
    <el-header>
      <div class="left-panel">
        <el-button v-auth="'admin.system.configForm.add'" type="primary" icon="el-icon-plus" @click="add"/>
        <el-button
          v-auth="'admin.system.configForm.delete'"
          type="danger"
          plain
          icon="el-icon-delete"
          :disabled="!state.selection.length"
          @click="batchDel"
        />
      </div>
      <div class="right-panel">
        <div class="right-panel-search config-form-search">
          <el-select v-model="state.search.status" class="status-select" placeholder="状态" clearable>
            <el-option label="启用" :value="1"/>
            <el-option label="停用" :value="0"/>
          </el-select>
          <el-input v-model="state.search.keyword" class="keyword-input" placeholder="名称 / key / 分组" clearable @clear="clearSearch"/>
          <el-button type="primary" icon="el-icon-search" @click="upSearch"/>
        </div>
      </div>
    </el-header>
    <el-main class="nopadding">
      <sc-table
        ref="table"
        :apiObj="apiObj"
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
  table.value.reload({
    keyword: "",
    status: state.search.status
  }, 1)
}

const handleSaveSuccess = () => {
  table.value.refresh()
}
</script>

<style scoped>
.config-form-search .status-select {
  width: 120px;
  min-width: 120px;
  flex: 0 0 120px;
}

.config-form-search .keyword-input {
  width: 260px;
  min-width: 180px;
  flex: 1 1 260px;
}

@media (max-width: 992px) {
  .config-form-search .status-select,
  .config-form-search .keyword-input {
    width: 100%;
    min-width: 0;
    flex: 1 1 auto;
  }
}
</style>
