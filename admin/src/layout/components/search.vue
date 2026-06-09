<template>
  <div class="sc-search">
    <el-input ref="inputRef" v-model="input" placeholder="搜索" size="large" clearable prefix-icon="el-icon-search"
              :trigger-on-focus="false" @input="inputChange"/>
    <div class="sc-search-history" v-if="history.length>0">
      <el-tag closable effect="dark" type="info" v-for="(item, index) in history" :key="item"
              @click="historyClick(item)" @close="historyClose(index)">{{ item }}
      </el-tag>
    </div>
    <div class="sc-search-result">
      <div class="sc-search-no-result" v-if="result.length<=0">暂无搜索结果</div>
      <ul v-else>
        <el-scrollbar max-height="366px">
          <li v-for="item in result" :key="item.path" @click="to(item)">
            <el-icon><component :is="item.icon || 'el-icon-menu'" /></el-icon>
            <span class="title">{{ item.breadcrumb }}</span>
          </li>
        </el-scrollbar>
      </ul>
    </div>
  </div>
</template>

<script setup>
import {onMounted, ref} from 'vue'
import {useRouter} from 'vue-router'
import tool from '@/utils/tool'

const router = useRouter()
const inputRef = ref()

// 响应式数据
const input = ref("")
const menu = ref([])
const result = ref([])
const history = ref([])

// 方法
const inputChange = (value) => {
  if(value){
    result.value = menuFilter(value)
  }else{
    result.value = []
  }
}

const filterMenu = (map) => {
  map.forEach(item => {
    if(item.meta.hidden || item.meta.type==="button"){
      return false
    }
    if(item.meta.type==='iframe'){
      item.path = `/i/${item.name}`
    }
    if(item.children && item.children.length > 0 && !item.component){
      filterMenu(item.children)
    }else{
      menu.value.push(item)
    }
  })
}

const menuFilter = (queryString) => {
  const res = []
  // 过滤菜单树
  const filterMenu = menu.value.filter((item) => {
    if((item.meta.title).toLowerCase().indexOf(queryString.toLowerCase()) >= 0){
      return true
    }
    if((item.name).toLowerCase().indexOf(queryString.toLowerCase()) >= 0){
      return true
    }
  })
  // 匹配系统路由
  const routerRoutes = router.getRoutes()
  const filterRouter = filterMenu.map((m) => {
    if(m.meta.type === "link"){
      return routerRoutes.find(r => r.path === '/'+m.path)
    }else{
      return routerRoutes.find(r => r.path === m.path)
    }
  })
  // 重组对象
  filterRouter.forEach(item => {
    if(item) {
      res.push({
        name: item.name,
        type: item.meta.type,
        path: item.meta.type==="link"?item.path.slice(1):item.path,
        icon: item.meta.icon,
        title: item.meta.title,
        breadcrumb: item.meta.breadcrumb?.map(v => v.meta.title).join(' - ') || item.meta.title
      })
    }
  })
  return res
}

const to = (item) => {
  if(!history.value.includes(input.value)){
    history.value.push(input.value)
    tool.data.set("SEARCH_HISTORY", history.value)
  }
  if(item.type==="link"){
    window.open(item.path)
  }else{
    router.push({path: item.path})
  }
}

const historyClick = (item) => {
  input.value = item
  result.value = menuFilter(item)
}

const historyClose = (index) => {
  history.value.splice(index, 1)
  tool.data.set("SEARCH_HISTORY", history.value)
}

// 生命周期
onMounted(() => {
  history.value = tool.data.get("SEARCH_HISTORY") || []
  const menuTree = tool.data.get("MENU")
  filterMenu(menuTree)
  inputRef.value.focus()
})
</script>

<style scoped>
.sc-search-no-result {
  text-align: center;
  margin: 40px 0;
  color: #999;
}

.sc-search-history {
  margin-top: 10px;
}

.sc-search-history .el-tag {
  cursor: pointer;
  margin-right: 5px;
}

.sc-search-result {
  margin-top: 15px;
}

.sc-search-result li {
  height: 56px;
  padding: 0 15px;
  background: var(--el-bg-color-overlay);
  border: 1px solid var(--el-border-color-light);
  list-style: none;
  border-radius: 4px;
  margin-bottom: 5px;
  font-size: 14px;
  display: flex;
  align-items: center;
  cursor: pointer;
}

.sc-search-result li i {
  font-size: 20px;
  margin-right: 15px;
}

.sc-search-result li:hover {
  background: var(--el-color-primary);
  color: #fff;
  border-color: var(--el-color-primary);
}
</style>
