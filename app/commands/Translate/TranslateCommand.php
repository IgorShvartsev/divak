<?php
namespace Translate;

use Contract\CommandInterface;
use Helper\FileManager;

class TranslateCommand implements CommandInterface
{
    /**
     * @var array
     */
    protected $targetFolders = [
        APP_PATH . '/controllers',
        APP_PATH . '/views',
    ];

    public function execute()
    {
        foreach ($this->targetFolders as $targetFolder) {
            foreach ($this->scanDir($targetFolder) as $file) {
                foreach(['_t', '->_'] as $fname) {
                    foreach ($this->readFile($file, $fname) as $text)  {
                        $this->translate($text);
                        $shortText = substr($text, 0, 40);
                        output($shortText . (strlen($text) > 40 ? ' ...' : '') . ' - ok');
                    }
                }
            }
        }
    }

    /**
     * Scan dir for files
     * 
     * @param string $dir
     * 
     * @return iterable
     */
    protected function scanDir($dir)
    {
        $fileManager = new FileManager($dir);
        $fileList = $fileManager->scanDirectory();

        foreach ($fileList as $file) {
            yield $file;
        }
    }

    /**
     * Read file
     * 
     * @param string $file
     * @param string $fname
     * 
     * @return iterable
     */
    protected function readFile($file, $fname = '_t')
    {
        $result = [];

        $content = file_get_contents($file);
        preg_match_all('#' . $fname . '\((\'|\")(.+)?(\'|\")\)?#', $content, $result);

        foreach ($result[2] as $text) {
            yield str_replace(["\'"], ["'"], $text);
        }
    }

    /**
     * Translate text
     * 
     * @param string $text
     */
    protected function translate($text)
    {
        $langs = \Config::get('app.all_langs');

        foreach ($langs as $lang) {
            $translator = new \Translator(
                APP_PATH . '/languages',
                strtoupper($lang)
            );
            $translator->_($text);
        }
    }
}
