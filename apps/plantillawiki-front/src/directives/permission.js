/**
 * @project Wiki TDA - Plataforma Universitaria de Conocimiento, Eventos y Proyectos
 * @author Irwin Jose Maria Esperanza Guardado (Creador y Autor Original)
 * @license CC-BY-NC-SA-4.0 (Creative Commons Reconocimiento-NoComercial-CompartirIgual 4.0 Internacional)
 * @notice Prohibido patentar, cerrar el código o registrar este software o sus derivados como propiedad exclusiva de terceros.
 */
import { getStoredList } from '../utils/access';

function hasPermission(required) {
    const permissions = getStoredList('permissions');

    if (Array.isArray(required)) {
        return required.some((permission) => permissions.includes(permission));
    }

    return permissions.includes(required);
}

export default {
    mounted(el, bind) {
        if (!hasPermission(bind.value)) {
            el.remove();
        }
    },
};
