<template>
  <div class="register-page">
    <header class="register-header">
      <router-link class="brand" to="/login">
        <img :alt="$CONFIG.APP_NAME" :src="$CONFIG.APP_LOGO">
        <span>{{ $CONFIG.APP_NAME }}</span>
      </router-link>
      <router-link class="back-login" to="/login">
        <el-icon><el-icon-arrow-left /></el-icon>
        返回登录
      </router-link>
    </header>

    <main class="register-container">
      <section class="register-intro">
        <div class="intro-content">
          <span class="intro-tag">创建账号</span>
          <h1>创建你的账号</h1>
          <p>只需填写基础账号与个人资料，即可完成注册。访问权限将在注册后由管理员统一分配。</p>
          <div class="intro-points">
            <div class="intro-point">
              <span class="point-icon"><el-icon><el-icon-key /></el-icon></span>
              <div>
                <strong>安全的账号凭证</strong>
                <span>密码需同时包含英文与数字</span>
              </div>
            </div>
            <div class="intro-point">
              <span class="point-icon"><el-icon><el-icon-user /></el-icon></span>
              <div>
                <strong>简洁的个人资料</strong>
                <span>仅需昵称，手机号可选填</span>
              </div>
            </div>
            <div class="intro-point">
              <span class="point-icon"><el-icon><el-icon-lock /></el-icon></span>
              <div>
                <strong>独立的权限管理</strong>
                <span>新账号默认不关联任何角色</span>
              </div>
            </div>
          </div>
        </div>
        <div class="intro-decoration intro-decoration-top"></div>
        <div class="intro-decoration intro-decoration-bottom"></div>
      </section>

      <section class="register-panel">
        <div v-if="stepActive < 2" class="panel-content">
          <div class="panel-heading">
            <span class="step-label">步骤 {{ stepActive + 1 }} / 2</span>
            <h2>{{ stepActive === 0 ? '设置登录信息' : '完善个人资料' }}</h2>
            <p>{{ stepActive === 0 ? '登录账号是进入系统的唯一凭证' : '告诉我们如何称呼你' }}</p>
          </div>

          <el-steps class="register-steps" :active="stepActive" finish-status="success" align-center>
            <el-step title="账号信息" />
            <el-step title="个人资料" />
          </el-steps>

          <el-form
            v-show="stepActive === 0"
            ref="accountFormRef"
            class="register-form"
            :model="form"
            :rules="accountRules"
            label-position="top"
            size="large"
            @keyup.enter="next"
          >
            <el-form-item label="登录账号" prop="username">
              <el-input
                v-model="form.username"
                prefix-icon="el-icon-user"
                placeholder="请输入 3-32 位登录账号"
                maxlength="32"
                clearable
                autocomplete="username"
              />
            </el-form-item>
            <el-form-item label="登录密码" prop="password">
              <el-input
                v-model="form.password"
                prefix-icon="el-icon-lock"
                type="password"
                placeholder="请输入包含英文、数字的 8 位以上密码"
                maxlength="64"
                show-password
                autocomplete="new-password"
              />
              <sc-password-strength v-model="form.password" />
            </el-form-item>
            <el-form-item label="确认密码" prop="confirmPassword">
              <el-input
                v-model="form.confirmPassword"
                prefix-icon="el-icon-lock"
                type="password"
                placeholder="请再次输入登录密码"
                maxlength="64"
                show-password
                autocomplete="new-password"
              />
            </el-form-item>
            <el-button class="primary-action" type="primary" round @click="next">
              继续完善资料
              <el-icon class="el-icon--right"><el-icon-arrow-right /></el-icon>
            </el-button>
          </el-form>

          <el-form
            v-show="stepActive === 1"
            ref="profileFormRef"
            class="register-form"
            :model="form"
            :rules="profileRules"
            label-position="top"
            size="large"
            @keyup.enter="submit"
          >
            <el-form-item label="昵称" prop="nickname">
              <el-input
                v-model="form.nickname"
                prefix-icon="el-icon-postcard"
                placeholder="请输入昵称"
                maxlength="32"
                show-word-limit
                clearable
              />
            </el-form-item>
            <el-form-item label="手机号（选填）" prop="mobile">
              <el-input
                v-model="form.mobile"
                prefix-icon="el-icon-phone"
                placeholder="请输入手机号"
                maxlength="11"
                clearable
                inputmode="numeric"
                autocomplete="tel"
              />
              <div class="field-hint">手机号仅作为个人资料保存，暂不用于登录。</div>
            </el-form-item>
            <div class="form-actions">
              <el-button class="secondary-action" round :disabled="submitting" @click="pre">
                上一步
              </el-button>
              <el-button class="primary-action" type="primary" round :loading="submitting" @click="submit">
                {{ submitting ? '正在创建账号' : '完成注册' }}
              </el-button>
            </div>
          </el-form>

          <div class="panel-footer">
            已有账号？<router-link to="/login">直接登录</router-link>
          </div>
        </div>

        <div v-else class="register-result">
          <div class="success-icon">
            <el-icon><el-icon-check /></el-icon>
          </div>
          <span class="success-tag">注册完成</span>
          <h2>账号注册成功</h2>
          <p>你的账号已创建，但目前暂无角色和访问权限。请联系管理员分配角色后再登录系统。</p>
          <el-button class="primary-action" type="primary" round @click="goLogin">
            返回登录
            <el-icon class="el-icon--right"><el-icon-arrow-right /></el-icon>
          </el-button>
        </div>
      </section>
    </main>
  </div>
</template>

<script setup>
import {reactive, ref} from 'vue'
import {useRouter} from 'vue-router'
import {ElMessage} from 'element-plus'
import authApi from '@/api/common/auth.js'
import scPasswordStrength from '@/components/scPasswordStrength'

const router = useRouter()
const accountFormRef = ref(null)
const profileFormRef = ref(null)
const stepActive = ref(0)
const submitting = ref(false)

const form = reactive({
  username: '',
  password: '',
  confirmPassword: '',
  nickname: '',
  mobile: ''
})

const validateUsername = (_, value, callback) => {
  if (!/^[A-Za-z0-9][A-Za-z0-9_.-]{2,31}$/.test(value || '')) {
    callback(new Error('账号须为 3-32 位字母、数字、下划线、点或短横线'))
    return
  }
  callback()
}

const validatePassword = (_, value, callback) => {
  const password = value || ''
  if (password.length < 8 || !/[A-Za-z]/.test(password) || !/\d/.test(password)) {
    callback(new Error('请输入包含英文、数字的 8 位以上密码'))
    return
  }
  callback()
}

const validateConfirmPassword = (_, value, callback) => {
  if (value !== form.password) {
    callback(new Error('两次输入密码不一致'))
    return
  }
  callback()
}

const validateMobile = (_, value, callback) => {
  if (value && !/^1\d{10}$/.test(value)) {
    callback(new Error('请输入合法的手机号'))
    return
  }
  callback()
}

const validateNickname = (_, value, callback) => {
  if (!value?.trim()) {
    callback(new Error('请输入昵称'))
    return
  }
  callback()
}

const accountRules = {
  username: [
    {required: true, message: '请输入登录账号', trigger: 'blur'},
    {validator: validateUsername, trigger: 'blur'}
  ],
  password: [
    {required: true, message: '请输入登录密码', trigger: 'blur'},
    {validator: validatePassword, trigger: 'blur'}
  ],
  confirmPassword: [
    {required: true, message: '请再次输入登录密码', trigger: 'blur'},
    {validator: validateConfirmPassword, trigger: 'blur'}
  ]
}

const profileRules = {
  nickname: [
    {required: true, message: '请输入昵称', trigger: 'blur'},
    {validator: validateNickname, trigger: 'blur'},
    {min: 1, max: 32, message: '昵称不能超过 32 个字符', trigger: 'blur'}
  ],
  mobile: [
    {validator: validateMobile, trigger: 'blur'}
  ]
}

const next = async () => {
  if (stepActive.value !== 0) {
    return
  }
  const valid = await accountFormRef.value.validate().catch(() => false)
  if (valid) {
    stepActive.value = 1
  }
}

const pre = () => {
  if (!submitting.value) {
    stepActive.value = 0
  }
}

const submit = async () => {
  if (submitting.value || stepActive.value !== 1) {
    return
  }
  const valid = await profileFormRef.value.validate().catch(() => false)
  if (!valid) {
    return
  }

  submitting.value = true
  try {
    const res = await authApi.register.post({
      username: form.username.trim(),
      password: form.password,
      confirm_password: form.confirmPassword,
      nickname: form.nickname.trim(),
      mobile: form.mobile.trim()
    })
    if (res.code !== 0) {
      ElMessage.warning(res.message || '注册失败，请稍后重试')
      return
    }
    stepActive.value = 2
  } catch (error) {
    ElMessage.warning(error?.data?.message || error?.message || '注册失败，请稍后重试')
  } finally {
    submitting.value = false
  }
}

const goLogin = () => {
  router.push({path: '/login'})
}
</script>

<style lang="scss" scoped>
.register-page {
  position: relative;
  box-sizing: border-box;
  width: 100%;
  min-height: 100%;
  padding: 88px 32px 40px;
  overflow: hidden;
  background:
    radial-gradient(circle at 12% 16%, rgba(91, 108, 255, 0.14), transparent 28%),
    radial-gradient(circle at 88% 84%, rgba(64, 158, 255, 0.12), transparent 26%),
    var(--el-fill-color-lighter);
}

.register-header {
  position: absolute;
  z-index: 2;
  top: 0;
  right: 0;
  left: 0;
  display: flex;
  height: 72px;
  padding: 0 5%;
  align-items: center;
  justify-content: space-between;
}

.brand,
.back-login {
  display: inline-flex;
  align-items: center;
  text-decoration: none;
}

.brand {
  gap: 10px;
  color: var(--el-text-color-primary);
  font-size: 20px;
  font-weight: 700;

  img {
    width: 36px;
    height: 36px;
    object-fit: contain;
  }
}

.back-login {
  gap: 6px;
  color: var(--el-text-color-regular);
  font-size: 14px;
  transition: color 0.2s ease, transform 0.2s ease;

  &:hover {
    color: var(--el-color-primary);
    transform: translateX(-2px);
  }
}

.register-container {
  position: relative;
  z-index: 1;
  display: grid;
  width: min(1080px, 100%);
  min-height: 650px;
  margin: 0 auto;
  overflow: hidden;
  grid-template-columns: 0.88fr 1.12fr;
  border: 1px solid var(--el-border-color-lighter);
  border-radius: 28px;
  background: var(--el-bg-color);
  box-shadow: 0 32px 80px rgba(35, 54, 105, 0.14);
}

.register-intro {
  position: relative;
  display: flex;
  padding: 64px 52px;
  overflow: hidden;
  align-items: center;
  color: #fff;
  background: linear-gradient(155deg, #4455dc 0%, #586df2 52%, #348fdf 100%);
}

.intro-content {
  position: relative;
  z-index: 1;
}

.intro-tag,
.success-tag,
.step-label {
  font-size: 12px;
  font-weight: 700;
  letter-spacing: 0.12em;
}

.intro-tag {
  color: rgba(255, 255, 255, 0.72);
}

.register-intro h1 {
  margin: 16px 0 18px;
  font-size: 40px;
  line-height: 1.2;
  letter-spacing: -0.04em;
}

.register-intro > .intro-content > p {
  margin: 0;
  color: rgba(255, 255, 255, 0.76);
  font-size: 15px;
  line-height: 1.8;
}

.intro-points {
  display: grid;
  margin-top: 42px;
  gap: 22px;
}

.intro-point {
  display: flex;
  align-items: center;
  gap: 14px;

  .point-icon {
    display: inline-flex;
    width: 42px;
    height: 42px;
    flex: 0 0 42px;
    align-items: center;
    justify-content: center;
    border: 1px solid rgba(255, 255, 255, 0.18);
    border-radius: 13px;
    background: rgba(255, 255, 255, 0.1);
    font-size: 18px;
    backdrop-filter: blur(8px);
  }

  div {
    display: grid;
    gap: 4px;
  }

  strong {
    font-size: 14px;
    font-weight: 600;
  }

  span:last-child {
    color: rgba(255, 255, 255, 0.66);
    font-size: 12px;
  }
}

.intro-decoration {
  position: absolute;
  border: 1px solid rgba(255, 255, 255, 0.12);
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.05);
}

.intro-decoration-top {
  top: -110px;
  right: -130px;
  width: 320px;
  height: 320px;
}

.intro-decoration-bottom {
  right: 46px;
  bottom: -120px;
  width: 240px;
  height: 240px;
}

.register-panel {
  display: flex;
  padding: 48px 64px;
  align-items: center;
  justify-content: center;
}

.panel-content,
.register-result {
  width: 100%;
  max-width: 460px;
}

.panel-heading {
  h2 {
    margin: 8px 0;
    color: var(--el-text-color-primary);
    font-size: 28px;
    line-height: 1.3;
  }

  p {
    margin: 0;
    color: var(--el-text-color-secondary);
    font-size: 14px;
  }
}

.step-label {
  color: var(--el-color-primary);
}

.register-steps {
  margin: 30px 0 28px;

  :deep(.el-step__title) {
    font-size: 13px;
  }

  :deep(.el-step__icon) {
    width: 28px;
    height: 28px;
    border-width: 1px;
  }

  :deep(.el-step__line) {
    top: 13px;
  }
}

.register-form {
  :deep(.el-form-item) {
    margin-bottom: 20px;
  }

  :deep(.el-form-item__label) {
    height: auto;
    padding-bottom: 8px;
    color: var(--el-text-color-primary);
    line-height: 1;
    font-weight: 600;
  }

  :deep(.el-input__wrapper) {
    min-height: 48px;
    padding: 0 15px;
    border-radius: 12px;
    background: var(--el-fill-color-lighter);
    box-shadow: 0 0 0 1px transparent inset;
    transition: background-color 0.2s ease, box-shadow 0.2s ease, transform 0.2s ease;
  }

  :deep(.el-input__wrapper:hover) {
    background: var(--el-fill-color-light);
  }

  :deep(.el-input__wrapper.is-focus) {
    background: var(--el-bg-color);
    box-shadow: 0 0 0 1px var(--el-color-primary) inset, 0 10px 24px rgba(64, 99, 255, 0.1);
    transform: translateY(-1px);
  }

  :deep(.el-input__prefix) {
    color: var(--el-text-color-secondary);
  }
}

.field-hint {
  margin-top: 7px;
  color: var(--el-text-color-placeholder);
  font-size: 12px;
  line-height: 1.5;
}

.form-actions {
  display: grid;
  grid-template-columns: 0.36fr 0.64fr;
  gap: 12px;

  .el-button + .el-button {
    margin-left: 0;
  }
}

.primary-action,
.secondary-action {
  height: 48px;
  font-size: 15px;
  font-weight: 600;
}

.primary-action {
  width: 100%;
  border: none;
  background: linear-gradient(135deg, var(--el-color-primary), #5b6cff);
  box-shadow: 0 12px 26px rgba(64, 99, 255, 0.24);
  transition: transform 0.2s ease, box-shadow 0.2s ease;

  &:hover,
  &:focus {
    transform: translateY(-1px);
    box-shadow: 0 16px 32px rgba(64, 99, 255, 0.3);
  }
}

.secondary-action {
  border-color: var(--el-border-color);
}

.panel-footer {
  margin-top: 22px;
  color: var(--el-text-color-secondary);
  text-align: center;
  font-size: 13px;

  a {
    color: var(--el-color-primary);
    font-weight: 600;
    text-decoration: none;
  }
}

.register-result {
  text-align: center;

  .success-icon {
    display: inline-flex;
    width: 82px;
    height: 82px;
    margin-bottom: 28px;
    align-items: center;
    justify-content: center;
    border-radius: 26px;
    color: #fff;
    background: linear-gradient(145deg, #42c78a, #22a76a);
    box-shadow: 0 18px 36px rgba(34, 167, 106, 0.24);
    font-size: 38px;
  }

  .success-tag {
    display: block;
    color: var(--el-color-success);
  }

  h2 {
    margin: 12px 0;
    color: var(--el-text-color-primary);
    font-size: 30px;
  }

  p {
    max-width: 400px;
    margin: 0 auto 34px;
    color: var(--el-text-color-secondary);
    font-size: 14px;
    line-height: 1.8;
  }
}

:global(html.dark) {
  .register-page {
    background:
      radial-gradient(circle at 12% 16%, rgba(91, 108, 255, 0.16), transparent 28%),
      radial-gradient(circle at 88% 84%, rgba(64, 158, 255, 0.1), transparent 26%),
      var(--el-bg-color-page);
  }

  .register-container {
    box-shadow: 0 32px 80px rgba(0, 0, 0, 0.28);
  }
}

@media (max-width: 900px) {
  .register-page {
    padding: 84px 20px 28px;
  }

  .register-container {
    min-height: auto;
    grid-template-columns: 1fr;
  }

  .register-intro {
    padding: 36px 42px;
  }

  .register-intro h1 {
    margin: 10px 0 12px;
    font-size: 32px;
  }

  .intro-points {
    display: none;
  }

  .register-panel {
    min-height: 570px;
    padding: 42px;
  }
}

@media (max-width: 560px) {
  .register-page {
    padding: 72px 0 0;
    overflow: visible;
  }

  .register-header {
    height: 64px;
    padding: 0 18px;
  }

  .brand {
    font-size: 17px;

    img {
      width: 30px;
      height: 30px;
    }
  }

  .register-container {
    border-width: 1px 0 0;
    border-radius: 0;
    box-shadow: none;
  }

  .register-intro {
    padding: 28px 24px;
  }

  .register-intro h1 {
    font-size: 28px;
  }

  .register-intro > .intro-content > p {
    font-size: 13px;
  }

  .register-panel {
    min-height: 560px;
    padding: 34px 24px;
  }

  .panel-heading h2 {
    font-size: 24px;
  }
}
</style>
