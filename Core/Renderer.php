<?php

namespace Core;

class Renderer
{
    protected function renderError(string $msg): string
    {
        return  '<div class="alert alert-danger">' . $msg . '</div>';
    }

    protected function renderWarning(string $msg): string
    {
        return  '<div class="alert alert-warning">' . $msg . '</div>';
    }
}
