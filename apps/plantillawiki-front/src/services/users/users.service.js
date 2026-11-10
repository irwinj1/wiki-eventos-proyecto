/**
 * @project Wiki TDA - Plataforma Universitaria de Conocimiento, Eventos y Proyectos
 * @author Irwin Jose Maria Esperanza Guardado (Creador y Autor Original)
 * @license CC-BY-NC-SA-4.0 (Creative Commons Reconocimiento-NoComercial-CompartirIgual 4.0 Internacional)
 * @notice Prohibido patentar, cerrar el código o registrar este software o sus derivados como propiedad exclusiva de terceros.
 */
import api from '../../utils/axios'

const obtenerUsuarios = async (page = 1, params = {}) => {
    return await api.get('auth/users', {
        params: {
            page,
            perPage: 10,
            ...params,
        },
    })
}

const crearUsuario = async (data) => await api.post('auth/users', data)
const actualizarUsuario = async (id, data) => await api.put(`auth/users/${id}`, data)
const cambiarEstadoUsuario = async (id) => await api.delete(`auth/users/${id}`)
const obtenerPerfilPropio = async () => await api.get('auth/profile')
const actualizarPerfilPropio = async (data) => await api.put('auth/profile', data)

export {
    obtenerUsuarios,
    crearUsuario,
    actualizarUsuario,
    cambiarEstadoUsuario,
    obtenerPerfilPropio,
    actualizarPerfilPropio,
}
