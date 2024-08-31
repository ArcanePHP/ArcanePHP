<?php

namespace Core;

class UserHelper
{

    private static function app_path(string $path)
    {


        $basePath = dirname(__DIR__);  // Assumes this function is in a subdirectory of your app
        $appPath = $basePath . DIRECTORY_SEPARATOR . 'App';

        if ($path) {
            return $appPath . DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR);
        }

        return $appPath;
    }
    public static function makeController(string $controllerName)
    {

        // Capitalize the first letter of the model name
        // $controllerName = ucfirst($modelName) . 'Controller';

        // Define the path where the controller will be created
        $controllerPath = self::app_path("Controllers/{$controllerName}.php");

        // Check if the controller already exists
        if (file_exists($controllerPath)) {
            echo "Controller {$controllerName} already exists.\n";
            return;
        }

        // Create the controller content
        $controllerContent = "<?php\n\nnamespace App\Controllers;\n\n";
        // $controllerContent .= "use App\Http\Controllers\Controller;\n";
        // $controllerContent .= "use App\Models\\{$modelName};\n";
        $controllerContent .= "use Core\Request;\n\n";
        $controllerContent .= "class {$controllerName}\n";
        $controllerContent .= "{\n";
        $controllerContent .= "    public function index()\n";
        $controllerContent .= "    {\n";
        $controllerContent .= "        // TODO: Implement index method\n";
        $controllerContent .= "    }\n\n";
        $controllerContent .= "    public function create()\n";
        $controllerContent .= "    {\n";
        $controllerContent .= "        // TODO: Implement create method\n";
        $controllerContent .= "    }\n\n";
        $controllerContent .= "    public function store(Request \$request)\n";
        $controllerContent .= "    {\n";
        $controllerContent .= "        // TODO: Implement store method\n";
        $controllerContent .= "    }\n\n";
        $controllerContent .= "    public function show(\$id)\n";
        $controllerContent .= "    {\n";
        $controllerContent .= "        // TODO: Implement show method\n";
        $controllerContent .= "    }\n\n";
        $controllerContent .= "    public function edit(\$id)\n";
        $controllerContent .= "    {\n";
        $controllerContent .= "        // TODO: Implement edit method\n";
        $controllerContent .= "    }\n\n";
        $controllerContent .= "    public function update(Request \$request, \$id)\n";
        $controllerContent .= "    {\n";
        $controllerContent .= "        // TODO: Implement update method\n";
        $controllerContent .= "    }\n\n";
        $controllerContent .= "    public function destroy(\$id)\n";
        $controllerContent .= "    {\n";
        $controllerContent .= "        // TODO: Implement destroy method\n";
        $controllerContent .= "    }\n";
        $controllerContent .= "}\n";

        // Write the controller file
        if (file_put_contents($controllerPath, $controllerContent) !== false) {
            echo "Controller {$controllerName} created successfully.\n";
        } else {
            echo "Failed to create controller {$controllerName}.\n";
        }
    }
}
