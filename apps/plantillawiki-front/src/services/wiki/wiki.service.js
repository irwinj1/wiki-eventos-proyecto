/**
 * @project Wiki TDA - Plataforma Universitaria de Conocimiento, Eventos y Proyectos
 * @author Irwin Jose Maria Esperanza Guardado (Creador y Autor Original)
 * @license CC-BY-NC-SA-4.0 (Creative Commons Reconocimiento-NoComercial-CompartirIgual 4.0 Internacional)
 * @notice Prohibido patentar, cerrar el código o registrar este software o sus derivados como propiedad exclusiva de terceros.
 */
import api from '../../utils/axios'

// Roles y Permisos (Solo Admin)
export const obtenerRoles = async () => await api.get('auth/roles')
export const crearRol = async (data) => await api.post('auth/roles', data)
export const actualizarRol = async (id, data) => await api.put(`auth/roles/${id}`, data)
export const eliminarRol = async (id) => await api.delete(`auth/roles/${id}`)
export const obtenerPermisos = async () => await api.get('auth/permissions')

// Categorías de la Wiki
export const obtenerCategorias = async (params = {}) => await api.get('categorias', { params })
export const crearCategoria = async (data) => await api.post('categorias/crear', data)
export const actualizarCategoria = async (id, data) => await api.put(`categorias/${id}`, data)
export const cambiarEstadoCategoria = async (id) => await api.delete(`categorias/${id}`)

// Etiquetas (Tags) de la Wiki
export const obtenerTags = async (params = {}) => await api.get('tags', { params })
export const crearTag = async (data) => await api.post('tags/crear', data)
export const actualizarTag = async (id, data) => await api.put(`tags/${id}`, data)
export const cambiarEstadoTag = async (id) => await api.delete(`tags/${id}`)

// Artículos y Tutoriales de la Wiki
export const obtenerArticulos = async (params = {}) => await api.get('articulos', { params })
export const obtenerArticuloDetalle = async (idOrSlug) => await api.get(`articulos/${idOrSlug}`)
export const crearArticulo = async (data) => await api.post('articulos/crear', data)
export const actualizarArticulo = async (id, data) => await api.put(`articulos/${id}`, data)
export const cambiarEstadoArticulo = async (id) => await api.delete(`articulos/${id}`)
