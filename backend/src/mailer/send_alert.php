<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendors/autoload.php';

$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host = SMTP_HOST;
    $mail->SMTPAuth = true;
    $mail->Username = SMTP_USER;
    $mail->Password = SMTP_PASS;
    $mail->SMTPSecure = 'tls';
    $mail->Port = SMTP_PORT;

    $mail->setFrom(SMTP_USER, 'Les Compagnons du BTP');
    $mail->addAddress('admin@entreprise.ci');
    $mail->isHTML(true);
    $mail->Subject = 'Alerte : Stock faible';
    $mail->Body = '<h3>Le ciment est en rupture de stock !</h3>';

    $mail->send();
    echo json_encode(['status' => 'Email envoyé']);
} catch (Exception $e) {
    echo json_encode(['error' => $mail->ErrorInfo]);
}
?>