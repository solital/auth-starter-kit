<?php

namespace Solital\Login;

use Solital\Core\Console\Interface\CommandInterface;
use Solital\Core\Console\Output\{ColorsEnum, ConsoleOutput};
use Solital\Core\Console\{Command, InputOutput};
use Solital\Core\FileSystem\HandleFiles;
use Solital\Core\Kernel\Console\HelpersTrait;
use Solital\Core\Kernel\{DebugCore, ServiceLoader};

class MakeAuth extends Command implements CommandInterface
{
    use HelpersTrait;

    /**
     * @var string
     */
    protected string $command = 'auth:skeleton';

    /**
     * @var array
     */
    protected array $arguments = [];

    /**
     * @var string
     */
    protected string $description = "Create 'Login' and 'Forgot Password' structures";

    /**
     * @var array
     */
    protected array $options = ['--login', '--forgot', '--remove'];

    /**
     * @var string
     */
    private string $controller_dir = '';

    /**
     * @var string
     */
    private string $middleware_dir = '';

    /**
     * @var string
     */
    private string $route_dir = '';

    /**
     * @var string
     */
    private string $view_dir = '';

    /**
     * @var array
     */
    private array $components = [];

    /**
     * @param object $arguments
     * @param object $options
     *
     * @return mixed
     */
    #[\Override]
    public function handle(object $arguments, object $options): mixed
    {
        ServiceLoader::loadDatabaseDirectory();
        $this->getAuthFolders();

        $handle_files = new HandleFiles();
        $this->createUserAuth();

        $templates_folder = __DIR__ . DIRECTORY_SEPARATOR . 'Templates'. DIRECTORY_SEPARATOR;

        if (isset($options->login)) {
            $login_components = $handle_files
                ->folder($templates_folder . 'LoginComponents')
                ->files();

            if (isset($options->remove)) {
                $this->removeAuthComponent([
                    $this->middleware_dir . 'AuthMiddleware.php',
                    $this->controller_dir . 'LoginController.php',
                    $this->route_dir . 'auth-login-routers.php',
                    $this->view_dir . 'auth-dashboard.php',
                    $this->view_dir . 'auth-form.php'
                ]);

                return true;
            }

            $this->createLoginSkeleton($login_components);
            return true;
        }

        if (isset($options->forgot)) {
            $forgot_components = $handle_files
                ->folder($templates_folder . 'ForgotComponents')
                ->files();

            if (isset($options->remove)) {
                $this->removeAuthComponent([
                    $this->controller_dir . 'ForgotController.php',
                    $this->route_dir . 'forgot-routers.php',
                    $this->view_dir . 'forgot-form.php',
                    $this->view_dir . 'forgot-change-pass.php'
                ]);

                return true;
            }

            $this->createForgotSkeleton($forgot_components);
            return true;
        }

        return $this;
    }

    /**
     * @param array $components
     *
     * @return MakeAuth
     */
    private function createLoginSkeleton(array $components): MakeAuth
    {
        $view_dir = [
            $components[0], $components[1]
        ];

        $components = [
            'route_dir' => $components[2],
            'middleware_dir' => $components[3],
            'controller_dir' => $components[4]
        ];

        $this->generateAuthTemplate($components, $view_dir);
        ConsoleOutput::success('Login components created successfully!')->print()->break();

        return $this;
    }

    /**
     * @param array $components
     *
     * @return MakeAuth
     */
    private function createForgotSkeleton(array $components): MakeAuth
    {
        $view_dir = [
            $components[0], $components[1], $components[2]
        ];

        $components_router_controller = [
            'route_dir' => $components[3],
            'controller_dir' => $components[4]
        ];

        $this->generateAuthTemplate($components_router_controller, $view_dir);
        ConsoleOutput::success('Forgot components created successfully!')->print()->break();

        return $this;
    }

    /**
     * Generate header and footer components in Auth view
     *
     * @return MakeAuth
     */
    private function createHeaderAndFooter(): MakeAuth
    {
        $templates_folder = __DIR__ . DIRECTORY_SEPARATOR . 'Templates'. DIRECTORY_SEPARATOR;
        /* $header_template = __DIR__ . DIRECTORY_SEPARATOR . 'header.php';
        $footer_template = __DIR__ . DIRECTORY_SEPARATOR . 'footer.php'; */

        $this->createAuthComponents(
            $this->view_dir,
            $templates_folder . 'header.php',
            'header.php'
        );

        $this->createAuthComponents(
            $this->view_dir,
            $templates_folder . 'footer.php',
            'footer.php'
        );

        return $this;
    }

    /**
     * Generate Auth components
     *
     * @param array $components
     * @param array $view_dir
     *
     * @return MakeAuth
     */
    private function generateAuthTemplate(array $components, array $view_dir): MakeAuth
    {
        foreach ($components as $key => $component) {
            $class = new \ReflectionClass($this);
            $property = $class->getProperty($key)->getValue($this);

            $this->createAuthComponents(
                $property,
                $component,
                basename($component)
            );
        }

        foreach ($view_dir as $view) {
            $this->createAuthComponents(
                $this->view_dir,
                $view,
                basename($view)
            );
        }

        $this->createHeaderAndFooter();
        return $this;
    }

    /**
     * @return MakeAuth
     */
    public function createUserAuth(): MakeAuth
    {
        $users = AuthModel::createUserTable();

        if (empty($users)) {
            $db = new AuthModel();
            $db->username = 'solital@email.com';
            $db->password = pass_hash('solital');
            $db->save();

            ConsoleOutput::success('User created successfully!')->print()->break();
        } else {
            ConsoleOutput::success('User already exists!')->print()->break();
        }

        return $this;
    }

    /**
     * Create Auth file with pre-existing template
     *
     * @param string $auth_controller_dir
     * @param string $auth_template_dir
     * @param string $file_name
     *
     * @return bool
     */
    private function createAuthComponents(
        string $auth_controller_dir,
        string $auth_template_dir,
        string $file_name
    ): bool {
        $handle_files = new HandleFiles();

        if (!is_dir($auth_controller_dir)) {
            $handle_files->create($auth_controller_dir);
            $handle_files->getAndPutContents(
                $auth_template_dir, 
                $auth_controller_dir . $file_name
            );

            return true;
        }

        $file_exists = $handle_files->folder($auth_controller_dir)->fileExists($file_name);

        if ($file_exists == false) {
            $handle_files->getAndPutContents(
                $auth_template_dir, 
                $auth_controller_dir . $file_name
            );

            return true;
        }

        return false;
    }

    /**
     * @param array $components
     *
     * @return void
     */
    private function removeAuthComponent(array $components): void
    {
        $exists = [];

        foreach ($components as $file) {
            (!file_exists($file)) ? $exists[] = '' : $exists[] = $file;
        }

        if (empty($exists))
            ConsoleOutput::success('No component found')->print()->break()->exit();

        $input_output = new InputOutput();
        $input_output->color(ColorsEnum::LIGHT_GREEN);
        $input_output->confirmDialog(
            'Are you sure you want to delete this components? (this process cannot be undone)? ', 
            'Y', 
            'N', 
            false
        );
        $input_output->confirm(function () use ($components) {
            foreach ($components as $file) {
                if (is_file($file)) unlink($file);
            }

            ConsoleOutput::success('Components successfully removed!')->print()->break();
        });

        $input_output->refuse(function () {
            ConsoleOutput::line('Abort!')->print()->break()->exit();
        });
    }

    /**
     * @return void
     */
    private function getAuthFolders(): void
    {
        $this->controller_dir = app_get_component(
            'Controller/Auth/', 
            DebugCore::isCoreDebugEnabled()
        );
        
        $this->middleware_dir = app_get_root(
            'app/Middleware/', 
            DebugCore::isCoreDebugEnabled()
        );

        $this->route_dir = app_get_root(
            'routers/', 
            DebugCore::isCoreDebugEnabled()
        );

        $this->view_dir = app_get_root(
            'resources/view/auth/', 
            DebugCore::isCoreDebugEnabled()
        );
    }
}
