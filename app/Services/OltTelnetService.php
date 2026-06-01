<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

class OltTelnetService
{
    private $connection;
    private $host;
    private $port;
    private $username;
    private $password;
    private $timeout = 10;

    public function __construct()
    {
        $this->host = Config::get('olt.host');
        $this->port = Config::get('olt.port');
        $this->username = Config::get('olt.username');
        $this->password = Config::get('olt.password');
    }

    public function connect()
    {
        try {
            $this->connection = fsockopen($this->host, $this->port, $errno, $errstr, $this->timeout);
            if (!$this->connection) {
                throw new Exception("Could not connect to OLT: $errstr ($errno)");
            }

            stream_set_timeout($this->connection, $this->timeout);

            // 1. Handle Handshake / Username prompt
            $output = $this->readUntil(['Username:', 'login:', 'User Name:']);
            $this->write($this->username);

            // 2. Handle Password prompt
            $output = $this->readUntil(['Password:', 'password:']);
            $this->write($this->password);

            // 3. Wait for Command Prompt
            $output = $this->readUntil(['>', '#']);

            return true;
        } catch (Exception $e) {
            Log::error("OLT Connection Error: " . $e->getMessage());
            $this->disconnect();
            throw $e;
        }
    }

    public function execute(string $command)
    {
        if (!$this->connection) {
            $this->connect();
        }

        $this->write($command);
        $output = $this->readUntil(['>', '#']);
        
        // Remove echo of the command from output
        return $this->cleanOutput($output, $command);
    }

    public function disconnect()
    {
        if ($this->connection) {
            @fclose($this->connection);
            $this->connection = null;
        }
    }

    private function write(string $data)
    {
        fputs($this->connection, $data . "\r\n");
    }

    private function readUntil(array $prompts)
    {
        $buffer = '';
        $start = time();

        while (!feof($this->connection)) {
            if (time() - $start > $this->timeout) {
                throw new Exception("Telnet Read Timeout for prompts: " . implode(', ', $prompts));
            }

            $char = fgetc($this->connection);
            if ($char === false) continue;
            
            $buffer .= $char;

            foreach ($prompts as $prompt) {
                if (str_ends_with(trim($buffer), $prompt)) {
                    return $buffer;
                }
            }

            // Handle --More-- pagination if needed
            if (str_ends_with($buffer, "--More--")) {
                $this->write(" "); // Send space to get more
                $buffer = str_replace("--More--", "", $buffer);
            }
        }

        return $buffer;
    }

    private function cleanOutput($output, $command)
    {
        // Split lines
        $lines = explode("\n", str_replace("\r", "", $output));
        
        // Remove first line if it's the echoed command
        if (isset($lines[0]) && trim($lines[0]) === trim($command)) {
            array_shift($lines);
        }

        // Remove last line (the prompt)
        if (count($lines) > 0) {
            array_pop($lines);
        }

        return trim(implode("\n", $lines));
    }

    public function __destruct()
    {
        $this->disconnect();
    }
}
