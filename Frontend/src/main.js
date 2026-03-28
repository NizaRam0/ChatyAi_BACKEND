import { createApp } from 'vue'
import './style.css'
import App from './App.vue'
import router from './router'

// The app is mounted with router so Upload and History are treated as real pages.
createApp(App).use(router).mount('#app')
