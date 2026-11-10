/**
 * @project Wiki TDA - Plataforma Universitaria de Conocimiento, Eventos y Proyectos
 * @author Irwin Jose Maria Esperanza Guardado (Creador y Autor Original)
 * @license CC-BY-NC-SA-4.0 (Creative Commons Reconocimiento-NoComercial-CompartirIgual 4.0 Internacional)
 * @notice Prohibido patentar, cerrar el código o registrar este software o sus derivados como propiedad exclusiva de terceros.
 */
import { useCarreraStore } from '../../../store/carrera/useCarrera.js'
import { computed } from 'vue'

export const useCarreras = () => {
    const store = useCarreraStore()
    const carreras = computed(() => store.carreras)
    const pagination = computed(() => store.pagination)

    const obtenerCarreras = async (params = {}) => {
        try {
            return await store.getCarreras(params)
        } catch (error) {
            console.error(error)
            return null
        }
    }

    const crearNuevaCarrera = async (data) => {
        try {
            const status = await store.crearCarrera(data)
            return status === 201 || status === 200
        } catch (error) {
            console.error(error)
            return false
        }
    }

    const actualizarCarreras = async (id, data) => {
        try {
            const status = await store.actualizarCarreras(id, data)
            return status === 200
        } catch (error) {
            console.error(error)
            return false
        }
    }

    const cambiarEstadoCarrera = async (id) => {
        try {
            const status = await store.cambiarEstadoCarrera(id)
            return status === 200
        } catch (error) {
            console.error(error)
            return false
        }
    }

    return {
        carreras,
        pagination,
        obtenerCarreras,
        crearNuevaCarrera,
        actualizarCarreras,
        cambiarEstadoCarrera,
    }
}
