import {defineAsyncComponent} from "vue"

const configRenderers = {
  test: defineAsyncComponent(() => import("./TestConfigPanel.vue")),
}

export default configRenderers
