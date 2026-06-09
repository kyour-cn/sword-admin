<template>
  <el-dialog
    v-model="state.visible"
    destroy-on-close
    :title="state.titleMap[state.mode]"
    :width="500"
    @closed="emits('closed')"
  >
    <el-form
      ref="dialogForm"
      :disabled="state.mode === 'show'"
      :model="state.form"
      :rules="state.rules"
      label-position="left"
      label-width="100px"
    >
      <el-form-item label="角色名称" prop="name">
        <el-input v-model="state.form.name" clearable></el-input>
      </el-form-item>
      <el-form-item label="排序" prop="sort">
        <el-input-number
          v-model="state.form.sort"
          controls-position="right"
          style="width: 100%;"
          :min="1"
        />
      </el-form-item>
      <el-form-item label="是否管理员" prop="status">
        <el-switch v-model="state.form.is_admin" :active-value="true" :inactive-value="false"></el-switch>
        <div class="el-form-item-msg">
          管理员将拥有当前应用的全部菜单和权限。
        </div>
      </el-form-item>
      <el-form-item label="是否启用" prop="status">
        <el-switch v-model="state.form.status" :active-value="true" :inactive-value="false"></el-switch>
      </el-form-item>
      <el-form-item label="备注" prop="remark">
        <el-input v-model="state.form.remark" clearable type="textarea"></el-input>
      </el-form-item>
    </el-form>
    <template #footer>
      <el-button @click="state.visible=false">取 消</el-button>
      <el-button v-if="state.mode !== 'show'" :loading="state.isSaving" type="primary" @click="submit()">保 存
      </el-button>
    </template>
  </el-dialog>
</template>

<script setup>
import systemApi from "@/api/admin/system.js";
import {reactive, ref} from "vue";
import {ElMessage, ElMessageBox} from "element-plus";

const emits = defineEmits(['success', 'closed'])
const dialogForm = ref(null)

const state = reactive({
  mode: "add",
  titleMap: {
    add: '新增',
    edit: '编辑',
    show: '查看'
  },
  visible: false,
  isSaving: false,
  //表单数据
  form: {
    id: 0,
    name: "",
    sort: 1,
    is_admin: 0,
    status: 1,
    remark: "",
    app_id: 0
  },
  //验证规则
  rules: {
    sort: [
      {required: true, message: '请输入排序', trigger: 'change'}
    ],
    name: [
      {required: true, message: '请输入角色名称'}
    ]
  }
})

//表单提交方法
const submit = () => {
  dialogForm.value.validate(async (valid) => {
    if (valid) {
      state.isSaving = true;

      // 拷贝表单数据，避免引用问题
      const formData = JSON.parse(JSON.stringify(state.form))
      // 格式转换
      formData.is_admin = formData.is_admin ? 1 : 0;
      formData.status = formData.status ? 1 : 0;

      if (state.mode === 'add') {
        delete formData.id
      }

      const res = await systemApi.role[state.mode].post(formData);
      state.isSaving = false;
      if (res.code === 0) {
        emits('success', state.form, state.mode)
        state.visible = false;
        ElMessage.success("操作成功");
      } else {
        await ElMessageBox.alert(res.message, "提示", {type: 'error'});
      }
    }
  })
}

const open = (mode = 'add') => {
  state.mode = mode
  state.visible = true
}

const setData = (data) => {
  Object.assign(state.form, data)
  state.form.is_admin = data.is_admin === 1
  state.form.status = data.status === 1
}

//暴露给父组件的方法
defineExpose({
  open, setData
})
</script>
