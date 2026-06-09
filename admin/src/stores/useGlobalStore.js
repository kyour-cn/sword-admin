import { defineStore } from 'pinia'
import config from '@/config'

export const useGlobalStore = defineStore('global', {
  state: () => ({
    //移动端布局
    ismobile: false,
    //布局
    layout: config.LAYOUT,
    //菜单是否折叠 toggle
    menuIsCollapse: config.MENU_IS_COLLAPSE,
    //多标签栏
    layoutTags: config.LAYOUT_TAGS,
    //主题
    theme: config.THEME
  }),
  actions: {
    SET_ismobile(value) {
      this.ismobile = value
    },
    SET_layout(value) {
      this.layout = value
    },
    SET_theme(value) {
      this.theme = value
    },
    TOGGLE_menuIsCollapse() {
      this.menuIsCollapse = !this.menuIsCollapse
    },
    TOGGLE_layoutTags() {
      this.layoutTags = !this.layoutTags
    }
  }
})
