<?php
/**
 * Library Name: Cloud Bill Master PHP Session Handler
 * Author: Showket Ahmed
 * Email: riyadhtayf@gmail.com 
 */

namespace Laika\Session\Handler;

use Laika\Session\Interface\SessionDriverInterface;

class FileSessionHandler implements SessionDriverInterface
{
    // Session Save Path
    protected string $path;
    
    // Session File Prefix
    protected string $prefix;

    public function __construct(?array $config = null)
    {
        $this->path = $config['path'] ?? session_save_path();
        $this->path = rtrim($this->path, '/\\');
        if (!is_dir($this->path)) {
            mkdir($this->path, 0777, true);
        }
        $this->prefix = strtoupper($config['prefix'] ?? 'CBMASTER');
    }

    // Setup Handler
    public function setup(): void {}

    // Session Open
    public function open($savePath, $sessionName): bool { return true; }

    // Session Close
    public function close(): bool { return true; }

    // Session Read
    public function read($id): string
    {
        $file = "{$this->path}/{$this->prefix}_{$id}";
        return file_exists($file) ? file_get_contents($file) : '';
    }

    // Session Write
    public function write($id, $data): bool
    {
        return file_put_contents("{$this->path}/{$this->prefix}_{$id}", $data) !== false;
    }

    // Session Destroy
    public function destroy($id): bool
    {
        $file = "{$this->path}/{$this->prefix}_{$id}";
        return file_exists($file) ? unlink($file) : true;
    }

    // Session Garbase Collection
    public function gc($maxlifetime): int|false
    {
        $count = 0;
        $files = glob("{$this->path}/{$this->prefix}_*");
        foreach($files as $file){
            if ((filemtime($file) + $maxlifetime) < time()) {
                if (unlink($file)) {
                    $count++;
                }
            }
        }
        return $count;
    }
}