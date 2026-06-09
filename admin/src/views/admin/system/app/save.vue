<template>
  <el-dialog
    v-model="state.visible"
    destroy-on-close
    :title="state.titleMap[state.mode]"
    :width="500"
    @closed="$emit('closed')"
  >
    <el-form
      ref="dialogForm"
      :disabled="state.mode === 'show'"
      :model="state.form"
      :rules="state.rules"
      label-position="left"
      label-width="100px"
    >
      <el-form-item label="名称" prop="name">
        <el-input v-model="state.form.name" placeholder="请输入名称" clearable></el-input>
      </el-form-item>
      <el-form-item label="应用key" prop="key">
        <el-input v-model="state.form.key" placeholder="请输入应用key" clearable></el-input>
      </el-form-item>
      <el-form-item label="备注信息" prop="remark">
        <el-input v-model="state.form.remark" placeholder="请备注信息" clearable type="textarea"></el-input>
      </el-form-item>
      <el-form-item label="状态" prop="status">
        <el-switch v-model="state.form.status" :active-value="true" :inactive-value="false"></el-switch>
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
import {ElMessage, ElMessageBox} from 'element-plus'

const emits = defineEmits(['success', 'closed']);
const dialogForm = ref(null);

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
    name: "",
    remark: "",
    key: "",
    status: true,
    sort: 0
  },
  //验证规则
  rules: {
    name: [
      {required: true, message: '请输入姓名'}
    ],
    key: [
      {required: true, message: '请输入应用key'}
    ],
    status: [
      {required: true, message: '请选择状态'}
    ]
  }
});

//表单提交方法
const submit = () => {
  dialogForm.value.validate(async (valid) => {
    if (valid) {
      state.isSaving = true;

      const data = {...state.form}
      data.status = data.status ? 1 : 0;

      if (state.mode === 'add') {
        delete data.id;
      }

      const res = await systemApi.app[state.mode].post(data);
      state.isSaving = false;
      if (res.code === 0) {
        emits('success', state.form, state.mode);
        state.visible = false;
        ElMessage.success("操作成功");
      } else {
        await ElMessageBox.alert(res.message, "提示", {type: 'error'});
      }
    }
  });
};

const open = (mode = 'add') => {
  state.mode = mode;
  state.visible = true;
};

const setData = (data) => {
  // 拷贝表单数据，避免引用问题
  const formData = JSON.parse(JSON.stringify(data))
  formData.status = formData.status === 1;
  Object.assign(state.form, formData);
};

//暴露给父组件的方法
defineExpose({
  open, setData
});
</script>

<style>
</style>
