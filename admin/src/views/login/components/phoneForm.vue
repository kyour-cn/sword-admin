<template>
  <el-form ref="loginForm" :model="state.form" :rules="state.rules" label-width="0" size="large" @keyup.enter="login">
    <el-form-item prop="phone">
      <el-input v-model="state.form.phone" prefix-icon="el-icon-iphone" clearable :placeholder="$t('login.mobilePlaceholder')">
        <template #prepend>+86</template>
      </el-input>
    </el-form-item>
    <el-form-item prop="yzm"  style="margin-bottom: 35px;">
      <div class="login-msg-yzm">
        <el-input v-model="state.form.yzm" prefix-icon="el-icon-unlock" clearable :placeholder="$t('login.smsPlaceholder')"></el-input>
        <el-button @click="getYzm" :disabled="state.disabled">{{$t('login.smsGet')}}<span v-if="state.disabled"> ({{state.time}})</span></el-button>
      </div>
    </el-form-item>
    <el-form-item>
      <el-button type="primary" style="width: 100%;" :loading="state.islogin" round @click="login">{{ $t('login.signIn') }}</el-button>
    </el-form-item>
    <div class="login-reg">
      {{$t('login.noAccount')}} <router-link to="/user_register">{{$t('login.createAccount')}}</router-link>
    </div>
  </el-form>
</template>

<script setup>
import { getCurrentInstance, reactive, ref, onUnmounted } from 'vue'
import { ElMessage } from 'element-plus'

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

<style>

</style>
