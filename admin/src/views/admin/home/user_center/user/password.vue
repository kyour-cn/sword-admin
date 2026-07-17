<template>
  <el-card shadow="never" header="修改密码">
    <el-alert title="密码更新成功后，您将被重定向到登录页面，您可以使用新密码重新登录。" type="info" show-icon style="margin-bottom: 15px;"/>
    <el-form ref="formRef" :model="form" :rules="rules" label-width="120px" style="margin-top:20px;">
      <el-form-item label="当前密码" prop="oldPassword">
        <el-input v-model="form.oldPassword" type="password" autocomplete="current-password" show-password placeholder="请输入当前密码"></el-input>
        <div class="el-form-item-msg">必须提供当前登录用户密码才能进行更改</div>
      </el-form-item>
      <el-form-item label="新密码" prop="newPassword">
        <el-input v-model="form.newPassword" type="password" autocomplete="new-password" show-password placeholder="请输入新密码"></el-input>
        <sc-password-strength v-model="form.newPassword"></sc-password-strength>
        <div class="el-form-item-msg">请输入包含英文、数字的8位以上密码</div>
      </el-form-item>
      <el-form-item label="确认新密码" prop="confirmNewPassword">
        <el-input v-model="form.confirmNewPassword" type="password" autocomplete="new-password" show-password placeholder="请再次输入新密码"></el-input>
      </el-form-item>
      <el-form-item>
        <el-button type="primary" :loading="saveLoading" @click="save">保存密码</el-button>
      </el-form-item>
    </el-form>
  </el-card>
</template>

<script setup>
import scPasswordStrength from '@/components/scPasswordStrength'
import {ref} from "vue";
import {ElMessage, ElMessageBox} from "element-plus";
import userApi from "@/api/common/user.js";
import router from "@/router/index.js";
import tool from "@/utils/tool.js";

const formRef = ref(null);
const saveLoading = ref(false);
const form = ref({
    oldPassword: "",
    newPassword: "",
    confirmNewPassword: ""
})

const rules = {
    oldPassword: [
        {required: true, message: '请输入当前密码'}
    ],
    newPassword: [
        {required: true, message: '请输入新密码'},
        {pattern: /^(?=.*[A-Za-z])(?=.*\d).{8,}$/, message: '请输入包含英文、数字的8位以上密码'}
    ],
    confirmNewPassword: [
        {required: true, message: '请再次输入新密码'},
        {
            validator: (rule, value, callback) => {
                if (value !== form.value.newPassword) {
                    callback(new Error('两次输入密码不一致'));
                } else {
                    callback();
                }
            }
        }
    ]
}

const resetForm = () => {
    form.value = {
        oldPassword: "",
        newPassword: "",
        confirmNewPassword: ""
    }
}

const logout = async () => {
    tool.cookie.remove("TOKEN")
    tool.data.remove("USER_INFO")
    tool.data.remove("MENU")
    tool.data.remove("PERMISSIONS")
    await router.replace({path: '/login'})
}

const save = () => {
    formRef.value.validate(async valid => {
        if (!valid) {
            return false
        }

        saveLoading.value = true
        try {
            const res = await userApi.password.post({
                old_password: form.value.oldPassword,
                new_password: form.value.newPassword,
                confirm_new_password: form.value.confirmNewPassword
            })
            if (res.code !== 0) {
                ElMessage.error(res.message || '密码修改失败，请稍后再试')
                return false
            }

            resetForm()
            await ElMessageBox.alert("密码修改成功，请使用新密码重新登录", "修改成功", {
                type: 'success',
                center: true,
                closeOnClickModal: false,
                closeOnPressEscape: false,
                showClose: false
            })
            await logout()
        } catch (error) {
            ElMessage.error(error?.data?.message || error?.message || '密码修改失败，请稍后再试')
        } finally {
            saveLoading.value = false
        }
    })
}

</script>
