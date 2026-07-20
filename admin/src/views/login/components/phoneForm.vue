<template>
  <el-form ref="loginForm" class="login-phone-form" :model="state.form" :rules="state.rules" label-width="0" size="large" @keyup.enter="login">
    <el-form-item prop="phone">
      <el-input v-model="state.form.phone" clearable :placeholder="$t('login.mobilePlaceholder')">
        <template #prefix>
          <span class="phone-code">+86</span>
        </template>
      </el-input>
    </el-form-item>
    <el-form-item prop="yzm" class="login-code-item">
      <div class="login-msg-yzm">
        <el-input v-model="state.form.yzm" prefix-icon="el-icon-unlock" clearable :placeholder="$t('login.smsPlaceholder')"></el-input>
        <el-button @click="getYzm" :disabled="state.disabled">{{$t('login.smsGet')}}<span v-if="state.disabled"> ({{state.time}})</span></el-button>
      </div>
    </el-form-item>
    <el-form-item>
      <el-button class="login-submit" type="primary" :loading="state.islogin" round @click="login">{{ $t('login.signIn') }}</el-button>
    </el-form-item>
    <div v-if="config.ACCOUNT_REGISTER" class="login-reg">
      {{$t('login.noAccount')}} <router-link to="/user_register">{{$t('login.createAccount')}}</router-link>
    </div>
  </el-form>
</template>

<script setup>
import { getCurrentInstance, reactive, ref, onUnmounted } from 'vue'
import { ElMessage } from 'element-plus'
import config from "@/config"

const proxy = getCurrentInstance().proxy

const loginForm = ref(null)
const timer = ref(null)

const state = reactive({
  form: {
    phone: '',
    yzm: ''
  },
  rules: {
    phone: [
      { required: true, message: proxy.$t('login.mobileError') }
    ],
    yzm: [
      { required: true, message: proxy.$t('login.smsError') }
    ]
  },
  disabled: false,
  time: 0,
  islogin: false
})

const getYzm = async () => {
  try {
    await loginForm.value.validateField('phone')
  } catch (e) {
    return false
  }

  // TODO: 待实现发送验证码

  ElMessage.success(proxy.$t('login.smsSent'))
  state.disabled = true
  state.time = 60

  if (timer.value) {
    clearInterval(timer.value)
    timer.value = null
  }
  timer.value = setInterval(() => {
    state.time -= 1
    if (state.time < 1) {
      clearInterval(timer.value)
      timer.value = null
      state.disabled = false
      state.time = 0
    }
  }, 1000)
}

const login = async () => {
  const validate = await loginForm.value.validate().catch(() => false)
  if (!validate) {
    return false
  }

  // TODO: 待实现登录逻辑

}

onUnmounted(() => {
  if (timer.value) {
    clearInterval(timer.value)
    timer.value = null
  }
})
</script>

<style lang="scss" scoped>
.login-phone-form {
  :deep(.el-form-item) {
    margin-bottom: 18px;
  }

  :deep(.el-input__wrapper) {
    min-height: 48px;
    padding: 0 16px;
    border-radius: 12px;
    background: var(--el-bg-color);
    box-shadow: 0 0 0 1px var(--el-border-color-light) inset;
  }

  :deep(.el-input__wrapper:hover) {
    box-shadow: 0 0 0 1px var(--el-color-primary-light-5) inset;
  }

  :deep(.el-input__wrapper.is-focus) {
    box-shadow: 0 0 0 1px var(--el-color-primary) inset;
  }

  .login-code-item {
    margin-bottom: 18px;
  }

  :deep(.phone-code) {
    display: inline-flex;
    height: 20px;
    padding-right: 12px;
    margin-right: 12px;
    align-items: center;
    border-right: 1px solid var(--el-border-color-light);
    color: var(--el-text-color-secondary);
    font-size: 14px;
    line-height: 1;
  }

  .login-msg-yzm .el-button {
    height: 48px;
    margin-left: 10px;
    border-radius: 12px;
  }
}

.login-submit {
  width: 100%;
  height: 50px;
  border: none;
  border-radius: 14px;
  font-size: 16px;
  font-weight: 600;
  background: linear-gradient(135deg, var(--el-color-primary), #5b6cff);
  box-shadow: none;
  transition: opacity 0.2s ease;

  &:hover,
  &:focus {
    opacity: 0.94;
  }
}

.login-reg {
  margin-top: 18px;
  color: var(--el-text-color-secondary);
  text-align: center;

  a {
    font-weight: 600;
  }
}
</style>
