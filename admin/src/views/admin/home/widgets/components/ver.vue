<template>
  <el-card shadow="hover" header="版本信息">
    <div style="height: 210px;text-align: center;">
      <img src="/admin/img/ver.svg" style="height:140px" alt=""/>
      <h2 style="margin-top: 15px;">GourdAdmin {{ $CONFIG.APP_VER }}</h2>
      <p style="margin-top: 5px;">最新版本 {{ ver }}</p>
    </div>
    <div style="margin-top: 20px;">
      <el-button type="primary" plain round @click="goLog">更新日志</el-button>
      <el-button type="primary" plain round @click="goGit">gitee</el-button>
      <el-button type="primary" plain round @click="goGithub">github</el-button>
    </div>
  </el-card>
</template>

<script setup>
import {ref, onMounted} from 'vue';

const ver = ref('');

// 获取版本信息
// const getVer = async () => {
//   const res = await $API.demo.ver.get();
//   ver.value = res.data;
// };

onMounted(() => {
  // getVer();
});

const goLog = () => {
  window.open("https://gitee.com/kyour/gourd-admin/releases")
}

const goGit = () => {
  window.open("https://gitee.com/kyour/gourd-admin")
}
const goGithub = () => {
  window.open("https://github.com/kyour-cn/gourd-admin")
}

// 页面方法
defineOptions({
  title: "版本信息",
  icon: "el-icon-monitor",
  description: "当前项目版本信息",
});
</script>
