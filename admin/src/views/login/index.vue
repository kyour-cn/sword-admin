<template>
  <div class="login_bg"
       style="background-image: url(/admin/img/login_bg.png);background-size: auto 100%;background-repeat: no-repeat;">
    <div class="login_config">
      <el-button :icon="config.dark?'el-icon-sunny':'el-icon-moon'" circle type="info" @click="configDark"></el-button>
      <el-dropdown trigger="click" placement="bottom-end" @command="configLang">
        <el-button circle>
          <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" aria-hidden="true"
               role="img" width="1em" height="1em" preserveAspectRatio="xMidYMid meet" viewBox="0 0 512 512">
            <path
              d="M478.33 433.6l-90-218a22 22 0 0 0-40.67 0l-90 218a22 22 0 1 0 40.67 16.79L316.66 406h102.67l18.33 44.39A22 22 0 0 0 458 464a22 22 0 0 0 20.32-30.4zM334.83 362L368 281.65L401.17 362z"
              fill="currentColor"></path>
            <path
              d="M267.84 342.92a22 22 0 0 0-4.89-30.7c-.2-.15-15-11.13-36.49-34.73c39.65-53.68 62.11-114.75 71.27-143.49H330a22 22 0 0 0 0-44H214V70a22 22 0 0 0-44 0v20H54a22 22 0 0 0 0 44h197.25c-9.52 26.95-27.05 69.5-53.79 108.36c-31.41-41.68-43.08-68.65-43.17-68.87a22 22 0 0 0-40.58 17c.58 1.38 14.55 34.23 52.86 83.93c.92 1.19 1.83 2.35 2.74 3.51c-39.24 44.35-77.74 71.86-93.85 80.74a22 22 0 1 0 21.07 38.63c2.16-1.18 48.6-26.89 101.63-85.59c22.52 24.08 38 35.44 38.93 36.1a22 22 0 0 0 30.75-4.9z"
              fill="currentColor"></path>
          </svg>
        </el-button>
        <template #dropdown>
          <el-dropdown-menu>
            <el-dropdown-item v-for="item in lang" :key="item.value" :command="item"
                              :class="{'selected':config.lang === item.value}">{{ item.name }}
            </el-dropdown-item>
          </el-dropdown-menu>
        </template>
      </el-dropdown>
    </div>
    <div class="login-form">
      <div class="login-header">
        <div class="logo">
          <img :alt="$CONFIG.APP_NAME" src="/admin/img/logo.png">
          <label>{{ $CONFIG.APP_NAME }}</label>
        </div>
      </div>
      <el-tabs>
        <el-tab-pane :label="$t('login.accountLogin')" lazy>
          <password-form></password-form>
        </el-tab-pane>
        <el-tab-pane :label="$t('login.mobileLogin')" lazy>
          <phone-form></phone-form>
        </el-tab-pane>
      </el-tabs>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, getCurrentInstance } from 'vue'
import passwordForm from './components/passwordForm'
import phoneForm from './components/phoneForm'
import tool from '@/utils/tool'
import { useKeepAliveStore } from '@/stores/useKeepAliveStore'
import { useViewTagsStore } from '@/stores/useViewTagsStore'
import { useIframeStore } from '@/stores/useIframeStore'

const { proxy } = getCurrentInstance()

const config = ref({
  lang: tool.data.get('APP_LANG') || proxy.$CONFIG.LANG,
  dark: tool.data.get('APP_DARK') || false
})

const lang = ref([
  {
    name: '简体中文',
    value: 'zh-cn',
  },
  {
    name: 'English',
    value: 'en',
  }
])

watch(() => config.value.dark, (val) => {
  if (val) {
    document.documentElement.classList.add("dark")
    tool.data.set("APP_DARK", val)
  } else {
    document.documentElement.classList.remove("dark")
    tool.data.remove("APP_DARK")
  }
})

watch(() => config.value.lang, (val) => {
  proxy.$i18n.locale = val
  tool.data.set("APP_LANG", val)
})

// 初始化清理
tool.cookie.remove("TOKEN")
tool.data.remove("USER_INFO")
tool.data.remove("MENU")
tool.data.remove("PERMISSIONS")
tool.data.remove("grid")
const viewTagsStore = useViewTagsStore()
const keepAliveStore = useKeepAliveStore()
const iframeStore = useIframeStore()
viewTagsStore.clearViewTags()
keepAliveStore.clearKeepLive()
iframeStore.clearIframeList()

const configDark = () => {
  config.value.dark = !config.value.dark
}

const configLang = (command) => {
  config.value.lang = command.value
}

</script>

<style lang="scss" scoped>
.login_bg {
  width: 100%;
  height: 100%;
  background: #F0F3FC;
  display: flex;
}

.login-form {
  width: 400px;
  margin: auto calc(50px + 5%) auto auto;
  padding: 20px 0;

  .el-divider {
    margin-top: 40px;
  }
}

.login-header {
  margin-bottom: 40px;

  .logo {
    display: flex;
    align-items: center;

    img {
      width: 40px;
      height: 40px;
      vertical-align: bottom;
      margin-right: 10px;
    }

    label {
      font-size: 26px;
      font-weight: bold;
    }
  }
}

.login-oauth {
  display: flex;
  justify-content: space-around;
}

.login-form:deep(.el-tabs) {
  .el-tabs__header {
    margin-bottom: 25px;
  }

  .el-tabs__header .el-tabs__item {
    font-size: 14px;
  }
}

.login-form:deep(.login-forgot) {
  text-align: right;

  a {
    color: var(--el-color-primary);
  }

  a:hover {
    color: var(--el-color-primary-light-3);
  }
}

.login-form:deep(.login-reg) {
  font-size: 14px;
  color: var(--el-text-color-primary);

  a {
    color: var(--el-color-primary);
  }

  a:hover {
    color: var(--el-color-primary-light-3);
  }
}

.login_config {
  position: absolute;
  top: 20px;
  right: 20px;
}

.login-form:deep(.login-msg-yzm) {
  display: flex;
  width: 100%;
}

.login-form:deep(.login-msg-yzm) .el-button {
  margin-left: 10px;
  --el-button-size: 42px;
}

.qrCodeLogin {
  text-align: center;
  position: relative;
  padding: 20px 0;
}

.qrCodeLogin img.qrCode {
  background: #fff;
  padding: 20px;
  border-radius: 10px;
}

.qrCodeLogin p.msg {
  margin-top: 15px;
}

.qrCodeLogin .qrCodeLogin-result {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  text-align: center;
  background: var(--el-mask-color);
}

@media (max-width: 1200px) {
  .login-form {
    width: 340px;
  }
}

@media (max-width: 1000px) {
  .login_main {
    display: block;
  }
  .login_main .login_config {
    position: static;
    padding: 20px 20px 0 20px;
    text-align: right;
  }
  .login-form {
    width: 100%;
    padding: 20px 40px;
  }
  .login_adv {
    display: none;
  }
}
</style>
