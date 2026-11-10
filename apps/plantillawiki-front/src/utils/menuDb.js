/**
 * @project Wiki TDA - Plataforma Universitaria de Conocimiento, Eventos y Proyectos
 * @author Irwin Jose Maria Esperanza Guardado (Creador y Autor Original)
 * @license CC-BY-NC-SA-4.0 (Creative Commons Reconocimiento-NoComercial-CompartirIgual 4.0 Internacional)
 * @notice Prohibido patentar, cerrar el código o registrar este software o sus derivados como propiedad exclusiva de terceros.
 */
export const menuItems = [
    {
        title: 'Inicio',
        icon: 'mdi-home-outline',
        to: '/',
        guestOnly: true,
    },
    {
        title: 'Wiki & Tutoriales',
        icon: 'mdi-book-open-page-variant-outline',
        to: '/explorar/wiki',
        guestOnly: true,
    },
    {
        title: 'Eventos & Proyectos',
        icon: 'mdi-calendar-star',
        to: '/explorar/eventos',
        guestOnly: true,
    },
    {
        title: 'Iniciar sesión',
        icon: 'mdi-login',
        to: '/login',
        guestOnly: true,
    },
    {
        title: 'Dashboard',
        icon: 'mdi-view-dashboard-outline',
        to: '/admin',
        requiresAuth: true,
        roles: ['admin'],
    },
    {
        title: 'Carreras',
        icon: 'mdi-school-outline',
        to: '/carrera',
        requiresAuth: true,
        roles: ['admin', 'coordinador', 'teacher', 'student'],
    },
    {
        title: 'Eventos',
        icon: 'mdi-calendar-outline',
        to: '/evento',
        requiresAuth: true,
        roles: ['admin', 'coordinador', 'teacher', 'student'],
    },
    {
        title: 'Proyectos',
        icon: 'mdi-folder-outline',
        to: '/proyecto',
        requiresAuth: true,
        roles: ['admin', 'coordinador', 'teacher', 'student'],
    },
    {
        title: 'Wiki',
        icon: 'mdi-book-open-page-variant-outline',
        to: '/wiki',
        requiresAuth: true,
        roles: ['admin', 'coordinador', 'teacher', 'student'],
    },
    {
        title: 'Portal Público',
        icon: 'mdi-earth',
        requiresAuth: true,
        roles: ['admin', 'coordinador', 'teacher', 'student'],
        children: [
            {
                title: 'Ver Wiki Pública',
                icon: 'mdi-book-open-outline',
                to: '/explorar/wiki',
                requiresAuth: true,
                roles: ['admin', 'coordinador', 'teacher', 'student'],
            },
            {
                title: 'Ver Eventos Públicos',
                icon: 'mdi-trophy-outline',
                to: '/explorar/eventos',
                requiresAuth: true,
                roles: ['admin', 'coordinador', 'teacher', 'student'],
            },
        ],
    },
    {
        title: 'Mi Perfil',
        icon: 'mdi-account-cog-outline',
        to: '/perfil',
        requiresAuth: true,
        roles: ['admin', 'coordinador', 'teacher', 'student'],
    },
    {
        title: 'Administración',
        icon: 'mdi-shield-account-outline',
        requiresAuth: true,
        roles: ['admin'],
        children: [
            {
                title: 'Usuarios',
                icon: 'mdi-account-group-outline',
                to: '/usuarios',
                requiresAuth: true,
                roles: ['admin'],
            },
            {
                title: 'Configuración',
                icon: 'mdi-cog-outline',
                to: '/admin/configuracion',
                requiresAuth: true,
                roles: ['admin'],
            },
        ],
    },
]