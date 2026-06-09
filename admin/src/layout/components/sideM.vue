<template>
  <div ref="dragRef" class="mobile-nav-button" @click="showMobileNav($event)" v-drag draggable="false"><el-icon><el-icon-menu /></el-icon></div>

  <el-drawer ref="mobileNavBoxRef" title="移动端菜单" :size="240" v-model="nav" direction="ltr" :with-header="false" destroy-on-close>
    <el-container class="mobile-nav">
      <el-header>
        <div class="logo-bar"><img class="logo" src="/admin/img/logo.png"><span>{{ $CONFIG.APP_NAME }}</span></div>
      </el-header>
      <el-main>
        <el-scrollbar>
          <el-menu :default-active="$route.meta.active || $route.fullPath" @select="select" router background-color="#212d3d" text-color="#fff" active-text-color="#409EFF">
            <NavMenu :navMenus="menu"></NavMenu>
          </el-menu>
        </el-scrollbar>
      </el-main>
    </el-container>
  </el-drawer>

</template>

<script setup>
import { ref, onMounted, getCurrentInstance } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import NavMenu from './NavMenu.vue'

const route = useRoute()
const router = useRouter()
const { proxy } = getCurrentInstance()
const dragRef = ref()
const mobileNavBoxRef = ref()

// 响应式数据
const nav = ref(false)
const menu = ref([])

// 方法
const showMobileNav = (e) => {
  const isdrag = e.currentTarget.getAttribute('drag-flag')
  if (isdrag === 'true') {
    return false
  } else {
    nav.value = true
  }
}

const select = () => {
  mobileNavBoxRef.value.handleClose()
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

// 自定义指令
const vDrag = {
  mounted(el) {
    let isDragging = false
    let startX = 0
    let startY = 0
    let initialX = 0
    let initialY = 0

    el.addEventListener('mousedown', (e) => {
      isDragging = true
      startX = e.clientX
      startY = e.clientY
      const rect = el.getBoundingClientRect()
      initialX = rect.left
      initialY = rect.top
      el.setAttribute('drag-flag', 'false')
    })

    document.addEventListener('mousemove', (e) => {
      if (!isDragging) return

      const deltaX = e.clientX - startX
      const deltaY = e.clientY - startY

      if (Math.abs(deltaX) > 5 || Math.abs(deltaY) > 5) {
        el.setAttribute('drag-flag', 'true')
        el.style.left = (initialX + deltaX) + 'px'
        el.style.top = (initialY + deltaY) + 'px'
        el.style.position = 'fixed'
      }
    })

    document.addEventListener('mouseup', () => {
      if (isDragging) {
        isDragging = false
        setTimeout(() => {
          el.setAttribute('drag-flag', 'false')
        }, 100)
      }
    })
  }
}

// 生命周期
onMounted(() => {
  const menuData = router.sc_getMenu()
  menu.value = filterUrl(menuData)
})
</script>

<style scoped>
  .mobile-nav-button {position: fixed;bottom:10px;left:10px;z-index: 10;width: 50px;height: 50px;background: #409EFF;box-shadow: 0 2px 12px 0 rgba(64, 158, 255, 1);border-radius: 50%;display: flex;align-items: center;justify-content: center;}
  .mobile-nav-button i {color: #fff;font-size: 20px;}

  .mobile-nav {background: #212d3d;}
  .mobile-nav .el-header {background: transparent;border: 0;}
  .mobile-nav .el-main {padding:0;}
  .mobile-nav .logo-bar {display: flex;align-items: center;font-weight: bold;font-size: 20px;color: #fff;}
  .mobile-nav .logo-bar img {width: 30px;margin-right: 10px;}
  .mobile-nav .el-submenu__title:hover {background: #fff!important;}
</style>
