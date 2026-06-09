<template>
  <div class="user-bar">
    <div class="panel-item hidden-sm-and-down" @click="openSearch">
      <el-icon><el-icon-search /></el-icon>
    </div>
    <div class="screen panel-item hidden-sm-and-down" @click="screen">
      <el-icon><el-icon-full-screen /></el-icon>
    </div>
    <div class="tasks panel-item" @click="tasks">
      <el-icon><el-icon-sort /></el-icon>
    </div>
    <div class="msg panel-item" @click="showMsg">
      <el-badge :hidden="msgList.length==0" :value="msgList.length" class="badge" type="danger">
        <el-icon><el-icon-chat-dot-round /></el-icon>
      </el-badge>
      <el-drawer title="新消息" v-model="msg" :size="400" append-to-body destroy-on-close>
        <el-container>
          <el-main class="nopadding">
            <el-scrollbar>
              <ul class="msg-list">
                <li v-for="item in msgList" v-bind:key="item.id">
                  <a :href="item.link" target="_blank">
                    <div class="msg-list__icon">
                      <el-badge is-dot type="danger">
                        <el-avatar :size="40" :src="item.avatar"></el-avatar>
                      </el-badge>
                    </div>
                    <div class="msg-list__main">
                      <h2>{{item.title}}</h2>
                      <p>{{item.describe}}</p>
                    </div>
                    <div class="msg-list__time">
                      <p>{{item.time}}</p>
                    </div>
                  </a>
                </li>
                <el-empty v-if="msgList.length==0" description="暂无新消息" :image-size="100"></el-empty>
              </ul>
            </el-scrollbar>
          </el-main>
          <el-footer>
            <el-button type="primary">消息中心</el-button>
            <el-button @click="markRead">全部设为已读</el-button>
          </el-footer>
        </el-container>
      </el-drawer>
    </div>
    <el-dropdown class="user panel-item" trigger="click" @command="handleUser">
      <div class="user-avatar">
        <el-avatar :size="30">{{ userNameF }}</el-avatar>
        <label>{{ userName }}</label>
        <el-icon class="el-icon--right"><el-icon-arrow-down /></el-icon>
      </div>
      <template #dropdown>
        <el-dropdown-menu>
          <el-dropdown-item command="uc">帐号信息</el-dropdown-item>
          <el-dropdown-item command="clearCache">清除缓存</el-dropdown-item>
          <el-dropdown-item divided command="outLogin">退出登录</el-dropdown-item>
        </el-dropdown-menu>
      </template>
    </el-dropdown>
  </div>

  <el-dialog v-model="searchVisible" :width="700"  title="搜索" center destroy-on-close>
    <SearchComponent @success="searchVisible=false"></SearchComponent>
  </el-dialog>

  <el-drawer v-model="tasksVisible" :size="450"  title="任务中心" destroy-on-close>
    <TasksComponent></TasksComponent>
  </el-drawer>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { ElMessageBox, ElLoading } from 'element-plus'
import SearchComponent from './search.vue'
import TasksComponent from './tasks.vue'
import tool from '@/utils/tool'

const router = useRouter()

// 响应式数据
const userName = ref("")
const userNameF = ref("")
const searchVisible = ref(false)
const tasksVisible = ref(false)
const msg = ref(false)
const msgList = ref([])

// 方法
const openSearch = () => {
  searchVisible.value = true
}

const screen = () => {
  const element = document.documentElement
  if (document.fullscreenElement) {
    document.exitFullscreen()
  } else {
    element.requestFullscreen()
  }
}

const tasks = () => {
  tasksVisible.value = true
}

const showMsg = () => {
  msg.value = true
}

const markRead = () => {
  msgList.value = []
}

// 个人信息处理
const handleUser = (command) => {
  if(command === "uc"){
    router.push({path: '/admin/home/user_center'})
  }
  if(command === "clearCache"){
    ElMessageBox.confirm('清除缓存会将系统初始化，包括登录状态、主题、语言设置等，是否继续？','提示', {
      type: 'info',
    }).then(() => {
      const loading = ElLoading.service()
      tool.data.clear()
      router.replace({path: '/login'})
      setTimeout(()=>{
        loading.close()
        location.reload()
      },1000)
    }).catch(() => {
      // 取消
    })
  }
  if(command === "outLogin"){
    ElMessageBox.confirm('确认是否退出当前用户？','提示', {
      type: 'warning',
      confirmButtonText: '退出',
      cancelButtonText: '取消'
    }).then(() => {
      const loading = ElLoading.service({
        lock: true,
        text: '正在注销...'
      })
      tool.data.remove("TOKEN")
      tool.data.remove("USER_INFO")
      setTimeout(()=>{
        loading.close()
        router.replace({path: '/login'})
      }, 500)
    }).catch(() => {
      // 取消
    })
  }
}

// 生命周期
onMounted(() => {
  const userInfo = tool.data.get("USER_INFO")
  if(userInfo) {
    userName.value = userInfo.nickname || ""
    userNameF.value = userName.value.substring(0, 1)
  }
})
</script>

<style scoped>
  .user-bar {display: flex;align-items: center;height: 100%;}
  .user-bar .panel-item {padding: 0 10px;cursor: pointer;height: 100%;display: flex;align-items: center;}
  .user-bar .panel-item i {font-size: 16px;}
  .user-bar .panel-item:hover {background: rgba(0, 0, 0, 0.1);}
  .user-bar .user-avatar {height:49px;display: flex;align-items: center;}
  .user-bar .user-avatar label {display: inline-block;margin-left:5px;font-size: 12px;cursor:pointer;}

  .msg-list li {border-top:1px solid #eee;}
  .msg-list li a {display: flex;padding:20px;}
  .msg-list li a:hover {background: #ecf5ff;}
  .msg-list__icon {width: 40px;margin-right: 15px;}
  .msg-list__main {flex: 1;}
  .msg-list__main h2 {font-size: 15px;font-weight: normal;color: #333;}
  .msg-list__main p {font-size: 12px;color: #999;line-height: 1.8;margin-top: 5px;}
  .msg-list__time {width: 100px;text-align: right;color: #999;}

  .dark .msg-list__main h2 {color: #d0d0d0;}
  .dark .msg-list li {border-top:1px solid #363636;}
  .dark .msg-list li a:hover {background: #383838;}
</style>
