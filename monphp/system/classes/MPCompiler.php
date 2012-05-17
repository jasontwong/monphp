<?php

/**
 * Compiles a group of php files, which is just concatenating them into one
 * large file so multiple includes are not needed. These files are saved into
 * the DIR_SCR directory. It automatically checks to see if the files are new
 * and will recompile based on the hash provided, which should be saved with
 * the MPData class.
 *
 * @package MPCompiler
 */

class MPCompiler
{
    //{{{ properties
    /**
     * Prefix of the filename. This serves as the identifier for the group of
     * files you are trying to compile. This way the MPExtensions class can
     * compile files with the prefix 'ext' and not collide with the MPModule 
     * class which uses the prefix 'mod'.
     */
    private $prefix;
    /**
     * Previously compiled file outputs for the given prefix. Hashes only.
     */
    private $compiled = array();
    private $compiled_file;
    private $files = array();
    private $hash;
    private $check_compiled = FALSE;
    private $tried_compile = FALSE;
    private $is_compiled = FALSE;
    //}}}
    //{{{ public function __construct($prefix, $hash = NULL)
    public function __construct($prefix, $hash = NULL)
    {
        $this->prefix = $prefix;
        $this->hash = $hash;
    }
    //}}}
    //{{{ public function add($file)
    /**
     * Add a file to the list of files for compilation
     * @param string $file filename
     * @return void
     */
    public function add($file)
    {
        $this->files[] = $file;
    }
    //}}}
    //{{{ public function compile($filename, $files)
    /**
     * Takes all extension files and compiles it into one for the scratch 
     * directory. $hash is the hash of combined filenames and meta data such as
     * modified date to check if compiled file reflects all current extensions.
     * @return bool
     */
    public function compile()
    {
        if (!$this->tried_compile)
        {
            if ($this->can_compile() && $this->needs_compile())
            {
                $cfile = DIR_SCR.'/'.$this->prefix.'.'.$this->files_hash().'.php';
                $data = '';
                foreach ($this->files as $file)
                {
                    $data .= file_get_contents($file);
                }
                $result = file_put_contents($cfile, $data);
                $this->compiled_file = $cfile;
                $this->is_compiled = $result !== FALSE;
            }
            else
            {
                $this->is_compiled = FALSE;
            }
            $this->tried_compile = TRUE;
        }
        return $this->is_compiled;
    }
    //}}}
    //{{{ public function import($once = FALSE)
    /**
     * Includes the files. Useful if you compiled only class or function
     * declarations and will go into the global namespace. It will determine if
     * it needs to compile or not.
     * @param bool $once use include_once, which is safer but slower
     * @return void
     */
    public function import()
    {
        $files = $this->get_files();
        foreach ($files as $file)
        {
            if ($once)
            {
                include_once $file;
            }
            else
            {
                include $file;
            }
        }
    }
    //}}}
    //{{{ public function get_files()
    /**
     * Returns files used for $this->import(). Can be called if namespace is
     * important.
     * @return array
     */
    public function get_files()
    {
        return $this->compile() ? array($this->compiled_file) : $this->files;
    }
    //}}}
    //{{{ private function can_compile($filename)
    /**
     * Checks if the scratch file is writeable or the directory only if the
     * file does not exist.
     * @param string $filename
     * @return boolean
     */
    private function can_compile($filename)
    {
        $file = DIR_COM.'/'.$filename;
        return is_writable($file)
            ? TRUE
            : !is_file($file) && is_writable(DIR_COM);
    }
    //}}}
    //{{{ private function compiled()
    /**
     * Retrieves compiled files in the DIR_COM directory based on $this->prefix
     * in the filename. Returns an array of the hashes only.
     * @return array
     */
    private function compiled()
    {
        if (!$this->check_compiled)
        {
            $files = scandir(DIR_COM);
            $pre = $this->prefix.'.';
            $len = strlen($pre);
            foreach ($files as $file)
            {
                $cf = DIR_COM.'/'.$file;
                if (is_file($cf) && 
                    substr($file, 0, $len) === $pre && 
                    substr($file, $len + 40) === '.php')
                    {
                        $this->compiled[] = substr($file, $len, 40);
                    }
            }
            $this->check_compiled = TRUE;
        }
        return $this->compiled;
    }
    //}}}
    //{{{ private function needs_compile()
    private function needs_compile()
    {
        return !in_array($this->files_hash(), $this->compiled());
    }
    //}}}
    //{{{ private function files_hash()
    /**
     * Creates a unique hash of files used mainly to determine if a file compile is
     * required. The files must be full filepaths.
     * @return string sha1 hash
     */
    private function files_hash()
    {
        if (is_null($this->hash))
        {
            $data = '';
            foreach ($this->files as $file)
            {
                $stat = stat($file);
                if ($stat === FALSE)
                {
                    // TODO should throw exception instead
                    return FALSE;
                }
                $mtime = $stat['mtime']
                $size = $stat['size']
                $data .= $file.$mtime.$ctime.$size;
            }
            $this->hash = sha1($data);
        }
        return $this->hash;
    }
    //}}}
}
