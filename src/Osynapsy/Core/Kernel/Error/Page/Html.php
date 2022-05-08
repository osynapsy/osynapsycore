<?php
namespace Osynapsy\Core\Kernel\Error\Page;

use Osynapsy\Kernel\Error\InterfacePage;

/**
 * Description of Html
 *
 * @author Pietro
 */
class Html implements InterfacePage
{
    public $code = 400;
    public $containerClass = '';
    public $comments = [];
    public $message;
    public $submessage;
    public $trace;

    public function get() : string
    {
        return (string) $this->render();
    }

    public function render()
    {
        $trace = $this->renderTrace();
        $this->containerClass = empty($trace) ? 'container-center' : 'container';
        $messageFontSize = empty($trace) ? 'font-2em' : 'font-1em';
        $title = strip_tags($this->message);
        $message = nl2br($this->message);
        $comment = implode(PHP_EOL, $this->comments);
        $submessage = $this->submessage;
        return <<<PAGE
            <html>
                <title>{$title}</title>
            <head>
                <style>
                * {font-family: Arial;}
                body {margin: 0px; position: relative;}
                div.container-center {position: absolute; top: 40%; width: 100%; text-align: center; margin: auto;}
                table {width: 100%; margin-top: 20px;}
                .font-2em {font-size: 2em;}
                .font-1em {font-size: 1em;}
                .submessage {font-size: 0.35em; margin-top: 10px; color: #ccc;}
                td,th {font-size: 12px; font-family: Arial; padding: 3px; border: 0.5px solid silver}
            </style>
            </head>
            <body>
            <div class="{$this->containerClass}">
                <div class="message {$messageFontSize}">{$message}<br><div class="submessage">{$submessage}</div></div>
                {$trace}
            </div>
            <!--
            {$comment}
            -->
            </body>
            </html>
PAGE;
    }

    protected function renderTrace()
    {
        if (empty($this->trace)) {
            return '';
        }
        $trace = '<table style="border-collapse: collapse;">';
        $trace .= '<tr>';
        $trace .= '<th>Class</th>';
        $trace .= '<th>Function</th>';
        $trace .= '<th>File</th>';
        $trace .= '<th>Line</th>';
        $trace .= '</tr>';
        foreach ($this->trace as $step) {
            $trace .= '<tr>';
            $trace .= '<td>'.(!empty($step['class']) ? $step['class'] : '&nbsp;').'</td>';
            $trace .= '<td>'.(!empty($step['function']) ? $step['function'] : '&nbsp;').'</td>';
            $trace .= '<td>'.(!empty($step['file']) ? $step['file'] : '&nbsp;').'</td>';
            $trace .= '<td>'.(!empty($step['line']) ? $step['line'] : '&nbsp;').'</td>';
            $trace .= '</tr>';
        }
        $trace .= '</table>';
        return $trace;
    }

    public function setComment($comment): void
    {
        $this->comments = array_merge($this->comments, $comment);
    }

    public function setMessage($message, $submessage = null): void
    {
        $this->message = $message;
        $this->submessage = $submessage;
    }

    public function setTrace(array $trace): void
    {
        $this->trace = $trace;
    }
}
