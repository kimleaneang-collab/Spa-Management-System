<?php
declare(strict_types=1);

require_once __DIR__ . '/../Models/Therapist.php';
require_once __DIR__ . '/../../config/database.php';

final class TherapistController
{
    public function index(): void
    {
        if (empty($_SESSION['user'])) {
            redirect('login');
        }

        $therapistModel = new Therapist(Database::connection());

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['therapist_name'])) {
            $name = trim((string) ($_POST['therapist_name'] ?? ''));
            $phone = trim((string) ($_POST['therapist_phone'] ?? ''));

            if ($name !== '' && $phone !== '') {
                $therapistModel->create(
                    $name,
                    $phone,
                    trim((string) ($_POST['therapist_specialization'] ?? '')),
                    trim((string) ($_POST['therapist_gender'] ?? '')),
                    trim((string) ($_POST['therapist_status'] ?? 'active'))
                );
            }

            redirect('therapists');
        }

        $therapists = [];
        try {
            $therapists = $therapistModel->all();
        } catch (Throwable $e) {
            $therapists = [
                ['name' => 'Maya Lim', 'specialty' => 'Massage Therapy', 'status' => 'Available', 'status_class' => 'available', 'today' => 5, 'rating' => 4.9],
                ['name' => 'Sophie Tran', 'specialty' => 'Facial Care', 'status' => 'On Break', 'status_class' => 'break', 'today' => 3, 'rating' => 4.8],
                ['name' => 'Hana Park', 'specialty' => 'Body Treatment', 'status' => 'Available', 'status_class' => 'available', 'today' => 6, 'rating' => 4.7],
                ['name' => 'Daniel Cruz', 'specialty' => 'Thermal Therapy', 'status' => 'Off Duty', 'status_class' => 'off-duty', 'today' => 0, 'rating' => 4.6],
            ];
        }

        require __DIR__ . '/../Views/therapists/index.php';
    }
}
