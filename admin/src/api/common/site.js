import config from "@/config"
import http from "@/utils/request"

export default {
  config: {
    url: `${config.API_URL}/admin/site/config`,
    name: "站点配置",
    get: async function () {
      return await http.get(this.url);
    }
  }
}
