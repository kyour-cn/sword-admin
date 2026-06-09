<template>
  <el-config-provider :locale="localeVal" :size="config.size" :zIndex="config.zIndex" :button="config.button">
    <router-view></router-view>
  </el-config-provider>
</template>

<script setup>
import colorTool from '@/utils/color'
import tool from '@/utils/tool'
import sysConfig from "@/config"
import {computed, onMounted} from "vue"
import {useI18n} from "vue-i18n";
import siteApi from "@/api/common/site.js"

const {locale, messages} = useI18n();

// 定义组件名称
defineOptions({
  name: 'App',
})

const config = {
  size: "default",
  zIndex: 2000,
  button: {
    autoInsertSpace: false
  }
}

let localeVal = computed(() => {
  return messages.value[locale.value]?.el || {};
})

onMounted(() => {

  // 从接口获取站点配置
  siteApi.config.get().then(res => {
    if (res.code === 0) {
      // 登录验证开关
      sysConfig.LOGIN_VERIFY = Boolean(res.data.admin_captcha_switch)
    }
  }).catch(err => {
    console.error(err)
  })

  //设置主题颜色
  const app_color = sysConfig.COLOR || tool.data.get('APP_COLOR')
  if (app_color) {
    document.documentElement.style.setProperty('--el-color-primary', app_color);
    for (let i = 1; i <= 9; i++) {
      document.documentElement.style.setProperty(`--el-color-primary-light-${i}`, colorTool.lighten(app_color, i / 10));
    }
    for (let i = 1; i <= 9; i++) {
      document.documentElement.style.setProperty(`--el-color-primary-dark-${i}`, colorTool.darken(app_color, i / 10));
    }
  }
})

</script>

<style lang="scss">
@use '@/style/style.scss' as style;
</style>
