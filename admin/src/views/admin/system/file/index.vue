<template>
  <el-container>
<!--          <el-alert title="资源库选择器即将弃用,将不会维护更新,与1.7版本之后将移除此组件" type="error" style="margin-bottom:20px;"/>-->
    <sc-file-select class="file-manage" v-model:multiple="multiple" :hideUpload="false" mode="manage"/>
  </el-container>
</template>

<script setup>
import scFileSelect from '@/components/scFileSelect'
import {ref} from "vue";

const multiple = ref(false)

</script>

<style scoped lang="scss">
.file-manage {
  width: 100%;
}
</style>
