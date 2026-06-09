<template>
  <div ref="scEcharts" :style="{height:height, width:width}"></div>
</template>

<script setup>
import { ref, watch, computed, nextTick, onMounted, onActivated, onDeactivated } from 'vue'
import * as echarts from 'echarts'
import T from './echarts-theme-T.js'

echarts.registerTheme('T', T)
const unwarp = (obj) => obj && (obj.__v_raw || obj.valueOf() || obj)

// Props定义
const props = defineProps({
  height: { type: String, default: "100%" },
  width: { type: String, default: "100%" },
  nodata: { type: Boolean, default: false },
  option: {
    type: Object,
    default: () => ({})
  }
})

// 响应式数据
const scEcharts = ref(null)
const isActivat = ref(false)
const myChart = ref(null)

// 计算属性
const myOptions = computed(() => {
  return props.option || {}
})

// 监听器
watch(() => props.option, (v) => {
  if (myChart.value) {
    unwarp(myChart.value).setOption(v, true)
  }
}, { deep: true })

// 生命周期钩子
onActivated(() => {
  if (!isActivat.value) {
    nextTick(() => {
      if (myChart.value) {
        myChart.value.resize()
      }
    })
  }
})

onDeactivated(() => {
  isActivat.value = false
})

onMounted(() => {
  isActivat.value = true
  nextTick(() => {
    draw()
  })
})

// 方法
const draw = () => {
  const chart = echarts.init(scEcharts.value, 'T')
  chart.setOption(myOptions.value, true)
  myChart.value = chart
  window.addEventListener('resize', () => chart.resize())
}

// 导出echarts的其他方法供外部使用
defineExpose({
  ...echarts,
  myChart
})
</script>
