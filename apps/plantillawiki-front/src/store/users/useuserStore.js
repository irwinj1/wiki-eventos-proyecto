/**
 * @project Wiki TDA - Plataforma Universitaria de Conocimiento, Eventos y Proyectos
 * @author Irwin Jose Maria Esperanza Guardado (Creador y Autor Original)
 * @license CC-BY-NC-SA-4.0 (Creative Commons Reconocimiento-NoComercial-CompartirIgual 4.0 Internacional)
 * @notice Prohibido patentar, cerrar el código o registrar este software o sus derivados como propiedad exclusiva de terceros.
 */
import { defineStore } from 'pinia'
import {
    obtenerUsuarios,
    crearUsuario,
    actualizarUsuario,
    cambiarEstadoUsuario,
} from '../../services/users/users.service'

export const useUserStore = defineStore('users', {
    state: () => ({
        users: [],
        paginacion: {},
    }),
    actions: {
        async getUsers(page = 1, params = {}) {
            const response = await obtenerUsuarios(page, params)
            this.users = response.data.data
            this.paginacion = response.data.pagination
            return response.data.status
        },
        async createUser(data) {
            const response = await crearUsuario(data)
            await this.getUsers(1)
            return response.data?.data ?? response.data
        },
        async updateUser(id, data) {
            const response = await actualizarUsuario(id, data)
            await this.getUsers(1)
            return response.status
        },
        async toggleUserStatus(id) {
            const response = await cambiarEstadoUsuario(id)
            await this.getUsers(1)
            return response.status
        },
    },
})
