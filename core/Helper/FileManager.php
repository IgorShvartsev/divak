<?php
namespace Helper;

/**
* File Manager Class
*
*/
class FileManager
{
    /** @var string absolute path to your custom root folder */
    public $root;
    /** @var array css classes for open and close folder stay */
    public $defaultFolderClass = ['open' => 'copen', 'close' => 'cclose'];
    /** @var string */
    public $defaultFileClass = 'cfile';
    /** @var array list of css classes associated with given files */
    public $classCss = [
        'txt' => 'ctext',
        'rtf' => 'ctext',
        'doc' => 'cdoc',
        'xls' => 'cxls',
        'pdf' => 'cpdf',
        'jpg' => 'cjpg',
        'png' => 'cpng',
        'gif' => 'cgif',
        'avi' => 'cavi',
        'wmf' => 'cwmf',
        'mov' => 'cmov',
        'flv' => 'cflv',
        'mp3' => 'cmp3',
        'wav' => 'cwav',
    ];

    /**
    * Ciunstructor
    *
    * @param string $rootPath path to your custom root folder
    */
    public function __construct($rootPath)
    {
        $this->root = $rootPath;
    }

    /**
    * Get folder content (file/directory list)
    *
    * @param string $path
    * @return array
    */
    public function getFolderContent($path = '')
    {
        $list = [];
        $absPath = $this->root . '/' . $path;
        if (is_dir($absPath)) {
            $h = opendir($absPath);
            if ($h) {
                $dirs = [];
                $files = [];
                while (false !== ($entry = readdir($h))) {
                    if ($entry === '.' || $entry === '..') {
                        continue;
                    }
                    $entryPath = $absPath . '/' . $entry;
                    if (is_dir($entryPath)) {
                        $dirs[] = $entry;
                    } else {
                        $files[] = $entry;
                    }
                }
                natsort($dirs);
                natsort($files);
                if (count($dirs) > 0 || count($files) > 0) {
                    $list = [
                        'dir' => $dirs,
                        'file' => $files
                    ];
                }
            }
        }
    
        return $list;
    }

    /**
     * Makes files/folders tree array
     * Result array consist of folders arrays. 
     * Every of them has records of entries with properties 'name','type','icon'.
     *               'name'  - name of entry
     *               'type'  - 'dir' or 'file'
     *               'icon'  - corresponding graphic file.
     *
     * @param string $path path relative to the root 
     * ( f.e. arhive/data , which inside looks like $root.'/'.archive/data  )
     * @param array  $tree reference variable  for output result tree data
     * @param array  $errors reference variable for output possible errors
     * @param string $last  used for recursive only
     */
    public function getTree($path, &$tree, &$errors, $last = '')
    {
        if (!is_dir($this->root)) {
            $errors[] = 'Root folder ' . $this->root . 'does not exist';

            return;
        }
        $absPath = $this->root . '/' . $path;
        if (is_dir($absPath)) {
            $dir = dir($absPath);
            $temp = [];
            while (false !== ($entry = $dir->read())) {
                if ($entry === '.' || $entry === '..') {
                    continue;
                }
                $entryPath = $absPath . '/' . $entry;
                if (is_dir($entryPath)) {
                    $type = 'dir';
                    $class = $entry === $last 
                        ? $this->defaultFolderClass['open'] 
                        : $this->defaultFolderClass['close'];
                } else {
                    $ext = strtolower(pathinfo($entry, PATHINFO_EXTENSION));
                    $class = isset($this->classCss[$ext]) 
                        ? $this->classCss[$ext] 
                        : $this->defaultFileClass;
                    $type = 'file';
                }
                $temp[] = [
                    'name' => (!empty($path) ? ($path.'/') : '').$entry,
                    'type' => $type,
                    'class' => $class,
               ];
            }
            $dir->close();
            $tree[] = $temp;
        } elseif (!is_file($absPath)) {
            $errors[] = $path . 'does not exist';
        }
        $pathInfo = explode('/', $path);
        if (!empty($path)) {
            $last = $pathInfo[count($pathInfo) - 1];
            unset($pathInfo[count($pathInfo) - 1]);
            $this->getTree(implode('/', $pathInfo), $tree, $errors, $last);
        } else {
            $tree = array_reverse($tree);
        }
    }

    /**
     * Scan directory
     * 
     * @param string $directory
     * 
     * @return array file pathes 
     */ 
    public function scanDirectory($dir = '') 
    {
        if (empty($dir)) {
            $dir = $this->root;
        }

        $result = [];
        $entities = scandir($dir);
        
        foreach ($entities as $entity) {
            if ($entity !== '.' && $entity !== '..') {
                if (is_dir($dir . '/' . $entity)) {
                    $result = array_merge($result, $this->scanDirectory($dir . '/' . $entity));
                } else {
                    $result[] = $dir . '/' . $entity;
                }
            }
        }

        return $result;
    }

    /**
    * Create folder
    *
    * @param string $path path relative to the root 
    * @param string $newFolder
    * @return boolean
    */
    public function createFolder($path, $newfolder)
    {
        $absPath = $this->root . '/' . $path . '/' . $newfolder;

        return @mkdir($absPath, 0777);
    }

    /**
    * Delete folder
    *
    * @param string $path path relative to the root 
    * @return boolean
    */
    public function deleteFolder($path)
    {
        $absPath = $this->root . '/' . $path;
        $result = false;
        if ($handle = opendir($absPath)) {
            $array = [];
            $entities = scandir($absPath);
            foreach ($entities as $entity) {
                if ($entity !== '.' && $entity !== '..') {
                    if (is_dir($absPath . '/' . $entity)) {
                        // Empty directory? Remove it
                        if (!@rmdir($absPath . '/' . $entity)) { 
                            // Not empty? Delete the files inside it
                            $this->deleteFolder($path . '/' . $entity); 
                        }
                    } else {
                        @unlink($absPath . '/' . $entity);
                    }
                }
            }
            closedir($handle);
            @rmdir($absPath);
            $result = true;
        }

        return $result;
    }

    /**
    * Delete file
    *
    * @param string $path path relative to the root 
    */
    public function deleteFile($path)
    {
        $absPath = $this->root . '/' . $path;
        @unlink($absPath);
    }

    /**
    * Rename folder/file
    *
    * @param string $path path relative to the root 
    * @param string $newname
    * @return boolean
    */
    public function rename($path, $newname)
    {
        $newname = str_replace(['/', '\\'], ['', ''], $newname);
        $absPath = $this->root . '/' . $path;
        $pos = strrpos($absPath, '/');
        $targetPath = substr($absPath, 0, $pos) . '/' . $newname;

        return rename($absPath, $targetPath);
    }

    /**
     * Copy files
     * 
     * @param string $fromDir abs path
     * @param string $toDir abs path
     * @param string $error
     * 
     * @return boolean
     */ 
    public function copyFiles($fromDir, $toDir, &$error = '')
    {
        $result = false;

        if (empty($fromDir) || empty($toDir)) {
            $error = 'Folder fromDir or toDir is not defined.';
            return false;
        }

        if (!is_dir($fromDir)) {
            $error = 'Folder doesnt exist ' . $fromDir;
            return false;        
        }

        if (!is_dir($toDir)) { 
            if (!mkdir($toDir)) {
                $error = 'Unable to create ' . $toDir;
                return false;
            }    
        }

        if ($handle = opendir($fromDir)) {
            $array = [];
            $entities = scandir($fromDir);

            foreach ($entities as $entity) {
                if ($entity !== '.' && $entity !== '..') {
                    if (is_dir($fromDir . '/' . $entity)) {
                        $this->copyFiles(
                            $fromDir . '/' . $entity, 
                            $toDir . '/' . $entity,
                            $error
                        );
                    } else {
                        $isSuccess = copy(
                            $fromDir . '/' . $entity, 
                            $toDir . '/' . $entity
                        );

                        if (!$isSuccess) {
                            $error = 'Unable copy  ' . $fromDir . '/' . $entity . ' to ' . $toDir . '/' . $entity;
                        }
                    }
                }
            }
            
            closedir($handle);
            $result = true;
        } else {
            $error = 'Unable to open ' . $fromDir;
        }

        return $result;
    }

    /**
    * Upload file
    *
    * @param string $path path relative to the root 
    * @return boolean
    */
    public function uploadFile($path)
    {
        $absPath = $this->root . '/' . $path;
        $result = false;
        if (!empty($_FILES) && !empty($_FILES['file']['name'])) {
            $newfile = preg_replace('/[^a-zA-Z0-9\.-_ ]/i', '', $_FILES['file']['name']);
            $absPath .= '/' . $newfile;
            move_uploaded_file($_FILES['file']['tmp_name'], $absPath);
            $result = true;
        }

        return $result;
    }

    /**
     * Download ZIP folder
     * 
     * @param string $path path relative to the root 
     * @param string $tempFolderPath path where to save zip file
     * 
     * @return boolean
     */ 
    public function downloadZipFolder($path, $tempFolderPath = '/tmp')
    {
        $dirName = basename($path);
        $absPath = $this->root . '/' . $path;
        $result = false;
        $zipFile = $dirName . '-' . date('Ymdhis') . '.zip';
        $tempFile = rtrim($tempFolderPath, '/') . '/' . $zipFile;
        $isAddedToZip = false;

        $zip = new Zip();

        if ($zip->open($tempFile, \ZIPARCHIVE::CREATE ) === true) {
            $isAddedToZip = $zip->addDir($absPath);
            $zip->close();
        }

        if ($isAddedToZip) {
            $result  = true;
            $result = $this->downloadFile($tempFile, $zipFile);
            unlink($tempFile);
            exit();
        }

        return $result;
    }

    /**
     * Download file without loading it into memory
     *
     * @param string $file file path or url
     * @param string $newFileName
     */
    public function downloadFile($file, $newFileName = '')
    {
        $handle = @fopen($file, 'rb');

        if (!$handle) {
            return false;
        }
        
        header_remove();

        if (preg_match('/^http/', $file)) {
            $headers = get_headers($file, 1);
            $headers = array_change_key_case($headers);
            if (!empty($headers['content-length'])) {
                header('Content-Length: ' . $headers['content-length']);
            }
        } else {
            header('Content-Length: ' . filesize($file)); 
        }

        if (empty($newFileName)) {
            $newFileName = basename($file);
        }
        
        ob_end_clean();

        header('Content-Type: ' . mime_content_type($file));
        header('Content-Disposition: attachment; filename=' .  $newFileName);
        header('Cache-Control: no-cache');
        header('Pragma: public');
        header('Expires: 0');
        header('Access-Control-Allow-Origin: *');
        
        if ($handle) {
            fpassthru($handle);
            fclose($handle);
        }

        return true;
    }

    /**
     * Copy file from remote server 
     * 
     * @param string $url
     * @param string $toFile relative path to $root
     * @param string $error retirned error message
     * 
     * @return bool | null 
     */ 
    public function copyRemoteFile($url, $toFile, &$error = '')
    {
        $toFile = $this->root . '/' . ltrim($toFile, '/');

        if (($h = fopen($url, 'r')) === false) {
            $error = 'Can\'t open remote resource ' . $url;
            return;
        }
        fclose($h);

        if (($h = fopen($toFile, 'wb')) === false) {
            $error = 'Can\'t open file ' . $toFile;
            return false;
        }
        fclose($h);

        $copyResult = @copy($url, $toFile);

        if (!$copyResult) {
            $errors= error_get_last();
            $error = 'COPY ERROR: ' . $errors['type']
                . ' MSG: ' . $errors['message'];

            return false;
        }

        return true;
    }

    /**
    * Get file perms
    *
    * @param string $file
    * @return array
    */
    public function getFilePerms($file)
    {   
        $result = [];
        $rwx = ['---', '--x', '-w-', '-wx', 'r--', 'r-x', 'rw-', 'rwx'];
        $file = $this->root . '/' . $file;

        if (
            (is_dir($file) || file_exists($file))
            && (function_exists('posix_getpwuid') && function_exists('posix_getgrgid'))
        ) {
            $perms = substr(sprintf('%o', fileperms($file)), -4);
            $result[] = $perms;
            $type = is_dir($file) ? 'd' : '-';
            $result[] = $type . $rwx[$perms[1]] . $rwx[$perms[2]] .$rwx[$perms[3]];
            $stat = stat($file);
            $pw = posix_getpwuid($stat[4]);
            $gr = posix_getgrgid($stat[5]);
            $result[] = $pw['name'] . ':' . $gr['name'];
        }

        if (empty($result)) {
            $result = ['', '', ''];
        }

        return $result;
    }

    public function getDateModified($file)
    {
        $file = $this->root . '/' . $file;

        return date ("Y-m-d H:i:s", filemtime($file));
    }
}
