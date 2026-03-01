<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\View;
use App\Repositories\BarRepository;
use App\Services\BoardService;

class AppController
{
    public function __construct(private BarRepository $repo, private BoardService $boardService)
    {
    }

    public function login(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $user = $this->repo->findUserByUsername(trim($_POST['username'] ?? ''));
            if ($user && $user['status'] === 'attivo' && password_verify($_POST['password'] ?? '', $user['password_hash'])) {
                Auth::login($user);
                View::redirect('./');
            }
            View::render('auth/login', ['error' => 'Credenziali non valide']);
            return;
        }
        View::render('auth/login');
    }

    public function logout(): void
    {
        Auth::logout();
        View::redirect('?action=login');
    }

    public function dashboard(): void
    {
        $this->guard();
        if (Auth::isAdmin()) {
            $boards = array_slice($this->repo->boards(), 0, 12);
            $notifications = array_slice($this->repo->notifications(), 0, 20);

            View::render('admin/dashboard', [
                'boards' => $boards,
                'notifications' => $notifications,
            ]);
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'])) {
            $message = trim((string) $_POST['message']);
            if ($message !== '') {
                $this->repo->createNotification((int) Auth::user()['id'], null, $message);
            }
            View::redirect('./');
        }

        $boards = $this->repo->boardsForConsultation();
        $selectedBoardId = (int) ($_GET['board_id'] ?? ($boards[0]['id'] ?? 0));
        $selectedBoard = null;

        foreach ($boards as $board) {
            if ((int) $board['id'] === $selectedBoardId) {
                $selectedBoard = $board;
                break;
            }
        }

        if ($selectedBoard === null && !empty($boards)) {
            $selectedBoard = $boards[0];
            $selectedBoardId = (int) $selectedBoard['id'];
        }

        View::render('consultation/dashboard', [
            'boards' => $boards,
            'selectedBoard' => $selectedBoard,
            'selectedBoardId' => $selectedBoardId,
            'shifts' => $this->repo->consultationShifts(),
            'notifications' => $this->repo->consultationNotifications(),
            'directoryUsers' => $this->repo->consultationDirectory(),
        ]);
    }

    public function boards(): void
    {
        $this->guardAdmin();
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_board'])) {
            $month = max(1, min(12, (int) ($_POST['month'] ?? 0)));
            $year = (int) ($_POST['year'] ?? 0);
            if ($year < 1970 || $year > 2100) {
                $year = (int) date('Y');
            }
            $id = $this->repo->createBoard($month, $year);
            $this->boardService->generate($id, $month, $year);
        }
        if (isset($_GET['delete'])) {
            $this->repo->deleteBoard((int) $_GET['delete']);
            View::redirect('?action=boards');
        }
        View::render('admin/boards', ['boards' => $this->repo->boards()]);
    }

    public function boardEdit(): void
    {
        $this->guard();
        $boardId = (int) ($_GET['id'] ?? 0);
        if ($boardId < 1) {
            View::redirect('./');
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && Auth::isAdmin()) {
            foreach ($_POST['day'] ?? [] as $dayId => $dayData) {
                $dayId = (int) $dayId;
                $dayTypeId = (int) ($dayData['day_type_id'] ?? 0);

                $this->repo->saveBoardDay([
                    'id' => $dayId,
                    'day_type_id' => $dayTypeId,
                    'notes' => $dayData['notes'] ?? null,
                ]);
                $this->repo->syncBoardDayShifts($dayId, $dayTypeId);

                foreach ($dayData['shifts'] ?? [] as $shiftId => $shiftData) {
                    $this->repo->updateBoardDayShiftVolunteers(
                        (int) $shiftId,
                        trim((string) ($shiftData['volunteers'] ?? '')),
                        trim((string) ($shiftData['responsabile_chiusura'] ?? '')) !== '' ? trim((string) ($shiftData['responsabile_chiusura'] ?? '')) : null
                    );
                }
            }
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !Auth::isAdmin() && isset($_POST['report_day'])) {
            $this->repo->createNotification((int) Auth::user()['id'], (int) $_POST['report_day'], trim($_POST['message']));
        }

        $days = $this->repo->boardDays($boardId);
        foreach ($days as $day) {
            $this->repo->syncBoardDayShifts((int) $day['id'], (int) $day['day_type_id']);
        }

        View::render('admin/board_edit', [
            'board' => $this->repo->board($boardId),
            'days' => $days,
            'dayTypes' => $this->repo->dayTypes(),
            'dayShifts' => $this->repo->boardDayShiftsMap($boardId),
            'activeUsers' => $this->repo->userDisplayNames(),
        ]);
    }

    public function users(): void
    {
        $this->guardAdmin();
        $duplicateUsernameError = '';
        $passwordChangeError = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['change_password_user_id'])) {
                $newPassword = (string) ($_POST['new_password'] ?? '');
                $confirmPassword = (string) ($_POST['confirm_new_password'] ?? '');

                if ($newPassword === '' || $newPassword !== $confirmPassword) {
                    $passwordChangeError = 'Le due password non coincidono.';
                } else {
                    $this->repo->changeUserPassword((int) $_POST['change_password_user_id'], $newPassword);
                }
            } elseif (isset($_POST['update_user_id'])) {
                $this->repo->updateUserProfile(
                    (int) $_POST['update_user_id'],
                    (string) ($_POST['last_name'] ?? ''),
                    (string) ($_POST['first_name'] ?? ''),
                    (string) ($_POST['phone'] ?? ''),
                    (string) ($_POST['role'] ?? 'user'),
                    (string) ($_POST['status'] ?? 'attivo')
                );
            } else {
                $username = trim((string) ($_POST['username'] ?? ''));
                if ($username !== '' && $this->repo->findUserByUsername($username) !== null) {
                    $duplicateUsernameError = 'Esiste già un utente con questa username.';
                } else {
                    $this->repo->saveUser($_POST);
                }
            }
        }
        if (isset($_GET['delete'])) {
            $this->repo->deleteUser((int) $_GET['delete']);
            View::redirect('?action=users');
        }
        View::render('admin/users', [
            'users' => $this->repo->allUsers(),
            'duplicateUsernameError' => $duplicateUsernameError,
            'passwordChangeError' => $passwordChangeError,
        ]);
    }

    public function dayTypes(): void
    {
        $this->guardAdmin();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->repo->saveDayType($_POST);
            View::redirect('?action=day_types');
        }
        if (isset($_GET['delete'])) {
            $this->repo->deleteDayType((int) $_GET['delete']);
            View::redirect('?action=day_types');
        }

        $editingId = isset($_GET['edit']) ? (int) $_GET['edit'] : 0;
        $editing = $editingId > 0 ? $this->repo->dayTypeById($editingId) : null;

        View::render('admin/day_types', [
            'types' => $this->repo->dayTypes(),
            'editing' => $editing,
        ]);
    }

    public function shiftConfig(): void
    {
        $this->guardAdmin();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $error = $this->repo->saveDailyShift($_POST);
            if ($error !== null) {
                View::redirect('?action=shift_config&error=' . urlencode($error));
            }
            View::redirect('?action=shift_config&saved=1');
        }

        if (isset($_GET['delete'])) {
            $this->repo->deleteDailyShift((int) $_GET['delete']);
            View::redirect('?action=shift_config');
        }

        $editingId = isset($_GET['edit']) ? (int) $_GET['edit'] : 0;
        $editing = $editingId > 0 ? $this->repo->dailyShiftById($editingId) : null;

        View::render('admin/shift_config', [
            'shifts' => $this->repo->shiftConfigs(),
            'dayTypes' => $this->repo->dayTypes(),
            'editing' => $editing,
            'error' => isset($_GET['error']) ? (string) $_GET['error'] : '',
            'saved' => isset($_GET['saved']),
        ]);
    }

    public function calendar(): void
    {
        $this->guardAdmin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $calendarDayId = (int) ($_POST['save_id'] ?? 0);
            $rowData = $_POST['row'][$calendarDayId] ?? null;
            if ($calendarDayId > 0) {
                $this->repo->updateCalendarDayDetails(
                    $calendarDayId,
                    trim((string) ($rowData['recurrence_name'] ?? '')),
                    trim((string) ($rowData['santo'] ?? '')),
                    (int) ($rowData['day_type_id'] ?? 0),
                    isset($rowData['is_special'])
                );
            }
            View::redirect('?action=calendar');
        }

        View::render('admin/calendar', [
            'days' => $this->repo->calendarDays($_GET['month'] ?? null),
            'types' => $this->repo->dayTypes(),
        ]);
    }


    public function information(): void
    {
        $this->guard();

        $serverSoftwareRaw = trim((string) ($_SERVER['SERVER_SOFTWARE'] ?? 'Non disponibile'));
        $httpServerName = 'Non disponibile';
        $httpServerVersion = 'Non disponibile';
        if ($serverSoftwareRaw !== '' && $serverSoftwareRaw !== 'Non disponibile') {
            $serverSoftwareParts = preg_split('/\s+/', $serverSoftwareRaw);
            $mainServerToken = (string) ($serverSoftwareParts[0] ?? '');
            if ($mainServerToken !== '' && str_contains($mainServerToken, '/')) {
                [$httpServerName, $httpServerVersion] = array_pad(explode('/', $mainServerToken, 2), 2, 'Non disponibile');
            } else {
                $httpServerName = $mainServerToken !== '' ? $mainServerToken : $serverSoftwareRaw;
            }
        }

        View::render('admin/information', [
            'programInfo' => $this->repo->programInfoSettings(),
            'serverInfo' => [
                'os_name' => php_uname('s') ?: 'Non disponibile',
                'os_version' => php_uname('r') ?: 'Non disponibile',
                'http_server_name' => $httpServerName,
                'http_server_version' => $httpServerVersion,
                'php_version' => PHP_VERSION,
            ],
        ]);
    }

    public function license(): void
    {
        $this->guard();

        $licensePath = dirname(__DIR__, 2) . '/LICENSE';
        $licenseContent = is_readable($licensePath) ? (string) file_get_contents($licensePath) : 'File LICENSE non disponibile.';

        header('Content-Type: text/html; charset=utf-8');
        echo '<!doctype html><html lang="it"><head><meta charset="utf-8"><title>Licenza programma</title>'
            . '<style>body{font-family:system-ui,-apple-system,Segoe UI,Roboto,sans-serif;margin:1.5rem;background:#f8f9fa;color:#212529}pre{white-space:pre-wrap;background:#fff;border:1px solid #dee2e6;border-radius:.375rem;padding:1rem;max-width:1000px;margin:0 auto}</style>'
            . '</head><body><pre>' . htmlspecialchars($licenseContent) . '</pre></body></html>';
    }

    public function setup(): void
    {
        $this->guardAdmin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->repo->saveSetupSettings($_POST);
            View::redirect('?action=setup&saved=1');
        }

        View::render('admin/setup', [
            'settings' => $this->repo->setupSettings(),
            'saved' => isset($_GET['saved']),
        ]);
    }

    public function notifications(): void
    {
        $this->guardAdmin();

        if (isset($_GET['delete'])) {
            $this->repo->deleteNotification((int) $_GET['delete']);
            View::redirect('?action=notifications');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['quick_status_id'], $_POST['quick_status'])) {
                $this->repo->updateNotificationStatus((int) $_POST['quick_status_id'], (string) $_POST['quick_status']);
            } else {
                $this->repo->saveNotification($_POST);
            }
            View::redirect('?action=notifications');
        }

        $editingId = isset($_GET['edit']) ? (int) $_GET['edit'] : 0;
        $editing = $editingId > 0 ? $this->repo->notificationById($editingId) : null;

        View::render('admin/notifications', [
            'notifications' => $this->repo->notifications(),
            'users' => $this->repo->activeUsers(),
            'boardDays' => $this->repo->boardDaysForSelect(),
            'editing' => $editing,
            'statuses' => ['inviata', 'letto', 'in_corso', 'chiuso'],
        ]);
    }

    private function guard(): void
    {
        if (!Auth::check()) {
            View::redirect('?action=login');
        }
    }

    private function guardAdmin(): void
    {
        $this->guard();
        if (!Auth::isAdmin()) {
            View::redirect('./');
        }
    }
}
