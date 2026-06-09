import { defineStore } from 'pinia'

export const useIframeStore = defineStore('iframe', {
  state: () => ({
    iframeList: []
  }),
  actions: {
    setIframeList(route) {
      this.iframeList = []
      this.iframeList.push(route)
    },
    pushIframeList(route) {
      const target = this.iframeList.find(item => item.path === route.path)
      if (!target) {
        this.iframeList.push(route)
      }
    },
    removeIframeList(route) {
      this.iframeList.forEach((item, index) => {
        if (item.path === route.path) {
          this.iframeList.splice(index, 1)
        }
      })
    },
    refreshIframe(route) {
      this.iframeList.forEach(item => {
        if (item.path == route.path) {
          const url = route.meta.url
          item.meta.url = ''
          setTimeout(() => {
            item.meta.url = url
          }, 200)
        }
      })
    },
    clearIframeList() {
      this.iframeList = []
    }
  }
})
