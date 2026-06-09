<template>
  <el-container v-loading="loading">
    <el-main>
      <el-empty v-if="tasks.length===0" :image-size="120" >
        <template #description>
          <h2>没有正在执行的任务</h2>
        </template>
        <p style="font-size: 14px;color: #999;line-height: 1.5;margin: 0 40px;">暂时还没有任务哦，快去创建一个吧！</p>
      </el-empty>
      <el-card v-for="task in tasks" :key="task.id" shadow="hover" class="user-bar-tasks-item">
        <div class="user-bar-tasks-item-body">
          <div class="taskIcon">
            <el-icon v-if="task.type==='export'" :size="20"><el-icon-paperclip /></el-icon>
            <el-icon v-if="task.type==='report'" :size="20"><el-icon-dataAnalysis /></el-icon>
          </div>
          <div class="taskMain">
            <div class="title">
              <h2>{{ task.title }}</h2>
              <p><span :title="task.created_at" v-time.tip="task.created_at"></span> 创建</p>
            </div>
            <div class="bottom">
              <div class="state">
                <el-tag type="info" v-if="task.status===-1">异常</el-tag>
                <el-tag type="info" v-if="task.status===0">等待中</el-tag>
                <el-tag type="info" v-if="task.status===1">执行中</el-tag>
                <el-tag v-if="task.status===2">完成</el-tag>
              </div>
              <div class="handler">
                <el-button
                  v-if="task.status===2 && task.type==='export'"
                  type="primary"
                  circle
                  icon="el-icon-download"
                  @click="download(task)"
                />
              </div>
            </div>
          </div>
        </div>
      </el-card>
    </el-main>
    <el-footer style="padding:10px;text-align: right;">
      <el-button circle icon="el-icon-refresh" @click="refresh"></el-button>
    </el-footer>
  </el-container>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import userApi from "@/api/common/user.js";
import sysConfig from "@/config";
import {ElMessageBox} from "element-plus";

// 响应式数据
const loading = ref(false)
const tasks = ref([])

// 方法
const getData = async () => {
  loading.value = true
  const res = await userApi.tasks.list();
  tasks.value = res.data
  loading.value = false
}

const refresh = () => {
  getData()
}

const download = (row) => {
  const result = JSON.parse(row.result)

  const a = document.createElement("a")
  a.style = "display: none"
  a.target = "_blank"
  a.href = sysConfig.API_URL + '/' + result.file
  document.body.appendChild(a)
  a.click()
  document.body.removeChild(a)
}

// 生命周期
onMounted(() => {
  getData()
})
</script>

<style scoped>
  .user-bar-tasks-item {margin-bottom: 10px;}
  .user-bar-tasks-item:hover {border-color: var(--el-color-primary);}
  .user-bar-tasks-item-body {display: flex;}
  .user-bar-tasks-item-body .taskIcon {width: 45px;height: 45px;background: var(--el-color-primary-light-9);margin-right: 20px;display: flex;justify-content:center;align-items: center;color: var(--el-color-primary);border-radius:20px;}
  .user-bar-tasks-item-body .taskMain {flex: 1;}
  .user-bar-tasks-item-body .title h2 {font-size: 15px;}
  .user-bar-tasks-item-body .title p {font-size: 12px;color: #999;margin-top: 5px;}
  .user-bar-tasks-item-body .bottom {display: flex;justify-content: space-between;align-items: center;padding-top: 20px;}
</style>
