/**
 * @project Wiki TDA - Plataforma Universitaria de Conocimiento, Eventos y Proyectos
 * @author Irwin Jose Maria Esperanza Guardado (Creador y Autor Original)
 * @license CC-BY-NC-SA-4.0 (Creative Commons Reconocimiento-NoComercial-CompartirIgual 4.0 Internacional)
 * @notice Prohibido patentar, cerrar el código o registrar este software o sus derivados como propiedad exclusiva de terceros.
 */
import { defineStore } from 'pinia'
import {
    carreras,
    crearcarrera,
    actualizarcarreras,
    eliminarcarrera,
} from '../../services/carrera/carrera.service.js'

export const useCarreraStore = defineStore('carreras', {
    state: () => ({
        carreras: [],
        pagination: {},
    }),
    actions: {
        async getCarreras(params = {}) {
            const response = await carreras(params)
            this.carreras = response.data.data
            this.pagination = response.data.pagination
            return response.status
        },
        async crearCarrera(data) {
            const response = await crearcarrera(data)
            await this.getCarreras()
            return response.status
        },
        async actualizarCarreras(id, data) {
            const response = await actualizarcarreras(id, data)
            await this.getCarreras()
            return response.status
        },
        async cambiarEstadoCarrera(id) {
            const response = await eliminarcarrera(id)
            await this.getCarreras()
            return response.status
        },
    },
})
