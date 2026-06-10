<template>
  <el-container class="config-page">
    <el-aside width="240px" class="config-aside">
      <el-scrollbar>
        <el-menu :default-active="state.activeGroupKey" @select="selectGroup">
          <el-menu-item v-for="group in state.groups" :key="group.key" :index="group.key">
            <el-icon><el-icon-setting /></el-icon>
            <span>{{ group.title }}</span>
          </el-menu-item>
        </el-menu>
      </el-scrollbar>
    </el-aside>
    <el-main v-loading="state.loading" class="config-main">
      <el-empty v-if="!currentGroup" description="暂无配置分组" />
      <template v-else>
        <el-tabs v-if="currentForms.length" v-model="state.activeKey" class="config-tabs" @tab-change="selectForm">
          <el-tab-pane v-for="form in currentForms" :key="form.key" :label="form.title" :name="form.key" />
        </el-tabs>
        <el-empty v-if="!current" description="当前分组暂无可配置项" />
        <template v-else>
          <div v-if="current.remark" class="config-remark">{{ current.remark }}</div>
          <el-card shadow="never" class="config-panel">
            <sc-form
              ref="formRef"
              v-model="state.formValue"
              :config="current.schema"
              :loading="state.saving"
              @submit="save"
            >
              <el-button v-auth="'admin.system.config.save'" type="primary" :loading="state.saving" @click="save">
                保 存
              </el-button>
            </sc-form>
          </el-card>
        </template>
      </template>
    </el-main>
  </el-container>
</template>

<script setup>
import {computed, onMounted, reactive, ref} from "vue"
import {ElMessage, ElMessageBox} from "element-plus"
import ScForm from "@/components/scForm"
import systemApi from "@/api/admin/system.js"

defineOptions({
  name: "config",
})

const formRef = ref(null)

const state = reactive({
  loading: false,
  saving: false,
  groups: [],
  activeGroupKey: "",
  activeKey: "",
  formValue: {}
})

const forms = computed(() => {
  return state.groups.flatMap(group => group.forms || [])
})

const current = computed(() => {
  return currentForms.value.find(item => item.key === state.activeKey) || null
})

const currentGroup = computed(() => {
  return state.groups.find(item => item.key === state.activeGroupKey) || null
})

const currentForms = computed(() => {
  return currentGroup.value?.forms || []
})

const getList = async () => {
  state.loading = true
  const res = await systemApi.config.list.get()
  state.loading = false
  if (res.code !== 0) {
    await ElMessageBox.alert(res.message, "提示", {type: "error"})
    return
  }

  state.groups = res.data || []
  const activeGroup = state.groups.find(item => item.key === state.activeGroupKey)
  const firstGroup = state.groups.find(item => (item.forms || []).length) || state.groups[0]
  if (activeGroup) {
    selectGroup(activeGroup.key, true)
  } else if (firstGroup) {
    selectGroup(firstGroup.key)
  } else {
    state.activeGroupKey = ""
    state.activeKey = ""
    state.formValue = {}
  }
}

const selectGroup = (key, keepActiveForm = false) => {
  state.activeGroupKey = key
  const group = state.groups.find(item => item.key === key)
  const groupForms = group?.forms || []
  if (!groupForms.length) {
    state.activeKey = ""
    state.formValue = {}
    return
  }

  const activeKey = keepActiveForm && groupForms.some(item => item.key === state.activeKey)
    ? state.activeKey
    : groupForms[0].key
  selectForm(activeKey)
}

const selectForm = (key) => {
  state.activeKey = key
  const form = currentForms.value.find(item => item.key === key) || forms.value.find(item => item.key === key)
  state.formValue = form ? JSON.parse(JSON.stringify(form.value || {})) : {}
}

const save = async () => {
  if (!current.value) {
    return
  }

  const valid = await validateForm()
  if (!valid) {
    return
  }

  state.saving = true
  const res = await systemApi.config.save.post({
    key: current.value.key,
    value: state.formValue
  })
  state.saving = false

  if (res.code === 0) {
    ElMessage.success("保存成功")
    const form = forms.value.find(item => item.key === current.value.key)
    if (form) {
      form.value = JSON.parse(JSON.stringify(state.formValue))
    }
  } else {
    await ElMessageBox.alert(res.message, "提示", {type: "error"})
  }
}

const validateForm = () => {
  return new Promise(resolve => {
    formRef.value.validate((valid) => {
      resolve(valid)
    })
  })
}

onMounted(() => {
  getList()
})
</script>

<style scoped>
.config-page {
  height: 100%;
}

.config-aside {
  border-right: 1px solid var(--el-border-color-light);
}

.config-main {
  padding-top: 15px;
}

.config-tabs {
  margin-bottom: 6px;
}

.config-remark {
  margin-bottom: 10px;
  color: var(--el-text-color-secondary);
  font-size: 13px;
  line-height: 20px;
}

.config-panel {
  border-radius: 4px;
}
</style>
