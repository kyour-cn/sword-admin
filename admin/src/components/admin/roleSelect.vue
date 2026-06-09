<template>
  <sc-table-select
    v-model="state.value"
    :apiObj="state.apiObj"
    :table-width="state.tableWidth"
    clearable
    multiple
    collapse-tags
    :placeholder="placeholder"
    collapse-tags-tooltip
    :props="state.props"
    @change="onChange"
  >
    <template #header="{form, submit}">
      <el-form :inline="true" :model="form">
        <el-form-item>
          <el-select v-model="form.app_id" placeholder="选择应用" clearable :teleported="false" style="width: 150px;">
            <el-option v-for="item in state.appList" :label="item.name" :value="item.id"></el-option>
          </el-select>
        </el-form-item>
        <el-form-item>
          <el-input v-model="form.name" placeholder="角色名称"></el-input>
        </el-form-item>
        <el-form-item>
          <el-button type="primary" @click="submit">查询</el-button>
        </el-form-item>
      </el-form>
    </template>
    <el-table-column prop="id" label="ID" width="180"/>
    <el-table-column prop="name" label="名称"/>
    <el-table-column prop="app.name" label="所属应用"/>
  </sc-table-select>
</template>

<script setup>
import {reactive} from "vue";
import systemApi from "@/api/admin/system.js";
import ScTableSelect from "@/components/scTableSelect";

defineOptions({
  name: 'roleSelect',
})

const props = defineProps({
  roles: {type: Array, default: () => []},
  tableWidth: Number,
  placeholder: {type: String, default: "请选择"}
})

const emits = defineEmits(['onChange'])

const state = reactive({
  apiObj: systemApi.role.list,
  tableWidth: props.tableWidth ? props.tableWidth : 600,
  props: {
    label: 'name',
    value: 'id',
  },
  value: props.roles,
  appList: [],
  selectedApp: 0
})

// systemApi.role.list.get({
//     pageSize: 50,
//     ids: props.ids
// }).then((res) => {
//     console.log( res.data.rows)
//     res.data.rows.forEach(item => {
//         state.value.push(item)
//     })
// })

const getApp = async () => {
  const res = await systemApi.app.list.get({page: 1, page_size: 50});
  state.appList = res.data.rows;
  state.selectedApp = res.data.rows[0].id;
}
getApp();

const onChange = (data) => {
  emits('onChange', data)
}

</script>
