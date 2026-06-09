<template>
  <el-container>
    <el-header style="height:50px;">
      <div class="common-header-left">
        <div class="common-header-logo">
          <img :alt="$CONFIG.APP_NAME" src="/admin/img/logo.png">
          <label>{{$CONFIG.APP_NAME}}</label>
        </div>
        <div class="common-header-title">{{props.title}}</div>
      </div>
      <div class="common-header-right">
        <router-link to="/login">返回登录</router-link>
      </div>
    </el-header>
    <el-main>
      <div class="common-container">
        <h2 class="common-title">{{props.title}}</h2>
        <div class="common-main el-card">
          <slot></slot>
        </div>
      </div>
    </el-main>
  </el-container>
</template>

<script setup>

const props = defineProps({
    title: {type: String, default: ""}
})

</script>

<style lang="scss" scoped>

.common-header-left {display: flex;align-items: center;}
.common-header-logo {display: flex;align-items: center;}
.common-header-logo img {height:30px;margin-right: 10px;vertical-align: bottom;}
.common-header-logo label {font-size: 20px;}
.common-header-title {font-size: 16px;border-left: 1px solid var(--el-border-color-light);margin-left: 15px;padding-left: 15px;}
.common-header-right {display: flex;align-items: center;}
.common-header-right a {font-size: 14px;color: var(--el-color-primary);cursor: pointer;}
.common-header-right a:hover {color: var(--el-color-primary-light-3);}
.common-container {max-width: 1240px;margin:30px auto 30px auto;}
.common-main {padding:20px;}
.common-title {font-size: 26px;margin-bottom: 20px;font-weight: normal;}
.common-main .el-form {width: 500px;margin:30px auto;}
.common-main .el-steps .el-step__title {font-size: 14px;}
.common-main .el-steps .el-step__icon {border: 1px solid;}
.common-main .yzm {display: flex;width: 100%;}
.common-main .yzm .el-button {margin-left: 10px;}
.common-main .link {color: var(--el-color-primary);cursor: pointer;}
.common-main .link:hover {color: var(--el-color-primary-light-3);}

</style>
