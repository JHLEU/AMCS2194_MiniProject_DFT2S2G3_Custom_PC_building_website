<?php
/**
 * BaseController
 * Shared rendering, redirection, and session flash message utilities.
 */

abstract class BaseController {
    /**
     * Render a view wrapped in the main application layout
     */
    protected function render(string $viewPath, array $data = []): void {
        extract($data);

        $flash = $this->getFlash();

        // Layout wrapper
        $headerFile = __DIR__ . '/../views/layout/header.php';
        $navbarFile = __DIR__ . '/../views/layout/navbar.php';
        $contentFile = __DIR__ . '/../views/' . $viewPath . '.php';
        $footerFile = __DIR__ . '/../views/layout/footer.php';

        if (!file_exists($contentFile)) {
            throw new RuntimeException("View file not found: {$contentFile}");
        }

        require $headerFile;
        require $navbarFile;
        require $contentFile;
        require $footerFile;
    }

    /**
     * Redirect to a route with optional query parameters
     */
    protected function redirect(string $route, array $params = []): void {
        $queryString = http_build_query(array_merge(['route' => $route], $params));
        header("Location: index.php?{$queryString}");
        exit;
    }

    /**
     * Flash messages stored in session for notifications
     */
    protected function setFlash(string $type, string $message): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['flash'] = [
            'type'    => $type, // success, danger, warning, info
            'message' => $message,
        ];
    }

    protected function getFlash(): ?array {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (isset($_SESSION['flash'])) {
            $flash = $_SESSION['flash'];
            unset($_SESSION['flash']);
            return $flash;
        }
        return null;
    }

    /**
     * Output JSON for AJAX requests
     */
    protected function json(array $data, int $statusCode = 200): void {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}
