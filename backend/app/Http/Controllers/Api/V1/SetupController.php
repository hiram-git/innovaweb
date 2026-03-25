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
    // Placeholder values that ship in .env.example — treat them as "not configured"
    private const PLACEHOLDER_HOST = 'NOMBRE_PC\SQLEXPRESS';
    private const PLACEHOLDER_DB   = 'NOMBRE_BASE_DE_DATOS';

    /**
     * Return whether the initial DB setup is still required.
     * Public endpoint — no authentication needed.
     */
    public function status(): JsonResponse
    {
        return response()->json(['needs_setup' => $this->needsSetup()]);
    }

    /**
     * Test the provided DB credentials and, on success, write .env from .env.example
     * with the user-supplied values substituted in.
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

        // Build .env from .env.example, substituting user-supplied values
        $this->writeEnvFromExample([
            'DB_HOST'      => $data['servidor'],
            'DB_DATABASE'  => $data['dbname'],
            'DB_USERNAME'  => $data['usuario'],
            'DB_PASSWORD'  => $data['password'],
            'APP_TIMEZONE' => $data['timezone'],
        ]);

        // Generate APP_KEY if .env.example left it blank
        Artisan::call('key:generate', ['--force' => true]);
        Artisan::call('config:clear');

        return response()->json(['message' => 'Configuración guardada correctamente.']);
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    /**
     * Setup is needed when:
     *  - .env does not exist, OR
     *  - DB_HOST / DB_DATABASE are empty or still hold the .env.example placeholders, OR
     *  - APP_KEY is empty (key:generate hasn't been run yet)
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
        preg_match('/^APP_KEY=(.*)$/m', $content, $keyMatch);

        $host   = trim($hostMatch[1] ?? '');
        $db     = trim($dbMatch[1] ?? '');
        $appKey = trim($keyMatch[1] ?? '');

        if ($host === '' || $host === self::PLACEHOLDER_HOST) {
            return true;
        }
        if ($db === '' || $db === self::PLACEHOLDER_DB) {
            return true;
        }
        if ($appKey === '') {
            return true;
        }

        return false;
    }

    /**
     * Copy .env.example → .env (full content), then replace the given keys.
     * Falls back to the existing .env content if .env.example is missing.
     */
    private function writeEnvFromExample(array $overrides): void
    {
        $examplePath = base_path('.env.example');
        $envPath     = base_path('.env');

        // Start from .env.example so all variables are present
        if (file_exists($examplePath)) {
            $content = file_get_contents($examplePath);
        } elseif (file_exists($envPath)) {
            $content = file_get_contents($envPath);
        } else {
            $content = '';
        }

        foreach ($overrides as $key => $value) {
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
     * Quote a .env value if it contains whitespace or shell-special characters.
     */
    private function escapeEnvValue(string $value): string
    {
        if (preg_match('/[\s#$"\'\\\\]/', $value)) {
            return '"' . str_replace('"', '\\"', $value) . '"';
        }

        return $value;
    }
}
