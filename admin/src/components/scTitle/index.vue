<template>
  <div class="sc-title">
    {{title}}
  </div>
</template>

<script setup>
// Props定义
defineProps({
  title: { type: String, required: true, default: "" },
})
</script>

<style scoped>
  .sc-title {border-bottom: 1px solid #eee;margin-bottom: 20px;font-size: 17px;padding-bottom: 15px;color: #3c4a54;font-weight: bold;}
</style>
