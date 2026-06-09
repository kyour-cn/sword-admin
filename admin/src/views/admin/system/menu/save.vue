<template>
  <el-row :gutter="40">
    <el-col v-if="!state.form.id">
      <el-empty description="请选择左侧菜单后操作" :image-size="100"></el-empty>
    </el-col>
    <template v-else>
      <el-col :lg="12">
        <h2>{{ state.form.meta.title || "新增菜单" }}</h2>
        <el-form :model="state.form" :rules="state.rules" ref="dialogForm" label-width="80px" label-position="left">
          <el-form-item label="显示名称" prop="meta.title">
            <el-input v-model="state.form.meta.title" clearable placeholder="菜单显示名字"/>
          </el-form-item>
          <el-form-item label="上级菜单" prop="pid">
            <el-cascader
              v-model="state.form.pid"
              :options="state.menuOptions"
              :props="menuProps"
              :show-all-levels="false"
              placeholder="顶级菜单"
              clearable
            />
          </el-form-item>
          <el-form-item label="类型" prop="meta.type">
            <el-radio-group v-model="state.form.meta.type">
              <el-radio-button value="menu">菜单</el-radio-button>
              <el-radio-button value="iframe">Iframe</el-radio-button>
              <el-radio-button value="link">外链</el-radio-button>
              <el-radio-button value="rule">权限</el-radio-button>
            </el-radio-group>
          </el-form-item>
          <el-form-item label="别名" prop="name">
            <el-input v-model="state.form.name" clearable placeholder="别名"></el-input>
            <div class="el-form-item-msg">
              系统唯一且与内置组件名一致，否则导致缓存失效。如类型为Iframe的菜单，别名将代替源地址显示在地址栏
            </div>
          </el-form-item>
          <el-form-item label="排序" prop="sort">
            <el-input v-model="state.form.sort" clearable placeholder="菜单排序"></el-input>
            <div class="el-form-item-msg">正序排列，数字越小越靠前</div>
          </el-form-item>
          <el-form-item label="菜单图标" prop="meta.icon">
            <sc-icon-select v-model="state.form.meta.icon" clearable></sc-icon-select>
          </el-form-item>
          <el-form-item label="路由地址" prop="path">
            <el-input v-model="state.form.path" clearable placeholder=""></el-input>
          </el-form-item>
          <el-form-item label="视图" prop="component">
            <el-input v-model="state.form.component" clearable placeholder="">
              <template #prepend>views/</template>
            </el-input>
            <div class="el-form-item-msg">如父节点、链接或Iframe等没有视图的菜单不需要填写</div>
          </el-form-item>
          <el-form-item label="是否隐藏" prop="meta.hidden">
            <el-checkbox v-model="state.form.meta.hidden">隐藏菜单</el-checkbox>
            <el-checkbox v-model="state.form.meta.hiddenBreadcrumb">隐藏面包屑</el-checkbox>
            <el-checkbox v-model="state.form.meta.affix">固定标签</el-checkbox>
            <div class="el-form-item-msg">菜单不显示在导航中，但用户依然可以访问，例如详情页，固定标签后标签不可关闭</div>
          </el-form-item>
          <el-form-item label="整页路由" prop="fullPage">
            <el-switch v-model="state.form.meta.fullPage"/>
          </el-form-item>
          <el-form-item>
            <el-button type="primary" @click="save" :loading="state.loading">保 存</el-button>
          </el-form-item>
        </el-form>
      </el-col>
      <el-col :lg="12" class="api-list">
        <h2>接口权限</h2>
        <sc-form-table v-model="state.form.apiList" :addTemplate="apiListAddTemplate" placeholder="暂无匹配接口权限">
          <el-table-column prop="tag" label="标识" width="150">
            <template #default="scope">
              <el-input v-model="scope.row.tag" placeholder="请输入内容"></el-input>
            </template>
          </el-table-column>
          <el-table-column prop="path" label="Api url">
            <template #default="scope">
              <el-input v-model="scope.row.path" placeholder="请输入内容"></el-input>
            </template>
          </el-table-column>
        </sc-form-table>
      </el-col>
    </template>
  </el-row>
</template>

<script setup>
import {reactive, ref, watch} from "vue"
import {ElMessage} from "element-plus"
import ScIconSelect from '@/components/scIconSelect'
import ScFormTable from '@/components/scFormTable'
import systemApi from "@/api/admin/system.js"

const props = defineProps({
  menu: {
    type: Object,
    default: () => ({})
  }
})

const emits = defineEmits(['refreshMenu'])

const dialogForm = ref(null)

const state = reactive({
  form: {
    id: "",
    app_id: 0,
    pid: 0,
    name: "",
    path: "",
    component: "",
    redirect: "",
    sort: "0",
    meta: {
      title: "",
      icon: "",
      active: "",
      color: "",
      type: "menu",
      fullPage: false,
      tag: "",
      hidden: false,
      hiddenBreadcrumb: false,
      affix: false
    },
    apiList: []
  },
  checkPid: 0, // 用于比对是否修改上级
  menuOptions: [],
  rules: {
    required: false // 避免表单验证错误
  },
  loading: false,
  appId: 0
})

const menuProps = {
  value: 'id',
  label: 'title',
  checkStrictly: true
}

const apiListAddTemplate = {
  tag: "",
  path: ""
}

// 监听props.menu的变化
watch(() => props.menu, () => {
  state.menuOptions = treeToMap(props.menu)
}, {deep: true})

// 简单化菜单
const treeToMap = (tree) => {
  let map = []
  tree.forEach(item => {
    const obj = {
      id: item.id,
      pid: item.pid,
      title: item.meta.title,
      children: item.children && item.children.length > 0 ? treeToMap(item.children) : null
    }
    map.push(obj)
  })
  return map
}

// 保存
const save = async () => {
  state.loading = true

  // 拷贝表单数据，避免引用问题
  const formData = JSON.parse(JSON.stringify(state.form))
  // 格式转换
  formData.pid = Array.isArray(formData.pid) ? formData.pid.at(-1) : formData.pid
  formData.pid = formData.pid || 0
  formData.title = formData.meta.title
  formData.type = formData.meta.type
  const sort = parseInt(formData.sort)
  formData.sort = Number.isNaN(sort) ? 0 : sort

  const res = await systemApi.menu.edit.post(formData)
  state.loading = false
  if (res.code === 0) {
    state.form.title = formData.title
    state.form.type = formData.type
    state.form.sort = formData.sort
    ElMessage.success("保存成功")
    if (state.checkPid !== state.form.pid) {
      emits('refreshMenu')
      state.checkPid = state.form.pid
    }
  } else {
    ElMessage.warning(res.message)
  }
}

// 表单注入数据
const setData = (data, pid) => {
  state.form = data
  state.form.apiList = data.apiList || []
  state.checkPid = pid
}

const unsetData = () => {
  state.form.id = 0
}

// 暴露给父组件的方法
defineExpose({
  setData,
  unsetData
})
</script>

<style scoped>
h2 {
  font-size: 17px;
  color: #3c4a54;
  padding: 0 0 30px 0;
}

.api-list {
  border-left: 1px solid #eee;
}

[data-theme="dark"] h2 {
  color: #fff;
}

[data-theme="dark"] .api-list {
  border-color: #434343;
}
</style>
