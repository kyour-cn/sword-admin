import config from "@/config"
import http from "@/utils/request"

export default {
  info: {
    url: `${config.API_URL}/admin/user/info`,
    name: "用户信息",
    get: async function () {
      return await http.get(this.url);
    },
    post: async function (data = {}) {
      return await http.post(this.url, data);
    }
  },
  password: {
    url: `${config.API_URL}/admin/user/password`,
    name: "修改密码",
    post: async function (data = {}) {
      return await http.post(this.url, data);
    }
  },
  tasks: {
    url: `${config.API_URL}/admin/user/`,
    name: "修改密码",
    list: async function (param = {}) {
      return await http.get(this.url+'taskList', param);
    }
  }
}
