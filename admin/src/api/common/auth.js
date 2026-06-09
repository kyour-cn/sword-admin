import config from "@/config"
import http from "@/utils/request"

export default {
  captcha: {
    url: `${config.API_URL}/admin/auth/captcha`,
    name: "验证码数据",
    get: async function () {
      return await http.get(this.url);
    }
  },
  login: {
    url: `${config.API_URL}/admin/auth/login`,
    name: "登录获取TOKEN",
    post: async function (data = {}) {
      return await http.post(this.url, data);
    }
  },
  menu: {
    url: `${config.API_URL}/admin/auth/menu`,
    name: "获取菜单",
    get: async function (params = {}) {
      return await http.get(this.url,  params);
    }
  }
}
