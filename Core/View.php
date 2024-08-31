<?php

namespace Core;

class View
{

    public static function render($template, $arg = [])
    {
        static $twig = null;

        if ($twig === null) {
            $loader = new \Twig\Loader\FilesystemLoader(
                ROOT . '/App/Views/'
            );
            $twig = new \Twig\Environment($loader, [
                'debug' => true
            ]);
            $twig->addExtension(new \Twig\Extension\DebugExtension());

            // Register your custom extension
            $twig->addExtension(TwigExtention::getInstance($arg));

            // $var = [];
            $var = array_merge(
                ['root' => ASSET_PATH],
                Router::getAllUrl()
            );
            // dd($var);
            addglobal($twig, $var);

        }
        // dd($var);

        // Render the template with the arguments
        echo $twig->render($template, $arg);
        exit();
    }

    public static function redirect(string $url, array|string $incomingData = [], int $http_response_code = 302): void
    {
        // $this->$data
        // dd(header("Location: $url", false, $http_response_code));

        header("Location: $url", false, $http_response_code);
        exit();
    }
}
