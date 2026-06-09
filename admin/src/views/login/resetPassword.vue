<template>
  <common-page title="重置密码">
    <el-steps :active="stepActive" simple finish-status="success">
        <el-step title="填写新密码" />
        <el-step title="完成重置" />
    </el-steps>
    <el-form v-if="stepActive===0" ref="form" :model="form" :rules="rules" :label-width="120">
      <el-form-item label="登录账号" prop="user">
        <el-input v-model="form.user" placeholder="请输入登录账号"></el-input>
        <div class="el-form-item-msg">请输入注册时填写的登录账号</div>
      </el-form-item>
      <el-form-item label="手机号码" prop="phone">
        <el-input v-model="form.phone" placeholder="请输入手机号码"></el-input>
      </el-form-item>
      <el-form-item label="短信验证码" prop="yzm">
        <div class="yzm">
          <el-input v-model="form.yzm" placeholder="请输入6位短信验证码"></el-input>
          <el-button @click="getYzm" :disabled="disabled">获取验证码<span v-if="disabled"> ({{time}})</span></el-button>
        </div>
      </el-form-item>
      <el-form-item label="新密码" prop="newpw">
        <el-input v-model="form.newpw" show-password placeholder="请输入新密码"></el-input>
        <div class="el-form-item-msg">请输入包含英文、数字的8位以上密码</div>
      </el-form-item>
      <el-form-item label="确认新密码" prop="newpw2">
        <el-input v-model="form.newpw2" show-password placeholder="请再一次输入新密码"></el-input>
      </el-form-item>

      <el-form-item>
        <el-button type="primary" @click="save">提交</el-button>
      </el-form-item>
    </el-form>
    <el-result v-if="stepActive==1" icon="success" title="密码重置成功" sub-title="请牢记自己的新密码,返回登录后使用新密码登录">
      <template #extra>
        <el-button type="primary" @click="backLogin">返回登录</el-button>
      </template>
    </el-result>
  </common-page>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { useRouter } from 'vue-router'
import { ElMessage } from 'element-plus'
import commonPage from './components/commonPage'

const stepActive = ref(0)
const form = reactive({
  user: '',
  phone: '',
  yzm: '',
  newpw: '',
  newpw2: ''
})
const rules = {
  user: [
    { required: true, message: '请输入登录账号' }
  ],
  phone: [
    { required: true, message: '请输入手机号' }
  ],
  yzm: [
    { required: true, message: '请输入短信验证码' }
  ],
  newpw: [
    { required: true, message: '请输入新的密码' }
  ],
  newpw2: [
    { required: true, message: '请再次输入新的密码' },
    { validator: (rule, value, callback) => {
      if (value !== form.newpw) {
        callback(new Error('两次输入密码不一致'))
      } else {
        callback()
      }
    } }
  ]
}
const disabled = ref(false)
const time = ref(0)
const formRef = ref(null)
const router = useRouter()

async function getYzm() {
  const validate = await formRef.value.validateField('phone').catch(() => {})
  if (!validate) return false
  ElMessage.success('已发送短信至手机号码')
  disabled.value = true
  time.value = 60
  const t = setInterval(() => {
    time.value -= 1
    if (time.value < 1) {
      clearInterval(t)
      disabled.value = false
      time.value = 0
    }
  }, 1000)
}

async function save() {
  const validate = await formRef.value.validate().catch(() => {})
  if (!validate) return false
  stepActive.value = 1
}

function backLogin() {
  router.push({ path: '/login' })
}
</script>

<style scoped>


</style>
