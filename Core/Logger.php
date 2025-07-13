<?php
namespace Core;

use Exception;

class Logger
{
    protected string $logFile;

    public function __construct(string $logFile = null)
    {
        $defaultPath = __DIR__ . '/../logs/app.log';
        $logDir = dirname($logFile ?? $defaultPath);

        if (!is_dir($logDir)) {
            if (!mkdir($logDir, 0755, true) && !is_dir($logDir)) {
                throw new Exception("Не удалось создать директорию для логов");
            }
        }

        $this->logFile = $logFile ?? $defaultPath;
    }

    public function error(string $message): void
    {
        $this->writeLog('ERROR', $message);
    }

    public function info(string $message): void
    {
        $this->writeLog('INFO', $message);
    }

    public function warning(string $message): void
    {
        $this->writeLog('WARNING', $message);
    }

    protected function writeLog(string $level, string $message): void
    {
        try {
            $date = date('Y-m-d H:i:s');
            $entry = sprintf("[%s] %s: %s%s", $date, $level, $message, PHP_EOL);
            file_put_contents($this->logFile, $entry, FILE_APPEND | LOCK_EX);
        } catch (Exception $e) {
            echo "Ошибка записи в лог: " . $e->getMessage();
        }
    }
}
