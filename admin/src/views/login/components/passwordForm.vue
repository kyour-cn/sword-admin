<template>
  <el-form ref="loginForm" :model="state.form" :rules="state.rules" label-width="0" size="large"
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

    <el-form-item style="margin-bottom: 10px;">
      <el-col :span="12">
        <el-checkbox :label="$t('login.rememberMe')" v-model="state.form.autologin"></el-checkbox>
      </el-col>
      <el-col :span="12" class="login-forgot">
        <router-link to="/reset_password">{{ $t('login.forgetPassword') }}？</router-link>
      </el-col>
    </el-form-item>
    <el-form-item>
      <el-popover :visible="state.captchaShow" placement="top-start" width="350">
        <CaptchaSlide
          v-if="state.captchaShow"
          :data="state.captchaData"
          :events="{
            close: closeCaptcha,
            refresh: refreshCaptcha,
            confirm: confirmEvent,
          }"
        />
        <template #reference>
          <el-button type="primary" style="width: 100%;" :loading="state.isLogin" round @click="refreshCaptcha">
            {{ $t('login.signIn') }}
          </el-button>
        </template>
      </el-popover>
    </el-form-item>
    <div class="login-reg">
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
import {Slide as CaptchaSlide} from 'go-captcha-vue'
import {getCurrentInstance, reactive, ref} from "vue";
import authApi from "@/api/common/auth.js"
import tool from "@/utils/tool.js";
import {ElMessage, ElMessageBox} from "element-plus";
import router from "@/router/index.js";
import "go-captcha-vue/dist/style.css"

const proxy = getCurrentInstance().proxy
const loginForm = ref(null)

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
  captchaShow: false,
  captchaData: null,
  dialogRoleVisible: false,
  appList: [],
})

const selectedApp = ref(null)

const refreshCaptcha = () => {
  if(!config.LOGIN_VERIFY) {
    confirmEvent()
    return
  }
  state.captchaShow = false
  authApi.captcha.get().then(res => {
    if (res.code === 0) {

      state.captchaData = {
        image: res.data.image_base64,
        thumb: res.data.tile_base64,
        captKey: res.data.captcha_key,
        thumbX: res.data.tile_x,
        thumbY: res.data.tile_y,
        thumbWidth: res.data.tile_width,
        thumbHeight: res.data.tile_height,
      }

      state.captchaShow = true
    } else {
      ElMessage.error(res.message)
    }
  })
}

const closeCaptcha = () => {
  state.captchaShow = false
}

const confirmEvent = async (point) => {
  closeCaptcha()

  const validate = await loginForm.value.validate().catch()
  if (!validate) {
    return false
  }

  state.isLogin = true
  const data = {
    username: state.form.user,
    password: tool.crypto.MD5(state.form.password),
    md5: true,
    point: point,
    captcha_key: state.captchaData?.captKey
  };
  //获取token
  const user = await authApi.login.post(data);
  if (user.code === 0) {
    tool.cookie.set("TOKEN", user.data.token, {
      expires: state.form.autologin ? user.data.expire : 0
    })
    tool.data.set("USER_INFO", user.data.userInfo)
  } else {
    if (user.code === 102) {
      refreshCaptcha()
    }
    state.isLogin = false
    ElMessage.warning(user.message)
    return false
  }

  state.appList = Object.values(user.data.apps)
  selectedApp.value = null

  // 获取应用
  if (state.appList.length === 0) {
    ElMessage.error("该账号暂无应用权限！")
    return
  } else if (state.appList.length === 1) {
    await getMenu(state.appList[0].id)
  } else {
    // 存在多个应用，让用户选择
    state.dialogRoleVisible = true
    // 默认选中第一个
    selectApp(state.appList[0])
  }

  state.isLogin = false
}

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
</style>
