<!--
 * @Descripttion: 趋势标记
 * @version: 1.0
 * @Author: sakuya
 * @Date: 2021年11月11日11:07:10
 * @LastEditors:
 * @LastEditTime:
-->

<template>
  <span class="sc-trend" :class="'sc-trend--'+type">
    <el-icon v-if="iconType=='P'" class="sc-trend-icon"><el-icon-top /></el-icon>
    <el-icon v-if="iconType=='N'" class="sc-trend-icon"><el-icon-bottom /></el-icon>
    <el-icon v-if="iconType=='Z'" class="sc-trend-icon"><el-icon-right /></el-icon>
    <em class="sc-trend-prefix">{{prefix}}</em>
    <em class="sc-trend-value">{{modelValue}}</em>
    <em class="sc-trend-suffix">{{suffix}}</em>
  </span>
</template>

<script setup>
import { computed } from 'vue'

// Props定义
const props = defineProps({
  modelValue: { type: Number, default: 0 },
  prefix: { type: String, default: "" },
  suffix: { type: String, default: "" },
  reverse: { type: Boolean, default: false }
})

// 计算属性
// const absValue = computed(() => {
//   return Math.abs(props.modelValue)
// })

const iconType = computed(() => {
  if (props.modelValue === 0) {
    return 'Z'
  } else if (props.modelValue < 0) {
    return 'N'
  } else if (props.modelValue > 0) {
    return 'P'
  }
})

const type = computed(() => {
  if (props.modelValue === 0) {
    return 'Z'
  } else if (props.modelValue < 0) {
    return props.reverse ? 'P' : 'N'
  } else if (props.modelValue > 0) {
    return props.reverse ? 'N' : 'P'
  }
})
</script>

<style scoped>
  .sc-trend {display: flex;align-items: center;}
  .sc-trend-icon {margin-right: 2px;}
  .sc-trend em {font-style: normal;}
  .sc-trend-prefix {margin-right: 2px;}
  .sc-trend-suffix {margin-left: 2px;}
  .sc-trend--P {color: #f56c6c;}
  .sc-trend--N {color: #67c23a;}
  .sc-trend--Z {color: #555;}
</style>
