import config from "./config"
// import { permission, rolePermission } from './utils/permission'

import ScStatusIndicator from './components/scMini/scStatusIndicator'

import auth from './directives/auth'
import time from './directives/time'
import errorHandler from './utils/errorHandler'

import * as elIcons from '@element-plus/icons-vue'
import * as scIcons from './assets/icons'

export default {
  install(app) {
    //挂载全局对象
    app.config.globalProperties.$CONFIG = config;
    // app.config.globalProperties.$AUTH = permission;
    // app.config.globalProperties.$ROLE = rolePermission;

    //注册全局组件
    app.component('ScStatusIndicator', ScStatusIndicator);

    //注册全局指令
    app.directive('auth', auth)
    app.directive('time', time)

    //统一注册el-icon图标
    for(let icon in elIcons){
      app.component(`ElIcon${icon}`, elIcons[icon])
    }
    //统一注册sc-icon图标
    for(let icon in scIcons){
      app.component(`ScIcon${icon}`, scIcons[icon])
    }

    //关闭async-validator全局控制台警告
    window.ASYNC_VALIDATOR_NO_WARNING = 1

    //全局代码错误捕捉
    app.config.errorHandler = errorHandler
  }
}
