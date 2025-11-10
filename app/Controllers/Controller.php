<?php

declare(strict_types=1);

namespace CoreNewspaper\Controllers;

use CoreNewspaper\Core\Response;

abstract class Controller
{
    protected function render(string $template, array $data = [], int $status = 200): Response
    {
        $response = new Response();
        $response->setStatus($status);
        $response->setHeader('Content-Type', 'text/html; charset=UTF-8');
        $response->setBody($this->renderView($template, $data));
        return $response;
    }

    protected function redirect(string $url): Response
    {
        $response = new Response();
        $response->setStatus(302);
        $response->setHeader('Location', $url);
        return $response;
    }

    private function renderView(string $template, array $data): string
    {
        $viewPath = __DIR__ . '/../../views/' . $template . '.php';
        if (!is_file($viewPath)) {
            throw new \RuntimeException('View not found: ' . $template);
        }

        extract($data, EXTR_OVERWRITE);
        ob_start();
        include $viewPath;
        return (string)ob_get_clean();
    }
}
