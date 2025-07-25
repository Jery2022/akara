<?php
                // backend/api/Core/Response.php
namespace Core; // Définir le namespace pour cette classe

class Response
{
    /**
     * Envoie une réponse JSON avec le code de statut HTTP spécifié.
     *
     * @param mixed $data Les données à encoder en JSON.
     * @param int $statusCode Le code de statut HTTP (par défaut 200 OK).
     */
    public static function json(mixed $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit; // Arrête l'exécution du script après l'envoi de la réponse
    }

    /**
     * Envoie une réponse d'erreur JSON avec un message et un code de statut HTTP.
     *
     * @param string $message Le message d'erreur.
     * @param int $statusCode Le code de statut HTTP de l'erreur (par défaut 500 Internal Server Error).
     */
    public static function error(string $message, int $statusCode = 500): void
    {
        self::json(['error' => true, 'message' => $message], $statusCode);
    }

    /**
     * Envoie une réponse 404 Not Found.
     *
     * @param string $message Le message spécifique pour le cas 404.
     */
    public static function notFound(string $message = 'Ressource non trouvée.'): void
    {
        self::error($message, 404);
    }

    /**
     * Envoie une réponse 401 Unauthorized.
     *
     * @param string $message Le message spécifique pour le cas 401.
     */
    public static function unauthorized(string $message = 'Accès non autorisé. Vous devez vous authentifier.'): void
    {
        self::error($message, 401);
    }

    /**
     * Envoie une réponse 403 Forbidden.
     * (Peut être utilisé pour les problèmes de permissions après l'authentification)
     *
     * @param string $message Le message spécifique pour le cas 403.
     */
    public static function forbidden(string $message = 'Accès refusé. Vous n\'avez pas la permission requise.'): void
    {
        self::error($message, 403);
    }

    /**
     * Envoie une réponse 400 Bad Request.
     * (Utilisé pour les requêtes mal formées ou avec des données invalides)
     *
     * @param string $message Le message spécifique pour le cas 400.
     */
    public static function badRequest(string $message = 'Requête invalide.'): void
    {
        self::error($message, 400);
    }

    /**
     * Envoie une réponse 201 Created.
     * Utilisé pour indiquer qu'une nouvelle ressource a été créée avec succès.
     *
     * @param mixed $data Les données de la ressource créée.
     * @param string $message Un message de succès.
     */
    public static function created(mixed $data = [], string $message = 'Ressource créée avec succès.'): void
    {
        self::json(['success' => true, 'message' => $message, 'data' => $data], 201);
    }

    /**
     * Envoie une réponse 200 OK pour un succès général (e.g., mise à jour, suppression).
     *
     * @param string $message Un message de succès.
     * @param mixed $data Des données supplémentaires à inclure (optionnel).
     */

    public static function success(string $message = 'Opération réussie.', mixed $data = []): void
    {
        self::json(['success' => true, 'message' => $message, 'data' => $data], 200);
    }

}
