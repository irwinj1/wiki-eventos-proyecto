/**
 * @project Wiki TDA - Plataforma Universitaria de Conocimiento, Eventos y Proyectos
 * @author Irwin Jose Maria Esperanza Guardado (Creador y Autor Original)
 * @license CC-BY-NC-SA-4.0 (Creative Commons Reconocimiento-NoComercial-CompartirIgual 4.0 Internacional)
 * @notice Prohibido patentar, cerrar el código o registrar este software o sus derivados como propiedad exclusiva de terceros.
 */
import api from '../../utils/axios'

// Proyectos Estudiantiles (`projects` + `project_members` + `project_files`)
export const obtenerProyectos = async (params = {}) => await api.get('proyectos', { params })
export const obtenerProyectoDetalle = async (id) => await api.get(`proyectos/${id}`)

export const crearProyecto = async (data) => {
    if (data instanceof FormData) {
        return await api.post('proyectos/crear', data, {
            headers: { 'Content-Type': 'multipart/form-data' },
        })
    }
    return await api.post('proyectos/crear', data)
}

export const actualizarProyecto = async (id, data) => {
    if (data instanceof FormData) {
        data.append('_method', 'PUT')
        return await api.post(`proyectos/${id}`, data, {
            headers: { 'Content-Type': 'multipart/form-data' },
        })
    }
    return await api.put(`proyectos/${id}`, data)
}

export const cambiarEstadoProyecto = async (id) => await api.delete(`proyectos/${id}`)

// Archivos de Proyectos (`project_files`)
export const obtenerArchivosProyecto = async (projectId) =>
    await api.get('archivos-proyecto', { params: { project_id: projectId } })

export const subirArchivoProyecto = async (payload) => {
    if (payload instanceof FormData) {
        return await api.post('archivos-proyecto/subir', payload, {
            headers: { 'Content-Type': 'multipart/form-data' },
        })
    }
    return await api.post('archivos-proyecto/subir', payload)
}

export const eliminarArchivoProyecto = async (id) => await api.delete(`archivos-proyecto/${id}`)

// Dashboard Stats
export const obtenerDashboardStats = async () => await api.get('dashboard/stats')
