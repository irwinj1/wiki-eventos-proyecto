/**
 * @project Wiki TDA - Plataforma Universitaria de Conocimiento, Eventos y Proyectos
 * @author Irwin Jose Maria Esperanza Guardado (Creador y Autor Original)
 * @license CC-BY-NC-SA-4.0 (Creative Commons Reconocimiento-NoComercial-CompartirIgual 4.0 Internacional)
 * @notice Prohibido patentar, cerrar el código o registrar este software o sus derivados como propiedad exclusiva de terceros.
 */
import { ref, computed } from 'vue'
import { useUserStore } from '../../../store/users/useuserStore'

export const useUsers = () => {
    const headers = ref([
        {
            title: 'Nombre',
            key: 'nombre',
        },
        {
            title: 'Correo electrónico',
            key: 'email',
        },
        {
            title: 'Carnet / Código',
            key: 'carnet',
        },
        {
            title: 'Rol',
            key: 'roles',
        },
        {
            title: 'Carrera',
            key: 'carrera',
        },
        {
            title: 'Estado',
            key: 'estado',
        },
        {
            title: 'Acciones',
            key: 'acciones',
        },
    ])

    const store = useUserStore()
    const usuarios = computed(() => store.users)
    const paginacion = computed(() => store.paginacion)

    const getUsuarios = async (page = 1, params = {}) => {
        return await store.getUsers(page, params)
    }

    const createUsuario = async (data) => {
        return await store.createUser(data)
    }

    const updateUsuario = async (id, data) => {
        return await store.updateUser(id, data)
    }

    const toggleEstadoUsuario = async (id) => {
        return await store.toggleUserStatus(id)
    }

    return {
        headers,
        usuarios,
        paginacion,
        getUsuarios,
        createUsuario,
        updateUsuario,
        toggleEstadoUsuario,
    }
}
