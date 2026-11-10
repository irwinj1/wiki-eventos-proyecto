/**
 * @project Wiki TDA - Plataforma Universitaria de Conocimiento, Eventos y Proyectos
 * @author Irwin Jose Maria Esperanza Guardado (Creador y Autor Original)
 * @license CC-BY-NC-SA-4.0 (Creative Commons Reconocimiento-NoComercial-CompartirIgual 4.0 Internacional)
 * @notice Prohibido patentar, cerrar el código o registrar este software o sus derivados como propiedad exclusiva de terceros.
 */
import { getStoredList } from '../utils/access';

function hasRole(required) {
    const roles = getStoredList('roles');

    if (Array.isArray(required)) {
        return required.some((role) => roles.includes(role));
    }

    return roles.includes(required);
}

export default {
    mounted(el, binding) {
        if (!hasRole(binding.value)) {
            el.remove();
        }
    },
};
