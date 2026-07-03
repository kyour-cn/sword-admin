<template>
  <!-- 通栏布局 -->
  <template v-if="layout==='header'">
    <header class="adminui-header">
      <div class="adminui-header-left">
        <div class="logo-bar">
          <img class="logo" :src="config.APP_LOGO">
          <span>{{ config.APP_NAME }}</span>
        </div>
        <ul v-if="!ismobile" class="nav">
          <li v-for="item in menu" :key="item" :class="pmenu.path===item.path?'active':''" @click="showMenu(item)">
            <el-icon><component :is="item.meta.icon || 'el-icon-menu'" /></el-icon>
            <span>{{ item.meta.title }}</span>
          </li>
        </ul>
      </div>
      <div class="adminui-header-right">
        <UserBar></UserBar>
      </div>
    </header>
    <section class="aminui-wrapper">
      <div v-if="!ismobile && nextMenu.length>0 || !pmenu.component" :class="menuIsCollapse?'aminui-side isCollapse':'aminui-side'">
        <div v-if="!menuIsCollapse" class="adminui-side-top">
          <h2>{{ pmenu.meta.title }}</h2>
        </div>
        <div class="adminui-side-scroll">
          <el-scrollbar>
            <el-menu class="side-menu" :default-active="active" router :collapse="menuIsCollapse" :unique-opened="config.MENU_UNIQUE_OPENED">
              <NavMenu :navMenus="nextMenu"></NavMenu>
            </el-menu>
          </el-scrollbar>
        </div>
        <div class="adminui-side-bottom" @click="globalStore.TOGGLE_menuIsCollapse()">
          <el-icon><el-icon-expand v-if="menuIsCollapse"/><el-icon-fold v-else /></el-icon>
        </div>
      </div>
      <Side-m v-if="ismobile"></Side-m>
      <div class="aminui-body el-container">
        <Topbar v-if="!ismobile"></Topbar>
        <Tags v-if="!ismobile && layoutTags"></Tags>
        <div class="adminui-main" id="adminui-main">
          <router-view v-slot="{ Component }">
              <keep-alive :include="keepAliveStore.keepLiveRoute">
                  <component :is="Component" :key="route.fullPath" v-if="keepAliveStore.routeShow"/>
              </keep-alive>
          </router-view>
          <iframe-view></iframe-view>
        </div>
      </div>
    </section>
  </template>

  <!-- 经典布局 -->
  <template v-else-if="layout==='menu'">
    <header class="adminui-header">
      <div class="adminui-header-left">
        <div class="logo-bar">
          <img class="logo" :src="config.APP_LOGO">
          <span>{{ config.APP_NAME }}</span>
        </div>
      </div>
      <div class="adminui-header-right">
        <UserBar></UserBar>
      </div>
    </header>
    <section class="aminui-wrapper">
      <div v-if="!ismobile" :class="menuIsCollapse?'aminui-side isCollapse':'aminui-side'">
        <div class="adminui-side-scroll">
          <el-scrollbar>
            <el-menu class="side-menu" :default-active="active" router :collapse="menuIsCollapse" :unique-opened="config.MENU_UNIQUE_OPENED">
              <NavMenu :navMenus="menu"></NavMenu>
            </el-menu>
          </el-scrollbar>
        </div>
        <div class="adminui-side-bottom" @click="globalStore.TOGGLE_menuIsCollapse()">
          <el-icon><el-icon-expand v-if="menuIsCollapse"/><el-icon-fold v-else /></el-icon>
        </div>
      </div>
      <Side-m v-if="ismobile"></Side-m>
      <div class="aminui-body el-container">
        <Topbar v-if="!ismobile"></Topbar>
        <Tags v-if="!ismobile && layoutTags"></Tags>
        <div class="adminui-main" id="adminui-main">
          <router-view v-slot="{ Component }">
              <keep-alive :include="keepAliveStore.keepLiveRoute">
                  <component :is="Component" :key="route.fullPath" v-if="keepAliveStore.routeShow"/>
              </keep-alive>
          </router-view>
          <iframe-view></iframe-view>
        </div>
      </div>
    </section>
  </template>

  <!-- 功能坞布局 -->
  <template v-else-if="layout==='dock'">
    <header class="adminui-header">
      <div class="adminui-header-left">
        <div class="logo-bar">
          <img class="logo" :src="config.APP_LOGO">
          <span>{{ config.APP_NAME }}</span>
        </div>
      </div>
      <div class="adminui-header-right">
        <div v-if="!ismobile" class="adminui-header-menu">
          <el-menu mode="horizontal" :default-active="active" router background-color="#222b45" text-color="#fff" active-text-color="var(--el-color-primary)">
            <NavMenu :navMenus="menu"></NavMenu>
          </el-menu>
        </div>
        <Side-m v-if="ismobile"></Side-m>
        <UserBar></UserBar>
      </div>
    </header>
    <section class="aminui-wrapper">
      <div class="aminui-body el-container">
        <Tags v-if="!ismobile && layoutTags"></Tags>
        <div class="adminui-main" id="adminui-main">
          <router-view v-slot="{ Component }">
              <keep-alive :include="keepAliveStore.keepLiveRoute">
                  <component :is="Component" :key="route.fullPath" v-if="keepAliveStore.routeShow"/>
              </keep-alive>
          </router-view>
          <iframe-view></iframe-view>
        </div>
      </div>
    </section>
  </template>

  <!-- 默认布局 -->
  <template v-else>
    <section class="aminui-wrapper">
      <div v-if="!ismobile" class="aminui-side-split">
        <div class="aminui-side-split-top">
          <router-link :to="config.DASHBOARD_URL">
            <img class="logo" :title="config.APP_NAME" :src="config.APP_LOGO" alt="">
          </router-link>
        </div>
        <div class="adminui-side-split-scroll">
          <el-scrollbar>
            <ul>
              <li v-for="item in menu" :key="item" :class="pmenu.path===item.path?'active':''"
                @click="showMenu(item)">
                <el-icon><component :is="item.meta.icon || 'el-icon-menu'" /></el-icon>
                <p>{{ item.meta.title }}</p>
              </li>
            </ul>
          </el-scrollbar>
        </div>
      </div>
      <div v-if="!ismobile && nextMenu.length>0 || !pmenu.component" :class="menuIsCollapse?'aminui-side isCollapse':'aminui-side'">
        <div v-if="!menuIsCollapse" class="adminui-side-top">
          <h2>{{ pmenu.meta.title }}</h2>
        </div>
        <div class="adminui-side-scroll">
          <el-scrollbar>
            <el-menu class="side-menu" :default-active="active" router :collapse="menuIsCollapse" :unique-opened="config.MENU_UNIQUE_OPENED">
              <NavMenu :navMenus="nextMenu"></NavMenu>
            </el-menu>
          </el-scrollbar>
        </div>
        <div class="adminui-side-bottom" @click="globalStore.TOGGLE_menuIsCollapse()">
          <el-icon><el-icon-expand v-if="menuIsCollapse"/><el-icon-fold v-else /></el-icon>
        </div>
      </div>
      <Side-m v-if="ismobile"></Side-m>
      <div class="aminui-body el-container">
        <Topbar>
          <UserBar></UserBar>
        </Topbar>
        <Tags v-if="!ismobile && layoutTags"></Tags>
        <div class="adminui-main" id="adminui-main">
          <router-view v-slot="{ Component }">
              <keep-alive :include="keepAliveStore.keepLiveRoute">
                  <component :is="Component" :key="route.fullPath" v-if="keepAliveStore.routeShow"/>
              </keep-alive>
          </router-view>
          <iframe-view></iframe-view>
        </div>
      </div>
    </section>
  </template>

  <div class="main-maximize-exit" @click="exitMaximize"><el-icon><el-icon-close /></el-icon></div>

  <div class="layout-setting" @click="openSetting"><el-icon><el-icon-brush-filled /></el-icon></div>

  <el-drawer title="布局实时演示" v-model="settingDialog" :size="400" append-to-body destroy-on-close>
    <setting></setting>
  </el-drawer>

  <auto-exit></auto-exit>
</template>

<script setup>
import config from "@/config";
import { ref, computed, onMounted, onUnmounted, nextTick, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useGlobalStore } from '@/stores/useGlobalStore'
import { useKeepAliveStore } from '@/stores/useKeepAliveStore'
import SideM from './components/sideM.vue'
import Topbar from './components/topbar.vue'
import Tags from './components/tags.vue'
import NavMenu from './components/NavMenu.vue'
import UserBar from './components/userbar.vue'
import setting from './components/setting.vue'
import iframeView from './components/iframeView.vue'
import autoExit from './other/autoExit.js'

// 获取store、route、router实例
const globalStore = useGlobalStore()
const keepAliveStore = useKeepAliveStore()
const route = useRoute()
const router = useRouter()

// 响应式数据
const settingDialog = ref(false)
const menu = ref([])
const nextMenu = ref([])
const pmenu = ref({})
const active = ref('')

// 计算属性
const ismobile = computed(() => globalStore.ismobile)
const layout = computed(() => globalStore.layout)
const layoutTags = computed(() => globalStore.layoutTags)
const menuIsCollapse = computed(() => globalStore.menuIsCollapse)

// 方法
const openSetting = () => {
  settingDialog.value = true
}

const onLayoutResize = () => {
  globalStore.SET_ismobile(document.body.clientWidth < 992)
}

// 路由监听高亮
const showThis = () => {
  pmenu.value = route.meta.breadcrumb ? route.meta.breadcrumb[0] : {}
  nextMenu.value = filterUrl(pmenu.value.children)
  nextTick(() => {
    active.value = route.meta.active || route.fullPath
  })
}

// 点击显示
const showMenu = (routeItem) => {
  pmenu.value = routeItem
  nextMenu.value = filterUrl(routeItem.children)
  if((!routeItem.children || routeItem.children.length === 0) && routeItem.component){
    router.push({path: routeItem.path})
  }
}

// 转换外部链接的路由
const filterUrl = (map) => {
  const newMap = []
  map && map.forEach(item => {
    item.meta = item.meta ? item.meta : {}
    // 处理隐藏
    if(item.meta.hidden || item.meta.type === "button"){
      return false
    }
    // 处理http
    if(item.meta.type === 'iframe'){
      item.path = `/i/${item.name}`
    }
    // 递归循环
    if(item.children && item.children.length > 0){
      item.children = filterUrl(item.children)
    }
    newMap.push(item)
  })
  return newMap
}

// 退出最大化
const exitMaximize = () => {
  globalStore.SET_isMaximize(false)
}

// 监听路由变化
watch(route, () => {
  showThis()
})

// 监听布局变化
watch(layout, (val) => {
  document.body.setAttribute('data-layout', val)
}, { immediate: true })

// 生命周期
onMounted(() => {
  onLayoutResize()
  window.addEventListener('resize', onLayoutResize)
  const menuData = router.sc_getMenu()
  menu.value = filterUrl(menuData)
  showThis()
})

onUnmounted(() => {
  window.removeEventListener('resize', onLayoutResize)
})
</script>
