/**
 * @project Wiki TDA - Plataforma Universitaria de Conocimiento, Eventos y Proyectos
 * @author Irwin Jose Maria Esperanza Guardado (Creador y Autor Original)
 * @license CC-BY-NC-SA-4.0 (Creative Commons Reconocimiento-NoComercial-CompartirIgual 4.0 Internacional)
 * @notice Prohibido patentar, cerrar el código o registrar este software o sus derivados como propiedad exclusiva de terceros.
 */
import { createApp } from 'vue'
import './style.css'
import router from './router/router'
import vuetify from './plugins/vuetify'
import { createPinia } from 'pinia'
import permission from './directives/permission.js'
import roles from './directives/role.js'
import App from './App.vue'


const pinia = createPinia()
const app = createApp(App)
app.directive('permission', permission)
app.directive('role', roles)
app.use(pinia)
app.use(router)
app.use(vuetify)
app.mount('#app')
