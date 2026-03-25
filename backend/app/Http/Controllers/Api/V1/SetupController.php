<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use PDO;
use PDOException;

class SetupController extends Controller
{
    /**
     * Return whether the initial DB setup is still required.
     * Public endpoint — no authentication needed.
     */
    public function status(): JsonResponse
    {
        return response()->json(['needs_setup' => $this->needsSetup()]);
    }

    /**
     * Test the provided DB credentials and, on success, persist them to .env.
     * Blocked once the app is already configured.
     */
    public function store(Request $request): JsonResponse
    {
        if (! $this->needsSetup()) {
            return response()->json(
                ['message' => 'La aplicación ya está configurada.'],
                403
            );
        }

        $data = $request->validate([
            'servidor' => ['required', 'string', 'max:255'],
            'dbname'   => ['required', 'string', 'max:255'],
            'usuario'  => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'max:255'],
            'timezone' => ['required', 'string', 'max:60'],
        ]);

        // Test connection before writing anything
        try {
            $dsn = "sqlsrv:server={$data['servidor']};database={$data['dbname']};Encrypt=false;TrustServerCertificate=true";
            $pdo = new PDO($dsn, $data['usuario'], $data['password']);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            return response()->json(
                ['message' => 'No se pudo conectar a la base de datos: ' . $e->getMessage()],
                422
            );
        }

        $this->writeEnv([
            'DB_HOST'      => $data['servidor'],
            'DB_DATABASE'  => $data['dbname'],
            'DB_USERNAME'  => $data['usuario'],
            'DB_PASSWORD'  => $data['password'],
            'APP_TIMEZONE' => $data['timezone'],
        ]);

        Artisan::call('config:clear');

        return response()->json(['message' => 'Configuración guardada correctamente.']);
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    /**
     * Setup is needed when DB_HOST or DB_DATABASE are absent/blank in .env.
     */
    private function needsSetup(): bool
    {
        $envPath = base_path('.env');

        if (! file_exists($envPath)) {
            return true;
        }

        $content = file_get_contents($envPath);

        preg_match('/^DB_HOST=(.*)$/m', $content, $hostMatch);
        preg_match('/^DB_DATABASE=(.*)$/m', $content, $dbMatch);

        $host = trim($hostMatch[1] ?? '');
        $db   = trim($dbMatch[1] ?? '');

        return $host === '' || $db === '';
    }

    /**
     * Replace or append key=value pairs in the .env file.
     * Wraps values that contain spaces in double quotes.
     */
    private function writeEnv(array $values): void
    {
        $envPath = base_path('.env');
        $content = file_exists($envPath) ? file_get_contents($envPath) : '';

        foreach ($values as $key => $value) {
            $escaped = $this->escapeEnvValue((string) $value);

            if (preg_match("/^{$key}=.*$/m", $content)) {
                $content = preg_replace("/^{$key}=.*$/m", "{$key}={$escaped}", $content);
            } else {
                $content .= PHP_EOL . "{$key}={$escaped}";
            }
        }

        file_put_contents($envPath, $content);
    }

    /**
     * Quote a value for .env if it contains whitespace or special characters.
     */
    private function escapeEnvValue(string $value): string
    {
        // If value has spaces, #, or $, wrap in double quotes and escape inner quotes
        if (preg_match('/[\s#$"\'\\\\]/', $value)) {
            $escaped = str_replace('"', '\\"', $value);
            return '"' . $escaped . '"';
        }

        return $value;
    }
}
