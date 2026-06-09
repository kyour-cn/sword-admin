<!--
 * @Descripttion: 局部水印组件
 * @version: 1.1
 * @Author: sakuya
 * @Date: 2021年12月18日12:16:16
 * @LastEditors: sakuya
 * @LastEditTime: 2022年1月5日09:52:59
-->

<template>
  <div class="sc-water-mark" ref="scWaterMarkRef">
    <slot></slot>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'

// Props定义
const props = defineProps({
  text: { type: String, required: true, default: "" },
  subtext: { type: String, default: "" },
  color: { type: String, default: "rgba(128,128,128,0.2)" }
})

// 响应式数据
const scWaterMarkRef = ref(null)

// 生命周期
onMounted(() => {
  create()
})

// 方法
const create = () => {
  clear()
  // 创建画板
  const canvas = document.createElement('canvas')
  canvas.width = 150
  canvas.height = 150
  canvas.style.display = 'none'

  // 绘制文字
  const text = canvas.getContext('2d')
  text.rotate(-45 * Math.PI / 180)
  text.translate(-75, 25)
  text.fillStyle = props.color
  text.font = "bold 20px SimHei"
  text.textAlign = "center"
  text.fillText(props.text, canvas.width / 2, canvas.height / 2)
  text.font = "14px Microsoft YaHei"
  text.fillText(props.subtext, canvas.width / 2, canvas.height / 2 + 20)

  // 创建水印容器
  const watermark = document.createElement('div')
  watermark.setAttribute('class', 'watermark')
  const styleStr = `position:absolute;top:0;left:0;right:0;bottom:0;z-index:99;pointer-events:none;background-repeat:repeat;background-image:url('${canvas.toDataURL("image/png")}');`
  watermark.setAttribute('style', styleStr)
  scWaterMarkRef.value.appendChild(watermark)
}

const clear = () => {
  const wmDom = scWaterMarkRef.value?.querySelector('.watermark')
  wmDom && wmDom.remove()
}

// 暴露方法给父组件
defineExpose({
  create,
  clear
})
</script>

<style scoped>
  .sc-water-mark {position: relative;display: inherit;width: 100%;height: 100%;}
</style>
