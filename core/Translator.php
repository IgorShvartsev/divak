<?php

/**
 * Translator class
 *
 * @author  Igor Shvartsev (igor.shvartsev@gmail.com)
 * @package Divak
 * @version 1.1
 */
class Translator
{
    /**
     * @var string $lang
     */
    protected $lang = '';

    /**
     * @var array $data
     */
    protected $data = [];

    /**
     * @var string $file
     */
    protected $file;

    /**
     * constructor.
     *
     * @param string $path folder where language files are located
     * @param string $lang locale
     * @param string $fileext if language file has extension (without dot at the beginning)
     *
     * @return Translator
     * 
     * @throws Exception
     */
    public function __construct($path, $lang, $fileext = 'php')
    {
        $localization_name = 'localization_' . $lang;
        global ${$localization_name};

        $this->lang = $lang;
        $this->file = rtrim($path, '/') . '/' . $lang . (!empty($fileext) ? ('.' . $fileext) : '');

        if (!file_exists($this->file)) {
            if ($fp = fopen($this->file, 'w+b')) {
                fwrite($fp, "<?php\n\t" . '$localization_' . $lang . " = [];\n");
                fclose($fp);
                chmod($this->file, 0777);
            } else {
                throw new \Exception("Could not open file $file");
            }
        }

        include_once $this->file;
        $this->data = ${$localization_name};
    }

    /**
     * get translated text.
     *
     * @param string $val
     *
     * @return string
     */
    public function _($val)
    {
        if (isset($this->data[md5($val)])) {
            return stripslashes($this->data[md5($val)]);
        }

        $this->data[md5($val)] = $val;
        
        if ($fp = fopen($this->file, 'a+b')) {
            fwrite(
                $fp, 
                "\t" . '$localization_' . $this->lang 
                . "['" . md5($val) . "'] = '" . str_replace("'", "\'", $val) . "';\n"
            );
        }

        return stripslashes($val);
    }
}
