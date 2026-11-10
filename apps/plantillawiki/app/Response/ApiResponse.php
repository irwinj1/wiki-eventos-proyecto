<?php
/**
 * @project Wiki TDA - Plataforma Universitaria de Conocimiento, Eventos y Proyectos
 * @author Irwin Jose Maria Esperanza Guardado (Creador y Autor Original)
 * @license CC-BY-NC-SA-4.0 (Creative Commons Reconocimiento-NoComercial-CompartirIgual 4.0 Internacional)
 * @notice Prohibido patentar, cerrar el código o registrar este software o sus derivados como propiedad exclusiva de terceros.
 */

namespace App\Response;

class ApiResponse{
    
    public static function success($message = 'Success', $code = 200, $data = [], $pagination = [])
    {
        return response()->json([
            'message' => $message,
            'status' => $code,
            'data' => $data,
            'pagination' => $pagination
        ], $code);
    }

    public static function error($message, $code = 500)
    {
        return response()->json([
            'message' => $message,
            'status' => $code
        ], $code);
    }
}