<!--
 * @Descripttion: 动态表单渲染器
 * @version: 1.0
 * @Author: sakuya
 * @Date: 2021年9月22日09:26:25
 * @LastEditors:
 * @LastEditTime:
-->

<template>
  <el-skeleton v-if="renderLoading || Object.keys(form).length === 0" animated />

  <el-form v-else ref="formRef" :model="form" :label-width="config.labelWidth" :label-position="config.labelPosition" v-loading="loading" element-loading-text="Loading...">
    <el-row :gutter="15">
      <template v-for="(item, index) in config.formItems" :key="index">
        <el-col :span="item.span || 24" v-if="!hideHandle(item)">
          <sc-title  v-if="item.component === 'title'" :title="item.label"></sc-title>
          <el-form-item v-else :prop="item.name" :rules="rulesHandle(item)">
            <template #label>
              {{item.label}}
              <el-tooltip v-if="item.tips" :content="item.tips">
                <el-icon><el-icon-question-filled /></el-icon>
              </el-tooltip>
            </template>
            <!-- input -->
            <template v-if="item.component === 'input'" >
              <el-input v-model="form[item.name]" :placeholder="item.options.placeholder" clearable :maxlength="item.options.maxlength" show-word-limit></el-input>
            </template>
            <!-- checkbox -->
            <template v-else-if="item.component === 'checkbox'" >
              <template v-if="item.name" >
                <el-checkbox v-model="form[item.name][_item.name]" :label="_item.label"  v-for="(_item, _index) in item.options.items" :key="_index"></el-checkbox>
              </template>
              <template v-else >
                <el-checkbox v-model="form[_item.name]" :label="_item.label"  v-for="(_item, _index) in item.options.items" :key="_index"></el-checkbox>
              </template>
            </template>
            <!-- checkboxGroup -->
            <template v-else-if="item.component === 'checkboxGroup'" >
              <el-checkbox-group v-model="form[item.name]">
                <el-checkbox v-for="_item in item.options.items" :key="_item.value" :label="_item.value">{{_item.label}}</el-checkbox>
              </el-checkbox-group>
            </template>
            <!-- upload -->
            <template v-else-if="item.component === 'upload'" >
              <el-col v-for="(_item, _index) in item.options.items" :key="_index">
                <el-form-item :prop="_item.name">
                  <sc-upload v-model="form[_item.name]" :title="_item.label"></sc-upload>
                </el-form-item>
              </el-col>
            </template>
            <!-- switch -->
            <template v-else-if="item.component === 'switch'" >
              <el-switch v-model="form[item.name]" />
            </template>
            <!-- select -->
            <template v-else-if="item.component === 'select'" >
              <el-select v-model="form[item.name]" :multiple="item.options.multiple" :placeholder="item.options.placeholder" clearable filterable style="width: 100%;">
                <el-option v-for="option in item.options.items" :key="option.value" :label="option.label" :value="option.value"></el-option>
              </el-select>
            </template>
            <!-- cascader -->
            <template v-else-if="item.component === 'cascader'" >
              <el-cascader v-model="form[item.name]" :options="item.options.items" clearable></el-cascader>
            </template>
            <!-- date -->
            <template v-else-if="item.component === 'date'" >
              <el-date-picker v-model="form[item.name]" :type="item.options.type" :shortcuts="item.options.shortcuts" :default-time="item.options.defaultTime" :value-format="item.options.valueFormat" :placeholder="item.options.placeholder || '请选择'"></el-date-picker>
            </template>
            <!-- number -->
            <template v-else-if="item.component === 'number'" >
              <el-input-number v-model="form[item.name]" controls-position="right"></el-input-number>
            </template>
            <!-- radio -->
            <template v-else-if="item.component === 'radio'" >
              <el-radio-group v-model="form[item.name]">
                <el-radio v-for="_item in item.options.items" :key="_item.value" :label="_item.value">{{_item.label}}</el-radio>
              </el-radio-group>
            </template>
            <!-- color -->
            <template v-else-if="item.component === 'color'" >
              <el-color-picker v-model="form[item.name]" />
            </template>
            <!-- rate -->
            <template v-else-if="item.component === 'rate'" >
              <el-rate style="margin-top: 6px;" v-model="form[item.name]"></el-rate>
            </template>
            <!-- slider -->
            <template v-else-if="item.component === 'slider'" >
              <el-slider v-model="form[item.name]" :marks="item.options.marks"></el-slider>
            </template>
            <!-- tableselect -->
            <template v-else-if="item.component === 'tableselect'" >
              <table-select-render v-model="form[item.name]" :item="item"></table-select-render>
            </template>
            <!-- editor -->
<!--            <template v-else-if="item.component === 'editor'" >-->
<!--              <sc-editor v-model="form[item.name]" placeholder="请输入" :height="400"></sc-editor>-->
<!--            </template>-->
            <!-- noComponent -->
            <template v-else>
              <el-tag type="danger">[{{item.component}}] Component not found</el-tag>
            </template>
            <div v-if="item.message" class="el-form-item-msg">{{item.message}}</div>
          </el-form-item>
        </el-col>
      </template>
      <el-col :span="24">
        <el-form-item>
          <slot>
            <el-button type="primary" @click="submit">提交</el-button>
          </slot>
        </el-form-item>
      </el-col>
    </el-row>
  </el-form>
</template>

<script setup>
import { ref, reactive, watch, computed, onMounted, defineAsyncComponent } from 'vue'
import http from "@/utils/request"
import ScTitle from "@/components/scTitle"
import ScUpload from "@/components/scUpload"

const TableSelectRender = defineAsyncComponent(() => import('./items/tableSelect'))

// Props定义
const props = defineProps({
  modelValue: { type: Object, default: () => ({}) },
  config: { type: Object, default: () => ({}) },
  loading: { type: Boolean, default: false },
})

// Emits定义
const emit = defineEmits(['update:modelValue', 'submit'])

// 响应式数据
const formRef = ref(null)
const form = reactive({})
const renderLoading = ref(false)

// 计算属性
const hasConfig = computed(() => {
  return Object.keys(props.config).length > 0
})

const hasValue = computed(() => {
  return Object.keys(props.modelValue).length > 0
})

// 监听器
watch(() => props.modelValue, () => {
  if (hasConfig.value) {
    deepMerge(form, props.modelValue)
  }
}, { deep: true })

watch(() => props.config, () => {
  render()
}, { deep: true })

watch(form, (val) => {
  emit("update:modelValue", val)
}, { deep: true })

// 生命周期
onMounted(() => {
  if (hasConfig.value) {
    render()
  }
})

// 方法
const render = () => {
  // 清空原有form数据
  Object.keys(form).forEach(key => {
    delete form[key]
  })

  props.config.formItems?.forEach((item) => {
    if (item.component === 'checkbox') {
      if (item.name) {
        const value = {}
        item.options.items.forEach((option) => {
          value[option.name] = option.value
        })
        form[item.name] = value
      } else {
        item.options.items.forEach((option) => {
          form[option.name] = option.value
        })
      }
    } else if (item.component === 'upload') {
      if (item.name) {
        const value = {}
        item.options.items.forEach((option) => {
          value[option.name] = option.value
        })
        form[item.name] = value
      } else {
        item.options.items.forEach((option) => {
          form[option.name] = option.value
        })
      }
    } else {
      form[item.name] = item.value
    }
  })

  if (hasValue.value) {
    deepMerge(form, props.modelValue)
  }
  getData()
}

const getData = () => {
  renderLoading.value = true
  const remoteData = []
  props.config.formItems?.forEach((item) => {
    if (item.options && item.options.remote) {
      const req = http.get(item.options.remote.api, item.options.remote.data).then(res => {
        item.options.items = res.data
      })
      remoteData.push(req)
    }
  })
  Promise.all(remoteData).then(() => {
    renderLoading.value = false
  })
}

const deepMerge = (obj1, obj2) => {
  let key
  for (key in obj2) {
    obj1[key] = obj1[key] && obj1[key].toString() === "[object Object]" && (obj2[key] && obj2[key].toString() === "[object Object]")
      ? deepMerge(obj1[key], obj2[key])
      : (obj1[key] = obj2[key])
  }
  return obj1
}

const evaluateExpression = (expression, scope = {}) => {
  try {
    const fn = new Function('form', 'return (' + expression.replace(/\$/g, 'form') + ')')
    return fn(scope)
  } catch (e) {
    console.warn(`Expression error: ${expression}`, e)
    return false
  }
}

const hideHandle = (item) => {
  if (item.hideHandle) {
    return evaluateExpression(item.hideHandle, form)
  }
  return false
}

const rulesHandle = (item) => {
  if (item.requiredHandle) {
    const exp = evaluateExpression(item.requiredHandle, form)
    const requiredRule = item.rules?.find(t => 'required' in t)
    if (requiredRule) {
      requiredRule.required = exp
    }
  }
  return item.rules
}

const validate = (valid, obj) => {
  return formRef.value.validate(valid, obj)
}

const scrollToField = (prop) => {
  return formRef.value.scrollToField(prop)
}

const resetFields = () => {
  return formRef.value.resetFields()
}

const submit = () => {
  emit("submit", form)
}

// 暴露方法给父组件
defineExpose({
  validate,
  scrollToField,
  resetFields,
  submit
})
</script>

<style>
</style>
