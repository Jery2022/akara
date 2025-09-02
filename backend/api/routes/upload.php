<?php
// backend/api/routes/upload.php

require_once __DIR__ . '/../../../vendor/autoload.php';

use Core\Response;

return [
    'POST' => function (array $params, ?object $currentUser) {
        if (!$currentUser) {
            Response::unauthorized('Accès non autorisé', 'Vous devez vous authentifier pour téléverser un fichier.');
            return;
        }

        if (isset($_FILES['file'])) {
            $uploadDir = __DIR__ . '/../../src/public/upload/';
            if (!is_dir($uploadDir)) {
                if (!mkdir($uploadDir, 0777, true)) {
                    Response::error('Échec de la création du dossier de téléversement.', 500);
                    return;
                }
            }

            $file = $_FILES['file'];
            $fileName = basename($file['name']); // Sanitize filename
            $fileTmpName = $file['tmp_name'];
            $fileSize = $file['size'];
            $fileError = $file['error'];

            if ($fileError !== UPLOAD_ERR_OK) {
                Response::error('Erreur lors du téléversement du fichier.', 500, ['details' => 'Upload error code: ' . $fileError]);
                return;
            }

            // Security checks
            $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            $allowedExt = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'];

            if (!in_array($fileExt, $allowedExt)) {
                Response::badRequest('Type de fichier non autorisé.');
                return;
            }

            if ($fileSize > 5000000) { // 5MB limit
                Response::badRequest('Le fichier est trop volumineux (limite de 5MB).');
                return;
            }

            // Create a unique name for the file
            $newFileName = uniqid('', true) . '.' . $fileExt;
            $destination = $uploadDir . $newFileName;

            if (move_uploaded_file($fileTmpName, $destination)) {
                // Return the web-accessible path
                $webPath = '/upload/' . $newFileName;
                Response::success('Fichier téléversé avec succès.', ['filePath' => $webPath]);
            } else {
                Response::error('Échec du déplacement du fichier téléversé.', 500);
            }
        } else {
            Response::badRequest('Aucun fichier n\'a été téléversé.');
        }
    }
];
