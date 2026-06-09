import config from '@/config';
import http from '@/utils/request';

export default {
  uploadImage: {
    url: `${config.API_URL}/admin/upload/image`,
    name: '图片上传',
    post: async function (params) {
      return await http.post(this.url, params);
    }
  },
  uploadFile: {
    url: `${config.API_URL}/admin/upload/file`,
    name: '文件上传',
    post: async function (params) {
      return await http.post(this.url, params);
    }
  }
}
