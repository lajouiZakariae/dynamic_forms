<?php

namespace Core;

class Renderer
{
    protected  function el(string $el, array $attrs = [], array|string $children = null, bool $self_closing = false): string
    {
        $html = '<' . $el . ' ';

        foreach ($attrs as $k => $v) {
            if (is_bool($v)) $html .=  $v ? $k . ' ' : '';
            else $html .=  $k . '="' . $v . '" ';
        };

        $html .= '>';

        if ($self_closing) return $html;

        if (is_string($children)) $html .= $children;

        if (is_array($children))
            foreach ($children as $child)
                $html .= $child;

        $html .= '</' . $el . '>';

        return $html;
    }


    protected function renderError(string $msg): string
    {
        return  '<div class="alert alert-danger">' . $msg . '</div>';
    }

    protected function renderWarning(string $msg): string
    {
        return  '<div class="alert alert-warning">' . $msg . '</div>';
    }
}
