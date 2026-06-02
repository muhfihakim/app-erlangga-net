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

        // 1. Clear any leftover data in the buffer
        stream_set_blocking($this->connection, false);
        while (fgetc($this->connection) !== false);
        stream_set_blocking($this->connection, true);

        // 2. Send command
        $this->write($command);
        
        // 3. Read response until prompt
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
            
            // 1. Filter out Telnet IAC sequences (starts with 0xFF / 255)
            $ord = ord($char);
            if ($ord === 255) {
                // Read next 2 bytes (Command + Option)
                fread($this->connection, 2);
                continue;
            }

            // 2. Handle Backspace (0x08)
            if ($ord === 8) {
                $buffer = substr($buffer, 0, -1);
                continue;
            }

            // 3. Keep printable characters and basic whitespace
            if ($ord === 10 || $ord === 13 || $ord === 9 || ($ord >= 32 && $ord <= 126)) {
                $buffer .= $char;
            }

            // 4. Check for prompts
            $trimmedBuffer = trim($buffer);
            foreach ($prompts as $prompt) {
                if (str_ends_with($trimmedBuffer, $prompt)) {
                    return $buffer;
                }
            }

            // 5. Handle --More-- pagination
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
