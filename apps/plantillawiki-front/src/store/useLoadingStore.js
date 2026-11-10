/**
 * @project Wiki TDA - Plataforma Universitaria de Conocimiento, Eventos y Proyectos
 * @author Irwin Jose Maria Esperanza Guardado (Creador y Autor Original)
 * @license CC-BY-NC-SA-4.0 (Creative Commons Reconocimiento-NoComercial-CompartirIgual 4.0 Internacional)
 * @notice Prohibido patentar, cerrar el código o registrar este software o sus derivados como propiedad exclusiva de terceros.
 */
import { defineStore } from 'pinia';

export const useLoadingStore = defineStore('loading', {
  state: () => ({
    activeRequests: 0,
  }),
  getters: {
    isLoading: (state) => state.activeRequests > 0,
  },
  actions: {
    startLoading() {
      this.activeRequests += 1;
    },
    finishLoading() {
      this.activeRequests = Math.max(0, this.activeRequests - 1);
    },
  },
});
