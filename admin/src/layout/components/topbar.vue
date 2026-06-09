<template>
  <div v-if="show" class="adminui-topbar">
    <div class="left-panel">
      <el-breadcrumb separator-icon="el-icon-arrow-right" class="hidden-sm-and-down">
        <transition-group name="breadcrumb">
          <template v-for="item in breadList" :key="item.title">
            <el-breadcrumb-item v-if="item.path!='/' &&  !item.meta.hiddenBreadcrumb" :key="item.meta.title">
              <el-icon class="icon" v-if="item.meta.icon">
                <component :is="item.meta.icon"/>
              </el-icon>
              {{ item.meta.title }}
            </el-breadcrumb-item>
          </template>
        </transition-group>
      </el-breadcrumb>
    </div>
    <div class="center-panel"></div>
    <div class="right-panel">
      <slot></slot>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { useGlobalStore } from '@/stores/useGlobalStore'

const globalStore = useGlobalStore()
const route = useRoute()

// 响应式数据
const breadList = ref([])

// 计算属性
const show = computed(() => {
  // 不显示面包屑的布局类型
  const layouts = ['menu', 'header']
  const layout = globalStore.layout
  return !layouts.includes(layout)
})

// 方法
const getBreadcrumb = () => {
  breadList.value = route.meta.breadcrumb || []
}

// 监听器
watch(route, () => {
  getBreadcrumb()
})

// 生命周期
onMounted(() => {
  getBreadcrumb()
})
</script>

<style scoped>
.el-breadcrumb {
  margin-left: 15px;
}

.el-breadcrumb .el-breadcrumb__inner .icon {
  font-size: 14px;
  margin-right: 5px;
  float: left;
}
</style>
