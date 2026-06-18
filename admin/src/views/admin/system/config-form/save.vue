<template>
  <el-dialog
    v-model="state.visible"
    class="config-form-dialog"
    destroy-on-close
    :title="state.titleMap[state.mode]"
    :width="1100"
    @closed="$emit('closed')"
  >
    <el-scrollbar max-height="68vh">
      <el-form
        ref="dialogForm"
        class="config-form-editor"
        :model="state.form"
        :rules="state.rules"
        label-position="top"
      >
        <div class="form-section">
          <div class="section-title">基础信息</div>
          <el-row :gutter="16">
            <el-col :span="12">
              <el-form-item label="名称" prop="title">
                <el-input v-model="state.form.title" placeholder="请输入名称" clearable/>
              </el-form-item>
            </el-col>
            <el-col :span="12">
              <el-form-item label="标识" prop="key">
                <el-input v-model="state.form.key" placeholder="请输入唯一标识" clearable/>
                <div class="el-form-item-msg">
                  标识会作为配置表单唯一KEY；如需自定义配置页与 renderers/index.js 中组件映射KEY一致。
                </div>
              </el-form-item>
            </el-col>
            <el-col :span="12">
              <el-form-item label="分组标识" prop="group_key">
                <el-input v-model="state.form.group_key" placeholder="如 base" clearable/>
              </el-form-item>
            </el-col>
            <el-col :span="12">
              <el-form-item label="分组名称" prop="group_title">
                <el-input v-model="state.form.group_title" placeholder="如 基础配置" clearable/>
              </el-form-item>
            </el-col>
            <el-col :span="24">
              <el-form-item label="备注">
                <el-input v-model="state.form.remark" type="textarea" :rows="2" placeholder="请输入备注"/>
              </el-form-item>
            </el-col>
          </el-row>
        </div>

        <div class="form-section form-section--compact">
          <div class="section-title">表单设置</div>
          <el-row :gutter="16">
            <el-col :span="7">
              <el-form-item label="标签宽度">
                <el-input v-model="state.form.schema.labelWidth" placeholder="120px"/>
              </el-form-item>
            </el-col>
            <el-col :span="7">
              <el-form-item label="标签位置">
                <el-select v-model="state.form.schema.labelPosition" style="width: 100%;">
                  <el-option label="左侧" value="left"/>
                  <el-option label="右侧" value="right"/>
                  <el-option label="顶部" value="top"/>
                </el-select>
              </el-form-item>
            </el-col>
            <el-col :span="5">
              <el-form-item label="排序" prop="sort">
                <el-input-number v-model="state.form.sort" controls-position="right" style="width: 100%;"/>
              </el-form-item>
            </el-col>
            <el-col :span="5">
              <el-form-item label="状态" prop="status">
                <el-switch v-model="state.form.status" :active-value="1" :inactive-value="0"/>
              </el-form-item>
            </el-col>
          </el-row>
        </div>

        <div class="form-section form-section--fields">
          <div class="section-title">字段配置</div>
          <sc-form-table v-model="state.form.schema.formItems" :addTemplate="fieldTemplate" drag-sort placeholder="暂无字段">
            <el-table-column label="名称" width="150">
              <template #default="scope">
                <el-input v-model="scope.row.label" placeholder="字段名称"/>
              </template>
            </el-table-column>
            <el-table-column label="标识" width="150">
              <template #default="scope">
                <el-input v-model="scope.row.name" :disabled="scope.row.component === 'title'" placeholder="字段标识"/>
              </template>
            </el-table-column>
            <el-table-column label="组件" width="130">
              <template #default="scope">
                <el-select v-model="scope.row.component" style="width: 100%;" @change="componentChange(scope.row)">
                  <el-option v-for="item in componentOptions" :key="item.value" :label="item.label" :value="item.value"/>
                </el-select>
              </template>
            </el-table-column>
            <el-table-column label="默认值" width="175">
              <template #default="scope">
                <el-switch v-if="scope.row.component === 'switch'" v-model="scope.row.value"/>
                <el-input-number v-else-if="scope.row.component === 'number'" v-model="scope.row.value" controls-position="right"/>
                <el-input v-else v-model="scope.row.value" placeholder="默认值"/>
              </template>
            </el-table-column>
            <el-table-column label="选项" min-width="180">
              <template #default="scope">
                <el-input
                  v-if="hasOptions(scope.row.component)"
                  v-model="scope.row.optionText"
                  type="textarea"
                  :rows="2"
                  placeholder="一行一个：标签=值"
                />
                <span v-else class="muted">无需配置</span>
              </template>
            </el-table-column>
            <el-table-column label="必填" width="60" align="center">
              <template #default="scope">
                <el-checkbox v-model="scope.row.required" :disabled="scope.row.component === 'title'"/>
              </template>
            </el-table-column>
            <el-table-column label="提示" width="200">
              <template #default="scope">
                <el-input v-model="scope.row.message" placeholder="字段说明"/>
              </template>
            </el-table-column>
          </sc-form-table>
        </div>
      </el-form>
    </el-scrollbar>
    <template #footer>
      <el-button @click="state.visible=false">取 消</el-button>
      <el-button :loading="state.isSaving" type="primary" @click="submit">保 存</el-button>
    </template>
  </el-dialog>
</template>

<script setup>
import {reactive, ref} from "vue"
import {ElMessage, ElMessageBox} from "element-plus"
import ScFormTable from "@/components/scFormTable"
import systemApi from "@/api/admin/system.js"

const emits = defineEmits(["success", "closed"])
const dialogForm = ref(null)

const componentOptions = [
  {label: "输入框", value: "input"},
  {label: "开关", value: "switch"},
  {label: "下拉选择", value: "select"},
  {label: "数字", value: "number"},
  {label: "单选", value: "radio"},
  {label: "多选", value: "checkboxGroup"},
  {label: "日期", value: "date"},
  {label: "颜色", value: "color"},
  {label: "上传", value: "upload"},
  {label: "标题", value: "title"},
]

const fieldTemplate = {
  name: "",
  label: "",
  component: "input",
  value: "",
  span: 24,
  options: {},
  rules: [],
  message: "",
  required: false,
  optionText: ""
}

const state = reactive({
  mode: "add",
  titleMap: {
    add: "新增配置表单",
    edit: "编辑配置表单"
  },
  visible: false,
  isSaving: false,
  form: defaultForm(),
  rules: {
    title: [{required: true, message: "请输入名称"}],
    key: [
      {required: true, message: "请输入唯一标识"},
      {pattern: /^[A-Za-z][A-Za-z0-9_]*$/, message: "需以字母开头，仅支持字母、数字和下划线"}
    ],
    group_key: [{required: true, message: "请输入分组标识"}],
    group_title: [{required: true, message: "请输入分组名称"}]
  }
})

function defaultForm() {
  return {
    title: "",
    key: "",
    group_key: "base",
    group_title: "基础配置",
    status: 1,
    sort: 0,
    remark: "",
    schema: {
      labelWidth: "120px",
      labelPosition: "left",
      formItems: []
    }
  }
}

const hasOptions = (component) => {
  return ["select", "radio", "checkboxGroup"].includes(component)
}

const componentChange = (row) => {
  if (row.component === "switch") {
    row.value = Boolean(row.value)
  } else if (row.component === "number") {
    row.value = Number(row.value) || 0
  } else if (row.component === "checkboxGroup" || row.component === "upload") {
    row.value = []
  } else if (row.component === "title") {
    row.name = row.name || `title_${Date.now()}`
    row.value = ""
    row.required = false
  } else {
    row.value = Array.isArray(row.value) ? "" : row.value
  }
}

const submit = () => {
  dialogForm.value.validate(async valid => {
    if (!valid) {
      return
    }

    const data = buildSubmitData()
    if (!data) {
      return
    }

    state.isSaving = true
    const api = state.mode === "add" ? systemApi.configForm.add : systemApi.configForm.edit
    const res = await api.post(data)
    state.isSaving = false

    if (res.code === 0) {
      emits("success", data, state.mode)
      state.visible = false
      ElMessage.success("操作成功")
    } else {
      await ElMessageBox.alert(res.message, "提示", {type: "error"})
    }
  })
}

const buildSubmitData = () => {
  const data = JSON.parse(JSON.stringify(state.form))
  const names = new Set()

  for (const item of data.schema.formItems) {
    if (!item.label) {
      ElMessage.warning("字段名称不能为空")
      return false
    }

    if (item.component !== "title") {
      if (!/^[A-Za-z][A-Za-z0-9_]*$/.test(item.name || "")) {
        ElMessage.warning("字段标识需以字母开头，仅支持字母、数字和下划线")
        return false
      }
      if (names.has(item.name)) {
        ElMessage.warning(`字段标识重复：${item.name}`)
        return false
      }
      names.add(item.name)
    }

    item.options = buildOptions(item)
    item.rules = item.required ? [{required: true, message: `请输入${item.label}`}] : []
    delete item.required
    delete item.optionText
  }

  if (state.mode === "add") {
    delete data.id
  }
  return data
}

const buildOptions = (item) => {
  const options = item.options || {}
  if (hasOptions(item.component)) {
    options.items = parseOptionText(item.optionText)
    options.placeholder = options.placeholder || "请选择"
  }
  if (item.component === "upload") {
    options.items = [{
      name: item.name,
      label: item.label,
      value: ""
    }]
  }
  if (item.component === "date") {
    options.type = options.type || "date"
    options.valueFormat = options.valueFormat || "YYYY-MM-DD"
  }
  return options
}

const parseOptionText = (text = "") => {
  return text.split("\n")
    .map(item => item.trim())
    .filter(Boolean)
    .map(item => {
      const index = item.indexOf("=")
      if (index === -1) {
        return {label: item, value: item}
      }
      return {
        label: item.slice(0, index).trim(),
        value: item.slice(index + 1).trim()
      }
    })
}

const open = (mode = "add") => {
  state.mode = mode
  state.form = defaultForm()
  state.visible = true
}

const setData = (data) => {
  const formData = JSON.parse(JSON.stringify(data))
  formData.status = Number(formData.status)
  formData.schema = formData.schema || defaultForm().schema
  formData.schema.formItems = (formData.schema.formItems || []).map(item => {
    const optionText = (item.options?.items || []).map(option => `${option.label}=${option.value}`).join("\n")
    return {
      ...fieldTemplate,
      ...item,
      required: Boolean(item.rules?.find(rule => rule.required)),
      optionText
    }
  })
  state.form = Object.assign(defaultForm(), formData)
}

defineExpose({
  open,
  setData
})
</script>

<style scoped>
.config-form-editor {
  padding-right: 8px;
}

.form-section {
  border: 1px solid var(--el-border-color-light);
  border-radius: 6px;
  padding: 16px 16px 2px;
  margin-bottom: 14px;
  background: var(--el-fill-color-blank);
}

.form-section--compact {
  padding-bottom: 0;
}

.form-section--fields {
  padding-bottom: 16px;
  margin-bottom: 0;
}

.section-title {
  font-size: 14px;
  font-weight: 600;
  color: var(--el-text-color-primary);
  margin-bottom: 14px;
}

.config-form-editor :deep(.el-form-item) {
  margin-bottom: 16px;
}

.config-form-editor :deep(.sc-form-table .el-table) {
  min-width: 980px;
}

.config-form-editor :deep(.sc-form-table .el-table__body-wrapper) {
  max-height: 360px;
  overflow-y: auto;
}

.muted {
  color: var(--el-text-color-secondary);
}
</style>
