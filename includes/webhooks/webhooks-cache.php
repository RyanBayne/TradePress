<?php
/**
* TradePress Webhooks Filing Cache - Part of processing incoming notifications...
*
* @author Ryan Bayne
* @category settings
* @package TradePress/Webhooks
* @version 1.0
*/

// Prohibit direct script loading
defined( 'ABSPATH' ) || die( 'Direct script access is not allowed!' ); 

if ( ! class_exists ( 'TradePress_Webhooks_Caching' ) ) :

class TradePress_Webhooks_Caching {
    /**
     * Location string
     *
     * @var string
     */
    protected $location;

    /**
     * Filename
     *
     * @var string
     */
    protected $filename;

    /**
     * File extension
     *
     * @var string
     */
    protected $extension;

    /**
     * File path
     *
     * @var string
     */
    protected $name;

    /**
     * Create a new cache object
     *
     * @param string $location 
     * @param string $name Unique ID for the cache
      * @version 1.0.0
     */
    public function __construct( $location, $name, $extension = 'txt' )
    {
        $this->location  = untrailingslashit( $location );
        $this->filename  = sanitize_file_name( $name );
        $this->extension = sanitize_key( $extension );
        $this->name      = $this->location . '/' . $this->filename . '.' . $this->extension;
    }

    /**
     * Save data to a file...
     *
     * @return bool
      * @version 1.0.0
      *
      * @param mixed $data
     */
    public function save( $data )
    {
        if (file_exists($this->name) && is_writable($this->name) || file_exists($this->location) && is_writable($this->location))
        {
            if ($data instanceof SimplePie)
            {
                $data = $data->data;
            }

            $data = serialize($data);
            return (bool) file_put_contents($this->name, $data, LOCK_EX);
        }
        return false;
    }

    /**
     * Retrieve the data saved to the cache file...
     *
     * @return array Data or boolean !file_exists() || !is_readable()
      * @version 1.0.0
     */
    public function load()
    {
        if (file_exists($this->name) && is_readable($this->name))
        {
            $contents = file_get_contents($this->name);

            if ( false === $contents ) {
                return false;
            }

            return unserialize( $contents, array( 'allowed_classes' => false ) );
        }
        return false;
    }

    /**
     * Retrieve the last modified time for a file...
     *
     * @return int Timestamp
      * @version 1.0.0
     */
    public function mtime()
    {
        return @filemtime($this->name);
    }

    /**
     * Set the last modified time to the current time...
     *                                              
     * @return bool
      * @version 1.0.0
     */
    public function touch()
    {
        return @touch($this->name);
    }

    /**
     * Remove the cache...
     *
     * @return bool
      * @version 1.0.0
     */
    public function unlink()
    {
        if (file_exists($this->name))
        {
            return unlink($this->name);
        }
        return false;
    }
}

endif;
