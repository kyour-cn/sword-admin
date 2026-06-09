<template>
  <el-dialog
    title="角色权限设置"
    v-model="state.visible"
    :width="500"
    destroy-on-close
    @closed="emit('closed')"
  >
    <div class="treeMain">
      <el-tree
        ref="rule"
        node-key="id"
        show-checkbox
        :data="state.rule.list"
        :default-expand-all="true"
        :props="state.rule.props"
      />
    </div>
    <template #footer>
      <el-button @click="state.visible = false">取 消</el-button>
      <el-button type="primary" :loading="state.saveLoading" @click="submit()">
        保 存
      </el-button>
    </template>
  </el-dialog>
</template>

<script setup>
import {nextTick, reactive, ref} from "vue";
import systemApi from "@/api/admin/system.js";
import {ElMessage, ElMessageBox} from "element-plus";

const emit = defineEmits(["success", "closed", "getNewData"])

const state = reactive({
  checkIds: [],
  visible: false,
  saveLoading: false,
  row: {
    id: 0,
    rules_checked: ''
  },
  rule: {
    list: [],
    checked: [],
    props: {
      label: (data) => {
        return data.meta.title
      }
    }
  },
})

const rule = ref(null)

const open = (row) => {
  state.row = row
  state.visible = true;
  getRule()
}

const submit = async () => {
  state.saveLoading = true;
  //选中的和半选的合并后传值接口
  const checkedKeys = rule.value.getCheckedKeys().concat(rule.value.getHalfCheckedKeys());
  const checked = rule.value.getCheckedKeys().join(',')
  let checkIds = checkedKeys.join(',');
  const data = {
    id: state.row.id,
    rules: checkIds,
    rules_checked: checked
  }
  const res = await systemApi.role.editPermission.post(data);
  if (res.code === 0) {
    state.saveLoading = false;
    state.visible = false;
    ElMessage.success("操作成功");
    emit('success')
    emit('getNewData')
  } else {
    await ElMessageBox.alert(res.message, "提示", {type: 'error'});
  }
}

const getRule = async () => {
  const res = await systemApi.menu.list.get({
    page: 1,
    page_size: 1000,
    app_id: state.row.app_id
  });
  if (res.code === 0) {
    state.rule.list = res.data
    state.checkIds = state.row.rules.split(',')
    //实时设置选中的tree
    await nextTick(() => {
      state.checkIds.forEach(item => {
        rule.value.setChecked(item, true, false)
      })
    })
  } else {
    await ElMessageBox.alert(res.message, "提示", {type: 'error'});
  }
}

const setData = (data) => {
  state.row = data
}

//暴露给父组件的方法
defineExpose({
  open, setData
})

</script>

<style scoped>
.treeMain {
  height: 280px;
  overflow: auto;
  border: 1px solid #dcdfe6;
  margin-bottom: 10px;
}
</style>
