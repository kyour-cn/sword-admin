<!--
 * @Descripttion: 处理iframe持久化，涉及store(Pinia)
 * @version: 1.0
 * @Author: sakuya
 * @Date: 2021年6月30日13:20:41
 * @LastEditors:
 * @LastEditTime:
-->

<template>
  <div v-show="$route.meta.type === 'iframe'" class="iframe-pages">
    <iframe v-for="item in iframeList" :key="item.meta.url" v-show="$route.meta.url === item.meta.url" :src="item.meta.url" frameborder='0'></iframe>
  </div>
</template>

<script setup>
import { computed, watch, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { useIframeStore } from '@/stores/useIframeStore'
import { useGlobalStore } from '@/stores/useGlobalStore'

const iframeStore = useIframeStore()
const globalStore = useGlobalStore()
const route = useRoute()

// 计算属性
const iframeList = computed(() => iframeStore.iframeList)
const ismobile = computed(() => globalStore.ismobile)
const layoutTags = computed(() => globalStore.layoutTags)

// 方法
const push = (routeItem) => {
  if(routeItem.meta.type === 'iframe'){
    if(ismobile.value || !layoutTags.value){
      iframeStore.setIframeList(routeItem)
    }else{
      iframeStore.pushIframeList(routeItem)
    }
  }
}

// 监听器
watch(route, (newRoute) => {
  push(newRoute)
})

// 生命周期
onMounted(() => {
  push(route)
})
</script>

<style scoped>
.iframe-pages {
  position: relative;
  width: 100%;
  height: 100%;
}

.iframe-pages iframe {
  width: 100%;
  height: 100%;
  border: none;
}
</style>
