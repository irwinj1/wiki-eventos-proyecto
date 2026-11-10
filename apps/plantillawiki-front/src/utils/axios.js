/**
 * @project Wiki TDA - Plataforma Universitaria de Conocimiento, Eventos y Proyectos
 * @author Irwin Jose Maria Esperanza Guardado (Creador y Autor Original)
 * @license CC-BY-NC-SA-4.0 (Creative Commons Reconocimiento-NoComercial-CompartirIgual 4.0 Internacional)
 * @notice Prohibido patentar, cerrar el código o registrar este software o sus derivados como propiedad exclusiva de terceros.
 */
// src/services/api.js

import axios from "axios";
import { jwtDecode } from "jwt-decode";
import { useAlertStore } from "../store/useAlertStore";
import { useLoadingStore } from "../store/useLoadingStore";
import { clearAuthSession, updateStoredUser } from "./authSession";
import router from "../router/router";

const api = axios.create({
  baseURL: import.meta.env.VITE_API_URL || "/api",
  timeout: 15000,
  headers: {
    "Content-Type": "application/json",
    Accept: "application/json",
  },
});

let refreshPromise;
let sessionExpiredRedirecting = false;

const isAuthEndpoint = (url = "") =>
  /(?:^|\/)auth\/(?:login|refresh)(?:[?#]|$)/.test(url);
const isHandledAuthRequest = (url = "") =>
  /(?:^|\/)auth\/(?:login|logout)(?:[?#]|$)/.test(url);

async function refreshAccessToken() {
  const response = await api.post("auth/refresh");
  const token = response.data?.data?.token ?? response.data?.token;
  const refreshedUser = response.data?.data?.user ?? response.data?.user;

  if (!token) {
    throw new Error("La respuesta de refresh no contiene un token.");
  }

  localStorage.setItem("token", token);
  try {
    const decodedToken = jwtDecode(token);
    if (Array.isArray(decodedToken?.roles)) {
      localStorage.setItem("roles", JSON.stringify(decodedToken.roles));
    }
    if (Array.isArray(decodedToken?.permissions)) {
      localStorage.setItem("permissions", JSON.stringify(decodedToken.permissions));
    }
  } catch {
    // Continúa aunque no se pueda decodificar localmente
  }

  if (refreshedUser) {
    updateStoredUser(refreshedUser);
  }

  sessionExpiredRedirecting = false;
  return token;
}

function handleExpiredSession() {
  clearAuthSession();

  if (!sessionExpiredRedirecting) {
    sessionExpiredRedirecting = true;
    useAlertStore().showAlert(
      "Tu sesión expiró. Has sido redirigido a la página principal pública.",
      "warning"
    );

    if (window.location.pathname !== "/") {
      router.push({ name: "Home" }).catch(() => {
        window.location.assign("/");
      });
    }
  }
}

/**
 * Interceptor de solicitudes
 * Agrega el token JWT automáticamente
 */
api.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem("token");

    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }

    config._globalLoaderTracked = true;
    useLoadingStore().startLoading();

    return config;
  },
  (error) => Promise.reject(error)
);

/**
 * Interceptor de respuestas
 */
api.interceptors.response.use(
  (response) => {
    if (response.config._globalLoaderTracked) {
      response.config._globalLoaderTracked = false;
      useLoadingStore().finishLoading();
    }

    return response;
  },

  async (error) => {
    if (error.config?._globalLoaderTracked) {
      error.config._globalLoaderTracked = false;
      useLoadingStore().finishLoading();
    }

    const alertStore = useAlertStore();

    const requestUrl = error.config?.url ?? "";
    if (!error.response) {
      if (isHandledAuthRequest(requestUrl)) {
        return Promise.reject(error);
      }

      console.error("Error de conexión con el servidor.");
      alertStore.showAlert("Error de conexión con el servidor.", "error");
      return Promise.reject(error);
    }

    if (error.response.status === 401) {
      if (isAuthEndpoint(requestUrl)) {
        return Promise.reject(error);
      }

      if (isHandledAuthRequest(requestUrl)) {
        return Promise.reject(error);
      }

      if (error.config && !error.config._retry && localStorage.getItem("token")) {
        error.config._retry = true;

        try {
          refreshPromise ??= refreshAccessToken();
          const token = await refreshPromise;
          error.config.headers.Authorization = `Bearer ${token}`;
          return api(error.config);
        } catch (refreshError) {
          handleExpiredSession();
          return Promise.reject(refreshError);
        } finally {
          refreshPromise = undefined;
        }
      }

      if (localStorage.getItem("token")) {
        handleExpiredSession();
      } else {
        alertStore.showAlert("Debes iniciar sesión para continuar.", "warning");
      }

      return Promise.reject(error);
    }

    switch (error.response.status) {
      case 400:
        console.error("Solicitud incorrecta.");
        alertStore.showAlert("Solicitud incorrecta.", "error");
        break;

      case 403:
        console.error("No tienes permisos.");
        alertStore.showAlert("No tienes permisos para realizar esta acción.", "error");
        break;

      case 404:
        console.error("Recurso no encontrado.");
        alertStore.showAlert("Recurso no encontrado.", "error");
        break;

      case 422: {
        console.error("Error de validación.");
        const serverMessage = error.response.data?.message;
        alertStore.showAlert(
          serverMessage || "Error de validación. Revisa los datos enviados.",
          "warning"
        );
        break;
      }

      case 409: {
        const conflictMsg = error.response.data?.message;
        alertStore.showAlert(
          conflictMsg || "No se puede completar la acción por dependencias activas.",
          "warning"
        );
        break;
      }

      case 500:
        console.error("Error interno del servidor.");
        alertStore.showAlert("Error interno del servidor.", "error");
        break;

      default:
        console.error("Ha ocurrido un error.");
        alertStore.showAlert("Ha ocurrido un error inesperado.", "error");
    }

    return Promise.reject(error);
  }
);

export default api;