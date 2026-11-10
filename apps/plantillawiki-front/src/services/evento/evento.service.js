/**
 * @project Wiki TDA - Plataforma Universitaria de Conocimiento, Eventos y Proyectos
 * @author Irwin Jose Maria Esperanza Guardado (Creador y Autor Original)
 * @license CC-BY-NC-SA-4.0 (Creative Commons Reconocimiento-NoComercial-CompartirIgual 4.0 Internacional)
 * @notice Prohibido patentar, cerrar el código o registrar este software o sus derivados como propiedad exclusiva de terceros.
 */
import api from '../../utils/axios'

// Eventos Académicos
export const obtenerEventos = async (params = {}) => await api.get('eventos', { params })
export const obtenerEventoDetalle = async (id) => await api.get(`eventos/${id}`)
export const crearEvento = async (data) => await api.post('eventos/crear', data)
export const actualizarEvento = async (id, data) => await api.put(`eventos/${id}`, data)
export const cambiarEstadoEvento = async (id) => await api.delete(`eventos/${id}`)

// Materias de Eventos (`event_subjects`)
export const obtenerMateriasEvento = async (params = {}) => await api.get('materias-evento', { params })
export const crearMateriaEvento = async (data) => await api.post('materias-evento/crear', data)
export const actualizarMateriaEvento = async (id, data) => await api.put(`materias-evento/${id}`, data)
export const cambiarEstadoMateriaEvento = async (id) => await api.delete(`materias-evento/${id}`)
