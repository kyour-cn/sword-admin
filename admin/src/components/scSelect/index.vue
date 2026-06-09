<!--
 * @Descripttion: 异步选择器
 * @version: 1.1
 * @Author: sakuya
 * @Date: 2021年8月3日15:53:37
 * @LastEditors: sakuya
 * @LastEditTime: 2023年2月23日15:17:24
-->

<template>
  <div class="sc-select">
    <div v-if="initloading" class="sc-select-loading">
      <el-icon class="is-loading"><el-icon-loading /></el-icon>
    </div>
    <el-select v-bind="$attrs" :loading="loading" @visible-change="visibleChange">
      <el-option v-for="item in options" :key="item[selectProps.value]" :label="item[selectProps.label]" :value="objValueType ? item : item[selectProps.value]">
        <slot name="option" :data="item"></slot>
      </el-option>
    </el-select>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, useAttrs } from 'vue'
import config from "@/config/select"

// Props定义
const props = defineProps({
  apiObj: { type: Object, default: () => ({}) },
  dic: { type: String, default: "" },
  objValueType: { type: Boolean, default: false },
  params: { type: Object, default: () => ({}) }
})

// 获取attrs
const attrs = useAttrs()

// 响应式数据
const dicParams = reactive({ ...props.params })
const loading = ref(false)
const options = ref([])
const selectProps = reactive(config.props)
const initloading = ref(false)

// 生命周期
onMounted(() => {
  // 如果有默认值就去请求接口获取options
  if (hasValue()) {
    initloading.value = true
    getRemoteData()
  }
})

// 方法
const visibleChange = (isOpen) => {
  if (isOpen && options.value.length === 0 && (props.dic || props.apiObj)) {
    getRemoteData()
  }
}

const getRemoteData = async () => {
  loading.value = true
  dicParams[config.request.name] = props.dic
  let res = {}
  if (props.apiObj) {
    res = await props.apiObj.get(props.params)
  } else if (props.dic) {
    res = await config.dicApiObj.get(props.params)
  }
  const response = config.parseData(res)
  options.value = response.data
  loading.value = false
  initloading.value = false
}

const hasValue = () => {
  if (Array.isArray(attrs.modelValue) && attrs.modelValue.length <= 0) {
    return false
  } else if (attrs.modelValue) {
    return true
  } else {
    return false
  }
}

// 暴露方法给父组件
defineExpose({
  getRemoteData,
  options
})
</script>

<style scoped>
  .sc-select {display: inline-block;position: relative;}
  .sc-select-loading {position: absolute;top:0;left:0;right:0;bottom:0;background: #fff;z-index: 100;border-radius: 5px;border: 1px solid #EBEEF5;display: flex;align-items: center;padding-left:10px;}
  .sc-select-loading i {font-size: 14px;}

  .dark .sc-select-loading {background: var(--el-bg-color-overlay);border-color: var(--el-border-color-light);}
</style>
