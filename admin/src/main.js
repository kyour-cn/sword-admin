import { createApp } from 'vue'
import ElementPlus from 'element-plus'
import 'element-plus/dist/index.css'
import 'element-plus/theme-chalk/display.css'
import scui from './scui'
import i18n from './locales'
import router from './router'
import { createPinia } from 'pinia'
import App from './App.vue'

const app = createApp(App);
const pinia = createPinia()

app.use(pinia);
app.use(router);
app.use(ElementPlus);
app.use(i18n);
app.use(scui);

//挂载app
app.mount('#app');
