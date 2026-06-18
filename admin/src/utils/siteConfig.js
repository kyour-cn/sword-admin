import sysConfig from "@/config"
import axios from "axios"

let loadingPromise = null

export function applySiteConfig(data = {}) {
  const siteName = data.site_name || sysConfig.APP_NAME
  sysConfig.APP_NAME = siteName
  sysConfig.APP_LOGO = data.site_logo || sysConfig.APP_LOGO
  document.title = siteName

  if (Object.prototype.hasOwnProperty.call(data, "admin_captcha_switch")) {
    sysConfig.LOGIN_VERIFY = Boolean(data.admin_captcha_switch)
  }
  if (Object.prototype.hasOwnProperty.call(data, "account_register_switch")) {
    sysConfig.ACCOUNT_REGISTER = Boolean(data.account_register_switch)
  }
  if (Object.prototype.hasOwnProperty.call(data, "password_reset_switch")) {
    sysConfig.PASSWORD_RESET = Boolean(data.password_reset_switch)
  }
}

export function loadSiteConfig() {
  if (loadingPromise) {
    return loadingPromise
  }

  loadingPromise = axios.get(`${sysConfig.API_URL}/admin/site/config`, {
    params: {
      _: new Date().getTime()
    }
  }).then(res => {
    if (res.data?.code === 0) {
      applySiteConfig(res.data.data)
    }
    return res.data
  }).catch(err => {
    console.error(err)
    return null
  })

  return loadingPromise
}
