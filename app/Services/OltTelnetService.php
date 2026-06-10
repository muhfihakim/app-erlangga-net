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
    private $timeout = 30; // Increased timeout

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
            $this->connection = @fsockopen($this->host, $this->port, $errno, $errstr, $this->timeout);
            if (!$this->connection) {
                throw new Exception("Could not connect to OLT {$this->host}:{$this->port} - $errstr ($errno)");
            }

            stream_set_timeout($this->connection, $this->timeout);

            // 1. Handle Handshake / Username prompt
            $this->readUntil(['Username:', 'login:', 'User Name:']);
            $this->write($this->username);

            // 2. Handle Password prompt
            $this->readUntil(['Password:', 'password:']);
            $this->write($this->password);

            // 3. Wait for Command Prompt
            $this->readUntil(['>', '#']);

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
        while (fread($this->connection, 1024) !== "");
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
                // Log the buffer so far to help debugging
                Log::warning("OLT Read Timeout. Buffer content: " . substr($buffer, -200));
                throw new Exception("Telnet Read Timeout for prompts: " . implode(', ', $prompts));
            }

            // Read in chunks for efficiency
            $chunk = fread($this->connection, 1024);
            if ($chunk === false || $chunk === "") {
                usleep(50000); // 50ms wait if no data
                continue;
            }

            // Process characters in chunk
            $processedChunk = '';
            for ($i = 0; $i < strlen($chunk); $i++) {
                $char = $chunk[$i];
                $ord = ord($char);

                // 1. Filter Telnet IAC
                if ($ord === 255) {
                    $i += 2; // Skip command + option
                    continue;
                }

                // 2. Handle Backspace
                if ($ord === 8) {
                    $processedChunk = substr($processedChunk, 0, -1);
                    continue;
                }

                // 3. Basic printable characters
                if ($ord === 10 || $ord === 13 || $ord === 9 || ($ord >= 32 && $ord <= 126)) {
                    $processedChunk .= $char;
                }
            }
            
            $buffer .= $processedChunk;

            // 4. Handle --More-- pagination
            if (str_contains($buffer, "--More--")) {
                $this->write(" "); // Send space to get more
                $buffer = str_replace("--More--", "", $buffer);
                continue; 
            }

            // 5. Check for prompts at the end of buffer
            $tail = substr($buffer, -20);
            foreach ($prompts as $prompt) {
                if (preg_match("/" . preg_quote($prompt, '/') . "\s*$/", $tail)) {
                    return $buffer;
                }
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
