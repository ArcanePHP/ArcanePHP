<?php

namespace Core;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Twig\Environment;

class TwigExtention extends AbstractExtension
{
    private static $instance = null;
    private static $arg;
    private static int $id = 0;
    private static $tableName;
    private static $primaryKey;
    private static $tableInfo;

    public static function getInstance($arg)
    {
        self::$arg = $arg;
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('csrf', [$this, 'csrf'], ['is_safe' => ['html']]),
            new TwigFunction('for', [$this, 'for'], ['needs_environment' => true, 'is_safe' => ['html']]),
            new TwigFunction('ajax', [$this, 'ajax'], ['is_safe' => ['js']]),
            new TwigFunction('userscript', [$this, 'userscript'], ['is_safe' => ['html']])

        ];
    }

    public function csrf()
    {
        echo 'csrf';
        // echo '<h1>'.self::$tableName.'</h1>';
        // Your CSRF implementation here
        // echo ''
        // dd(self::$tableName);
        // vd(Model::getTableInfo('product')['primary_key']);

    }

    public function for(Environment $env, $iterable, $body)
    {
        // dd($body);
        self::$tableName = isset($iterable['tablename']) ? $iterable['tablename'] : false;
        if (self::$tableName) {
            self::$tableInfo = Model::getTableInfo(self::$tableName);
            self::$primaryKey = self::$tableInfo['primary_key'] ?? false;
            unset($iterable['tablename']);
        } else {
        }
        // dd($this->somefun($body));
        $output = '';
        $extra = '
        <input type="hidden">
        <input type="hidden">
        ';
        list($body, $matches) = $this->somefun($body);
        // dd($matches);
        // dd($this->somefun($body, []));
        $primaryKey = self::$primaryKey;

        foreach ($iterable as $key => $item) {

            $valueTaker = '<input type="hidden" ajax-value-taker ' . $primaryKey . '={{item.' . "$primaryKey" . '}}>';

            $output .= $env->createTemplate(
                $body . $valueTaker . $matches
            )->render(
                ['item' => $item]
            );
        }

        return $output;
    }
    function somefun($body)

    {
        // dd($body);
        // Find the position of the last closing tag
        $lastClosingTagPos = strrpos($body, '</');

        if ($lastClosingTagPos !== false) {
            // Extract the last closing tag
            $matches = substr($body, $lastClosingTagPos);

            // Remove the last closing tag from the body
            $newbody = substr($body, 0, $lastClosingTagPos);
            // dd($matches);
            return [$newbody, $matches];
        }

        // If no closing tag is found, return the original body and an empty string
        return [$body, ''];
    }


    public function ajax()
    {
        // echo ROOT;
        // exit;
        echo "   
        function getEndParent(element) {
  // Base case: if we've reached the top of the DOM or found the element
  if (!element || element.tagName === 'BODY') {
    return null;
  }

  // Check if the current element is the hidden input we're looking for
  if (element.matches('input[type=\"hidden\"][ajax-value-taker]')) {
    return element;
  }

  // Check immediate children
  const hiddenInput = element.querySelector('input[type=\"hidden\"][ajax-value-taker]');
  if (hiddenInput) {
    return element;
  }

  // Recursively check the parent
  return getEndParent(element.parentElement);
}

         function ajaxCall(data,method,csrf,url) {
        $.ajax({
            url: url,
            type: method,
            data: data,
            headers: {
                'X-CSRF-TOKEN': csrf
            },
            success: function (response) {
                console.log(response);
            },
            error: function (jqXHR, textStatus, errorThrown) {
                console.error(textStatus, errorThrown);
            }
        });
    }
";
    }
    public function userscript()
    {
        $script = ASSET_PATH . 'ajax/userscript.js';
        echo "        <script scr='{$script}'></script>
";
    }
}
