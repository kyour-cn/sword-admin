import uploadApi from "../api/common/upload.js";

//上传配置
export default {
  apiObj: uploadApi.uploadImage, // 上传图片请求API对象
  filename: "file",              // form请求时文件的key
  successCode: 0,                // 请求完成代码
  maxSize: 10,                   // 最大文件大小 默认10MB
  parseData(res) {
    return {
      code: res.code,               // 分析状态字段结构
      fileName: res.data?.fileName, // 分析文件名称
      src: res.data?.url || res.data?.src, // 分析图片远程地址结构
      msg: res.message              // 分析描述字段结构
    }
  },
  apiObjFile: uploadApi.uploadFile,  // 附件上传请求API对象
  maxSizeFile: 10                    // 附件最大文件大小 默认10MB
}
