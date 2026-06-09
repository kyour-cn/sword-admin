<template>
  <div class="sc-upload" :class="{'sc-upload-round':round}" :style="style">
    <div v-if="file && file.status !== 'success'" class="sc-upload__uploading">
      <div class="sc-upload__progress">
        <el-progress :percentage="file.percentage" :text-inside="true" :stroke-width="16"/>
      </div>
      <el-image class="image" :src="file.tempFile" fit="cover"></el-image>
    </div>
    <div v-if="file && file.status==='success'" class="sc-upload__img">
      <el-image class="image" :src="file.url" :preview-src-list="[file.url]" fit="cover" hide-on-click-modal append-to-body :z-index="9999">
        <template #placeholder>
          <div class="sc-upload__img-slot">
            Loading...
          </div>
        </template>
      </el-image>
      <div class="sc-upload__img-actions" v-if="!disabled">
        <span class="del" @click="handleRemove()"><el-icon><el-icon-delete /></el-icon></span>
      </div>
    </div>
    <el-upload v-if="!file" class="uploader" ref="uploaderRef"
      :auto-upload="cropper?false:autoUpload"
      :disabled="disabled"
      :show-file-list="showFileList"
      :action="action"
      :name="name"
      :data="data"
      :accept="accept"
      :limit="1"
      :http-request="request"
      :on-change="change"
      :before-upload="before"
      :on-success="success"
      :on-error="error"
      :on-exceed="handleExceed">
      <slot>
        <div class="el-upload--picture-card">
          <div class="file-empty">
            <el-icon><component :is="icon" /></el-icon>
            <h4 v-if="title">{{title}}</h4>
          </div>
        </div>
      </slot>
    </el-upload>
    <span style="display:none!important"><el-input v-model="value"></el-input></span>
    <el-dialog title="剪裁" draggable v-model="cropperDialogVisible" :width="580" @closed="cropperClosed" destroy-on-close>
      <sc-cropper :src="cropperFile?.tempCropperFile" :compress="compress" :aspectRatio="aspectRatio" ref="cropperRef"></sc-cropper>
      <template #footer>
        <el-button @click="cropperDialogVisible=false" >取 消</el-button>
        <el-button type="primary" @click="cropperSave">确 定</el-button>
      </template>
    </el-dialog>
  </div>
</template>

<script setup>
import {ref, reactive, watch, onMounted, nextTick, defineAsyncComponent} from 'vue'
import {ElNotification, ElMessage, genFileId} from 'element-plus';
import config from "@/config/upload"
import tool from '@/utils/tool'

const scCropper = defineAsyncComponent(() => import('@/components/scCropper'))

// Props定义
const props = defineProps({
  modelValue: {type: String, default: ""},
  height: {type: Number, default: 148},
  width: {type: Number, default: 148},
  title: {type: String, default: ""},
  icon: {type: String, default: "el-icon-plus"},
  action: {type: String, default: ""},
  apiObj: {type: Object, default: () => {}},
  name: {type: String, default: config.filename},
  data: {type: Object, default: () => {}},
  accept: {type: String, default: "image/gif, image/jpeg, image/png"},
  maxSize: {type: Number, default: config.maxSizeFile},
  limit: {type: Number, default: 1},
  autoUpload: {type: Boolean, default: true},
  showFileList: {type: Boolean, default: false},
  disabled: {type: Boolean, default: false},
  round: {type: Boolean, default: false},
  onSuccess: {type: Function, default: () => {return true}},
  cropper: {type: Boolean, default: false},
  compress: {type: Number, default: 1},
  aspectRatio: {type: Number, default: NaN}
})

// Emits定义
const emit = defineEmits(['update:modelValue'])

// 响应式数据
const uploaderRef = ref(null)
const cropperRef = ref(null)
const value = ref("")
const file = ref(null)
const cropperDialogVisible = ref(false)
const cropperFile = ref(null)

const style = reactive({
  width: props.width + "px",
  height: props.height + "px"
})

// 监听器
watch(() => props.modelValue, (val) => {
  val = tool.resUrl(val)
  value.value = val
  newFile(val)
})

watch(value, (val) => {
  emit('update:modelValue', val)
})

// 生命周期
onMounted(() => {
  value.value = props.modelValue
  newFile(props.modelValue)
})

// 方法
const newFile = (url) => {
  if (url) {
    file.value = {
      status: "success",
      url: url
    }
  } else {
    file.value = null
  }
}

const cropperSave = () => {
  cropperRef.value.getCropFile(cropFile => {
    cropFile.uid = cropperFile.value.uid
    cropperFile.value.raw = cropFile

    file.value = cropperFile.value
    file.value.tempFile = URL.createObjectURL(file.value.raw)
    uploaderRef.value.submit()
  }, cropperFile.value.name, cropperFile.value.type)
  cropperDialogVisible.value = false
}

const cropperClosed = () => {
  if (cropperFile.value?.tempCropperFile) {
    URL.revokeObjectURL(cropperFile.value.tempCropperFile)
    delete cropperFile.value.tempCropperFile
  }
}

const handleRemove = () => {
  clearFiles()
}

const clearFiles = () => {
  if (file.value?.tempFile) {
    URL.revokeObjectURL(file.value.tempFile)
  }
  value.value = ""
  file.value = null
  nextTick(() => {
    uploaderRef.value?.clearFiles()
  })
}

const change = (uploadFile, files) => {
  if (files.length > 1) {
    files.splice(0, 1)
  }
  if (props.cropper && uploadFile.status === 'ready') {
    const acceptIncludes = ["image/gif", "image/jpeg", "image/png"].includes(uploadFile.raw.type)
    if (!acceptIncludes) {
      ElNotification.warning({
        title: '上传文件警告',
        message: '选择的文件非图像类文件'
      })
      return false
    }
    cropperFile.value = uploadFile
    cropperFile.value.tempCropperFile = URL.createObjectURL(uploadFile.raw)
    cropperDialogVisible.value = true
    return false
  }
  file.value = uploadFile
  if (uploadFile.status === 'ready') {
    uploadFile.tempFile = URL.createObjectURL(uploadFile.raw)
  }
}

const before = (uploadFile) => {
  const acceptIncludes = props.accept.replace(/\s/g, "").split(",").includes(uploadFile.type)
  if (!acceptIncludes) {
    ElNotification.warning({
      title: '上传文件警告',
      message: '选择的文件非图像类文件'
    })
    clearFiles()
    return false
  }
  const maxSize = uploadFile.size / 1024 / 1024 < props.maxSize
  if (!maxSize) {
    ElMessage.warning(`上传文件大小不能超过 ${props.maxSize}MB!`)
    clearFiles()
    return false
  }
}

const handleExceed = (files) => {
  const uploadFile = files[0]
  uploadFile.uid = genFileId()
  uploaderRef.value.handleStart(uploadFile)
}

const success = (res, uploadFile) => {
  // 释放内存删除blob
  if (uploadFile.tempFile) {
    URL.revokeObjectURL(uploadFile.tempFile)
    delete uploadFile.tempFile
  }
  const os = props.onSuccess(res, uploadFile)
  if (os !== undefined && os === false) {
    nextTick(() => {
      file.value = null
      value.value = ""
    })
    return false
  }
  const response = config.parseData(res)
  uploadFile.url = response.src
  value.value = uploadFile.url
}

const error = (err) => {
  nextTick(() => {
    clearFiles()
  })
  ElNotification.error({
    title: '上传文件未成功',
    message: err
  })
}

const request = (param) => {
  let apiObj = config.apiObj
  if (props.apiObj) {
    apiObj = props.apiObj
  }
  const data = new FormData()
  data.append(param.filename, param.file)
  for (const key in param.data) {
    data.append(key, param.data[key])
  }
  apiObj.post(data, {
    onUploadProgress: e => {
      const complete = parseInt(((e.loaded / e.total) * 100) | 0, 10)
      param.onProgress({ percent: complete })
    }
  }).then(res => {
    const response = config.parseData(res)
    if (response.code === config.successCode) {
      param.onSuccess(res)
    } else {
      param.onError(response.msg || "未知错误")
    }
  }).catch(err => {
    param.onError(err)
  })
}

// 暴露方法给父组件
defineExpose({
  clearFiles,
  file,
  value
})
</script>

<style scoped>
  .el-form-item.is-error .sc-upload .el-upload--picture-card {border-color: var(--el-color-danger);}
  .sc-upload .el-upload--picture-card {border-radius: 0;}

  .sc-upload .uploader,.sc-upload:deep(.el-upload) {width: 100%;height: 100%;}

  .sc-upload__img {width: 100%;height: 100%;position: relative;}
  .sc-upload__img .image {width: 100%;height: 100%;}
  .sc-upload__img-actions {position: absolute;top:0;right: 0;display: none;}
  .sc-upload__img-actions span {display: flex;justify-content: center;align-items: center;width: 25px;height:25px;cursor: pointer;color: #fff;}
  .sc-upload__img-actions span i {font-size: 12px;}
  .sc-upload__img-actions .del {background: #F56C6C;}
  .sc-upload__img:hover .sc-upload__img-actions {display: block;}
  .sc-upload__img-slot {display: flex;justify-content: center;align-items: center;width: 100%;height: 100%;font-size: 12px;background-color: var(--el-fill-color-lighter);}

  .sc-upload__uploading {width: 100%;height: 100%;position: relative;}
  .sc-upload__progress {position: absolute;width: 100%;height: 100%;display: flex;justify-content: center;align-items: center;background-color: var(--el-overlay-color-lighter);z-index: 1;padding:10px;}
  .sc-upload__progress .el-progress {width: 100%;}
  .sc-upload__uploading .image {width: 100%;height: 100%;}

  .sc-upload .file-empty {width: 100%;height: 100%;display: flex;justify-content: center;align-items: center;flex-direction: column;}
  .sc-upload .file-empty i {font-size: 28px;}
  .sc-upload .file-empty h4 {font-size: 12px;font-weight: normal;color: #8c939d;margin-top: 8px;}

  .sc-upload.sc-upload-round {border-radius: 50%;overflow: hidden;}
  .sc-upload.sc-upload-round .el-upload--picture-card {border-radius: 50%;}
  .sc-upload.sc-upload-round .sc-upload__img-actions {top: auto;left: 0;right: 0;bottom: 0;}
  .sc-upload.sc-upload-round .sc-upload__img-actions span {width: 100%;}
</style>
