/**
 * @project Wiki TDA - Plataforma Universitaria de Conocimiento, Eventos y Proyectos
 * @author Irwin Jose Maria Esperanza Guardado (Creador y Autor Original)
 * @license CC-BY-NC-SA-4.0 (Creative Commons Reconocimiento-NoComercial-CompartirIgual 4.0 Internacional)
 * @notice Prohibido patentar, cerrar el código o registrar este software o sus derivados como propiedad exclusiva de terceros.
 */
import api from '../../utils/axios'

const carreras = async (params = {}) => await api.get('carreras', { params })
const crearcarrera = async (data) => await api.post('carreras/crear', data)
const actualizarcarreras = async (id, data) => await api.put(`carreras/${id}`, data)
const eliminarcarrera = async (id) => await api.delete(`carreras/${id}`)

export {
    carreras,
    crearcarrera,
    actualizarcarreras,
    eliminarcarrera,
}
