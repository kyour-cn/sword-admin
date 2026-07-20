<template>
  <el-form ref="loginForm" class="login-password-form" :model="state.form" :rules="state.rules" label-width="0" size="large"
           @keyup.enter="refreshCaptcha">
    <el-form-item prop="user">
      <el-input v-model="state.form.user" prefix-icon="el-icon-user" clearable
                :placeholder="$t('login.userPlaceholder')">
        <!--        <template #append>-->
        <!--          <el-select v-model="userType" style="width: 130px;">-->
        <!--            <el-option :label="$t('login.admin')" value="admin"></el-option>-->
        <!--            <el-option :label="$t('login.user')" value="user"></el-option>-->
        <!--          </el-select>-->
        <!--        </template>-->
      </el-input>
    </el-form-item>

    <el-form-item prop="password">
      <el-input v-model="state.form.password" prefix-icon="el-icon-lock" clearable show-password
                :placeholder="$t('login.PWPlaceholder')"></el-input>
    </el-form-item>

    <el-form-item class="login-options">
      <el-col :span="12">
        <el-checkbox :label="$t('login.rememberMe')" v-model="state.form.autologin"></el-checkbox>
      </el-col>
      <el-col v-if="config.PASSWORD_RESET" :span="12" class="login-forgot">
        <router-link to="/reset_password">{{ $t('login.forgetPassword') }}？</router-link>
      </el-col>
    </el-form-item>
    <el-form-item class="login-action">
      <div v-if="config.LOGIN_VERIFY" class="login-altcha">
        <altcha-widget
          v-if="state.altchaChallengeJson"
          ref="altchaRef"
          :key="state.altchaKey"
          :challengejson="state.altchaChallengeJson"
          :language="altchaLanguage"
          auto="off"
          hidefooter
          hidelogo
        />
      </div>
      <el-button class="login-submit" type="primary" :loading="state.isLogin" round @click="refreshCaptcha">
        {{ $t('login.signIn') }}
      </el-button>
    </el-form-item>
    <div v-if="config.ACCOUNT_REGISTER" class="login-reg">
      {{ $t('login.noAccount') }}
      <router-link to="/user_register">{{ $t('login.createAccount') }}</router-link>
    </div>
  </el-form>

  <el-dialog
    v-model="state.dialogRoleVisible"
    title="请选择应用"
    width="600px"
    :before-close="handleClose"
  >
    <div class="app-list">
      <el-row :gutter="20">
        <el-col
          v-for="item in state.appList"
          :key="item.id"
          :span="12"
        >
          <el-card
            :class="['app-item', { 'is-selected': selectedApp?.id === item.id }]"
            shadow="hover"
            @click="selectApp(item)"
          >
            <div class="app-header">
              <span class="app-name">{{ item.name }}</span>
            </div>
            <div class="app-remark">{{ item.remark }}</div>
          </el-card>
        </el-col>
      </el-row>
    </div>

    <template #footer>
      <el-button @click="handleClose">取 消</el-button>
      <el-button type="primary" @click="confirm">确 定</el-button>
    </template>
  </el-dialog>
</template>

<script setup>
import config from "@/config"
import 'altcha'
import 'altcha/i18n/zh-cn'
import {computed, getCurrentInstance, nextTick, onBeforeUnmount, onMounted, reactive, ref, watch} from "vue";
import authApi from "@/api/common/auth.js"
import tool from "@/utils/tool.js";
import {ElMessage, ElMessageBox} from "element-plus";
import router from "@/router/index.js";

const proxy = getCurrentInstance().proxy
const loginForm = ref(null)
const altchaRef = ref(null)

const state = reactive({
  form: {
    user: "",
    password: "",
    autologin: false,
  },
  rules: {
    user: [
      {required: true, message: proxy.$t('login.userError'), trigger: 'blur'}
    ],
    password: [
      {required: true, message: proxy.$t('login.PWError'), trigger: 'blur'}
    ]
  },
  isLogin: false,
  pendingLogin: false,
  altchaKey: 0,
  altchaChallengeJson: "",
  altchaPayload: "",
  dialogRoleVisible: false,
  appList: [],
})

const selectedApp = ref(null)
const altchaLanguage = computed(() => (proxy.$i18n.locale === 'zh-cn' ? 'zh-cn' : 'en'))

const loadAltchaChallenge = async () => {
  if (!config.LOGIN_VERIFY) {
    return true
  }

  try {
    const res = await authApi.captcha.get()
    if (res.code !== 0) {
      ElMessage.warning(res.message || '获取人机验证失败')
      return false
    }

    state.altchaPayload = ""
    state.altchaChallengeJson = JSON.stringify(res.data)
    await nextTick()
    return true
  } catch (error) {
    ElMessage.warning(error?.data?.message || error?.message || '获取人机验证失败')
    return false
  }
}

const refreshCaptcha = async () => {
  if (state.isLogin || state.pendingLogin) {
    return false
  }

  const validate = await loginForm.value.validate().catch(() => false)
  if (!validate) {
    return false
  }

  if(!config.LOGIN_VERIFY) {
    await confirmEvent()
    return
  }

  if (!state.altchaChallengeJson && (await loadAltchaChallenge()) === false) {
    return false
  }

  const verifiedPayload = getAltchaPayload()
  if (verifiedPayload) {
    state.altchaPayload = verifiedPayload
    await confirmEvent()
    return
  }

  try {
    const widget = altchaRef.value
    if (!widget?.verify) {
      ElMessage.warning('人机验证组件未加载完成，请稍后再试')
      return false
    }

    state.pendingLogin = true
    await widget.verify()
    await nextTick()

    const payload = getAltchaPayload()
    if (payload) {
      state.altchaPayload = payload
      await submitPendingLogin()
      return
    }

    if (state.pendingLogin && widget.getState?.() !== 'verifying') {
      state.pendingLogin = false
      ElMessage.warning('请点击上方“我不是机器人”完成人机验证')
    }
  } catch (error) {
    state.pendingLogin = false
    ElMessage.warning(error?.message || '人机验证失败，请重新验证')
    resetAltcha()
  }
}

const confirmEvent = async (event) => {
  const validate = await loginForm.value.validate().catch(() => false)
  if (!validate) {
    return false
  }

  const altchaPayload = event?.detail?.payload || state.altchaPayload
  if (config.LOGIN_VERIFY && !altchaPayload) {
    ElMessage.warning('请先完成人机验证')
    return false
  }
  state.altchaPayload = altchaPayload

  state.isLogin = true
  try {
    const data = {
      username: state.form.user,
      password: tool.crypto.MD5(state.form.password),
      md5: true,
      altcha: altchaPayload
    };
    //获取token
    const user = await authApi.login.post(data);
    if (user.code === 0) {
      tool.cookie.set("TOKEN", user.data.token, {
        expires: state.form.autologin ? user.data.expire : 0
      })
      tool.data.set("USER_INFO", user.data.userInfo)
    } else {
      resetAltcha()
      ElMessage.warning(user.message)
      return false
    }

    state.appList = Object.values(user.data.apps)
    selectedApp.value = null

    // 获取应用
    if (state.appList.length === 0) {
      // 无角色账号不保留无效登录态，避免后续路由误判为已登录。
      tool.cookie.remove("TOKEN")
      tool.data.remove("USER_INFO")
      resetAltcha()
      ElMessage.error("该账号暂无应用权限！")
      return false
    } else if (state.appList.length === 1) {
      if (await getMenu(state.appList[0].id) === false) {
        return false
      }
    } else {
      // 存在多个应用，让用户选择
      state.dialogRoleVisible = true
      // 默认选中第一个
      selectApp(state.appList[0])
    }
  } catch (error) {
    resetAltcha()
    ElMessage.warning(error?.message || '登录失败')
    return false
  } finally {
    state.isLogin = false
  }
}

const getAltchaPayload = () => {
  return state.altchaPayload || altchaRef.value?.querySelector?.('input[name="altcha"]')?.value || ""
}

const getAltchaEventDetail = (event) => {
  return event?.detail || event || {}
}

const handleAltchaVerified = async (event) => {
  const detail = getAltchaEventDetail(event)
  state.altchaPayload = detail.payload || getAltchaPayload()
  await submitPendingLogin()
}

const handleAltchaStateChange = (event) => {
  const detail = getAltchaEventDetail(event)
  state.altchaPayload = detail.payload || getAltchaPayload()

  if (detail.state === 'verified') {
    submitPendingLogin()
    return
  }

  if (['error', 'expired', 'unverified'].includes(detail.state)) {
    state.pendingLogin = false
    if (detail.state !== 'verified') {
      state.altchaPayload = ""
    }
  }
}

const submitPendingLogin = async () => {
  if (!state.pendingLogin) {
    return
  }

  const payload = getAltchaPayload()
  if (!payload) {
    return
  }

  state.pendingLogin = false
  state.altchaPayload = payload
  await confirmEvent()
}

const resetAltcha = () => {
  state.pendingLogin = false
  state.altchaPayload = ""
  state.altchaChallengeJson = ""
  state.altchaKey += 1
  loadAltchaChallenge()
}

watch(altchaRef, (element, oldElement) => {
  oldElement?.removeEventListener?.('verified', handleAltchaVerified)
  oldElement?.removeEventListener?.('statechange', handleAltchaStateChange)
  element?.addEventListener?.('verified', handleAltchaVerified)
  element?.addEventListener?.('statechange', handleAltchaStateChange)
})

onBeforeUnmount(() => {
  altchaRef.value?.removeEventListener?.('verified', handleAltchaVerified)
  altchaRef.value?.removeEventListener?.('statechange', handleAltchaStateChange)
})

onMounted(() => {
  loadAltchaChallenge()
})

// 点击选中
function selectApp(item) {
  selectedApp.value = item
}

// 确认选择
function confirm() {
  if (!selectedApp.value) {
    return ElMessage.warning('请先选择一个应用')
  }
  state.dialogRoleVisible = false
  getMenu(selectedApp.value.id)
}

// 关闭弹窗前清空
function handleClose() {
  selectedApp.value = null
  state.dialogRoleVisible = false
}

const getMenu = async (appId) => {
  //获取菜单
  const res = await authApi.menu.get({
    app_id: appId
  });
  if (res.code === 0) {
    if (res.data.menu.length === 0) {
      state.isLogin = false
      await ElMessageBox.alert("当前用户无任何菜单权限，请联系系统管理员", "无权限访问", {
        type: 'error',
        center: true
      })
      return false
    }
    tool.data.set("MENU", res.data.menu)
    tool.data.set("PERMISSIONS", res.data.permissions)
  } else {
    state.isLogin = false
    ElMessage.warning(res.message)
    return false
  }
  //默认路由地址
  const defaultRoute = config.DASHBOARD_URL;

  //递归菜单及子菜单，判断是否存在默认路由
  const findDefaultRoute = (menu) => {
    for (let i = 0; i < menu.length; i++) {
      if (menu[i].path === defaultRoute) {
        return true
      }
      if (menu[i].children && menu[i].children.length) {
        return findDefaultRoute(menu[i].children)
      }
    }
    return false
  }
  //不存在默认路由，跳转到第一个菜单
  if (!findDefaultRoute(res.data.menu)) {
    //取第一个菜单
    let menuItem = res.data.menu[0];
    if (menuItem.children?.length) {
      menuItem = menuItem.children[0]
    }
    await router.replace({
      path: menuItem.path
    })
  } else {
    await router.replace({
      path: defaultRoute
    })
  }

  ElMessage.success("登录成功")
}

</script>

<style lang="scss" scoped>
.login-password-form {
  :deep(.el-form-item) {
    margin-bottom: 18px;
  }

  :deep(.el-input__wrapper) {
    min-height: 48px;
    padding: 0 16px;
    border-radius: 12px;
    background: var(--el-bg-color);
    box-shadow: 0 0 0 1px var(--el-border-color-light) inset;
    transition: box-shadow 0.2s ease, background-color 0.2s ease;
  }

  :deep(.el-input__wrapper:hover) {
    box-shadow: 0 0 0 1px var(--el-color-primary-light-5) inset;
  }

  :deep(.el-input__wrapper.is-focus) {
    box-shadow: 0 0 0 1px var(--el-color-primary) inset;
  }

  :deep(.el-input__prefix) {
    color: var(--el-text-color-secondary);
  }

  .login-options {
    margin: 4px 0 18px;

    :deep(.el-form-item__content) {
      align-items: center;
      line-height: 1;
    }

    :deep(.el-checkbox) {
      height: 24px;
      color: var(--el-text-color-regular);
      font-weight: 500;
    }

    :deep(.el-checkbox__inner) {
      width: 17px;
      height: 17px;
      border-radius: 6px;
      box-shadow: 0 2px 8px rgba(45, 70, 130, 0.08);
    }
  }

  .login-action {
    margin-bottom: 0;
  }
}

.app-list {
  margin-top: 20px;
}

.app-item {
  cursor: pointer;
  border: 1px solid #ebeef5;
  border-radius: 8px;
  transition: all 0.3s ease;

  &.is-selected {
    border-color: #409eff;
    background-color: #f0f9ff;
  }

  &:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  }

  .app-header {
    .app-name {
      font-size: 16px;
      font-weight: 600;
      color: #303133;
    }
  }

  .app-remark {
    margin-top: 8px;
    font-size: 14px;
    color: #606266;
    line-height: 1.4;
  }
}

.el-dialog__footer {
  text-align: right;
}

.login-altcha {
  width: 100%;
  margin-bottom: 16px;

  altcha-widget {
    display: block;
    width: 100%;
    --altcha-max-width: 100%;
    --altcha-border-radius: 12px;
    --altcha-color-border: var(--el-border-color-light);
    --altcha-color-border-focus: var(--el-color-primary);
    --altcha-color-active: var(--el-color-primary);
    --altcha-color-base: var(--el-bg-color);
    --altcha-color-text: var(--el-text-color-primary);
    --altcha-color-border-hover: var(--el-color-primary-light-5);
    filter: none;
  }
}

.login-submit {
  width: 100%;
  height: 50px;
  border: none;
  border-radius: 14px;
  font-size: 16px;
  font-weight: 600;
  letter-spacing: 0;
  background: linear-gradient(135deg, var(--el-color-primary), #5b6cff);
  box-shadow: none;
  transition: opacity 0.2s ease;

  &:hover,
  &:focus {
    opacity: 0.94;
  }

  &:active {
    opacity: 0.88;
  }
}

.login-reg {
  margin-top: 18px;
  text-align: center;
  color: var(--el-text-color-secondary);

  a {
    font-weight: 600;
  }
}

:global(html.dark) {
  .login-password-form {
    :deep(.el-input__wrapper) {
      background: var(--el-bg-color-overlay);
      box-shadow: 0 0 0 1px var(--el-border-color-light) inset;
    }
  }

  .login-altcha {
    altcha-widget {
      --altcha-color-base: var(--el-bg-color-overlay);
      filter: none;
    }
  }
}
</style>
