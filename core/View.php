<?php

/**
* View class
*
* @author Igor Shvartsev (igor.shvartsev@gmail.com)
* @package Divak
* @version 1.0
*/
class View
{
    /**
     * Layout name.
     *
     * @var string
     */
    protected $layout;

    /**
     * Page name.
     *
     * @var string
     */
    protected $page;

    /**
     * Controller name.
     *
     * @var string
     */
    protected $controller;

    /**
     * View folder path.
     *
     * @var string
     */
    protected $viewPath;

    /**
     * Use controler name in view path or not.
     *
     * @var bool
     */
    protected $noController;

    /**
     * Language.
     *
     * @var string
     */
    protected $lang;

    /**
     * Base Url.
     *
     * @var string
     */
    protected $baseUrl = '';

    /**
     * Template data to be view.
     *
     * @var array
     */
    protected $templateData = [];

    /**
     * Constructor.
     *
     * @param string $controller
     * @param string $layout
     * @param string $page
     * @param mixed $lang
     */
    public function __construct($controller = 'index', $layout = null, $page = null, $lang = '')
    {
        $this->layout = $layout;
        $this->page = $page;
        $this->controller = $controller;
        $this->viewPath = APP_PATH . '/views/';
        $this->lang = $lang;
        $this->baseUrl = \Config::get('app.base_url');
    }

    /**
     * Magic set.
     *
     * @param string $name
     * @param mixed  $var
     */
    public function __set($name, $var)
    {
        $this->templateData[$name] = $var;
    }

    /**
     * Magic get.
     *
     * @param string $name
     */
    public function __get($name)
    {
        $val = null;
        if (isset($this->templateData[$name])) {
            $val = $this->templateData[$name];
        }
        if (null === $val) {
            $val = isset($this->$name) ? $this->$name : '';
        }

        return $val;
    }

    /**
     *  Quick render page.
     *
     *  @param string $templatePath - relative path to the file (without extension .phtml)
     *  @param array
     *  @param string
     * @param mixed $data
     * @param mixed $lang
     */
    public static function quickRender($templatePath, $data, $lang = 'en')
    {
        $view = new \View(null, null, $templatePath, $lang);
        $view->setData($data);
        echo $view->render();
    }

    /**
     * Set variable into view object.
     *
     * @param string $name
     * @param mixed  $var
     */
    public function set($name, $var)
    {
        if (property_exists($this, $name)) {
            $property = new \ReflectionProperty($this, $name);
            if ($property->isProtected() || $property->isPrivate()) {
                return;
            }
        }
        $this->__set($name, $var);
    }

    /**
     * Set array of data to template.
     *
     * @param array $data
     */
    public function setData($data)
    {
        $this->templateData = array_merge($this->templateData, $data);
    }

    /**
     * Set layout name.
     *
     * @param string $layout
     */
    public function setLayout($layout)
    {
        $this->layout = $layout;
    }

    /**
     * Set base url (relative).
     *
     * @param mixed $baseUrl
     *
     * @return string
     */
    public function setBaseUrl($baseUrl)
    {
        $this->baseUrl = $baseUrl;
    }

    /**
     * Get title for the site.
     */
    public function title()
    {
        $title = '';
        $title = !empty(\Config::get('app.title')) ? \Config::get('app.title') : '';
        if (!empty($this->templateData['title'])) {
            $title = $this->templateData['title'] . ' - ' . $title;
        }

        return $title;
    }

    /**
     * Calls another template.
     *
     * @param string $template
     * @param mixed  $params
     */
    public function partial($template, $params = [])
    {
        if (file_exists($this->viewPath.$template.'.phtml')) {
            extract($params);
            include $this->viewPath.$template.'.phtml';
            echo "\n";
        } else {
            throw new \Exception('Template '.$template.' not found.');
        }
    }

    /**
     *  url helper
     *  Helps to make relative safe url.
     *
     *  @param string $url
     *  @param bool $echo
     */
    public function url($url = '', $echo = true)
    {
        if (
            false !== strpos($url, 'http://') 
            || false !== strpos($url, 'https://') 
            || false !== strpos($url, 'mailto:')
        ) {
        } elseif (empty($url)) {
            $url = $this->baseUrl;
        } else {
            $url = $this->baseUrl . '/' . ltrim($url, '/');
        }

        if ($echo) {
            echo $url;
        } else {
            return $url;
        }
    }

    /**
     * language url.
     *
     * @param string $lang
     */
    public function langUrl($lang = 'en')
    {
        $lang = strtolower($lang);
        $lang = strtolower(\Config::get('default_lang')) === $lang ? '' : $lang;
        $url = str_replace($this->baseUrl, '', $_SERVER['REQUEST_URI']);
        $url = preg_replace('/(\/[^\/]{2})(($)|(\/.*))/', '$4', $url);
        if (2 === strlen($lang)) {
            $url = $this->baseUrl . '/' . $lang . $url;
        } else {
            $url = $this->baseUrl . '/' . ltrim($url, '/');
        }
        echo $url;
    }

    /**
     * Translator helper
     * Instance of Translator class should be created as `translator` property.
     *
     * @param string $text target text
     * @param bool   $echo to echo or to return final result
     * @param string $replaceBy string which can replace %s in the $text
     */
    public function _($text, $echo = true, $replaceBy = '')
    {
        $translator = \App::isValid(\Translator::class) ? \App::make(\Translator::class) : null;

        if ($translator) {
            $out = !empty($replaceBy) ? str_replace('%s', $replaceBy, $translator->_($text)) : $translator->_($text);
            if ($echo) {
                echo $out;
            } else {
                return $out;
            }
        } else {
            $out = !empty($replaceBy) ? str_replace('%s', $replaceBy, $text) : $text;
            if ($echo) {
                echo $out;
            } else {
                return $out;
            }
        }
    }

    /**
     * Get content.
     */
    public function getContent()
    {
        extract($this->templateData);
        if (!empty($this->page)) {
            $file = $this->viewPath . ($this->noController || empty($this->controller) 
                    ? '' 
                    : (str_replace('_', '/', $this->controller) . '/'))
                . $this->page . '.phtml';
            if (file_exists($file)) {
                include_once $file;
                echo "\n";
            } else {
                throw new \Exception(
                    'Page ' . $this->page . '.phtml does not exists in views '
                    . str_replace('_', '/', $this->controller) . '  folder'
                );
            }
        }
    }

    /**
     * Render View.
     *
     * @param string $page
     * @param bool   $noController
     */
    public function render($page = null, $noController = false)
    {
        if ($page) {
            $this->page = $page;
        }
        $this->noController = $noController;

        return $this->output();
    }

    /**
     * Outputs rendered html.
     *
     * @param bool $return
     */
    protected function output($return = false)
    {
        extract($this->templateData);
        if ($this->layout) {
            if (file_exists($this->viewPath . $this->layout . '.phtml')) {
                ob_start();
                include_once $this->viewPath . $this->layout . '.phtml';
                $out = ob_get_clean();

                return $out;
            }
            throw new \Exception(
                'Layout ' . $this->layout . '.phtml does not exist in views  root folder'
            );
        }
        ob_start();
        $this->getContent();
        $out = ob_get_clean();

        return $out;
    }
}
