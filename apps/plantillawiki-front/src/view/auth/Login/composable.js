/**
 * @project Wiki TDA - Plataforma Universitaria de Conocimiento, Eventos y Proyectos
 * @author Irwin Jose Maria Esperanza Guardado (Creador y Autor Original)
 * @license CC-BY-NC-SA-4.0 (Creative Commons Reconocimiento-NoComercial-CompartirIgual 4.0 Internacional)
 * @notice Prohibido patentar, cerrar el código o registrar este software o sus derivados como propiedad exclusiva de terceros.
 */
import { useLoginStore } from '../../../store/auth/useLoginStore.js';
import { getStoredList } from '../../../utils/access.js';
import { isPasswordChangeRequired } from '../../../utils/authSession.js';
import { computed } from 'vue';
import { useRouter } from 'vue-router';

export const useLogin = () => {
    const router = useRouter();
    const store = useLoginStore();
    
    const formulario = computed(() => store.formulario);

    const submit = async () => {
        const response = await store.submit();
        if (response == 200) {
            if (isPasswordChangeRequired()) {
                await router.push({ path: '/perfil', query: { firstLogin: '1' } });
                return;
            }
            const roles = getStoredList('roles');
            if (roles.includes('admin')) {
                await router.push({ name: 'Dashboard' });
            } else if (roles.includes('coordinador')) {
                await router.push({ path: '/evento' });
            } else if (roles.includes('teacher')) {
                await router.push({ path: '/proyecto' });
            } else {
                await router.push({ path: '/wiki' });
            }
        }
    }

    return{
        formulario,
        submit
    }
}