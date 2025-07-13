<?php
class App {
    protected $controller = 'AuthController';
    protected $method = 'login';
    protected $params = [];
    
    public function __construct() {
        $url = $this->parseUrl();
        
        // Jika ada URL parameter
        if ($url && !empty($url[0])) {
            $controllerName = ucfirst($url[0]) . 'Controller';
            
            if (file_exists('../app/controllers/' . $controllerName . '.php')) {
                $this->controller = $controllerName;
                unset($url[0]);
            }
        }
        
        require_once '../app/controllers/' . $this->controller . '.php';
        $this->controller = new $this->controller;
        
        if (isset($url[1])) {
            if (method_exists($this->controller, $url[1])) {
                $this->method = $url[1];
                unset($url[1]);
            }
        }
        
        $this->params = $url ? array_values($url) : [];
        
        call_user_func_array([$this->controller, $this->method], $this->params);
    }
    
    public function parseUrl() {
        if (isset($_GET['url'])) {
            return explode('/', filter_var(rtrim($_GET['url'], '/'), FILTER_SANITIZE_URL));
        }
        return ['Auth', 'login']; // Default ke Auth/login
    }
}
?>