<template>
  <el-form ref="formRef" label-width="120px" label-position="left" style="padding:0 20px;">
    <el-alert title="以下配置可实时预览，开发者可在 config/category.js 中配置默认值，非常不建议在生产环境下开放布局设置" type="error" :closable="false"></el-alert>
    <el-divider></el-divider>
    <el-form-item :label="$t('user.nightmode')">
      <el-switch v-model="dark"></el-switch>
    </el-form-item>
    <el-form-item :label="$t('user.language')">
      <el-select v-model="lang">
        <el-option label="简体中文" value="zh-cn"></el-option>
        <el-option label="English" value="en"></el-option>
      </el-select>
    </el-form-item>
    <el-divider></el-divider>
    <el-form-item label="主题颜色">
      <el-color-picker v-model="colorPrimary" :predefine="colorList">></el-color-picker>
    </el-form-item>
    <el-divider></el-divider>
    <el-form-item label="框架布局">
      <el-select v-model="layout" placeholder="请选择">
        <el-option label="默认" value="default"></el-option>
        <el-option label="通栏" value="header"></el-option>
        <el-option label="经典" value="menu"></el-option>
<!--        <el-option label="功能坞" value="dock"></el-option>-->
      </el-select>
    </el-form-item>
    <el-form-item label="折叠菜单">
      <el-switch v-model="menuIsCollapse"></el-switch>
    </el-form-item>
    <el-form-item label="标签栏">
      <el-switch v-model="layoutTags"></el-switch>
    </el-form-item>
    <el-divider></el-divider>
  </el-form>
</template>

<script setup>
import { ref, watch, getCurrentInstance } from 'vue'
import { useI18n } from 'vue-i18n'
import { useGlobalStore } from '@/stores/useGlobalStore'
import colorTool from '@/utils/color'
import tool from '@/utils/tool'

const globalStore = useGlobalStore()
const { t } = useI18n()
const { proxy } = getCurrentInstance()
const formRef = ref()

// 响应式数据
const layout = ref(globalStore.layout)
const menuIsCollapse = ref(globalStore.menuIsCollapse)
const layoutTags = ref(globalStore.layoutTags)
const lang = ref(tool.data.get('APP_LANG') || proxy.$CONFIG.LANG)
const dark = ref(tool.data.get('APP_DARK') || false)
const colorList = ref(['#409EFF', '#009688', '#536dfe', '#ff5c93', '#c62f2f', '#fd726d'])
const colorPrimary = ref(tool.data.get('APP_COLOR') || proxy.$CONFIG.COLOR || '#409EFF')

// 监听器
watch(layout, (val) => {
  globalStore.SET_layout(val)
})

watch(menuIsCollapse, () => {
  globalStore.TOGGLE_menuIsCollapse()
})

watch(layoutTags, () => {
  globalStore.TOGGLE_layoutTags()
})

watch(dark, (val) => {
  if(val){
    document.documentElement.classList.add("dark")
    tool.data.set("APP_DARK", val)
  }else{
    document.documentElement.classList.remove("dark")
    tool.data.remove("APP_DARK")
  }
})

watch(lang, (val) => {
  proxy.$i18n.locale = val
  tool.data.set("APP_LANG", val)
})

watch(colorPrimary, (val) => {
  if(!val){
    val = '#409EFF'
    colorPrimary.value = '#409EFF'
  }
  document.documentElement.style.setProperty('--el-color-primary', val)
  for (let i = 1; i <= 9; i++) {
    document.documentElement.style.setProperty(`--el-color-primary-light-${i}`, colorTool.lighten(val,i/10))
  }
  for (let i = 1; i <= 9; i++) {
    document.documentElement.style.setProperty(`--el-color-primary-dark-${i}`, colorTool.darken(val,i/10))
  }
  tool.data.set("APP_COLOR", val)
})
</script>

<style>
</style>
