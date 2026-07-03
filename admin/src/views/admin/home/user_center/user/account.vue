<template>
  <el-card shadow="never" header="个人信息">
    <el-form ref="formRef" :model="formData" :rules="rules" label-width="120px" style="margin-top:20px;">
      <el-form-item label="头像">
        <sc-upload v-model="formData.avatar" :cropper="true" :width="100" :height="100" title="上传头像"></sc-upload>
      </el-form-item>
      <el-form-item label="账号">
        <el-input v-model="formData.username" disabled></el-input>
        <div class="el-form-item-msg">账号信息用于登录，系统不允许修改</div>
      </el-form-item>
      <el-form-item label="昵称" prop="nickname">
        <el-input v-model="formData.nickname" placeholder="请输入昵称"></el-input>
      </el-form-item>
      <el-form-item label="手机号" prop="mobile">
        <el-input v-model="formData.mobile" placeholder="请输入手机号" clearable></el-input>
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
        <el-button type="primary" :loading="saveLoading" @click="onSubmit">保存</el-button>
      </el-form-item>
    </el-form>
  </el-card>
</template>

<script setup>
import {reactive, ref, watch} from "vue";
import tool from "@/utils/tool.js";
import userApi from "@/api/common/user.js";
import {ElMessage, ElMessageBox} from "element-plus";
import ScUpload from "@/components/scUpload/index.vue";

const props = defineProps({
    userInfo: {
        type: Object,
        default: () => ({})
    }
})
const emit = defineEmits(['user-update'])

const formRef = ref(null);
const saveLoading = ref(false);
const formData = reactive({
    username: "",
    nickname: "",
    mobile: "",
    avatar: "",
})

const rules = {
    nickname: [
        {required: true, message: '请输入昵称'}
    ],
    mobile: [
        {
            validator: (_, value, callback) => {
                if (!value) {
                    callback();
                    return;
                }
                const regMobile = /^1\d{10}$/;
                if (regMobile.test(value)) {
                    callback();
                    return;
                }
                callback(new Error('请输入合法的手机号'));
            },
            trigger: 'blur'
        }
    ]
}

const setFormData = (userInfo = {}) => {
    formData.username = userInfo.username || "";
    formData.nickname = userInfo.nickname || "";
    formData.mobile = userInfo.mobile || "";
    formData.avatar = userInfo.avatar || "";
}

watch(() => props.userInfo, (userInfo) => {
    setFormData(userInfo)
}, {immediate: true, deep: true})

const onSubmit = async () => {
    formRef.value.validate(async valid => {
        if (!valid) {
            return false;
        }

        saveLoading.value = true;
        let res;
        try {
            res = await userApi.info.post({
                nickname: formData.nickname,
                mobile: formData.mobile,
                avatar: formData.avatar
            });
        } finally {
            saveLoading.value = false;
        }

        if (res.code === 0) {
            const userInfo = res.data || {
                ...tool.data.get("USER_INFO"),
                ...props.userInfo,
                nickname: formData.nickname,
                mobile: formData.mobile,
                avatar: formData.avatar
            };
            tool.data.set("USER_INFO", userInfo);
            emit('user-update', userInfo);
            window.dispatchEvent(new CustomEvent('user-info-updated', {detail: userInfo}));
            ElMessage.success("操作成功");
        } else {
            await ElMessageBox.alert(res.message, "提示", {type: 'error'});
        }
    })
}
</script>
