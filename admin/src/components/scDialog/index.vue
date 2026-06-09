<!--
 * @Descripttion: 弹窗扩展组件
 * @version: 2.0
 * @Author: sakuya
 * @Date: 2021年8月27日08:51:52
 * @LastEditors: sakuya
 * @LastEditTime: 2022年5月14日15:13:41
-->

<template>
  <div class="sc-dialog" ref="scDialog">
  <el-dialog ref="dialog" v-model="dialogVisible" :fullscreen="isFullscreen" v-bind="$attrs" :show-close="false">
    <template #header>
      <slot name="header">
        <span class="el-dialog__title">{{ title }}</span>
      </slot>
      <div class="sc-dialog__headerbtn">
        <button v-if="showFullscreen" aria-label="fullscreen" type="button" @click="setFullscreen">
          <el-icon v-if="isFullscreen" class="el-dialog__close"><el-icon-bottom-left /></el-icon>
          <el-icon v-else class="el-dialog__close"><el-icon-full-screen /></el-icon>
        </button>
        <button v-if="showClose" aria-label="close" type="button" @click="closeDialog">
          <el-icon class="el-dialog__close"><el-icon-close /></el-icon>
        </button>
      </div>
    </template>
    <div v-loading="loading">
      <slot></slot>
    </div>
    <template #footer>
      <slot name="footer"></slot>
    </template>
  </el-dialog>
  </div>
</template>

<script setup>
import { ref, watch, onMounted } from 'vue'

// Props定义
const props = defineProps({
  modelValue: { type: Boolean, default: false },
  title: { type: String, default: "" },
  showClose: { type: Boolean, default: true },
  showFullscreen: { type: Boolean, default: true },
  loading: { type: Boolean, default: false }
})

// Emits定义
const emit = defineEmits(['update:modelValue'])

// 响应式数据
const scDialog = ref(null)
const dialog = ref(null)
const dialogVisible = ref(false)
const isFullscreen = ref(false)

// 监听器
watch(() => props.modelValue, (newVal) => {
  dialogVisible.value = newVal
  if (dialogVisible.value) {
    isFullscreen.value = false
  }
})

watch(dialogVisible, (newVal) => {
  emit('update:modelValue', newVal)
})

// 生命周期
onMounted(() => {
  dialogVisible.value = props.modelValue
})

// 方法
const closeDialog = () => {
  dialogVisible.value = false
}

const setFullscreen = () => {
  isFullscreen.value = !isFullscreen.value
}
</script>

<style scoped>
  .sc-dialog__headerbtn {position: absolute;top: var(--el-dialog-padding-primary);right: var(--el-dialog-padding-primary);}
  .sc-dialog__headerbtn button {padding: 0;background: transparent;border: none;outline: none;cursor: pointer;font-size: var(--el-message-close-size,16px);margin-left: 15px;color: var(--el-color-info);}
  .sc-dialog__headerbtn button:hover .el-dialog__close {color: var(--el-color-primary);}
  .sc-dialog:deep(.el-dialog).is-fullscreen {display: flex;flex-direction: column;top:0px !important;left:0px !important;}
  .sc-dialog:deep(.el-dialog).is-fullscreen .el-dialog__header {}
  .sc-dialog:deep(.el-dialog).is-fullscreen .el-dialog__body {flex:1;overflow: auto;}
  .sc-dialog:deep(.el-dialog).is-fullscreen .el-dialog__footer {padding-bottom: 10px;border-top: 1px solid var(--el-border-color-base);}
</style>
