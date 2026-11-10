/**
 * @project Wiki TDA - Plataforma Universitaria de Conocimiento, Eventos y Proyectos
 * @author Irwin Jose Maria Esperanza Guardado (Creador y Autor Original)
 * @license CC-BY-NC-SA-4.0 (Creative Commons Reconocimiento-NoComercial-CompartirIgual 4.0 Internacional)
 * @notice Prohibido patentar, cerrar el código o registrar este software o sus derivados como propiedad exclusiva de terceros.
 */
import {defineStore} from 'pinia';
import {login, logout as requestLogout} from '../../services/auth/login.service.js';
import {jwtDecode} from 'jwt-decode';
import { clearAuthSession } from '../../utils/authSession.js';

export const useLoginStore = defineStore('login', {
  state: () => ({
    formulario: {
      email: '',
      password: ''
    }
  }),
  actions: {
    async submit() {
      const response = await login(this.formulario);
      const data = response?.data;

      if (!data?.token) {
        throw new Error('La respuesta de inicio de sesión no contiene un token.');
      }

      const decodedToken = jwtDecode(data.token);
      const mustChange = Boolean(
        data.must_change_password ??
        data.user?.must_change_password ??
        decodedToken?.must_change_password
      );
      const userObj = {
        ...(data.user || {}),
        must_change_password: mustChange,
      };
      localStorage.setItem('roles', JSON.stringify(decodedToken.roles ?? []));
      localStorage.setItem('permissions', JSON.stringify(decodedToken.permissions ?? []));
      localStorage.setItem('token', data.token);
      localStorage.setItem('user', JSON.stringify(userObj));
      localStorage.setItem('must_change_password', mustChange ? 'true' : 'false');
      window.dispatchEvent(new CustomEvent('wiki-user-updated', { detail: userObj }));
      return response.status;
    },

    async logout() {
      try {
        return await requestLogout();
      } finally {
        clearAuthSession();
      }
    },
  }
})