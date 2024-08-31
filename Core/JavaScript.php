<?php

namespace Core;

class JavaScript
{
    private static string  $unmatchedColumns;
    public function __construct(string $need) {}
    public static function ajax($data, $route)
    {
        $filename = './public/assets/ajax/userscript.js';

        // Open the file in read mode
        $file = fopen($filename, 'r');

        if ($file) {
            // Read the entire contents of the file
            $existing_content = stream_get_contents($file);
            fclose($file);

            $className = $data[1];
            $urlName = $data[2];
            $url = $route['url'];
            $requiredColumn = '';

            if (isset($route['required_column_with_url'])) {
                $requiredColumn = $route['required_column_with_url'];
            }

            $columns = explode(',', $data[4]);
            $tableName = $data[3];
            $tableInfo = Model::getTableInfo($tableName);
            $method = $route['method'];

            if (!$tableInfo) {
                echo 'there is no such table :' . $tableName;
                return false;
            }

            $requestMethod = str_replace('--', '', $data['method']);
            if ($method !== $requestMethod) {
                echo 'Method ' . $requestMethod . ' not allowed for route named ' . $urlName . ' .Allowed method : ' . $route['method'] . ';' . PHP_EOL;
                return false;
            }

            $matchedColumns = self::match(array_values($columns), array_keys($tableInfo));
            if (!$matchedColumns) {
                echo 'Columns not found in table :' . self::$unmatchedColumns . PHP_EOL;
                echo 'Check your ajax command again' . PHP_EOL;
                return false;
            }

            $script = '';
            $data = '        
                let data ={';
            foreach ($columns as $column) {
                $script .= "
                        let {$column} = valuetaker.getAttribute('{$column}');
                       
            " . PHP_EOL;
                $data .= "
                        '$column':$column ," . PHP_EOL;
            }

            if (!isset($tableInfo[$requiredColumn])) {
                echo 'Required column  "' . $requiredColumn . '" not found in ' . $tableName . ' table' . PHP_EOL;
                echo 'check web.php  where "' . $urlName . '" router is defined' . PHP_EOL;
                echo 'change value to valid  column in ' . $tableName . ' instead of /{' . $requiredColumn . '}' . PHP_EOL;
                echo 'And refresh the page once ' . PHP_EOL;
                return false;
            }

            $data .= '
                        };';
            $script .= $data;

            $fun = '';
            $url = str_replace(':' . $method, '', $url);
            if ($requiredColumn) {
                $fun = " ajaxCall(data,'{$method}','','{$url}'+data.{$requiredColumn})";
            } else {
                $fun = " ajaxCall(data,'{$method}','')";
            }

            $content = "
        document.addEventListener('DOMContentLoaded', function () {

        let elements = document.querySelectorAll('.{$className}');

                        elements.forEach(element => {
                        element.addEventListener('click', function (e) {
                            let endParent = getEndParent(e.target);
                            let valuetaker = endParent.querySelector('input[type=\"hidden\"][ajax-value-taker]');

                            $script

                        "
                . $fun .
                "
                    
                })
                    })           
                });
";

            // Check if the generated content is already present in the file
            if (strpos($existing_content, $content) !== false) {
                echo "The generated content already exists in the file. Updating the existing content.";
                // Replace the existing content with the new content
                $updated_content = str_replace($content, $content, $existing_content);
            } else {
                $updated_content = $existing_content . $content;
            }

            // Open the file in write mode and write the updated content
            $file = fopen($filename, 'w');
            if ($file) {
                fwrite($file, $updated_content);
                fclose($file);
                echo "File '$filename' updated successfully!";
            } else {
                echo "Failed to open the file for writing.";
            }
        } else {
            echo "Failed to open the file.";
        }
    }

    public static function match(array $array1, array $array2)
    {
        // Check if all elements of array1 are present in array2
        $missingKeys = array_diff($array1, $array2);
        if (empty($missingKeys)) {
            // If no keys are missing, return true
            return true;
        } else {
            // If some keys are missing, return a message indicating which keys are missing
            self::$unmatchedColumns = implode(', ', $missingKeys);
            return false;
        }
    }
}
