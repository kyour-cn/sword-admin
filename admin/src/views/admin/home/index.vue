<template>
  <div v-if="pageLoading">
    <el-main>
      <el-card shadow="never">
        <el-skeleton :rows="1"/>
      </el-card>
      <el-card shadow="never" style="margin-top: 15px;">
        <el-skeleton/>
      </el-card>
    </el-main>
  </div>
  <widgets @on-mounted="onMounted"></widgets>
</template>

<script setup>
import {defineAsyncComponent, ref} from 'vue';

defineOptions({
  name: 'dashboard',
})

const widgets = defineAsyncComponent(() => import('./widgets/index.vue'));

const pageLoading = ref(true);
const onMounted = () => {
  pageLoading.value = false;
}

</script>
