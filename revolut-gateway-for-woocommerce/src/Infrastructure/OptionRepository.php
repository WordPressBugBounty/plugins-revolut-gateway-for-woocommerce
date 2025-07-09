<?php

namespace Revolut\Wordpress\Infrastructure;

use Revolut\Plugin\Services\Repositories\OptionRepositoryInterface;

class OptionRepository implements OptionRepositoryInterface
{
    private $wpdb;
    private $table;

    public function __construct()
    {
        global $wpdb;
        $this->wpdb     = $wpdb;
        $this->wpdb->suppress_errors();
        $this->table    = $wpdb->options;
        $this->wpdb->suppress_errors();
    }

    private function maybeEncode($value)
    {
        return maybe_serialize($value);
    }

    private function maybeDecode($value)
    {
        if (is_string($value) && $this->isJson($value)) {
            $decoded = json_decode($value, true);
            return (json_last_error() === JSON_ERROR_NONE) ? $decoded : $value;
        }
        
        return maybe_unserialize($value);
    }

    private function isJson($string)
    {
        if (!is_string($string)) return false;
        json_decode($string);
        return (json_last_error() === JSON_ERROR_NONE);
    }

    public function add($name, $value, $autoload = 'yes')
    {
        $value = $this->maybeEncode($value);

        $res = $this->wpdb->query(
            $this->wpdb->prepare("INSERT INTO {$this->table} (option_name, option_value, autoload) 
                                 VALUES (%s, %s, %s)", $name, $value, $autoload)
        );

        // RLog::info($this->wpdb->last_query);
        return $res;
    }

    public function addOrUpdate($name, $value, $autoload = 'yes')
    {
        $value = $this->maybeEncode($value);

        $res = $this->wpdb->query(
            $this->wpdb->prepare(
                "INSERT INTO {$this->table} (option_name, option_value, autoload)
                 VALUES (%s, %s, %s)
                 ON DUPLICATE KEY UPDATE option_value = VALUES(option_value), autoload = VALUES(autoload)",
                $name,
                $value,
                $autoload
            )
        );

        // RLog::info($this->wpdb->last_query);
        return $res;
    }

    public function get($name)
    {
        $value = $this->wpdb->get_var(
            $this->wpdb->prepare(
                "SELECT option_value FROM {$this->table} WHERE option_name = %s",
                $name
            )
        );
        return $this->maybeDecode($value);
    }

    public function update($name, $value)
    {
        $value = $this->maybeEncode($value);
   
        $res = $this->wpdb->update(
            $this->table,
            [ 'option_value' => $value ],
            [ 'option_name'  => $name ],
            [ '%s' ],
            [ '%s' ]
        );


        // RLog::info($this->wpdb->last_query);
        return $res;
    }

    public function delete($name)
    {
        return $this->wpdb->delete(
            $this->table,
            [ 'option_name' => $name ],
            [ '%s' ]
        );
    }

    public function exists($name)
    {
        $count = $this->wpdb->get_var(
            $this->wpdb->prepare(
                "SELECT COUNT(*) FROM {$this->table} WHERE option_name = %s",
                $name
            )
        );
        return $count > 0;
    }
}
