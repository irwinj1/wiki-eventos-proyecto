/**
 * @project Wiki TDA - Plataforma Universitaria de Conocimiento, Eventos y Proyectos
 * @author Irwin Jose Maria Esperanza Guardado (Creador y Autor Original)
 * @license CC-BY-NC-SA-4.0 (Creative Commons Reconocimiento-NoComercial-CompartirIgual 4.0 Internacional)
 * @notice Prohibido patentar, cerrar el código o registrar este software o sus derivados como propiedad exclusiva de terceros.
 */
import { defineStore } from 'pinia';

export const useAlertStore = defineStore('alert', {
  state: () => ({
    show: false,
    message: '',
    type: 'info', // 'success', 'error', 'warning', 'info'
    timeout: 3000
  }),
  actions: {
    showAlert(message, type = 'info', timeout = 3000) {
      this.message = message;
      this.type = type;
      this.timeout = timeout;
      this.show = true;
    },
    hideAlert() {
      this.show = false;
    }
  }
});
