<template>
  <div :class="['widgets-home', customizing ? 'customizing' : '']" ref="main">
    <div class="widgets-actions">
      <el-button v-if="!customizing" class="diy-btn" type="primary" icon="el-icon-edit" round @click="custom">
        自定义
      </el-button>
    </div>

    <div class="widgets-content">
      <div class="widgets" ref="widgets">
        <div class="widgets-wrapper">
          <div v-if="nowCompsList.length <= 0" class="no-widgets">
            <el-empty image="img/no-widgets.svg" description="没有部件啦" :image-size="280"></el-empty>
          </div>
          <el-row :gutter="15">
            <el-col v-for="(item, index) in grid.layout" v-bind:key="index" :md="item" :xs="24">
              <draggable
                v-model="grid.copmsList[index]"
                animation="200"
                handle=".customize-overlay"
                group="people"
                item-key="com"
                dragClass="aaaaa"
                force-fallback
                fallbackOnBody
                class="draggable-box">
                <template #item="{ element }">
                  <div class="widgets-item">
                    <component :is="allComps[element]"></component>
                    <div v-if="customizing" class="customize-overlay">
                      <el-button class="close" type="danger" plain icon="el-icon-close"
                             size="small" @click="remove(element)"></el-button>
                      <label>
                        <el-icon>
                          <component :is="allComps[element]?.icon"/>
                        </el-icon>
                        {{ allComps[element]?.title }}
                      </label>
                    </div>
                  </div>
                </template>
              </draggable>
            </el-col>
          </el-row>
        </div>
      </div>
    </div>
    <div v-if="customizing" class="widgets-aside">
      <el-container>
        <el-header>
          <div class="widgets-aside-title">
            <el-icon>
              <el-icon-circle-plus-filled/>
            </el-icon>
            添加部件
          </div>
          <div class="widgets-aside-close" @click="close()">
            <el-icon>
              <el-icon-close/>
            </el-icon>
          </div>
        </el-header>
        <el-header style="height: auto">
          <div class="selectLayout">
            <div class="selectLayout-item item01" :class="{ active: grid.layout.join(',') == '12,6,6' }"
               @click="setLayout([12, 6, 6])">
              <el-row :gutter="2">
                <el-col :span="12"><span></span></el-col>
                <el-col :span="6"><span></span></el-col>
                <el-col :span="6"><span></span></el-col>
              </el-row>
            </div>
            <div class="selectLayout-item item02" :class="{ active: grid.layout.join(',') == '24,16,8' }"
               @click="setLayout([24, 16, 8])">
              <el-row :gutter="2">
                <el-col :span="24"><span></span></el-col>
                <el-col :span="16"><span></span></el-col>
                <el-col :span="8"><span></span></el-col>
              </el-row>
            </div>
            <div class="selectLayout-item item03" :class="{ active: grid.layout.join(',') == '24' }"
               @click="setLayout([24])">
              <el-row :gutter="2">
                <el-col :span="24"><span></span></el-col>
                <el-col :span="24"><span></span></el-col>
                <el-col :span="24"><span></span></el-col>
              </el-row>
            </div>
            <div class="selectLayout-item item04" :class="{ active: grid.layout.join(',') == '8,8,8' }"
               @click="setLayout([8, 8, 8])">
              <el-row :gutter="2">
                <el-col :span="8"><span></span></el-col>
                <el-col :span="8"><span></span></el-col>
                <el-col :span="8"><span></span></el-col>
              </el-row>
            </div>
          </div>
        </el-header>
        <el-main class="nopadding">
          <div class="widgets-list">
            <div v-if="myCompsList.length <= 0" class="widgets-list-nodata">
              <el-empty description="没有部件啦" :image-size="60"></el-empty>
            </div>
            <div v-for="item in myCompsList" :key="item.title" class="widgets-list-item">
              <div class="item-logo">
                <el-icon>
                  <component :is="item.icon"/>
                </el-icon>
              </div>
              <div class="item-info">
                <h2>{{ item.title }}</h2>
                <p>{{ item.description }}</p>
              </div>
              <div class="item-actions">
                <el-button type="primary" icon="el-icon-plus" size="small"
                       @click="push(item)"></el-button>
              </div>
            </div>
          </div>
        </el-main>
        <el-footer style="height: 51px">
          <el-button @click="backDefault()">恢复默认</el-button>
          <!--      <el-button type="primary" @click="save">保存</el-button>-->
        </el-footer>
      </el-container>
    </div>
  </div>
</template>

<script setup>
import {computed, getCurrentInstance, nextTick, onMounted, reactive, ref} from 'vue'
import draggable from 'vuedraggable'
import allComps from './components'
import tool from '@/utils/tool'

const gridKey = 'grid_system'

const emit = defineEmits(['on-mounted'])

const main = ref()
const widgets = ref()

// 响应式数据
const customizing = ref(false)

// 从全局配置获取默认网格配置
const defaultGrid = ref(getCurrentInstance().appContext.config.globalProperties.$CONFIG.DEFAULT_GRID)

const grid = reactive(
  tool.data.get(gridKey) || JSON.parse(JSON.stringify(defaultGrid.value))
)

// 计算属性
const allCompsList = computed(() => {
  const allCompsList = []
  for (const key in allComps) {
    allCompsList.push({
      key: key,
      title: allComps[key].title,
      icon: allComps[key].icon,
      description: allComps[key].description
    })
  }
  const myCopmsList = grid.copmsList.reduce((a, b) => a.concat(b))
  for (let comp of allCompsList) {
    const _item = myCopmsList.find((item) => item === comp.key)
    if (_item) {
      comp.disabled = true
    }
  }
  return allCompsList
})

const myCompsList = computed(() => {
  return allCompsList.value.filter((item) => !item.disabled)
})

const nowCompsList = computed(() => {
  return grid.copmsList.reduce((a, b) => a.concat(b))
})

// 开启自定义
const custom = () => {
  customizing.value = true
  const oldWidth = widgets.value.offsetWidth
  nextTick(() => {
    const scale = widgets.value.offsetWidth / oldWidth
    widgets.value.style.setProperty('transform', `scale(${scale})`)
  })
}

// 设置布局
const setLayout = (layout) => {
  grid.layout = layout
  if (layout.join(',') === '24') {
    // 使用Vue的响应式方式重新赋值数组
    const newCompsList = [...grid.copmsList]
    newCompsList[0] = [...grid.copmsList[0], ...grid.copmsList[1], ...grid.copmsList[2]]
    newCompsList[1] = []
    newCompsList[2] = []
    grid.copmsList = newCompsList
  }
}

// 追加
const push = (item) => {
  // 使用Vue的响应式方式添加元素
  const newCompsList = [...grid.copmsList]
  newCompsList[0] = [...newCompsList[0], item.key]
  grid.copmsList = newCompsList
}

// 隐藏组件
const remove = (item) => {
  // 使用Vue的响应式方式移除元素
  grid.copmsList = grid.copmsList.map(obj =>
    obj.filter((o) => o !== item)
  )
}

// 保存
const save = () => {
  customizing.value = false
  widgets.value.style.removeProperty('transform')
  tool.data.set(gridKey, grid)
}

// 恢复默认
const backDefault = () => {
  customizing.value = false
  widgets.value.style.removeProperty('transform')
  Object.assign(grid, JSON.parse(JSON.stringify(defaultGrid.value)))
  tool.data.remove(gridKey)
}

// 关闭
const close = () => {
  save()
}

onMounted(() => {
  emit('on-mounted')
});
</script>

<style scoped lang="scss">
.widgets-home {
  display: flex;
  flex-direction: row;
  flex: 1;
  height: 100%;
  position: relative;
}

.widgets-content {
  flex: 1;
  overflow: auto;
  overflow-x: hidden;
  padding: 15px;
}

.widgets-aside {
  width: 360px;
  background: #fff;
  box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
  position: relative;
  overflow: auto;
}

.widgets-aside-title {
  font-size: 14px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.widgets-aside-title i {
  margin-right: 10px;
  font-size: 18px;
}

.widgets-aside-close {
  font-size: 18px;
  width: 30px;
  height: 30px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 3px;
  cursor: pointer;
}

.widgets-aside-close:hover {
  background: rgba(180, 180, 180, 0.1);
}

//.widgets-top {margin-bottom: 15px;display: flex;justify-content: space-between;align-items: center;}
//.widgets-top-title {font-size: 18px;font-weight: bold;}

.widgets {
  transform-origin: top left;
  transition: transform 0.15s;
}

.draggable-box {
  height: 100%;
}

.customizing .widgets-wrapper {
  margin-right: -360px;
}

.customizing .widgets-wrapper .el-col {
  padding-bottom: 15px;
}

.customizing .widgets-wrapper .draggable-box {
  border: 1px dashed var(--el-color-primary);
  padding: 15px;
}

.customizing .widgets-wrapper .no-widgets {
  display: none;
}

.customizing .widgets-item {
  position: relative;
  margin-bottom: 15px;
}

.customize-overlay {
  position: absolute;
  top: 0;
  right: 0;
  bottom: 0;
  left: 0;
  z-index: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  background: rgba(255, 255, 255, 0.9);
  cursor: move;
}

.customize-overlay label {
  background: var(--el-color-primary);
  color: #fff;
  height: 40px;
  padding: 0 30px;
  border-radius: 40px;
  font-size: 18px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: move;
}

.customize-overlay label i {
  margin-right: 15px;
  font-size: 24px;
}

.customize-overlay .close {
  position: absolute;
  top: 15px;
  right: 15px;
}

.customize-overlay .close:focus,
.customize-overlay .close:hover {
  background: var(--el-button-hover-color);
}

.widgets-list {
}

.widgets-list-item {
  display: flex;
  flex-direction: row;
  padding: 15px;
  align-items: center;
}

.widgets-list-item .item-logo {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: rgba(180, 180, 180, 0.1);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 18px;
  margin-right: 15px;
  color: #6a8bad;
}

.widgets-list-item .item-info {
  flex: 1;
}

.widgets-list-item .item-info h2 {
  font-size: 16px;
  font-weight: normal;
  cursor: default;
}

.widgets-list-item .item-info p {
  font-size: 12px;
  color: #999;
  cursor: default;
}

.widgets-list-item:hover {
  background: rgba(180, 180, 180, 0.1);
}

.widgets-wrapper .sortable-ghost {
  opacity: 0.5;
}

.selectLayout {
  width: 100%;
  display: flex;
}

.selectLayout-item {
  width: 60px;
  height: 60px;
  border: 2px solid var(--el-border-color-light);
  padding: 5px;
  cursor: pointer;
  margin-right: 15px;
}

.selectLayout-item span {
  display: block;
  background: var(--el-border-color-light);
  height: 46px;
}

.selectLayout-item.item02 span {
  height: 30px;
}

.selectLayout-item.item02 .el-col:nth-child(1) span {
  height: 14px;
  margin-bottom: 2px;
}

.selectLayout-item.item03 span {
  height: 14px;
  margin-bottom: 2px;
}

.selectLayout-item:hover {
  border-color: var(--el-color-primary);
}

.selectLayout-item.active {
  border-color: var(--el-color-primary);
}

.selectLayout-item.active span {
  background: var(--el-color-primary);
}

.dark {
  .widgets-aside {
    background: #2b2b2b;
  }

  .customize-overlay {
    background: rgba(43, 43, 43, 0.9);
  }
}

@media (max-width: 992px) {
  .customizing .widgets {
    transform: scale(1) !important;
  }
  .customizing .widgets-aside {
    width: 100%;
    position: absolute;
    top: 50%;
    right: 0;
    bottom: 0;
  }
  .customizing .widgets-wrapper {
    margin-right: 0;
  }
}

//自定义按钮样式
.widgets-actions {
  position: fixed;
  right: -60px;
  top: 160px;
  z-index: 999;
  opacity: 0.8;
  transition: all 0.8s;
}

.widgets-actions:hover {
  opacity: 1;
  right: 5px;

  .el-button {
    background: #1b50f1;
    border-color: #1b50f1;
  }
}
</style>
