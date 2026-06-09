<template>
  <el-card shadow="never" header="个人信息">
    <el-form ref="form" :model="formData" label-width="120px" style="margin-top:20px;">
      <el-form-item label="账号">
        <el-input v-model="formData.username" disabled></el-input>
        <div class="el-form-item-msg">账号信息用于登录，系统不允许修改</div>
      </el-form-item>
      <el-form-item label="昵称">
        <el-input v-model="formData.nickname"></el-input>
      </el-form-item>
<!--      <el-form-item label="性别">-->
<!--        <el-select v-model="form.sex" placeholder="请选择">-->
<!--          <el-option label="保密" value="0"></el-option>-->
<!--          <el-option label="男" value="1"></el-option>-->
<!--          <el-option label="女" value="2"></el-option>-->
<!--        </el-select>-->
<!--      </el-form-item>-->
<!--      <el-form-item label="个性签名">-->
<!--        <el-input v-model="form.about" type="textarea"></el-input>-->
<!--      </el-form-item>-->
      <el-form-item>
        <el-button type="primary" @click="onSubmit">保存</el-button>
      </el-form-item>
    </el-form>
  </el-card>
</template>

<script setup>
import {reactive} from "vue";
import tool from "@/utils/tool.js";
import userApi from "@/api/common/user.js";
import {ElMessage, ElMessageBox} from "element-plus";

const userInfo = tool.data.get("USER_INFO")

const formData = reactive({
    username: userInfo.username,
    nickname: userInfo.nickname,
})

const onSubmit = async () => {
    const res = await userApi.info.post({
        nickname: formData.nickname
    });

    userInfo.nickname = formData.nickname;
    tool.data.set("USER_INFO", userInfo);

    if (res.code === 0) {
        ElMessage.success("操作成功");
    } else {
        await ElMessageBox.alert(res.message, "提示", {type: 'error'});
    }
}
</script>
