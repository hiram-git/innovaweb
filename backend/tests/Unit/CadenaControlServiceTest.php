<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\CadenaControlService;
use PHPUnit\Framework\TestCase;

/**
 * Tests unitarios para CadenaControlService
 *
 * Verifica que las claves de control generadas cumplan el formato
 * del ERP Clarion: {dias}{hora_7dig}{aleatorio_5dig}{sufijo}
 */
class CadenaControlServiceTest extends TestCase
{
    private CadenaControlService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new CadenaControlService();
    }

    public function test_generar_retorna_string_no_vacio(): void
    {
        $clave = $this->service->generar('01');
        $this->assertNotEmpty($clave);
        $this->assertIsString($clave);
    }

    public function test_clave_termina_con_sufijo(): void
    {
        foreach (['01', '02', 'FAC', 'OT'] as $sufijo) {
            $clave = $this->service->generar($sufijo);
            $this->assertStringEndsWith($sufijo, $clave, "La clave debe terminar con '{$sufijo}'");
        }
    }

    public function test_clave_tiene_longitud_minima(): void
    {
        // días (5-6 dígitos) + hora (7) + aleatorio (5) + sufijo (≥2) = ≥19
        $clave = $this->service->generar('01');
        $this->assertGreaterThanOrEqual(19, strlen($clave));
    }

    public function test_componentes_devuelve_tres_elementos(): void
    {
        $componentes = $this->service->componentes();
        $this->assertCount(3, $componentes);
    }

    public function test_dias_son_positivos_y_mayores_que_epoch(): void
    {
        [$dias] = $this->service->componentes();
        // Desde 1800-12-28 hasta hoy deben ser ≥80000 días (año 2020+)
        $this->assertGreaterThan(80_000, $dias);
    }

    public function test_hora_clarion_tiene_exactamente_7_digitos(): void
    {
        [, $hora] = $this->service->componentes();
        $this->assertMatchesRegularExpression('/^\d{7}$/', $hora);
    }

    public function test_aleatorio_esta_entre_10000_y_99999(): void
    {
        [,, $aleatorio] = $this->service->componentes();
        $this->assertGreaterThanOrEqual(10_000, $aleatorio);
        $this->assertLessThanOrEqual(99_999, $aleatorio);
    }

    public function test_claves_consecutivas_son_diferentes(): void
    {
        $claves = array_map(fn () => $this->service->generar('01'), range(1, 10));
        // Con aleatorio de 5 dígitos la probabilidad de colisión es despreciable
        $unicos = array_unique($claves);
        $this->assertGreaterThan(1, count($unicos), 'Las claves deben ser únicas entre sí');
    }

    public function test_fecha_clarion_coincide_con_dias_en_componentes(): void
    {
        $fechaClarion = $this->service->fechaClarion();
        [$dias] = $this->service->componentes();
        // Pueden diferir en 1 si el día cambia entre llamadas (poco probable)
        $this->assertEqualsWithDelta($fechaClarion, $dias, 1);
    }

    public function test_clave_solo_contiene_digitos_y_sufijo_alfanumerico(): void
    {
        $clave = $this->service->generar('01');
        $this->assertMatchesRegularExpression('/^\d+$/', $clave, 'Con sufijo numérico, la clave entera debe ser numérica');
    }
}
