<template>
  <el-container>
    <el-aside width="300px" v-loading="state.menuLoading">
      <el-container>
        <el-header>
          <el-select v-model="state.selectedApp">
            <el-option
              v-for="item in state.appList"
              :key="item.id"
              :label="item.name"
              :value="item.id"
            />
          </el-select>
          <el-input
            style="margin-left: 10px;"
            placeholder="输入关键字过滤"
            clearable
            v-model="state.menuFilterText"
          />
        </el-header>
        <el-main class="nopadding">
          <el-tree
            ref="menuRef"
            class="menu"
            node-key="id"
            draggable
            highlight-current
            check-strictly
            show-checkbox
            :expand-on-click-node="false"
            :check-on-click-leaf="false"
            :data="state.menuList"
            :props="menuProps"
            :filter-node-method="menuFilterNode"
            @node-click="menuClick"
            @node-drop="nodeDrop"
          >
            <template #default="{node, data}">
              <span class="custom-tree-node">
                <span class="label">
                  {{ node.label }}
                </span>
                <span class="do">
                  <el-button icon="el-icon-plus" size="small" @click.stop="add(node, data)"/>
                </span>
            </span>
            </template>
          </el-tree>
        </el-main>
        <el-footer style="height:51px;">
          <el-button type="primary" size="small" icon="el-icon-plus" @click="add()"></el-button>
          <el-button type="danger" size="small" plain icon="el-icon-delete" @click="delMenu"></el-button>
        </el-footer>
      </el-container>
    </el-aside>
    <el-container>
      <el-main class="nopadding" style="padding:20px;" ref="mainRef">
        <save-dialog ref="saveDialogRef" :menu="state.menuList" @refreshMenu="refreshMenu"/>
      </el-main>
    </el-container>
  </el-container>
</template>

<script setup>
import { reactive, ref, watch, onMounted } from "vue"
import { ElMessage, ElMessageBox } from "element-plus"
import systemApi from "@/api/admin/system.js"
import SaveDialog from './save'

defineOptions({
  name: "menus"
})

const menuRef = ref(null)
const mainRef = ref(null)
const saveDialogRef = ref(null)

let newMenuIndex = 1

const state = reactive({
  menuLoading: false,
  menuList: [],
  menuFilterText: "",
  appList: [],
  selectedApp: 0
})

const menuProps = {
  label: (data) => {
    return data.meta.title
  }
}

// 监听器
watch(() => state.menuFilterText, (val) => {
  menuRef.value.filter(val)
})

watch(() => state.selectedApp, () => {
  getMenu()
  sessionStorage.setItem("sys_menu_app_id", state.selectedApp)
})

// 挂载时执行
onMounted(() => {
  getApp()
})

// 获取应用列表
const getApp = async () => {
  const res = await systemApi.app.list.get({page: 1, page_size: 500})
  state.appList = res.data.rows

  // 读取缓存 sys_menu_app_id
  const appId = sessionStorage.getItem("sys_menu_app_id")
  if (appId) {
    state.selectedApp = Number(appId)
  } else {
    state.selectedApp = res.data.rows[0]?.id
  }
}

// 加载树数据
const getMenu = async () => {
  state.menuLoading = true
  const res = await systemApi.menu.list.get({
    page: 1,
    page_size: 50,
    app_id: state.selectedApp
  })
  saveDialogRef.value.unsetData()

  state.menuLoading = false
  state.menuList = res.data
}

// 树点击
const menuClick = (data, node) => {
  const pid = node.level === 1 ? 0 : node.parent.id
  saveDialogRef.value.setData(data, pid)
  mainRef.value.$el.scrollTop = 0
}

// 树过滤
const menuFilterNode = (value, data) => {
  if (!value) return true
  const targetText = data.meta.title
  return targetText.indexOf(value) !== -1
}

// 树拖拽
const nodeDrop = (draggingNode, dropNode, dropType) => {
  saveDialogRef.value.setData({})
  ElMessage(`拖拽对象：${draggingNode.data.meta.title}, 释放对象：${dropNode.data.meta.title}, 释放对象的位置：${dropType}`)
}

// 增加
const add = async (node, data) => {
  const newMenuName = "未命名" + newMenuIndex++
  let newMenuData = {
    pid: data ? data.id : 0,
    name: newMenuName,
    path: "",
    component: "",
    sort: 0,
    meta: {
      title: newMenuName,
      icon: "",
      active: "",
      color: "",
      type: "menu",
      fullPage: false,
      tag: "",
      affix: false,
      hidden: false,
      hiddenBreadcrumb: false
    },
    app_id: state.selectedApp
  }
  state.menuLoading = true
  const res = await systemApi.menu.add.post(newMenuData)
  state.menuLoading = false
  newMenuData.id = res.data.id

  menuRef.value.append(newMenuData, node)
  menuRef.value.setCurrentKey(newMenuData.id)
  const pid = node ? node.data.id : ""
  saveDialogRef.value.setData(newMenuData, pid)
}

// 删除菜单
const delMenu = async () => {
  const CheckedNodes = menuRef.value.getCheckedNodes()
  if (CheckedNodes.length === 0) {
    ElMessage.warning("请选择需要删除的项")
    return false
  }

  const confirm = await ElMessageBox.confirm('确认删除已选择的菜单吗？', '提示', {
    type: 'warning',
    confirmButtonText: '删除',
    confirmButtonClass: 'el-button--danger'
  }).catch(() => {
  })
  if (confirm !== 'confirm') {
    return false
  }

  state.menuLoading = true
  const reqData = {
    ids: CheckedNodes.map(item => item.id)
  }
  const res = await systemApi.menu.delete.post(reqData)
  state.menuLoading = false

  if (res.code === 0) {
    CheckedNodes.forEach(item => {
      const node = menuRef.value.getNode(item);
      if (node.isCurrent) {
        saveDialogRef.value.setData({})
      }
      menuRef.value.remove(item)
    })
  } else {
    ElMessage.warning(res.message)
  }
}

// 子组件刷新菜单
const refreshMenu = () => {
  getMenu()
}
</script>

<style scoped>

.custom-tree-node {
  display: flex;
  flex: 1;
  align-items: center;
  justify-content: space-between;
  font-size: 14px;
  height: 100%;
  padding-right: 24px;
}

.custom-tree-node .label {
  display: flex;
  align-items: center;
  height: 100%;
}

.custom-tree-node .label .el-tag {
  margin-left: 5px;
}

.custom-tree-node .do {
  display: none;
}

.custom-tree-node .do i {
  margin-left: 5px;
  color: #999;
}

.custom-tree-node .do i:hover {
  color: #333;
}

.custom-tree-node:hover .do {
  display: inline-block;
}
</style>
